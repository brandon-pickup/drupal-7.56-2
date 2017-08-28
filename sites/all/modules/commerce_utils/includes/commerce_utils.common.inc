<?php

/**
 * @file
 * Auxiliary functionality.
 */

/**
 * Extracts payment plugin from payment method definition and compiles it.
 *
 * @param array $payment_method
 *   Definition of payment method.
 *
 * @return \Commerce\Utils\PaymentPlugin
 *   An instance of payment plugin.
 */
function commerce_utils_get_payment_plugin(array $payment_method) {
  if (empty($payment_method['payment_plugin'])) {
    throw new \InvalidArgumentException(t('Payment method "@method_id" does not have payment plugin.', [
      '@method_id' => $payment_method['method_id'],
    ]));
  }

  return $payment_method['payment_plugin']->compile();
}

/**
 * Sets settings for payment method.
 *
 * Use this function with caution, because accidental loss of some settings
 * could make payment flow mad.
 *
 * @param string $method_id
 *   An ID of payment method.
 * @param array $settings
 *   Settings for payment method.
 */
function commerce_utils_set_payment_settings($method_id, array $settings) {
  /* @var \RulesReactionRule $rule_config */
  $rule_config = rules_config_load("commerce_payment_{$method_id}");

  if (FALSE !== $rule_config) {
    /* @var \RulesAction $action */
    foreach ($rule_config->actions() as $action) {
      if (
        !empty($action->settings['payment_method']) &&
        "commerce_payment_enable_{$method_id}" === $action->getElementName()
      ) {
        // During initialization of action the "payment_method" key will
        // contain the method ID.
        if (!is_array($action->settings['payment_method'])) {
          $action->settings['payment_method'] = [];
        }

        // Ensure that we have an array in "settings" key.
        $action->settings['payment_method'] += ['settings' => []];

        // Forcibly set the method ID.
        $action->settings['payment_method']['method_id'] = $method_id;
        // Override necessary options.
        $action->settings['payment_method']['settings'] = array_merge($action->settings['payment_method']['settings'], $settings);
      }
    }

    $rule_config->save();
  }
}
