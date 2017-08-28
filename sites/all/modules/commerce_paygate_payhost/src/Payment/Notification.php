<?php

namespace Drupal\commerce_paygate_payhost\Payment;

use Commerce\Utils\NotificationInterface;

/**
 * Wraps upcoming DataFeed notification.
 *
 * @todo Add notifications handling if possible.
 */
class Notification implements NotificationInterface {

  /**
   * {@inheritdoc}
   */
  public function __construct(\stdClass $data) {

  }

}
