<?php

namespace Drupal\commerce_paygate_payhost\Payment\Composition;

/**
 * Billing information representation.
 */
class BillingInformation extends BaseComposition {

  use AddressTrait, CustomerTrait;

  /**
   * {@inheritdoc}
   */
  protected function getDefaultValues() {
    return [
      'Customer' => NULL,
      'Address' => NULL,
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getRequiredFields() {
    return ['Customer', 'Address'];
  }

}
