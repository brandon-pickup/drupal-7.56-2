<?php

namespace Commerce\Utils\Payment\Authorisation;

/**
 * Base payment request.
 */
abstract class RequestBase extends DataContainer {

  /**
   * Sign payment request.
   */
  abstract public function signRequest();

  /**
   * Returns endpoint URL.
   *
   * @return string
   *   Endpoint URL.
   */
  abstract public function getEndpoint();

  /**
   * Get return link.
   *
   * @param string $type
   *   One of "return", "back" or custom one.
   *
   * @return string
   *   FQ URL to return page.
   *
   * @see commerce_payment_redirect_pane_checkout_form()
   */
  final public function returnLink($type) {
    $order = $this->getOrder()->value();

    return url('checkout/' . $order->order_id . '/payment/' . $type . '/' . $order->data['payment_redirect_key'], [
      'absolute' => TRUE,
    ]);
  }

}
