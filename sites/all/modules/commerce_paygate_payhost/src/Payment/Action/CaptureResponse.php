<?php

namespace Drupal\commerce_paygate_payhost\Payment\Action;

/**
 * Wraps response of capture request.
 */
class CaptureResponse extends ActionResponse {

  /**
   * {@inheritdoc}
   */
  public function isSuccessful() {
    // This code returns alongside with "ResultDescription" which has
    // the "Settlement record already exists" value. Let's assume it as
    // successful capturing.
    return parent::isSuccessful() || 202 === $this->getResultCode();
  }

}
