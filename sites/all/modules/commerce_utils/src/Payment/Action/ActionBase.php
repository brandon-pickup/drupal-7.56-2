<?php

namespace Commerce\Utils\Payment\Action;

use Commerce\Utils\OrderPayment;
use Commerce\Utils\NotificationControllerBase;

/**
 * Base API action.
 */
abstract class ActionBase {

  use OrderPayment;

  /**
   * The type of action.
   *
   * @var string
   */
  protected $action = '';
  /**
   * Transaction.
   *
   * @var \Commerce\Utils\Transaction
   */
  protected $transaction;
  /**
   * Response on action.
   *
   * @var mixed
   */
  private $response;

  /**
   * ActionBase constructor.
   *
   * @param \stdClass|int|string $order
   *   Commerce order object or order ID.
   * @param string $remote_transaction_status
   *   Remote status of the transaction.
   * @param string $action
   *   An action to perform. Use one of constants.
   */
  public function __construct($order, $remote_transaction_status, $action) {
    // Process an order to have entity wrapper for it.
    $this->setOrder($order);

    // Get plain object of the order entity.
    $order = $this
      ->getOrder()
      ->value();

    // Order must have passed all checkout process to have this value and to be
    // available for performing actions. This value sets in:
    /* @see commerce_payment_pane_checkout_form_submit() */
    if (empty($order->data['payment_method'])) {
      throw new \RuntimeException(t('Payment method is not specified for an order.'));
    }

    $this->action = strtolower($action);
    $this->transaction = commerce_utils_get_transaction_instance($order->data['payment_method'], $this->getTransactionType(), $order, $remote_transaction_status);

    $this->setPaymentMethod($this->transaction->getPaymentMethod());
  }

  /**
   * Checks whether action is available.
   *
   * @return bool
   *   A state of check.
   */
  abstract public function isAvailable();

  /**
   * Build request to send to API.
   *
   * Methods separation for "buildRequest" and "sendRequest" is needed for
   * saving raw request to logs.
   *
   * @return mixed
   *   Request which will be passed as an argument to "sendRequest" method.
   */
  abstract protected function buildRequest();

  /**
   * Perform API request.
   *
   * @param mixed $request
   *   Request which was built in "buildRequest" method.
   *
   * @return mixed
   *   Response on request.
   *
   * @throws \Exception
   *   When request failed even in terms of unacceptable response.
   */
  abstract protected function sendRequest($request);

  /**
   * Validate action type and return transaction type.
   *
   * @return string
   *   Type of transaction: "payment" or "refund".
   *
   * @throws \InvalidArgumentException
   *   When action type is invalid.
   */
  abstract protected function getTransactionType();

  /**
   * Send a request.
   *
   * @return bool
   *   Whether request was successfully sent or not.
   */
  public function request() {
    $payment_method = $this->transaction->getPaymentMethod();
    $request = $this->buildRequest();

    $state = 'received';
    $status = TRUE;
    $message = "Request for %action has been %state by @gateway";
    $severity = WATCHDOG_INFO;
    $variables = [];

    try {
      $this->response = $this->sendRequest($request);
    }
    catch (\Exception $e) {
      $error = $e->getMessage();

      drupal_set_message(sprintf('%s: %s', $payment_method['title'], $error), 'error');

      $state = 'rejected';
      $status = FALSE;
      $message .= ' with an error: "@error"';
      $severity = WATCHDOG_ERROR;
      $variables['@error'] = $error;
    }

    $variables['%state'] = $state;
    $variables['%action'] = $this->action;
    $variables['@gateway'] = $payment_method['title'];
    $variables['@payload'] = NotificationControllerBase::dump($request);

    watchdog($payment_method['method_id'], "{$message}.<pre>@payload</pre>", $variables, $severity);
    module_invoke_all("{$payment_method['method_id']}_{$this->action}_{$state}", $this->transaction, $this->transaction->getOrder()->value(), $this->response);

    return $status;
  }

  /**
   * Returns transaction associated with an action.
   *
   * @return \Commerce\Utils\Transaction
   *   Transaction associated with an action.
   */
  public function getTransaction() {
    return $this->transaction;
  }

  /**
   * {@inheritdoc}
   */
  public function getResponse() {
    return $this->response;
  }

}
