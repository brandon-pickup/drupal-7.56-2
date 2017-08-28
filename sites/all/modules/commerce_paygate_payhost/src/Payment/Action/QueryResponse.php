<?php

namespace Drupal\commerce_paygate_payhost\Payment\Action;

/**
 * Wraps response of API query.
 *
 * @see \Drupal\commerce_paygate_payhost\Payment\Action\Query
 */
class QueryResponse extends ActionResponse {

  /**
   * Returns payment amount.
   *
   * @return int
   *   Payment amount.
   */
  public function getAmount() {
    return $this->data->Amount;
  }

  /**
   * Returns currency code.
   *
   * @return string
   *   Currency code.
   */
  public function getCurrencyCode() {
    return $this->data->Currency;
  }

}
