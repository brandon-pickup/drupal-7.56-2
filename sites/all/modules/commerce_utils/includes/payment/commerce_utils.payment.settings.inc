<?php

/**
 * @file
 * Payment settings.
 */

/**
 * Implements PAYMENT_METHOD_BASE_settings_form().
 *
 * {@inheritdoc}
 *
 * @see commerce_utils_commerce_payment_method_info_alter()
 * @see \RulesDataUIPaymentSettings::inputForm()
 * @see \RulesPluginUI::getParameterForm()
 * @see commerce_utils_form_rules_ui_edit_element_alter()
 *
 * @internal
 */
function commerce_utils_settings_form(array $settings) {
  // Must be empty in favor of form alter.
}
