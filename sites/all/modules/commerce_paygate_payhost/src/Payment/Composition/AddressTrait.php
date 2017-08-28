<?php

namespace Drupal\commerce_paygate_payhost\Payment\Composition;

/**
 * Extend an object by an ability to contain an address data.
 *
 * @private
 */
trait AddressTrait {

  /**
   * Set an address.
   *
   * @param \Drupal\commerce_paygate_payhost\Payment\Composition\Address $address
   *   An address.
   */
  public function setAddress(Address $address) {
    $this->data['Address'] = $address;
  }

  /**
   * Returns an address.
   *
   * @return \Drupal\commerce_paygate_payhost\Payment\Composition\Address
   *   An address.
   */
  public function getAddress() {
    return $this->data['Address'];
  }

}
