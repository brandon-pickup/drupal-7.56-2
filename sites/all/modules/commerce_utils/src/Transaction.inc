<?php

namespace Commerce\Utils;

/**
 * Base wrapper of a payment transaction.
 */
abstract class Transaction {

  use OrderPayment;

  /**
   * Is transaction has been created?
   *
   * @var bool
   */
  private $isNew = TRUE;
  /**
   * Commerce payment transaction.
   *
   * @var \stdClass
   */
  protected $transaction;

  /**
   * Authorise a transaction.
   *
   * @param string|int $remote_id
   *   Remote identifier of a payment transaction.
   */
  abstract public function authorise($remote_id);

  /**
   * Check if payment was only authorised and waiting for capturing.
   *
   * @return bool
   *   A state of check.
   */
  abstract public function isAuthorised();

  /**
   * Fail a transaction.
   *
   * @param string|int $remote_id
   *   Remote identifier of a payment transaction.
   */
  abstract public function fail($remote_id);

  /**
   * Check if payment failed.
   *
   * @return bool
   *   A state of check.
   */
  abstract public function isFailed();

  /**
   * Finalize a transaction. Must be used only after successful capturing.
   */
  abstract public function finalize();

  /**
   * Check if payment has been finalized.
   *
   * @return bool
   *   A state of check.
   */
  abstract public function isFinalized();

  /**
   * Transaction constructor.
   *
   * @param \EntityDrupalWrapper|\stdClass|int|string $order
   *   Commerce order object or order ID.
   * @param array $payment_method
   *   An instance of the payment method.
   * @param string $remote_status
   *   Will be used as condition for loading existing transaction. Will
   *   not be used as a value of a new transaction.
   *
   * @see commerce_utils_get_transaction_instance()
   */
  public function __construct($order, array $payment_method, $remote_status = '') {
    $this->setPaymentMethod($payment_method);

    try {
      $this->setOrder($order);

      $this->transaction = $this->load($remote_status);
      $this->isNew = FALSE === $this->transaction;

      $order_wrapper = $this->getOrder();

      if ($this->isNew) {
        $this->transaction = commerce_payment_transaction_new();
        $this->transaction->amount = (int) $order_wrapper->commerce_order_total->amount->value();
      }

      // Do not allow to change values below.
      $this->transaction->uid = $order_wrapper->uid->value();
      $this->transaction->order_id = $order_wrapper->order_id->value();
      $this->transaction->currency_code = $order_wrapper->commerce_order_total->currency_code->value();
    }
    catch (\Exception $e) {
      // Fallback. An order cannot be loaded. Assume that transaction is new.
      // In this case it cannot be saved since "order_id" property is required.
      $this->transaction = commerce_payment_transaction_new();

      $this->error('Transaction cannot exist without the order! Input argument: <pre>@argument</pre>.', [
        '@argument' => print_r($order, TRUE),
      ]);
    }

    // Properties below are provided to make this object working
    // even when order cannot be loaded.
    if ($this->isNew) {
      $this->transaction->status = '';
      $this->transaction->remote_id = '';
      $this->transaction->remote_status = '';
      $this->transaction->message = '';
      $this->transaction->message_variables = [];
      $this->transaction->payload = [];
    }

    $this->transaction->instance_id = $payment_method['instance_id'];
    $this->transaction->payment_method = $payment_method['method_id'];
  }

  /**
   * Returns the name of payment method.
   *
   * @return string
   *   Name of payment method.
   */
  public function getPaymentMethodName() {
    return $this->getPaymentMethod()['method_id'];
  }

  /**
   * Returns an ID of instance of a payment method.
   *
   * @return string
   *   Instance ID.
   */
  public function getPaymentInstanceId() {
    return $this->getPaymentMethod()['instance_id'];
  }

  /**
   * Status: new or existing transaction.
   *
   * @return bool
   *   A state of checking.
   */
  public function isNew() {
    return $this->isNew;
  }

  /**
   * Set amount of a transaction.
   *
   * @param int $amount
   *   Transaction amount.
   */
  public function setAmount($amount) {
    $this->transaction->amount = (int) $amount;
  }

  /**
   * Returns amount of a transaction.
   *
   * @return int
   *   Transaction amount.
   */
  public function getAmount() {
    return $this->transaction->amount;
  }

  /**
   * Returns currency code of a transaction.
   *
   * @return string
   *   Currency code.
   */
  public function getCurrency() {
    return $this->transaction->currency_code;
  }

  /**
   * Set status of payment transaction.
   *
   * @param string $status
   *   Transaction status.
   */
  public function setStatus($status) {
    $statuses = commerce_payment_transaction_statuses();

    if (isset($statuses[$status])) {
      $this->transaction->status = $status;
    }
    else {
      $this->error('The "@status" is invalid. It must be one of: @statuses.', [
        '@status' => $status,
        '@statuses' => implode(', ', array_keys($statuses)),
      ]);
    }
  }

  /**
   * Get status of payment transaction.
   *
   * @return string
   *   Transaction status.
   */
  public function getStatus() {
    return $this->transaction->status;
  }

  /**
   * Set remote transaction ID.
   *
   * @param string $remote_id
   *   Remote transaction ID.
   */
  public function setRemoteId($remote_id) {
    $this->transaction->remote_id = $remote_id;
  }

  /**
   * Get remote transaction ID.
   *
   * @return string
   *   Remote transaction ID.
   */
  public function getRemoteId() {
    return $this->transaction->remote_id;
  }

  /**
   * Set remote status of a transaction.
   *
   * @param string $remote_status
   *   Remote status of a transaction.
   */
  public function setRemoteStatus($remote_status) {
    $this->transaction->remote_status = strtolower($remote_status);
  }

  /**
   * Get remote status of a transaction.
   *
   * @return string
   *   Remote status of a transaction.
   */
  public function getRemoteStatus() {
    return $this->transaction->remote_status;
  }

  /**
   * Set message for a transaction.
   *
   * @param string $message
   *   Transaction message with placeholders for replacing. Do not
   *   process the string with "t()".
   * @param array $variables
   *   Values for placeholders in message.
   *
   * @see format_string()
   */
  public function setMessage($message, array $variables = []) {
    $this->transaction->message = $message;
    $this->transaction->message_variables = $variables;
  }

  /**
   * Get message for a transaction.
   *
   * @return string
   *   Transaction message.
   */
  public function getMessage() {
    return format_string($this->transaction->message, $this->transaction->message_variables);
  }

  /**
   * Set transaction payload.
   *
   * @param mixed $payload
   *   Transaction payload.
   */
  public function setPayload($payload) {
    $this->transaction->payload[REQUEST_TIME] = $payload;
  }

  /**
   * Get transaction payload.
   *
   * @return array
   *   Transaction payload.
   */
  public function getPayload() {
    return $this->transaction->payload;
  }

  /**
   * Save transaction in a database.
   */
  public function save() {
    foreach (['order_id', 'status', 'remote_id', 'remote_status'] as $property) {
      if (empty($this->transaction->{$property})) {
        $this->error('Transaction cannot be saved. The "@property" property is not set.', [
          '@property' => $property,
        ]);

        return;
      }
    }

    commerce_payment_transaction_save($this->transaction);
  }

  /**
   * Load commerce payment transaction.
   *
   * @param string $remote_status
   *   Will be used as condition for loading existing transaction. Will
   *   not be used as a value of a new transaction.
   *
   * @return \stdClass|bool
   *   Commerce payment transaction or FALSE if it cannot be loaded.
   */
  protected function load($remote_status) {
    $conditions = [
      'order_id' => $this->getOrder()->order_id->value(),
      'instance_id' => $this->getPaymentInstanceId(),
      'payment_method' => $this->getPaymentMethodName(),
    ];

    if (!empty($remote_status)) {
      $conditions['remote_status'] = $remote_status;
    }

    $transactions = commerce_payment_transaction_load_multiple(FALSE, $conditions);

    return reset($transactions);
  }

  /**
   * Set a watchdog error.
   *
   * @param string $message
   *   Error message with placeholders.
   * @param array $arguments
   *   Values for placeholders.
   */
  protected function error($message, array $arguments = []) {
    watchdog($this->getPaymentMethodName(), $message, $arguments, WATCHDOG_ERROR);
  }

}
