<?php

/**
 * @file
 * Rules integration.
 */

use Commerce\Utils\Payment\Capture\CaptureProcessorBase;

/**
 * Implements hook_rules_data_info_alter().
 *
 * @internal
 */
function commerce_utils_rules_data_info_alter(array &$data) {
  // Make sure this type has not been defined somewhere else.
  if (empty($data['payment_method'])) {
    $data['payment_method'] = [
      'label' => t('payment method'),
      'group' => t('Commerce Payment'),
    ];
  }

  $data['payment_capture_processor'] = [
    'label' => t('payment capture processor'),
    'group' => t('Commerce Payment'),
  ];
}

/**
 * Implements hook_rules_event_info().
 *
 * @internal
 */
function commerce_utils_rules_event_info() {
  $events = [];
  $variables = [
    'commerce_order' => [
      'type' => 'commerce_order',
      'label' => t('Commerce order'),
      'description' => t('An order whose status is changing to "completed".'),
    ],
    'payment_method' => [
      'type' => 'payment_method',
      'label' => t('Payment method'),
      'description' => t('An instance of payment method used to pay for an order.'),
    ],
  ];

  // An order has been successfully created/updated in the database and has
  // a transaction with "authorised" or "success" status. This event can occur
  // only after the "commerce_payment_order_paid_in_full". No other way.
  /* @see commerce_utils_commerce_order_update() */
  $events['commerce_utils_order_paid_in_full'] = [
    'label' => t('When the order is paid in full'),
    'group' => t('Commerce Payment'),
    'access callback' => 'commerce_order_rules_access',
    'variables' => $variables,
  ];

  // An order status in changing to "completed". This event can occur only
  // after the "commerce_utils_order_paid_in_full". No other way.
  /* @see commerce_utils_commerce_order_presave() */
  $events['commerce_utils_order_completed'] = [
    'label' => t('When the order is paid in full and its status is changing to "completed"'),
    'group' => t('Commerce Payment'),
    'access callback' => 'commerce_order_rules_access',
    'variables' => $variables + [
      'payment_capture_processor' => [
        'type' => 'payment_capture_processor',
        'label' => t('Payment capture processor'),
        // Not every payment method has a capture processor. In this case,
        // we must not break everything.
        /* @see \RulesPlugin::setUpState() */
        'optional' => TRUE,
        'description' => t('A capture processor (if a payment method has it declared)'),
      ],
    ],
  ];

  return $events;
}

/**
 * Implements hook_rules_action_info().
 *
 * @internal
 */
function commerce_utils_rules_action_info() {
  $actions = [];

  /* @see commerce_utils_payment_capture() */
  $actions['commerce_utils_payment_capture'] = [
    'label' => t('Capture authorised payment'),
    'group' => t('Commerce Payment'),
    'parameter' => [
      'payment_capture_processor' => [
        'type' => 'payment_capture_processor',
        'label' => t('Payment capture processor'),
        'optional' => TRUE,
        'allow null' => TRUE,
      ],
    ],
  ];

  return $actions;
}

/**
 * Perform capture action using the processor of particular payment method.
 *
 * IMPORTANT! Do not save the order object inside of this action since it's
 * added to an event, which runs in "hook_commerce_order_presave()". If you
 * need to modify order's object - feel free.
 *
 * @param \Commerce\Utils\Payment\Capture\CaptureProcessorBase $payment_capture_processor
 *   A capture processor for the payment method.
 *
 * @see commerce_utils_commerce_order_presave()
 *
 * @internal
 */
function commerce_utils_payment_capture(CaptureProcessorBase $payment_capture_processor = NULL) {
  NULL !== $payment_capture_processor && $payment_capture_processor->capture();
}
