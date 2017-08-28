<?php

namespace Drupal\commerce_paygate_payhost\Payment\Authorisation;

use Commerce\Utils\Payment\Authorisation\RequestBase;
use Drupal\commerce_paygate_payhost\Entity\PaymentWatcher;
use Drupal\commerce_paygate_payhost\Payment\Environment;
use Drupal\commerce_paygate_payhost\Payment\NotificationController;
use Drupal\commerce_paygate_payhost\Payment\Composition\Account;
use Drupal\commerce_paygate_payhost\Payment\Composition\Address;
use Drupal\commerce_paygate_payhost\Payment\Composition\Customer;
use Drupal\commerce_paygate_payhost\Payment\Composition\CustomerTrait;
use Drupal\commerce_paygate_payhost\Payment\Composition\BillingInformation;
use Drupal\commerce_paygate_payhost\Payment\Composition\ShippingInformation;

/**
 * {@inheritdoc}
 *
 * @link https://github.com/PayGate/sample-code-php/tree/master/PayHost
 * @link http://events.go-time.me/paygate/PayHost/singlePayment/webPayment/index.php
 */
class Request extends RequestBase {

  use CustomerTrait;

  /**
   * PayHost account.
   *
   * @var \Drupal\commerce_paygate_payhost\Payment\Composition\Account
   */
  private $account;
  /**
   * PayGate PayHost environment.
   *
   * @var \Drupal\commerce_paygate_payhost\Payment\Environment
   */
  private $environment;
  /**
   * Variable for a delayed writing of URL to payment endpoint.
   *
   * @var string
   */
  private $endpoint;
  /**
   * {@inheritdoc}
   *
   * DO NOT NEVER CHANGE ORDERING OF THE ELEMENTS HERE WITHOUT A NEED.
   */
  protected $data = [
    /* @see \Drupal\commerce_paygate_payhost\Payment\Composition\Account */
    'Account' => NULL,
    /* @see \Drupal\commerce_paygate_payhost\Payment\Composition\Customer */
    'Customer' => NULL,
    'Redirect' => [
      'NotifyUrl' => '',
      'ReturnUrl' => '',
    ],
    'Order' => [
      'MerchantOrderId' => '',
      'Currency' => '',
      'Amount' => '',
      'TransactionDate' => '',
      /* @see \Drupal\commerce_paygate_payhost\Payment\Composition\BillingInformation */
      'BillingDetails' => NULL,
      /* @see \Drupal\commerce_paygate_payhost\Payment\Composition\ShippingInformation */
      'ShippingDetails' => NULL,
      'Locale' => '',
    ],
    'Risk' => [
      'IpV4Address' => '',
      'UserId' => '',
    ],
    'UserDefinedFields' => NULL,
  ];

  /**
   * {@inheritdoc}
   */
  public function __construct(\stdClass $order, array $payment_method) {
    if (empty($payment_method['settings'])) {
      throw new \UnexpectedValueException(t('You are not configured payment gateway!'));
    }

    parent::__construct($order, $payment_method);

    $this->account = Account::createFromSettings($payment_method['settings']);
    $this->environment = Environment::createFromSettings($payment_method['settings']);

    $details = [];
    $order_wrapper = $this->getOrder();

    // These values must be never changed.
    $this->data['Redirect'] = [
      'NotifyUrl' => url(NotificationController::NOTIFICATIONS_PATH, ['absolute' => TRUE]),
      'ReturnUrl' => $this->returnLink('return'),
    ];

    $this->data['Order']['Currency'] = $order_wrapper->commerce_order_total->currency_code->value();
    $this->data['Order']['TransactionDate'] = date('c');

    $this->setShopperLocale($payment_method['settings']['shopper_locale']);
    $this->setMerchantAccount($this->account);
    $this->setMerchantReference($order->order_number);

    $this->setPaymentAmount($order_wrapper->commerce_order_total->amount->value());

    $this->setShopperIp(ip_address());
    // For "Anonymous" users we need to pass an email as shopper reference.
    $this->setShopperReference($order->uid > 0 ? $order_wrapper->owner->name->value() : $order->mail);

    foreach ([
      'billing' => BillingInformation::class,
      'shipping' => ShippingInformation::class,
    ] as $type => $class) {
      $profile = 'commerce_customer_' . $type;

      // We must check the property in raw order object because its wrapper
      // will definitely contain it for "billing" type of profiles and value
      // there will be "NULL" in a very base configuration of commerce.
      if (isset($order->{$profile})) {
        $address = Address::createFromCustomerProfile($order_wrapper->{$profile});

        $customer = new Customer();
        $customer->setFirstName($order_wrapper->{$profile}->commerce_customer_address->first_name->value());
        $customer->setLastName($order_wrapper->{$profile}->commerce_customer_address->last_name->value());
        $customer->setEmail($order->mail);
        $customer->setAddress($address);

        /* @var \Drupal\commerce_paygate_payhost\Payment\Composition\BillingInformation $info */
        $info = new $class();
        $info->setAddress($address);
        $info->setCustomerInformation($customer);

        $details[$type] = $info;
      }
    }

    if (isset($details['billing'])) {
      $this->setBillingInformation($details['billing']);
      $this->setCustomerInformation($details['billing']->getCustomerInformation());
    }

    if (isset($details['shipping'])) {
      $this->setShippingInformation($details['shipping']);
    }

    $this->setAdditionalMerchantReference($order->mail);
    $this->setAdditionalMerchantReference($order->created);
  }

  /**
   * Set merchant account.
   *
   * @param \Drupal\commerce_paygate_payhost\Payment\Composition\Account $account
   *   PayHost account.
   */
  public function setMerchantAccount(Account $account) {
    $this->data['Account'] = $account;
  }

  /**
   * Get merchant account.
   *
   * @return \Drupal\commerce_paygate_payhost\Payment\Composition\Account
   *   Merchant account.
   */
  public function getMerchantAccount() {
    return $this->data['Account'];
  }

  /**
   * Get currency code.
   *
   * WARNING! No setter for currency because this value fully depends on the
   * order.
   *
   * @return string
   *   Currency code.
   */
  public function getCurrencyCode() {
    return $this->data['Order']['Currency'];
  }

  /**
   * Set amount of a payment.
   *
   * @param float $payment_amount
   *   Payment amount. Specified in minor units.
   *
   * @throws \InvalidArgumentException
   */
  public function setPaymentAmount($payment_amount) {
    $this->data['Order']['Amount'] = $payment_amount;
  }

  /**
   * Get amount of a payment.
   *
   * @return float
   *   Amount of a payment.
   */
  public function getPaymentAmount() {
    return $this->data['Order']['Amount'];
  }

  /**
   * Set merchant reference.
   *
   * @param string $merchant_reference
   *   Merchant reference.
   *
   * @example
   * $payment->setMerchantReference('DE-LW-2013');
   */
  public function setMerchantReference($merchant_reference) {
    $this->data['Order']['MerchantOrderId'] = $merchant_reference;
  }

  /**
   * Get merchant reference.
   *
   * @return string
   *   Merchant reference.
   */
  public function getMerchantReference() {
    return $this->data['Order']['MerchantOrderId'];
  }

  /**
   * Set shopper locale.
   *
   * @param string $locale
   *   Shopper locale.
   *
   * @see \Drupal\commerce_paygate_payhost\Payment\PaymentLocaleInterface
   */
  public function setShopperLocale($locale) {
    $this->data['Order']['Locale'] = $locale;
  }

  /**
   * Get shopper locale.
   *
   * @return string
   *   Shopper locale.
   */
  public function getShopperLocale() {
    return $this->data['Order']['Locale'];
  }

  /**
   * Set shopper reference.
   *
   * @param string $shopper_reference
   *   Shopper reference.
   *
   * @example
   * $payment->setShopperReference('admin');
   */
  public function setShopperReference($shopper_reference) {
    $this->data['Risk']['UserId'] = $shopper_reference;
  }

  /**
   * Returns shopper reference.
   *
   * @return string
   *   Shopper reference.
   */
  public function getShopperReference() {
    return $this->data['Risk']['UserId'];
  }

  /**
   * Set shopper IP address.
   *
   * @param string $shopper_ip
   *   Shopper IP address.
   */
  public function setShopperIp($shopper_ip) {
    $this->data['Risk']['IpV4Address'] = $shopper_ip;
  }

  /**
   * Get shopper IP address.
   *
   * @return string
   *   Shopper IP address.
   */
  public function getShopperIp() {
    return $this->data['Risk']['IpV4Address'];
  }

  /**
   * Set billing information.
   *
   * @param \Drupal\commerce_paygate_payhost\Payment\Composition\BillingInformation $billing_information
   *   Billing information.
   */
  public function setBillingInformation(BillingInformation $billing_information) {
    $this->data['Order']['BillingDetails'] = $billing_information;
  }

  /**
   * Returns billing information.
   *
   * @return \Drupal\commerce_paygate_payhost\Payment\Composition\BillingInformation
   *   Billing information.
   */
  public function getBillingInformation() {
    return $this->data['Order']['BillingDetails'];
  }

  /**
   * Set shipping information.
   *
   * @param \Drupal\commerce_paygate_payhost\Payment\Composition\ShippingInformation $shipping_information
   *   Shipping information.
   */
  public function setShippingInformation(ShippingInformation $shipping_information) {
    $this->data['Order']['ShippingDetails'] = $shipping_information;
  }

  /**
   * Returns shipping information.
   *
   * @return \Drupal\commerce_paygate_payhost\Payment\Composition\ShippingInformation
   *   Shipping information.
   */
  public function getShippingInformation() {
    return $this->data['Order']['ShippingDetails'];
  }

  /**
   * Set additional merchant reference.
   *
   * @param string $merchant_reference
   *   Custom merchant reference.
   */
  public function setAdditionalMerchantReference($merchant_reference) {
    static $counter = 0;

    $this->data['UserDefinedFields'][] = [
      'key' => 'reference_' . $counter++,
      'value' => $merchant_reference,
    ];
  }

  /**
   * Returns additional merchant reference.
   *
   * @param int $index
   *   Index of additional merchant reference.
   *
   * @return string
   *   Additional merchant reference.
   */
  public function getAdditionalMerchantReference($index) {
    return isset($this->data['UserDefinedFields'][$index]) ? $this->data['UserDefinedFields'][$index]['value'] : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getSignature() {
    throw new \LogicException('PayHost requests are goes outside a website with password for merchant account, so signature is not needed.');
  }

  /**
   * {@inheritdoc}
   */
  public function getEndpoint() {
    return $this->endpoint;
  }

  /**
   * {@inheritdoc}
   *
   * @see \Drupal\commerce_paygate_payhost\Payment\Authorisation\Response::getSignature()
   */
  public function signRequest() {
    $response = $this->environment->request('SinglePayment', [
      'WebPaymentRequest' => $this->data,
    ]);

    if (empty($response->WebPaymentResponse->Redirect)) {
      throw new \RuntimeException(t('Cannot proceed with a redirect to PayGate. The following status has been returned: !status', [
        '!status' => '<pre>' . var_export((array) $response->WebPaymentResponse->Status, TRUE) . '</pre>',
      ]));
    }

    $this->data = [];
    $this->endpoint = $response->WebPaymentResponse->Redirect->RedirectUrl;

    foreach ($response->WebPaymentResponse->Redirect->UrlParams as $param) {
      $this->data[$param->key] = $param->value;
    }

    // Store to calculate checksum on response.
    $_SESSION['paygate'][$this->data['PAY_REQUEST_ID']] = $this->account->toArray() + [
      'cancel_url' => $this->returnLink('back'),
    ];

    PaymentWatcher::createForOrder(
      $this->getOrder()->value(),
      $this->getPaymentMethod()['method_id'],
      $this->data['PAY_REQUEST_ID']
    );
  }

}
