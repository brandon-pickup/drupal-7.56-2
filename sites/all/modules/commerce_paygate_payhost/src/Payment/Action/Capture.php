<?php

namespace Drupal\commerce_paygate_payhost\Payment\Action;

/**
 * Capture money of authorised payment.
 *
 * @method \Drupal\commerce_paygate_payhost\Payment\Action\CaptureResponse getResponse()
 */
class Capture extends Action {

  /**
   * {@inheritdoc}
   */
  const RESPONSE_CLASS = CaptureResponse::class;

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
