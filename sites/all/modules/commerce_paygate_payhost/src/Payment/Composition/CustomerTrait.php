<?php

namespace Drupal\commerce_paygate_payhost\Payment\Composition;

/**
 * Extend an object by an ability to contain a customer data.
 *
 * @private
 */
trait CustomerTrait {

  /**
   * Set customer information.
   *
   * @param \Drupal\commerce_paygate_payhost\Payment\Composition\Customer $customer
   *   Information about the customer.
   */
  public function setCustomerInformation(Customer $customer) {
    $this->data['Customer'] = $customer;
  }

  /**
   * Returns customer information.
   *
   * @return \Drupal\commerce_paygate_payhost\Payment\Composition\Customer
   *   Information about the customer.
   */
  public function getCustomerInformation() {
    return $this->data['Customer'];
  }

}
