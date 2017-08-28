<?php

namespace Drupal\commerce_paygate_payhost\Payment\Action;

use Drupal\commerce_paygate_payhost\Entity\PaymentWatcher;

/**
 * Query existing payment.
 *
 * @method \Drupal\commerce_paygate_payhost\Payment\Action\QueryResponse getResponse()
 */
class Query extends Action {

  /**
   * {@inheritdoc}
   */
  const RESPONSE_CLASS = QueryResponse::class;

  /**
   * {@inheritdoc}
   */
  public function __construct($order, PaymentWatcher $watcher = NULL) {
    parent::__construct($order, '', self::QUERY, $watcher);
  }

  /**
   * {@inheritdoc}
   */
  public function isAvailable() {
    return !$this->watcher->isNew();
  }

}
