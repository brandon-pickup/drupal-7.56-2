<?php

/**
 * @file
 * Commerce Utilities API.
 */

/**
 * Allow alter the data used to create payment authorisation.
 *
 * @param \Commerce\Utils\Payment\Authorisation\RequestBase $payment
 *   Payment authorisation request that will be sent to gateway.
 * @param \stdClass $order
 *   Commerce order.
 * @param array $payment_method
 *   Commerce payment method.
 *
 * @see commerce_utils_redirect_form()
 * @see \Commerce\Utils\Payment\Form\RedirectFormBase::form()
 */
function hook_PAYMENT_METHOD_payment_authorisation_request_alter(\Commerce\Utils\Payment\Authorisation\RequestBase $payment, \stdClass $order, array $payment_method) {

}

/**
 * Allow alter the data used to process payment authorisation.
 *
 * @param \Commerce\Utils\Payment\Authorisation\ResponseBase $payment
 *   Payment authorisation response that has been received from gateway.
 * @param \stdClass $order
 *   Commerce order.
 * @param array $payment_method
 *   Commerce payment method.
 *
 * @see commerce_utils_redirect_form_validate()
 * @see \Commerce\Utils\Payment\Form\RedirectFormBase::response()
 */
function hook_PAYMENT_METHOD_payment_authorisation_response_alter(\Commerce\Utils\Payment\Authorisation\ResponseBase $payment, \stdClass $order, array $payment_method) {

}

/**
 * Handle gateway notifications.
 *
 * @param string $event
 *   One of event codes in uppercase.
 * @param \stdClass $order
 *   Commerce order.
 * @param \stdClass $data
 *   Received data (from $_POST superglobal).
 *
 * @see commerce_utils_notification()
 * @see \Commerce\Utils\NotificationControllerBase
 */
function hook_PAYMENT_METHOD_notification($event, \stdClass $order, \stdClass $data) {

}

/**
 * Modify a rule for capturing the payments.
 *
 * @param \RulesReactionRule $rule
 *   A rule to capture payment of particular payment method.
 * @param array $payment_method
 *   Commerce payment method.
 *
 * @see commerce_utils_default_rules_configuration()
 */
function hook_commerce_payment_capture_rule_alter(\RulesReactionRule $rule, array $payment_method) {
  if ('commerce_PAYMENT_METHOD' === $payment_method['method_id']) {
    $rule
      ->condition('data_set');
  }
}

/**
 * React on a state after capture request.
 *
 * NOTE: this hook will be invoked only for those payment methods which has a
 * capture processor.
 *
 * @param bool $status
 *   Indicates whether capture has been performed/rejected or NULL if it's
 *   not needed for two reasons: not needed at all or has been already done.
 * @param \stdClass $order
 *   Commerce order.
 * @param array $payment_method
 *   Commerce payment method.
 *
 * @see commerce_utils_commerce_order_presave()
 */
function hook_commerce_payment_capture_occurred($status, \stdClass $order, array $payment_method) {

}
