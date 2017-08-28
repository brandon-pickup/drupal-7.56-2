<?php

namespace Drupal\commerce_utils_test\Payment\Authorisation;

use Commerce\Utils\Payment\Authorisation\RequestBase;

/**
 * {@inheritdoc}
 */
class Request extends RequestBase {

  /**
   * {@inheritdoc}
   */
  public function __construct(\stdClass $order, array $payment_method) {
    if (empty($payment_method['settings'])) {
      throw new \UnexpectedValueException(t('You are not configured payment gateway!'));
    }

    parent::__construct($order, $payment_method);

    $order_wrapper = $this->getOrder();

    $this->data['merchantReference'] = $order->order_number;
    $this->data['currencyCode'] = $order_wrapper->commerce_order_total->currency_code->value();
    $this->data['amount'] = $order_wrapper->commerce_order_total->amount->value();
    $this->data['countryCode'] = $order_wrapper->commerce_customer_billing->commerce_customer_address->country->value();
    $this->data['shopperEmail'] = $order->mail;
    $this->data['urlBack'] = $this->returnLink('back');
    $this->data['urlReturn'] = $this->returnLink('return');
  }

  /**
   * {@inheritdoc}
   */
  public function getSignature() {
    return 'I AM A SIGNATURE OF THE PAYMENT REQUEST';
  }

  /**
   * {@inheritdoc}
   */
  public function getEndpoint() {
    /* @see commerce_utils_test_menu() */
    return url(sprintf('commerce/utils/test-gateway/%s', $this->getPaymentMethod()['instance_id']), [
      'absolute' => TRUE,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function signRequest() {
    $this->data['signature'] = $this->getSignature();
  }

}
