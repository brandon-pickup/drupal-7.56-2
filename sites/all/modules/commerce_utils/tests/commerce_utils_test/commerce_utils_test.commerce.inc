<?php

/**
 * @file
 * Commerce integration.
 */

use Commerce\Utils\PaymentPlugin;
use Drupal\commerce_utils_test\Payment\Form\SubmitForm;
use Drupal\commerce_utils_test\Payment\Form\SettingsForm;
use Drupal\commerce_utils_test\Payment\Form\RedirectForm;
use Drupal\commerce_utils_test\Payment\Capture\CaptureProcessor;
use Drupal\commerce_utils_test\Payment\Transaction\Refund;
use Drupal\commerce_utils_test\Payment\Transaction\Payment;
use Drupal\commerce_utils_test\Payment\Authorisation\Request;
use Drupal\commerce_utils_test\Payment\Authorisation\Response;

/**
 * Implements hook_commerce_payment_method_info().
 */
function commerce_utils_test_commerce_payment_method_info() {
  $info = [];

  $info[COMMERCE_UTILS_TEST_PAYMENT_METHOD_WITH_PLUGIN_WITHOUT_CAPTURE] = [
    'title' => t('Payment method with plugin (without capture processor)'),
    'active' => TRUE,
    'offsite' => TRUE,
    'terminal' => FALSE,
    'offsite_autoredirect' => TRUE,
    'payment_plugin' => (new PaymentPlugin())
      ->setSettingsFormClass(SettingsForm::class)
      ->setSubmitFormClass(SubmitForm::class)
      ->setRedirectFormClass(RedirectForm::class)
      ->setAuthorisationRequestClass(Request::class)
      ->setAuthorisationResponseClass(Response::class)
      ->setPaymentTransactionClass(Payment::class)
      ->setRefundTransactionClass(Refund::class),
  ];

  $info[COMMERCE_UTILS_TEST_PAYMENT_METHOD_WITH_PLUGIN] = [
    'title' => t('Payment method with plugin'),
    'active' => TRUE,
    'offsite' => TRUE,
    'terminal' => FALSE,
    'offsite_autoredirect' => TRUE,
    'payment_plugin' => (new PaymentPlugin())
      ->setSettingsFormClass(SettingsForm::class)
      ->setSubmitFormClass(SubmitForm::class)
      ->setRedirectFormClass(RedirectForm::class)
      ->setAuthorisationRequestClass(Request::class)
      ->setAuthorisationResponseClass(Response::class)
      ->setPaymentTransactionClass(Payment::class)
      ->setRefundTransactionClass(Refund::class)
      ->setPaymentCaptureProcessor(CaptureProcessor::class),
  ];

  $info[COMMERCE_UTILS_TEST_PAYMENT_METHOD_WITHOUT_PLUGIN] = [
    'title' => t('Payment method without plugin'),
    'active' => TRUE,
    'offsite' => TRUE,
    'terminal' => FALSE,
    'offsite_autoredirect' => TRUE,
  ];

  return $info;
}

/**
 * Implements hook_payment_with_plugin_payment_authorisation_request_alter().
 */
function commerce_utils_test_payment_with_plugin_payment_authorisation_request_alter(Request $payment, \stdClass $order, array $payment_method) {
  $order->data['hook_payment_with_plugin_payment_authorisation_request_alter'] = TRUE;
  commerce_order_save($order);
}

/**
 * Implements hook_payment_with_plugin_payment_authorisation_response_alter().
 */
function commerce_utils_test_payment_with_plugin_payment_authorisation_response_alter(Response $payment, \stdClass $order, array $payment_method) {
  $order->data['hook_payment_with_plugin_payment_authorisation_response_alter'] = TRUE;
  commerce_order_save($order);
}

/**
 * Implements hook_payment_with_plugin_without_capture_payment_authorisation_request_alter().
 */
function commerce_utils_test_payment_with_plugin_without_capture_payment_authorisation_request_alter(Request $payment, \stdClass $order, array $payment_method) {
  $order->data['payment_with_plugin_without_capture_payment_authorisation_request_alter'] = TRUE;
  commerce_order_save($order);
}

/**
 * Implements hook_payment_with_plugin_without_capture_payment_authorisation_response_alter().
 */
function commerce_utils_test_payment_with_plugin_without_capture_payment_authorisation_response_alter(Response $payment, \stdClass $order, array $payment_method) {
  $order->data['hook_payment_with_plugin_without_capture_payment_authorisation_response_alter'] = TRUE;
  commerce_order_save($order);
}
