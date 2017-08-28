<?php

namespace Drupal\commerce_paygate_payhost\Payment\Transaction;

use Commerce\Utils\Transaction;
use Drupal\commerce_paygate_payhost\Payment\PaymentStatusInterface;

/**
 * Payment transaction.
 */
class Payment extends Transaction {

  /**
   * {@inheritdoc}
   */
  public function authorise($remote_id) {
    $this->setRemoteId($remote_id);
    $this->setStatus(COMMERCE_PAYMENT_STATUS_AUTHORISED);
    $this->setRemoteStatus(PaymentStatusInterface::AUTHORISED);
    $this->setMessage('Payment has been successfully authorised.');
  }

  /**
   * {@inheritdoc}
   */
  public function isAuthorised() {
    return
      $this->getStatus() === COMMERCE_PAYMENT_STATUS_AUTHORISED &&
      $this->getRemoteStatus() === PaymentStatusInterface::AUTHORISED;
  }

  /**
   * {@inheritdoc}
   */
  public function fail($remote_id) {
    $this->setRemoteId($remote_id);
    $this->setStatus(COMMERCE_PAYMENT_STATUS_FAILURE);
    $this->setRemoteStatus(PaymentStatusInterface::DECLINED);
    $this->setMessage('Payment failed.');
  }

  /**
   * {@inheritdoc}
   */
  public function isFailed() {
    return
      $this->getStatus() === COMMERCE_PAYMENT_STATUS_FAILURE &&
      $this->getRemoteStatus() === PaymentStatusInterface::DECLINED;
  }

  /**
   * {@inheritdoc}
   */
  public function finalize() {
    $this->setStatus(COMMERCE_PAYMENT_STATUS_SUCCESS);
    $this->setRemoteStatus(PaymentStatusInterface::APPROVED);
    $this->setMessage('Payment has been captured and completed.');
  }

  /**
   * {@inheritdoc}
   */
  public function isFinalized() {
    return
      $this->getStatus() === COMMERCE_PAYMENT_STATUS_SUCCESS &&
      $this->getRemoteStatus() === PaymentStatusInterface::APPROVED;
  }

}
