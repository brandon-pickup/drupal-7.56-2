<?php

namespace Drupal\commerce_paygate_payhost\Payment\Authorisation;

use Commerce\Utils\Payment\Authorisation\ResponseBase;
use Drupal\commerce_paygate_payhost\Payment\PaymentStatusInterface;
use Drupal\commerce_paygate_payhost\Payment\Transaction\Payment as PaymentTransaction;

/**
 * {@inheritdoc}
 *
 * @method \Drupal\commerce_paygate_payhost\Payment\Transaction\Payment getTransaction()
 */
class Response extends ResponseBase {

  /**
   * {@inheritdoc}
   */
  public function __construct(PaymentTransaction $transaction, array $payment_method) {
    parent::__construct($transaction, $payment_method);

    // When no POST query received then it means that our redirection
    // worked because previously we've received a transaction status
    // different from "APPROVED". Check the "drupal_goto()" below.
    if (!empty($_POST)) {
      $this->data = $_POST;

      if (
        empty($this->data['CHECKSUM']) ||
        empty($this->data['PAY_REQUEST_ID']) ||
        empty($_SESSION['paygate'][$this->data['PAY_REQUEST_ID']])
      ) {
        throw new \RuntimeException(t('An invalid PayHost response given.'));
      }

      parent::__construct($transaction, $payment_method);

      if ($this->getSignature() !== $this->data['CHECKSUM']) {
        throw new \RuntimeException(t('Response from PayHost was corrupted.'));
      }

      // Redirect checkout back if transaction is not approved.
      if (PaymentStatusInterface::APPROVED !== $this->data['TRANSACTION_STATUS']) {
        drupal_goto($_SESSION['paygate'][$this->data['PAY_REQUEST_ID']]['cancel_url'], [
          'query' => [
            // Store received data between redirects.
            session_name() => $this->data,
          ],
        ]);
      }

      // We're done with this payment, remove an information from the session.
      unset($_SESSION['paygate'][$this->data['PAY_REQUEST_ID']]);
    }
    else {
      $session_name = session_name();

      // Passed by "drupal_goto()" above.
      if (empty($_GET[$session_name])) {
        throw new \LogicException('You must never see this error! If it appears then someone incorrectly uses this object!');
      }

      $this->data = $_GET[$session_name];
    }
  }

  /**
   * {@inheritdoc}
   *
   * @see \Drupal\commerce_paygate_payhost\Payment\Authorisation\Request::signRequest()
   * @link https://github.com/PayGate/sample-code-php/blob/master/PayHost/result.php#L19
   */
  public function getSignature() {
    $paygate_account = $_SESSION['paygate'][$this->data['PAY_REQUEST_ID']];

    return md5(
      $paygate_account['PayGateId'] .
      $this->data['PAY_REQUEST_ID'] .
      $this->data['TRANSACTION_STATUS'] .
      $this->getTransaction()->getOrder()->order_number->value() .
      $paygate_account['Password']
    );
  }

  /**
   * Returns transaction status.
   *
   * @return string
   *   Transaction status.
   *
   * @see \Drupal\commerce_paygate_payhost\Payment\PaymentStatusInterface
   * @see \Drupal\commerce_paygate_payhost\Payment\Action\QueryResponse::getPaymentStatus()
   */
  public function getPaymentStatus() {
    return commerce_paygate_payhost_convert_transaction_status($this->data['TRANSACTION_STATUS'], $this->getPaymentMethod());
  }

}
