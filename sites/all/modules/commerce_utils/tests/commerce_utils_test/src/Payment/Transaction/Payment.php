<?php

namespace Drupal\commerce_utils_test\Payment\Transaction;

use Commerce\Utils\Transaction;

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
    $this->setRemoteStatus('authorised');
    $this->setMessage('Payment has been successfully authorised.');
  }

  /**
   * {@inheritdoc}
   */
  public function isAuthorised() {
    return
      $this->getStatus() === COMMERCE_PAYMENT_STATUS_AUTHORISED &&
      $this->getRemoteStatus() === 'authorised';
  }

  /**
   * {@inheritdoc}
   */
  public function fail($remote_id) {
    $this->setRemoteId($remote_id);
    $this->setStatus(COMMERCE_PAYMENT_STATUS_FAILURE);
    $this->setRemoteStatus('failed');
    $this->setMessage('Payment failed.');
  }

  /**
   * {@inheritdoc}
   */
  public function isFailed() {
    return
      $this->getStatus() === COMMERCE_PAYMENT_STATUS_FAILURE &&
      $this->getRemoteStatus() === 'failed';
  }

  /**
   * {@inheritdoc}
   */
  public function finalize() {
    $this->setStatus(COMMERCE_PAYMENT_STATUS_SUCCESS);
    $this->setRemoteStatus('approved');
    $this->setMessage('Payment has been captured and completed.');
  }

  /**
   * {@inheritdoc}
   */
  public function isFinalized() {
    return
      $this->getStatus() === COMMERCE_PAYMENT_STATUS_SUCCESS &&
      $this->getRemoteStatus() === 'approved';
  }

}
