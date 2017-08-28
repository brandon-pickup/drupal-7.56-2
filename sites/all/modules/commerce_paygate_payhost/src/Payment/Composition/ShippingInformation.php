<?php

namespace Drupal\commerce_paygate_payhost\Payment\Composition;

/**
 * Shipping information representation.
 */
class ShippingInformation extends BillingInformation {

  /**
   * {@inheritdoc}
   */
  protected function getDefaultValues() {
    return parent::getDefaultValues() + [
      'DeliveryDate' => '',
      'DeliveryMethod' => '',
      'InstallationRequested' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   *
   * @param string|int $date
   *   Delivery date.
   */
  public function setDeliveryDate($date) {
    $this->data['DeliveryDate'] = date('c', is_numeric($date) ? $date : strtotime($date));
  }

  /**
   * {@inheritdoc}
   *
   * @return string
   *   Delivery date.
   */
  public function getDeliveryDate() {
    return $this->data['DeliveryDate'];
  }

  /**
   * {@inheritdoc}
   *
   * @param string $delivery_method
   *   Delivery method.
   */
  public function setDeliveryMethod($delivery_method) {
    $this->data['DeliveryMethod'] = $delivery_method;
  }

  /**
   * {@inheritdoc}
   *
   * @return string
   *   Delivery method.
   */
  public function getDeliveryMethod() {
    return $this->data['DeliveryMethod'];
  }

  /**
   * {@inheritdoc}
   *
   * @param bool $status
   *   Whether a customer needs help to install the product.
   */
  public function requestInstallation($status) {
    $this->data['InstallationRequested'] = (bool) $status;
  }

  /**
   * {@inheritdoc}
   *
   * @return bool
   *   Whether product installation required.
   */
  public function isInstallationRequested() {
    return $this->data['InstallationRequested'];
  }

}
