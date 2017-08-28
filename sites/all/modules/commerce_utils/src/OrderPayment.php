<?php

namespace Commerce\Utils;

/**
 * Abstraction for storing an order and payment method in the object.
 */
trait OrderPayment {

  /**
   * Entity wrapper of "commerce_order" entity.
   *
   * @var \EntityDrupalWrapper
   */
  private $order;
  /**
   * Payment method definition.
   *
   * @var array
   */
  private $paymentMethod = [];

  /**
   * Set an order object.
   *
   * @param \EntityDrupalWrapper|\stdClass|string|int $order
   *   Entity wrapper of "commerce_order" entity, entity itself or entity ID.
   */
  public function setOrder($order) {
    if ($order instanceof \EntityMetadataWrapper) {
      $this->order = $order;
    }
    else {
      $this->order = entity_metadata_wrapper('commerce_order', $order);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getOrder() {
    return $this->order;
  }

  /**
   * Set payment method.
   *
   * @param array $payment_method
   *   Payment method definition.
   */
  public function setPaymentMethod(array $payment_method) {
    if (!isset($payment_method['method_id'])) {
      throw new \InvalidArgumentException(t('Incorrect payment method'));
    }

    $this->paymentMethod = $payment_method;
  }

  /**
   * {@inheritdoc}
   */
  public function getPaymentMethod() {
    return $this->paymentMethod;
  }

}
