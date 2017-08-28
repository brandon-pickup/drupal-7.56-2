<?php

/**
 * @file
 * Transaction-related logic.
 */

/**
 * Get an instance of transaction.
 *
 * @param string|array $payment_method
 *   An ID of payment method instance or instance itself.
 * @param string $transaction_type
 *   One of transaction types of given payment method.
 * @param \EntityDrupalWrapper|\stdClass|int|string $order
 *   Commerce order object or order ID.
 * @param string $remote_status
 *   Will be used as condition for loading existing transaction.
 *
 * @return \Commerce\Utils\Transaction
 *   An instance of transaction object.
 */
function commerce_utils_get_transaction_instance($payment_method, $transaction_type, $order, $remote_status = '') {
  if (is_string($payment_method)) {
    $payment_method = commerce_payment_method_instance_load($payment_method);
  }

  if (!is_array($payment_method) || empty($payment_method['instance_id'])) {
    throw new \InvalidArgumentException(t('An instance of payment method cannot be loaded!'));
  }

  $plugin = commerce_utils_get_payment_plugin($payment_method);

  switch ($transaction_type) {
    case 'payment':
      $transaction_type = $plugin->getPaymentTransactionClass();
      break;

    case 'refund':
      $transaction_type = $plugin->getRefundTransactionClass();
      break;

    default:
      throw new \InvalidArgumentException(t('Transaction type %transaction_type is not registered.', [
        '%transaction_type' => $transaction_type,
      ]));
  }

  return new $transaction_type($order, $payment_method, $remote_status);
}
