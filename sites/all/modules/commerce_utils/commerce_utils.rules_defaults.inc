<?php

/**
 * @file
 * Rules integration.
 */

/**
 * Implements hook_default_rules_configuration().
 */
function commerce_utils_default_rules_configuration() {
  $configs = [];

  foreach (commerce_payment_methods() as $payment_method => $payment_info) {
    try {
      // Define an event only for payment methods needing a capture.
      if (NULL !== commerce_utils_get_payment_plugin($payment_info)->getPaymentCaptureProcessor()) {
        $rule = rules_reaction_rule();
        $rule->active = TRUE;

        $rule->tags = [
          t('Commerce Payment'),
        ];

        $rule->label = t('Capture authorised payment (@label)', [
          '@label' => $payment_info['title'],
        ]);

        $rule
          /* @see commerce_utils_rules_event_info() */
          ->event('commerce_utils_order_completed')
          /* @see commerce_payment_rules_condition_info() */
          ->condition('commerce_payment_selected_payment_method', [
            'commerce_order:select' => 'commerce-order',
            'method_id' => $payment_method,
          ])
          /* @see commerce_utils_rules_action_info() */
          ->action('commerce_utils_payment_capture', [
            'payment_capture_processor:select' => 'payment-capture-processor',
          ]);

        drupal_alter('commerce_payment_capture_rule', $rule, $payment_info);

        $configs["commerce_payment_capture__{$payment_method}"] = $rule;
      }
    }
    catch (\Exception $e) {
      // An exception will be thrown for payment methods not having
      // a payment plugin declared.
    }
  }

  return $configs;
}
