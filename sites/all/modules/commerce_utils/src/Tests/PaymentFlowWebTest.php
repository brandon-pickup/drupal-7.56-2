<?php

namespace Drupal\commerce_utils\Tests;

/**
 * Tests payment web flow.
 */
class PaymentFlowWebTest extends UnitTest {

  /**
   * {@inheritdoc}
   */
  const MODULES_SET = 'all';

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return static::info('Tests payment web flow.');
  }

  /**
   * {@inheritdoc}
   */
  public static function dependencies() {
    return ['commerce_utils_test'];
  }

  /**
   * Tests payment flow from a customer perspective.
   */
  public function test() {
    $payment_method = $this->getPaymentMethod(
      COMMERCE_UTILS_TEST_PAYMENT_METHOD_WITH_PLUGIN,
      COMMERCE_UTILS_TEST_PAYMENT_METHOD_WITH_PLUGIN_INSTANCE,
      ['option' => 'value']
    );

    // Ensure the payment method has a payment plugin (throws an exception).
    commerce_utils_get_payment_plugin($payment_method);

    $currency = 'USD';
    $country = 'US';
    $quantity = 3;
    $price = 300;

    // Run actions as admin because checkout logic is already tested by
    // the "commerce" module.
    $customer = $this->createStoreAdmin();
    $product = $this->createDummyProduct('prdct1', 'Product 1', $price, $currency);
    $order = $this->createDummyOrder($customer->uid, [
      $product->product_id => $quantity,
    ]);

    $this->drupalLogin($customer);

    $this->drupalGet($this->getCommerceUrl('checkout'));
    $this->assertResponse(200, 'The owner of the order can access the checkout page.');

    // Generate random information, as city, postal code, etc.
    $address_info = $this->generateAddressInformation();
    // Fill in the billing address information.
    $billing_pane = $this->xpath("//select[starts-with(@name, 'customer_profile_billing[commerce_customer_address]')]");

    $this->drupalPostAJAX(NULL, [(string) $billing_pane[0]['name'] => $country], (string) $billing_pane[0]['name']);
    // Check if the country has been selected correctly, this uses XPath as the
    // AJAX call replaces the element and the ID may change.
    $this->assertFieldByXPath("//select[starts-with(@id, 'edit-customer-profile-billing-commerce-customer-address')]//option[@selected='selected']", $country, 'Country selected');

    $billing_info = [];

    foreach ($address_info as $field => $value) {
      $billing_info["customer_profile_billing[commerce_customer_address][und][0][$field]"] = $value;
    }

    /* @see \CommercePaymentOffsiteTest::testCommercePaymentOffsitePayment() */
    $this->drupalPost(NULL, $billing_info, t('Continue to next step'));
    $this->drupalPostAJAX(NULL, ['commerce_payment[payment_method]' => $payment_method['instance_id']], 'commerce_payment[payment_method]');
    $this->drupalPost(NULL, ['commerce_payment[payment_method]' => $payment_method['instance_id']], t('Continue to next step'));

    // The request is always a plain array.
    /* @see commerce_utils_redirect_form() */
    $request = [
      /* @see \Drupal\commerce_utils_test\Payment\Authorisation\Request::__construct() */
      'merchantReference' => $order->order_number,
      'currencyCode' => $currency,
      'amount' => $price * $quantity,
      'countryCode' => $country,
      'shopperEmail' => $customer->mail,
      /* @see \Drupal\commerce_utils_test\Payment\Authorisation\Request::signRequest() */
      'signature' => 'I AM A SIGNATURE OF THE PAYMENT REQUEST',
    ];

    /* @var \SimpleXMLElement $element */
    foreach ($this->xpath("//input[starts-with(@name, 'url')]") as $element) {
      $attributes = $element->attributes();
      $request[(string) $attributes['name']] = (string) $attributes['value'];
    }

    foreach ($request as $field => $value) {
      $this->assertFieldByName($field, $value, sprintf('The "%s" field has expected value.', $field));
    }

    if (
      $this->assertTrue(isset($request['urlBack']), 'Payment cancellation URL is presented in a request.') &&
      $this->assertTrue(isset($request['urlReturn']), 'Return URL is presented in a payment request.')
    ) {
      $this->drupalPost(NULL, $request, NULL, [], [], 'commerce-utils-redirect-form');
      $this->drupalGet($request['urlReturn'], ['query' => $request]);
      $this->assertRaw(t('Checkout complete'));
      $this->drupalPost("admin/commerce/orders/$order->order_id/edit", ['status' => 'completed'], t('Save order'));

      // Reload an order.
      $order = $this->loadEntity('commerce_order', $order->order_id);

      $this->assertTrue(
        isset($order->data['payment_method']) && $payment_method['instance_id'] === $order->data['payment_method'],
        'Order data stored an ID of payment method instance.'
      );

      foreach (['request', 'response'] as $hook_type) {
        $hook = "hook_payment_with_plugin_payment_authorisation_{$hook_type}_alter";
        $this->assertFalse(empty($order->data[$hook]), sprintf('The "%s" hook has been executed.', $hook));
      }

      // All of the payment lifecycle events will be in order's data only
      // after successful capturing.
      /* @see commerce_utils_is_event_available() */
      $this->assertFalse(
        empty($order->data['commerce_payment_order_paid_in_full_invoked']) &&
        empty($order->data['commerce_utils_order_paid_in_full_invoked']) &&
        empty($order->data['commerce_utils_order_completed_invoked']),
        'The order has been successfully completed.'
      );
    }
  }

}
