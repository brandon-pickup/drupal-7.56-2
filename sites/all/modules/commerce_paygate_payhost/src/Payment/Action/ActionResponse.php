<?php

namespace Drupal\commerce_paygate_payhost\Payment\Action;

use Drupal\commerce_paygate_payhost\Payment\PaymentStatusInterface;

/**
 * Base implementation of response wrapper.
 */
abstract class ActionResponse {

  /**
   * Response representation.
   *
   * @var array
   */
  protected $data = [];

  /**
   * ActionResponse constructor.
   *
   * @param \stdClass $data
   *   Query response.
   */
  public function __construct(\stdClass $data) {
    $this->data = $data;
  }

  /**
   * {@inheritdoc}
   */
  public function __sleep() {
    return ['data'];
  }

  /**
   * Returns status whether transaction was successful.
   *
   * @return bool
   *   A state of check.
   */
  public function isSuccessful() {
    return in_array($this->getPaymentStatus(), [
      PaymentStatusInterface::APPROVED,
      PaymentStatusInterface::AUTHORISED,
      PaymentStatusInterface::RECEIVED,
    ]);
  }

  /**
   * Action result code.
   *
   * @return int
   *   Result code.
   */
  public function getResultCode() {
    return (int) $this->data->ResultCode;
  }

  /**
   * Returns message with which query was ended.
   *
   * @return string
   *   Query status message.
   */
  public function getMessage() {
    return $this->data->TransactionStatusDescription;
  }

  /**
   * Returns the data as an array.
   *
   * @return array
   *   Query response.
   */
  public function toArray() {
    return (array) $this->data;
  }

  /**
   * Returns payment status.
   *
   * @return string
   *   One of payment statuses, described in "PaymentStatusInterface".
   *
   * @see \Drupal\commerce_paygate_payhost\Payment\PaymentStatusInterface
   */
  public function getPaymentStatus() {
    return $this->data->TransactionStatusCode;
  }

  /**
   * Returns unique ID of payment on gateway.
   *
   * @return string
   *   Unique ID of payment.
   */
  public function getPaymentReference() {
    return $this->data->TransactionId;
  }

  /**
   * Returns oder number.
   *
   * @return string
   *   Number of commerce order.
   */
  public function getMerchantReference() {
    return $this->data->Reference;
  }

}
