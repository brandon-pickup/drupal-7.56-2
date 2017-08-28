<?php

namespace Drupal\commerce_paygate_payhost\Payment\Capture;

use Commerce\Utils\Payment\Capture\CaptureProcessorBase;
use Drupal\commerce_paygate_payhost\Payment\Action\Capture;

/**
 * PayGate PayHost payments capturing.
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
        $response = $capture->getResponse();
        $transaction = $capture->getTransaction();

        if ($response->isSuccessful()) {
          $transaction->finalize();

          $this->setCaptured(TRUE, t('Payment has been successfully captured.'));
        }
        else {
          $transaction->fail($response->getPaymentReference());

          $this->setCaptured(FALSE, t('Payment capture failed with a message: @message', [
            '@message' => $response->getMessage(),
          ]));
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
