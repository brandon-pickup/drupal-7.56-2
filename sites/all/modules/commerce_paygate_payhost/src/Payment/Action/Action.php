<?php

namespace Drupal\commerce_paygate_payhost\Payment\Action;

use Commerce\Utils\Payment\Action\ActionBase;
use Drupal\commerce_paygate_payhost\Entity\PaymentWatcher;
use Drupal\commerce_paygate_payhost\Payment\Environment;
use Drupal\commerce_paygate_payhost\Payment\Composition\Account;

/**
 * {@inheritdoc}
 *
 * @todo Implement "refund" and "void" actions.
 *
 * @property \Drupal\commerce_paygate_payhost\Payment\Transaction\Payment $transaction
 * @method \Drupal\commerce_paygate_payhost\Payment\Transaction\Payment getTransaction()
 */
abstract class Action extends ActionBase {

  /**
   * Action: API query (must be in lowercase).
   */
  const QUERY = 'query';
  /**
   * Action: Payment capture (must be in lowercase).
   */
  const CAPTURE = 'settlement';

  /**
   * Fully qualified path to class to wrap response in.
   */
  const RESPONSE_CLASS = '';

  /**
   * Payment watcher.
   *
   * @var \Drupal\commerce_paygate_payhost\Entity\PaymentWatcher
   */
  protected $watcher;

  /**
   * {@inheritdoc}
   */
  public function __construct($order, $remote_transaction_status, $action, PaymentWatcher $watcher = NULL) {
    parent::__construct($order, $remote_transaction_status, $action);

    $this->watcher = $watcher ?: PaymentWatcher::loadForOrder($this->transaction->getOrder()->value(), $this->transaction->getPaymentMethodName());
  }

  /**
   * {@inheritdoc}
   */
  protected function buildRequest() {
    $payment_method = $this->transaction->getPaymentMethod();
    $request = [
      'Account' => Account::createFromSettings($payment_method['settings']),
    ];

    switch ($this->action) {
      case self::QUERY:
        if (empty($this->watcher->remote_id)) {
          throw new \RuntimeException(
            sprintf(
              'Cannot perform the "%s" action because payment watcher does not have an ID of payment request',
              $this->action
            )
          );
        }

        $request['PayRequestId'] = $this->watcher->remote_id;
        break;

      case self::CAPTURE:
        $request['TransactionId'] = $this->transaction->getRemoteId();
        break;
    }

    return [
      'method' => 'SingleFollowUp',
      'data' => [ucfirst($this->action) . 'Request' => $request],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function sendRequest($request) {
    $response_class = static::RESPONSE_CLASS;
    $payment_method = $this->transaction->getPaymentMethod();
    $response = Environment::createFromSettings($payment_method['settings'])
      ->request($request['method'], $request['data']);

    // Get nested data.
    foreach ([ucfirst($this->action) . 'Response', 'Status'] as $property) {
      $response = isset($response->{$property}) ? $response->{$property} : $response;
    }

    if (!empty($response_class) && class_exists($response_class)) {
      $response->TransactionStatusCode = commerce_paygate_payhost_convert_transaction_status($response->TransactionStatusCode, $payment_method);
      $response = new $response_class($response);

      /* @var \Drupal\commerce_paygate_payhost\Payment\Action\ActionResponse $response */
      if (is_subclass_of($response, ActionResponse::class) && !$response->isSuccessful()) {
        throw new \RuntimeException($response->getMessage());
      }
    }

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  protected function getTransactionType() {
    switch ($this->action) {
      case self::QUERY:
      case self::CAPTURE:
        return 'payment';

      default:
        throw new \InvalidArgumentException(t('The "@action" API action is not supported.', [
          '@action' => $this->action,
        ]));
    }
  }

}
