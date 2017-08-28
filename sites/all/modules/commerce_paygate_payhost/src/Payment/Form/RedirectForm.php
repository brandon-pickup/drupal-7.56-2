<?php

namespace Drupal\commerce_paygate_payhost\Payment\Form;

use Commerce\Utils\Payment\Form\RedirectFormBase;
use Drupal\commerce_paygate_payhost\Entity\PaymentWatcher;
use Drupal\commerce_paygate_payhost\Payment\PaymentStatusInterface;

/**
 * {@inheritdoc}
 *
 * @method \Drupal\commerce_paygate_payhost\Payment\Authorisation\Response getResponse()
 */
class RedirectForm extends RedirectFormBase {

  /**
   * {@inheritdoc}
   */
  public function cancel() {
    switch ($this->getResponse()->getPaymentStatus()) {
      case PaymentStatusInterface::CANCELLED:
        // Use "\Exception" to show just warning message.
        throw new \Exception(t('Payment has been manually canceled by the customer.'));

      case PaymentStatusInterface::REJECTED:
        throw new \RuntimeException(t('Payment cannot be processed by the gateway.'));

      case PaymentStatusInterface::DECLINED:
        throw new \RuntimeException(t('Payment has been declined.'));
    }

    throw new \RuntimeException(t('Payment has been failed.'));
  }

  /**
   * {@inheritdoc}
   */
  public function response() {
    $response = $this->getResponse();
    $watcher = PaymentWatcher::loadForOrder($response->getOrder()->value(), $response->getTransaction()->getPaymentMethodName());

    // No need to check payment status for the non-existent watcher.
    if (!$watcher->isNew()) {
      $watcher->checkPaymentStatus();
    }
  }

}
