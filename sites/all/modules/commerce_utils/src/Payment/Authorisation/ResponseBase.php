<?php

namespace Commerce\Utils\Payment\Authorisation;

use Commerce\Utils\Transaction;

/**
 * Base payment response.
 */
abstract class ResponseBase extends DataContainer {

  /**
   * Payment transaction.
   *
   * @var \Commerce\Utils\Transaction
   */
  private $transaction;

  /**
   * {@inheritdoc}
   */
  public function __construct(Transaction $transaction, array $payment_method) {
    parent::__construct($transaction->getOrder(), $payment_method);

    $this->setTransaction($transaction);
  }

  /**
   * Set payment transaction.
   *
   * @param \Commerce\Utils\Transaction $transaction
   *   Payment transaction.
   */
  public function setTransaction(Transaction $transaction) {
    $this->transaction = $transaction;
  }

  /**
   * Returns payment transaction.
   *
   * @return \Commerce\Utils\Transaction
   *   Payment transaction.
   */
  public function getTransaction() {
    return $this->transaction;
  }

}
