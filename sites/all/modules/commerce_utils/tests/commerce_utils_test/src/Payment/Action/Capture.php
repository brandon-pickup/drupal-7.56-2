<?php

namespace Drupal\commerce_utils_test\Payment\Action;

/**
 * Capture money of authorised payment.
 */
class Capture extends Action {

  /**
   * {@inheritdoc}
   */
  public function __construct($order) {
    parent::__construct($order, '', self::CAPTURE);
  }

  /**
   * {@inheritdoc}
   */
  public function isAvailable() {
    return $this->transaction->isAuthorised() && !empty($this->transaction->getRemoteId());
  }

}
