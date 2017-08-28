<?php

namespace Drupal\commerce_utils\Tests;

/**
 * Tests events cascade of the payment flow.
 */
class EventsCascadeTest extends UnitTest {

  /**
   * Dummy order object.
   *
   * @var \stdClass
   */
  protected $order;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return static::info('Tests events cascade of the payment flow.');
  }

  /**
   * {@inheritdoc}
   */
  public static function dependencies() {
    return ['commerce_paygate_payhost'];
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    module_load_include('commerce.inc', 'commerce_utils');

    $this->order = commerce_order_new();
    $this->order->data = [
      'payment_method' => COMMERCE_PAYGATE_PAYHOST_PAYMENT_METHOD_INSTANCE,
    ];
  }

  /**
   * Ensure event availability checker works as expected.
   */
  public function testPositive() {
    list($event) = commerce_utils_is_event_available($this->order, 'commerce_payment_order_paid_in_full');
    $this->assertTrue('' !== $event, 'First event from a loop is available.');
    // Spoof event's invocation.
    $this->order->data['commerce_payment_order_paid_in_full_invoked'] = TRUE;
    list($event) = commerce_utils_is_event_available($this->order, 'commerce_payment_order_paid_in_full');
    $this->assertTrue('' === $event, 'First event from a loop is no longer available after invocation.');

    list($event) = commerce_utils_is_event_available($this->order, 'commerce_utils_order_paid_in_full');
    $this->assertTrue('' !== $event, 'Second event from a loop is available.');
    // Spoof event's invocation.
    $this->order->data['commerce_utils_order_paid_in_full_invoked'] = TRUE;
    list($event) = commerce_utils_is_event_available($this->order, 'commerce_utils_order_paid_in_full');
    $this->assertTrue('' === $event, 'Second event from a loop is no longer available after invocation.');

    list($event) = commerce_utils_is_event_available($this->order, 'commerce_utils_order_completed');
    $this->assertTrue('' !== $event, 'Third event from a loop is available.');
    // Spoof event's invocation.
    $this->order->data['commerce_utils_order_completed_invoked'] = TRUE;
    list($event) = commerce_utils_is_event_available($this->order, 'commerce_utils_order_completed');
    $this->assertTrue('' === $event, 'Third event from a loop is no longer available after invocation.');
  }

  /**
   * Ensure that events cascade followed.
   */
  public function testNegative() {
    list($event) = commerce_utils_is_event_available($this->order, 'commerce_payment_order_paid_in_full');
    $this->assertTrue('' !== $event, 'First event from a loop is available.');

    list($event) = commerce_utils_is_event_available($this->order, 'commerce_utils_order_paid_in_full');
    $this->assertTrue('' === $event, 'Second event from a loop is not available since first was not executed.');

    list($event) = commerce_utils_is_event_available($this->order, 'commerce_utils_order_completed');
    $this->assertTrue('' === $event, 'Third event from a loop is not available since first and second were not executed.');
  }

}
