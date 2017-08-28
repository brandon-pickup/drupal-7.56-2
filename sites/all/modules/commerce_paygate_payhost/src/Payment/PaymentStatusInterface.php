<?php

namespace Drupal\commerce_paygate_payhost\Payment;

/**
 * Lists payment statuses.
 *
 * All values of constants MUST BE in lowercase!
 *
 * @see \Commerce\Utils\Transaction::setRemoteStatus()
 * @see \Drupal\commerce_paygate_payhost\Payment\Action\QueryResponse::getPaymentStatus()
 */
interface PaymentStatusInterface {

  const NOT_DONE = '0';
  const APPROVED = '1';
  /**
   * IT IS THE CUSTOM STATUS! It never will be returned by PayGate!
   *
   * PayGate returns "APPROVED" for successful transaction independently of
   * the "auto-settlement" option. Due to this we have a custom "AUTHORISED"
   * we spoofing "APPROVED" with when "auto_settlement_disabled" option on
   * a Drupal side is enabled.
   *
   * WARNING! Always remember that just enabling/disabling the "auto-settlement"
   * option on a Drupal side is not enough action to get it working. The PayGate
   * support MUST be contacted with a request to do the same action on their
   * side for your merchant account.
   *
   * @see commerce_paygate_payhost_convert_transaction_status()
   * @see \Drupal\commerce_paygate_payhost\Payment\Action\ActionResponse::getPaymentStatus()
   * @see \Drupal\commerce_paygate_payhost\Payment\Authorisation\Response::getPaymentStatus()
   */
  const AUTHORISED = '66';
  const DECLINED = '2';
  /**
   * Issues during the payment attempt.
   */
  const REJECTED = '3';
  /**
   * Cardholder clicks "cancel" and doesn't experience any problems.
   */
  const CANCELLED = '4';
  const RECEIVED = '5';
  const SETTLEMENT_VOIDED = '7';

}
