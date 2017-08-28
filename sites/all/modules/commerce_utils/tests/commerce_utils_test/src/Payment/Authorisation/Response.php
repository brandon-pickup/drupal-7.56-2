<?php

namespace Drupal\commerce_utils_test\Payment\Authorisation;

use Commerce\Utils\Payment\Authorisation\ResponseBase;
use Drupal\commerce_utils_test\Payment\Transaction\Payment as PaymentTransaction;

/**
 * {@inheritdoc}
 */
class Response extends ResponseBase {

  /**
   * {@inheritdoc}
   */
  public function __construct(PaymentTransaction $transaction, array $payment_method) {
    parent::__construct($transaction, $payment_method);

    if ('I AM A SIGNATURE OF THE PAYMENT REQUEST' !== $this->getSignature()) {
      throw new \RuntimeException('Invalid signature!');
    }

    $transaction->setPayload($_GET);
    $transaction->authorise('test_remote_id');
    $transaction->save();
  }

  /**
   * {@inheritdoc}
   */
  public function getSignature() {
    return $_GET['signature'];
  }

}
