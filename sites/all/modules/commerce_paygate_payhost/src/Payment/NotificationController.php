<?php

namespace Drupal\commerce_paygate_payhost\Payment;

use Commerce\Utils\NotificationControllerBase;
use Commerce\Utils\Payment\Exception\NotificationException;

/**
 * Notifications handler.
 *
 * @todo Add notifications handling if possible.
 *
 * @property \Drupal\commerce_paygate_payhost\Payment\Notification $data
 */
class NotificationController extends NotificationControllerBase {

  /**
   * {@inheritdoc}
   */
  const NOTIFICATIONS_PATH = 'commerce/paygate-payhost/notification';
  /**
   * {@inheritdoc}
   */
  const NOTIFICATION_WRAPPER = Notification::class;

  /**
   * {@inheritdoc}
   */
  public function getEvent() {
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function locateOrder() {
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function handle(\stdClass $order) {

  }

  /**
   * {@inheritdoc}
   */
  public function error(NotificationException $exception) {

  }

  /**
   * {@inheritdoc}
   */
  public function terminate(\Exception $exception) {

  }

  /**
   * {@inheritdoc}
   */
  public function getResponse() {
    print 'OK';
    drupal_exit();
  }

}
