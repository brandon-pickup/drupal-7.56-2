<?php

namespace Drupal\commerce_utils_test\Payment\Capture;

use Commerce\Utils\Payment\Capture\CaptureProcessorBase;
use Drupal\commerce_utils_test\Payment\Action\Capture;

/**
 * Payments capturing.
 */
class CaptureProcessor extends CaptureProcessorBase {

  /**
   * {@inheritdoc}
   */
  public function isStatusChangeDelayed() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  protected function perform() {
    $capture = new Capture($this->getOrder());

    if ($capture->isAvailable()) {
      if ($capture->request()) {
        /* @see \Drupal\commerce_utils_test\Payment\Action\Action::sendRequest() */
        $response = $capture->getResponse();
        $transaction = $capture->getTransaction();

        if ('success' === $response->status) {
          $transaction->finalize();

          $this->setCaptured(TRUE, t('Payment has been successfully captured.'));
        }
        else {
          $transaction->fail($response->paymentReference);

          $this->setCaptured(FALSE, t('Payment capture failed.'));
        }

        $transaction->setPayload($response);
        $transaction->save();
      }
      else {
        $this->setCaptured(FALSE, t('Payment capture failed on a gateway communication stage.'));
      }
    }
    elseif ($capture->getTransaction()->isFinalized()) {
      $this->setCaptured(TRUE, t('Payment is already captured and completed.'));
    }
    else {
      $this->setCaptured(FALSE, t('An order cannot be completed because it does not have a payment.'));
    }
  }

}
