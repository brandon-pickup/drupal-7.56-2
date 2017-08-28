<?php

namespace Drupal\commerce_utils\Tests;

use Drupal\commerce_utils_test\Payment\Transaction\Payment;

/**
 * Tests payment flow.
 */
class PaymentFlowTest extends UnitTest {

  /**
   * {@inheritdoc}
   */
  const MODULES_SET = 'api';

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return static::info('Tests payment flow.');
  }

  /**
   * {@inheritdoc}
   */
  public static function dependencies() {
    return ['commerce_utils_test'];
  }

  /**
   * Tests payment flow with payment plugin.
   */
  public function testWithPaymentPlugin() {
    $this->runTheTest(
      COMMERCE_UTILS_TEST_PAYMENT_METHOD_WITH_PLUGIN_WITHOUT_CAPTURE,
      COMMERCE_UTILS_TEST_PAYMENT_METHOD_WITH_PLUGIN_WITHOUT_CAPTURE_INSTANCE,
      function (\stdClass $order, array $payment_method) {
        return commerce_utils_get_transaction_instance($payment_method, 'payment', $order);
      },
      function (Payment $transaction) {
        $transaction->authorise('test_remote_id');
      },
      function (Payment $transaction) {
        $transaction->finalize();
      },
      function (Payment $transaction) {
        $transaction->save();
      }
    );
  }

  /**
   * Tests payment flow without payment plugin.
   *
   * Here you can see the difference of how to not use the payment plugins.
   * Much harder. Less laconic.
   */
  public function testWithoutPaymentPlugin() {
    $this->runTheTest(
      COMMERCE_UTILS_TEST_PAYMENT_METHOD_WITHOUT_PLUGIN,
      COMMERCE_UTILS_TEST_PAYMENT_METHOD_WITHOUT_PLUGIN_INSTANCE,
      function (\stdClass $order, array $payment_method) {
        // All code in this function is a rough shorten of the reference below.
        // Please, consider the usage of payment plugin and avoid usage of low
        // level API.
        /* @see \Commerce\Utils\Transaction::__construct() */
        $order_wrapper = entity_metadata_wrapper('commerce_order', $order);
        $transaction = commerce_payment_transaction_new(
          $payment_method['method_id'],
          $order->order_id
        );

        $transaction->uid = $order->uid;
        $transaction->amount = (int) $order_wrapper->commerce_order_total->amount->value();
        $transaction->instance_id = $payment_method['instance_id'];
        $transaction->currency_code = $order_wrapper->commerce_order_total->currency_code->value();

        return $transaction;
      },
      function (\stdClass $transaction) {
        $transaction->status = COMMERCE_PAYMENT_STATUS_AUTHORISED;
        $transaction->remote_id = 'test_remote_id';
      },
      function (\stdClass $transaction) {
        $transaction->status = COMMERCE_PAYMENT_STATUS_SUCCESS;
      },
      function (\stdClass $transaction) {
        commerce_payment_transaction_save($transaction);
      }
    );
  }

  /**
   * Abstraction of payment flow test.
   *
   * @param string $method_id
   *   An ID of payment method.
   * @param string $instance_id
   *   An ID of the payment method instance.
   * @param callable $transaction_produce
   *   A callback for creating payment transaction.
   * @param callable $transaction_authorise
   *   A callback for authorising payment transaction.
   * @param callable $transaction_finalize
   *   A callback for finalizing payment transaction.
   * @param callable $transaction_save
   *   A callback for saving payment transaction.
   */
  protected function runTheTest(
    $method_id,
    $instance_id,
    callable $transaction_produce,
    callable $transaction_authorise,
    callable $transaction_finalize,
    callable $transaction_save
  ) {
    $creator = $this->drupalCreateUser();

    /* @see commerce_utils_test_commerce_payment_method_info() */
    $payment_method = $this->getPaymentMethod($method_id, $instance_id);

    $order = $this->createOrder($creator, [
      $this->createDummyProduct('prdct1', 'Product 1', 200, 'USD', $creator->uid),
      $this->createDummyProduct('prdct2', 'Product 2', 900, 'USD', $creator->uid),
    ]);

    $transaction = $transaction_produce($order, $payment_method, $creator);

    $transaction_authorise($transaction);
    /* @see commerce_utils_commerce_payment_transaction_update() */
    $transaction_save($transaction);

    $this->assertTrue(
      empty($order->data['commerce_payment_order_paid_in_full_invoked']),
      'The "commerce_payment_order_paid_in_full" event was not invoked on transaction save because an order does not have "payment_method".'
    );

    // Set the "payment_method" to the order to invoke events.
    /* @see commerce_utils_is_event_available() */
    $order->data['payment_method'] = $payment_method['instance_id'];
    /* @see commerce_utils_commerce_payment_transaction_update() */
    $transaction_save($transaction);

    $this->assertFalse(
      empty($order->data['commerce_payment_order_paid_in_full_invoked']) &&
      empty($order->data['commerce_utils_order_paid_in_full_invoked']),
      'The "commerce_payment_order_paid_in_full" event was invoked on transaction save and triggered the "commerce_utils_order_paid_in_full" event on order save.'
    );

    $order->status = 'completed';
    commerce_order_save($order);
    $messages = drupal_get_messages();

    /* @see commerce_utils_commerce_order_presave() */
    $this->assertTrue(
      'pending' === $order->status,
      'Order status was not changed due to missing payment.'
    );

    $this->assertTrue(
      in_array(t('An order cannot be marked as completed since it does not have a successful payment.'), $messages['warning']),
      'An expected warning message is found.'
    );

    $this->assertTrue(
      empty($order->data['commerce_utils_order_completed']),
      'The "commerce_utils_order_completed" event was invoked but unsuccessfully.'
    );

    // Make a transaction finalized in order to be able to complete the order.
    $transaction_finalize($transaction);
    $transaction_save($transaction);

    $order->status = 'completed';
    commerce_order_save($order);

    $this->assertTrue(
      'completed' === $order->status,
      'Order successfully completed since it has a finalized transaction.'
    );

    $this->assertFalse(
      empty($order->data['commerce_utils_order_completed_invoked']),
      'The "commerce_utils_order_completed" event was invoked successfully.'
    );
  }

}
