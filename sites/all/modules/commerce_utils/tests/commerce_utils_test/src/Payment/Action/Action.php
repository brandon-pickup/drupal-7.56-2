<?php

namespace Drupal\commerce_utils_test\Payment\Action;

use Commerce\Utils\Payment\Action\ActionBase;

/**
 * {@inheritdoc}
 *
 * @property \Drupal\commerce_utils_test\Payment\Transaction\Payment $transaction
 * @method \Drupal\commerce_utils_test\Payment\Transaction\Payment getTransaction()
 */
abstract class Action extends ActionBase {

  /**
   * Action: API query (must be in lowercase).
   */
  const QUERY = 'query';
  /**
   * Action: Payment capture (must be in lowercase).
   */
  const CAPTURE = 'capture';

  /**
   * {@inheritdoc}
   */
  protected function buildRequest() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  protected function sendRequest($request) {
    $response = [];

    switch ($this->action) {
      case self::CAPTURE:
        $response = [
          'status' => isset($_SESSION['commerce_utils_test_capture_status']) ? $_SESSION['commerce_utils_test_capture_status'] : 'success',
          'paymentReference' => 'test_remote_id',
        ];
        break;
    }

    return (object) $response;
  }

  /**
   * {@inheritdoc}
   */
  protected function getTransactionType() {
    switch ($this->action) {
      case self::QUERY:
      case self::CAPTURE:
        return 'payment';

      default:
        throw new \InvalidArgumentException(t('The "@action" API action is not supported.', [
          '@action' => $this->action,
        ]));
    }
  }

}
