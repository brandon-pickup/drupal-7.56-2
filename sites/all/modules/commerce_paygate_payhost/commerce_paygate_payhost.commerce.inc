<?php

/**
 * @file
 * Commerce integration.
 */

use Commerce\Utils\PaymentPlugin;
use Drupal\commerce_paygate_payhost\Entity\PaymentWatcher;
use Drupal\commerce_paygate_payhost\Payment\NotificationController;
use Drupal\commerce_paygate_payhost\Payment\Form\SettingsForm;
use Drupal\commerce_paygate_payhost\Payment\Form\SubmitForm;
use Drupal\commerce_paygate_payhost\Payment\Form\RedirectForm;
use Drupal\commerce_paygate_payhost\Payment\Capture\CaptureProcessor;
use Drupal\commerce_paygate_payhost\Payment\Authorisation\Request;
use Drupal\commerce_paygate_payhost\Payment\Authorisation\Response;
use Drupal\commerce_paygate_payhost\Payment\Transaction\Payment;
use Drupal\commerce_paygate_payhost\Payment\Transaction\Refund;

/**
 * Implements hook_commerce_payment_method_info().
 */
function commerce_paygate_payhost_commerce_payment_method_info() {
  $info = [];

  $info[COMMERCE_PAYGATE_PAYHOST_PAYMENT_METHOD] = [
    'title' => 'PayGate PayHost',
    'description' => t('Redirect users to submit payments through South African online payment gateway.'),
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
      ->setNotificationsController(NotificationController::class)
      ->setPaymentWatcherEntityClass(PaymentWatcher::class)
      ->setPaymentCaptureProcessor(CaptureProcessor::class),
  ];

  return $info;
}
