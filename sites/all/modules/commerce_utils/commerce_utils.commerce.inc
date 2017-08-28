<?php

/**
 * @file
 * Commerce integration.
 */

use Commerce\Utils\PaymentPlugin;

/**
 * Implements hook_commerce_payment_method_info_alter().
 *
 * @internal
 */
function commerce_utils_commerce_payment_method_info_alter(array &$payment_methods) {
  foreach ($payment_methods as $payment_method => $payment_info) {
    // Payment plugin is not defined: skip further checks.
    if (empty($payment_info['payment_plugin'])) {
      // Remove any value, treated as "FALSE".
      unset($payment_methods[$payment_method]['payment_plugin']);
      continue;
    }

    $plugin = $payment_info['payment_plugin'];

    // Payment plugin is incorrect: remove it from payment method.
    if (!($plugin instanceof PaymentPlugin)) {
      // Payment plugin cannot be used.
      unset($payment_methods[$payment_method]['payment_plugin']);

      watchdog('commerce_utils', '"@payment_method" payment method, provided by "@module" module, declares a payment plugin which is not an instance of "@class".', [
        '@payment_method' => $payment_method,
        '@module' => $payment_info['module'],
        '@class' => PaymentPlugin::class,
      ], WATCHDOG_WARNING);

      continue;
    }

    try {
      $plugin->compile();
    }
    catch (\Exception $e) {
      // Payment method cannot be used.
      unset($payment_methods[$payment_method]);

      watchdog('commerce_utils', 'Payment plugin of "@payment_method", provided by "@module" module, cannot be compiled due to an error: @error', [
        '@payment_method' => $payment_method,
        '@module' => $payment_info['module'],
        '@error' => $e->getMessage(),
      ], WATCHDOG_ERROR);

      continue;
    }

    $payment_methods[$payment_method]['file'] = 'includes/commerce_utils.payment.inc';
    $payment_methods[$payment_method]['module'] = 'commerce_utils';
    // Store original module name as "provider".
    $payment_methods[$payment_method]['provider'] = $payment_info['module'];

    foreach ([
      // Settings form.
      'settings_form',
      // Submit form.
      'submit_form',
      'submit_form_validate',
      'submit_form_submit',
      // Redirect form.
      'redirect_form',
      'redirect_form_back',
      'redirect_form_validate',
      'redirect_form_submit',
    ] as $type) {
      $payment_methods[$payment_method]['callbacks'][$type] = $payment_methods[$payment_method]['module'] . '_' . $type;
    }
  }
}

/**
 * Implements hook_commerce_payment_transaction_status_info().
 *
 * @internal
 */
function commerce_utils_commerce_payment_transaction_status_info() {
  $module_path = drupal_get_path('module', 'commerce_utils');
  $info = [];

  foreach ([
    COMMERCE_PAYMENT_STATUS_AUTHORISED => [
      'title' => t('Authorised'),
      'total' => TRUE,
    ],
  ] as $status => $data) {
    $info[$status] = $data + [
      'status' => $status,
      'icon' => "$module_path/icons/$status.png",
    ];
  }

  return $info;
}

/**
 * Compute whether Rules event is available for invocation for given order.
 *
 * @param \stdClass $order
 *   Commerce order.
 * @param string $rules_event
 *   An event to check.
 *
 * @return array
 *   0 - name of Rules event (the same as an argument) or an empty string
 *       if an event is not available for invocation.
 *   1 - a marker which must be stored inside of the order's data after
 *       successful event invocation.
 *   2 - an instance of payment method used to pay for an order.
 *
 * @internal
 */
function commerce_utils_is_event_available(\stdClass $order, $rules_event) {
  $invoked_marker = $rules_event . '_invoked';

  if (empty($order->data[$invoked_marker]) && !empty($order->data['payment_method'])) {
    static $payment_methods = [];

    if (!isset($payment_methods[$order->data['payment_method']])) {
      // The "payment_method" will contain an instance ID of payment method.
      /* @see commerce_payment_pane_checkout_form_submit() */
      $payment_methods[$order->data['payment_method']] = commerce_payment_method_instance_load($order->data['payment_method']);
    }

    if (FALSE === $payment_methods[$order->data['payment_method']]) {
      watchdog('commerce_utils', 'Unable to invoke the "@rules_event" Rules event because an instance of payment method cannot be loaded using "@instance_id" as instance ID.', [
        '@rules_event' => $rules_event,
        '@instance_id' => $order->data['payment_method'],
      ], WATCHDOG_WARNING);
    }
    else {
      // DO NOT NEVER CHANGE the ordering of items in this array! It formed in
      // a way to assume that every next event must be available for invocation
      // only in case of all previous were invoked.
      $states = [
        'commerce_payment_order_paid_in_full',
        'commerce_utils_order_paid_in_full',
        'commerce_utils_order_completed',
      ];

      $key = array_search($rules_event, $states);

      if (FALSE !== $key) {
        $state = 1;

        // All events before given one MUST be invoked!
        for ($i = 0; $i < $key; $i++) {
          $state &= !empty($order->data[$states[$i] . '_invoked']);
        }

        if (1 === $state) {
          return [
            $rules_event,
            $invoked_marker,
            $payment_methods[$order->data['payment_method']],
          ];
        }
      }
    }
  }

  return ['', '', []];
}

/**
 * Implements hook_commerce_payment_transaction_insert().
 *
 * This implementation is essentially the same as referenced below hook but
 * it directed to handle "authorised" status of a transaction. We may not set
 * it to "success" at once in order to provide the next stage - "capture".
 *
 * We MUST NOT have this implementation in single "presave" hook instead
 * of "insert" and "update" because the "presave" executes before storing
 * an entity in the database and, if some error will be occurred, we will
 * not know whether an entity was created/updated successfully.
 *
 * @see commerce_payment_commerce_payment_transaction_insert()
 *
 * @internal
 */
function commerce_utils_commerce_payment_transaction_insert(\stdClass $transaction) {
  $order = commerce_order_load($transaction->order_id);
  /* @see commerce_payment_rules_event_info() */
  list($rules_event, $invoked_marker, $payment_method) = commerce_utils_is_event_available($order, 'commerce_payment_order_paid_in_full');

  if ('' !== $rules_event) {
    // This function will throw an exception for payment methods without
    // payment plugin.
    try {
      $authorised = commerce_utils_get_transaction_instance($payment_method, 'payment', $order)->isAuthorised();
    }
    catch (\Exception $e) {
      $authorised = COMMERCE_PAYMENT_STATUS_AUTHORISED === $transaction->status && !empty($transaction->remote_id);
    }

    if ($authorised) {
      // Check the order balance and invoke the event.
      $balance = commerce_payment_order_balance($order);

      if (FALSE !== $balance && $balance['amount'] <= 0) {
        // Invoke the event including a hook of the same name.
        rules_invoke_all($rules_event, $order, $transaction);
        // Update the order's data to indicate an event just happened.
        $order->data[$invoked_marker] = TRUE;
        // Save the updated order.
        commerce_order_save($order);
      }
    }
  }
}

/**
 * Implements hook_commerce_payment_transaction_update().
 *
 * @see commerce_payment_commerce_payment_transaction_update()
 *
 * @internal
 */
function commerce_utils_commerce_payment_transaction_update(\stdClass $transaction) {
  commerce_utils_commerce_payment_transaction_insert($transaction);
}

/**
 * Implements hook_commerce_order_insert().
 *
 * @see commerce_utils_commerce_payment_transaction_insert()
 *
 * @internal
 */
function commerce_utils_commerce_order_insert(\stdClass $order) {
  /* @see commerce_utils_rules_event_info() */
  list($rules_event, $invoked_marker, $payment_method) = commerce_utils_is_event_available($order, 'commerce_utils_order_paid_in_full');

  // Okay, we successfully pass a first stage.
  if ('' !== $rules_event) {
    rules_invoke_event($rules_event, $order, $payment_method);
    // Update the order's data to indicate an event just happened.
    $order->data[$invoked_marker] = TRUE;
    // Save the updated order.
    commerce_order_save($order);
  }
}

/**
 * Implements hook_commerce_order_update().
 *
 * @internal
 */
function commerce_utils_commerce_order_update(\stdClass $order) {
  commerce_utils_commerce_order_insert($order);
}

/**
 * Implements hook_commerce_order_presave().
 *
 * @internal
 */
function commerce_utils_commerce_order_presave(\stdClass $order) {
  // A logic of this callback operates only on order status change.
  // We are interested only in a stage when the order becomes "completed".
  if (isset($order->original) && $order->status !== $order->original->status && 'completed' === $order->status) {
    /* @see commerce_utils_rules_event_info() */
    list($rules_event, $invoked_marker, $payment_method) = commerce_utils_is_event_available($order, 'commerce_utils_order_completed');

    // Okay, we successfully pass a first and second stages.
    if ('' !== $rules_event) {
      // Capture processor exists not for every payment method, so assume
      // initially that it doesn't.
      $capture_processor = NULL;
      // Store new status to be able to restore it back after successful
      // completion.
      $new_order_status = $order->status;
      // Disallow status from being changed until we get the confirmation
      // of successful completion.
      $order->status = $order->original->status;

      // Not every payment method has a payment plugin. An exception will be
      // thrown if it doesn't exist and this must not break further logic.
      try {
        $capture_processor = commerce_utils_get_payment_plugin($payment_method)
          ->getPaymentCaptureProcessor();

        // Instantiate a capture processor in a case it exists.
        if (NULL !== $capture_processor) {
          $capture_processor = new $capture_processor($order, $payment_method);
        }
      }
      catch (\Exception $e) {
      }

      rules_invoke_event($rules_event, $order, $payment_method, $capture_processor);

      // A capture processor doesn't exist and an event was successfully
      // executed. Who are we to not allow status change?
      if (NULL === $capture_processor) {
        $transactions = commerce_payment_transaction_load_multiple(FALSE, [
          'payment_method' => $payment_method['method_id'],
          'instance_id' => $payment_method['instance_id'],
          'order_id' => $order->order_id,
          'status' => COMMERCE_PAYMENT_STATUS_SUCCESS,
        ]);

        if (empty($transactions)) {
          drupal_set_message(t('An order cannot be marked as completed since it does not have a successful payment.'), 'warning');
        }
        else {
          $order->status = $new_order_status;
          // Mark an event as invoked to not allow action repeat.
          $order->data[$invoked_marker] = TRUE;
        }
      }
      // It might be the case when rules conditions prevented a capture.
      /* @see commerce_utils_payment_capture() */
      elseif ($capture_processor->isPerformed()) {
        // Now event invocation status directly determined by a capture status.
        $order->data[$invoked_marker] = $capture_processor->isCaptured();

        // Capture status MUST be determined! "TRUE" or "FALSE". Nothing else!
        if (NULL === $order->data[$invoked_marker]) {
          throw new \RuntimeException(format_string('Payment method "@payment_method" performed a capture but did not set a status whether an action has been successful.', [
            '@payment_method' => $payment_method['method_id'],
          ]));
        }

        if ($order->data[$invoked_marker]) {
          $message_type = 'status';

          if ($capture_processor->isStatusChangeDelayed()) {
            $message_type = 'warning';
          }
          // Captured successfully and status change is not delayed.
          else {
            $order->status = $new_order_status;
          }
        }
        else {
          $message_type = 'error';
        }

        drupal_set_message($capture_processor->getMessage(), $message_type);
        module_invoke_all('commerce_payment_capture_occurred', $order->data[$invoked_marker], $order, $payment_method);
      }
      else {
        drupal_set_message(t('Payment method "@payment_method" has a capture processor but capture was not performed. Due to this, an order cannot be completed.', [
          '@payment_method' => $payment_method['title'],
        ]), 'warning');
      }
    }
  }
}
