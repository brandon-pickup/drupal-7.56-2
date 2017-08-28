<?php

namespace Drupal\commerce_paygate_payhost\Tests\Payment\Authorisation;

use Drupal\commerce_utils\Tests\UnitTest;
use Drupal\commerce_paygate_payhost\Payment\Environment;
use Drupal\commerce_paygate_payhost\Payment\Composition\Account;
use Drupal\commerce_paygate_payhost\Payment\Authorisation\Request;

/**
 * Test payment authorisation request.
 */
class RequestTest extends UnitTest {

  /**
   * {@inheritdoc}
   */
  const MODULES_SET = 'api';

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return static::info('Test payment authorisation request.');
  }

  /**
   * {@inheritdoc}
   */
  public static function dependencies() {
    return ['commerce_paygate_payhost'];
  }

  /**
   * Test payment authorisation request.
   */
  public function test() {
    $payment_method = $this->getPaymentMethod(
      COMMERCE_PAYGATE_PAYHOST_PAYMENT_METHOD,
      COMMERCE_PAYGATE_PAYHOST_PAYMENT_METHOD_INSTANCE,
      [
        'mode' => Environment::TEST,
        'shopper_locale' => 'ru',
        Environment::TEST => [
          'paygate_id' => '10011072130',
          'password' => 'test',
        ],
      ]
    );

    $order = $this->createOrder($this->drupalCreateUser(), [
      $this->createDummyProduct('prdct1', 'Product 1', 200, 'USD'),
      $this->createDummyProduct('prdct2', 'Product 2', 900, 'USD'),
    ]);

    $request = new Request($order, $payment_method);

    $this->assertTrue(
      1100.0 === $request->getPaymentAmount(),
      'Amount for payment request correctly set.'
    );

    $this->assertTrue(
      'USD' === $request->getCurrencyCode(),
      'Payment request has correct currency code.'
    );

    $account_expected = Account::createFromSettings($payment_method['settings']);
    $account_actual = $request->getMerchantAccount();

    $this->assertTrue($account_expected == $account_actual, 'Payment request has correct PayGate account.');

    // @todo Continue testing.
  }

}
