<?php

namespace Drupal\commerce_paygate_payhost\Entity;

use Drupal\commerce_paygate_payhost\Payment\Action\Query;
use Drupal\commerce_paygate_payhost\Payment\PaymentStatusInterface;
use Drupal\commerce_payment_watcher\Entity\PaymentWatcherEntity;

/**
 * {@inheritdoc}
 */
class PaymentWatcher extends PaymentWatcherEntity {

  /**
   * Checks payment status, creates a transaction and dies.
   */
  public function checkPaymentStatus() {
    $query = new Query($this->order, $this);

    if (!$query->isAvailable()) {
      throw new \RuntimeException(t('Payment query is not available for "@label".', [
        '@label' => $this->label(),
      ]));
    }

    if (!$query->request()) {
      throw $this->paymentNotFound();
    }

    $payment = $query->getResponse();

    switch ($payment->getPaymentStatus()) {
      // Kinda "rejected".
      case PaymentStatusInterface::DECLINED:
        $transaction = $query->getTransaction();
        $transaction->fail($payment->getPaymentReference());
        $transaction->setPayload($payment);
        $transaction->save();

        // Self-destruction.
        $this->delete();
        break;

      // Kinda "authorised".
      case PaymentStatusInterface::AUTHORISED:
        $transaction = $query->getTransaction();
        $transaction->authorise($payment->getPaymentReference());
        $transaction->setPayload($payment);
        $transaction->save();

        commerce_payment_redirect_pane_next_page($this->order);

        // Self-destruction.
        $this->delete();
        break;

      // Kinda "finalized".
      case PaymentStatusInterface::APPROVED:
        $transaction = $query->getTransaction();

        if (!$transaction->isAuthorised()) {
          $transaction->authorise($payment->getPaymentReference());
        }

        // In normal mode payment acceptance is not distributed (happens
        // immediately, without notifications process).
        $transaction->finalize();
        $transaction->setPayload($payment);
        $transaction->save();

        commerce_payment_redirect_pane_next_page($this->order);

        // Self-destruction.
        $this->delete();
        break;

      default:
        $this->saveAttempt();
    }
  }

}
