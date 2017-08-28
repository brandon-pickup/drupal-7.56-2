<?php

namespace Commerce\Utils\Payment\Authorisation;

use Commerce\Utils\OrderPayment;

/**
 * Collection of payment data.
 */
abstract class DataContainer implements \Iterator {

  use OrderPayment;

  /**
   * Payment data.
   *
   * @var array
   */
  protected $data = [];

  /**
   * DataContainer constructor.
   *
   * @param \EntityDrupalWrapper|\stdClass|string|int $order
   *   Entity wrapper of "commerce_order" entity, entity itself or entity ID.
   * @param array $payment_method
   *   Payment method information.
   */
  public function __construct($order, array $payment_method) {
    $this->setOrder($order);
    $this->setPaymentMethod($payment_method);
  }

  /**
   * Returns calculated signature of request.
   *
   * @return string
   *   Calculated signature.
   */
  abstract protected function getSignature();

  /**
   * {@inheritdoc}
   */
  public function current() {
    return current($this->data);
  }

  /**
   * {@inheritdoc}
   */
  public function next() {
    next($this->data);
  }

  /**
   * {@inheritdoc}
   */
  public function key() {
    return key($this->data);
  }

  /**
   * {@inheritdoc}
   */
  public function valid() {
    return $this->key() !== NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function rewind() {
    reset($this->data);
  }

}
