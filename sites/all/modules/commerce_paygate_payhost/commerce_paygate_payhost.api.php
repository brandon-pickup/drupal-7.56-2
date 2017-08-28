<?php

/**
 * @file
 * Commerce PayGate PayHost API.
 */

/**
 * {@inheritdoc}
 *
 * @see hook_PAYMENT_METHOD_payment_authorisation_request_alter()
 */
function hook_commerce_paygate_payhost_payment_authorisation_request_alter(\Drupal\commerce_paygate_payhost\Payment\Authorisation\Request $payment, \stdClass $order, array $payment_method) {
  $payment->setShopperLocale('ua');
}

/**
 * {@inheritdoc}
 *
 * @see hook_PAYMENT_METHOD_payment_authorisation_response_alter()
 */
function hook_commerce_paygate_payhost_payment_authorisation_response_alter(\Drupal\commerce_paygate_payhost\Payment\Authorisation\Response $payment, \stdClass $order, array $payment_method) {
  $transaction = $payment->getTransaction();
  $transaction->setMessage('Custom message');
}

/**
 * React on response of API query.
 *
 * @see \Commerce\Utils\Payment\Action\ActionBase::request()
 * @see \Drupal\commerce_paygate_payhost\Payment\Action\Query
 */
function hook_commerce_paygate_payhost_query_received(\Drupal\commerce_paygate_payhost\Payment\Transaction\Payment $transaction, \stdClass $order, \Drupal\commerce_paygate_payhost\Payment\Action\QueryResponse $response) {

}

/**
 * React on response of API query.
 *
 * @see \Commerce\Utils\Payment\Action\ActionBase::request()
 * @see \Drupal\commerce_paygate_payhost\Payment\Action\Query
 */
function hook_commerce_paygate_payhost_query_rejected(\Drupal\commerce_paygate_payhost\Payment\Transaction\Payment $transaction, \stdClass $order, \Drupal\commerce_paygate_payhost\Payment\Action\QueryResponse $response) {

}

/**
 * React on response of capturing.
 *
 * @see \Commerce\Utils\Payment\Action\ActionBase::request()
 * @see \Drupal\commerce_paygate_payhost\Payment\Action\Capture
 */
function hook_commerce_paygate_payhost_capture_received(\Drupal\commerce_paygate_payhost\Payment\Transaction\Payment $transaction, \stdClass $order, \Drupal\commerce_paygate_payhost\Payment\Action\CaptureResponse $response) {

}

/**
 * React on response of capturing.
 *
 * @see \Commerce\Utils\Payment\Action\ActionBase::request()
 * @see \Drupal\commerce_paygate_payhost\Payment\Action\Capture
 */
function hook_commerce_paygate_payhost_capture_rejected(\Drupal\commerce_paygate_payhost\Payment\Transaction\Payment $transaction, \stdClass $order, \Drupal\commerce_paygate_payhost\Payment\Action\CaptureResponse $response) {

}

/**
 * {@inheritdoc}
 *
 * @see \Drupal\commerce_paygate_payhost\Payment\NotificationController::getEvent()
 * @see hook_PAYMENT_METHOD_notification()
 */
function hook_commerce_paygate_payhost_notification($event, \stdClass $order, \Drupal\commerce_paygate_payhost\Payment\Notification $notification) {

}
