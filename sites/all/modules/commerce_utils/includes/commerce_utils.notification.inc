<?php

/**
 * @file
 * Notifications receiver.
 */

use Commerce\Utils\Payment\Exception\NotificationException;

/**
 * Receive notifications and distribute them to providers.
 *
 * @param string $controller
 *   FQN of class, extending "NotificationControllerBase".
 * @param string $payment_method_id
 *   An ID of payment method.
 *
 * @return mixed
 *   Data, produced by controller.
 *
 * @internal
 * @see commerce_utils_menu()
 */
function commerce_utils_notification($controller, $payment_method_id) {
  /* @var \Commerce\Utils\NotificationControllerBase $controller */
  $controller = new $controller($payment_method_id);

  try {
    if (empty($_POST)) {
      throw new \RuntimeException('Empty notification.');
    }

    $controller->setData((object) $_POST);

    // Try to get event here to allow fail early.
    $event_name = $controller->getEvent();
    $order_number = $controller->locateOrder();

    if (empty($order_number)) {
      throw new \RuntimeException('Cannot extract order number from notification.');
    }

    $order = commerce_order_load_by_number($order_number);

    if (empty($order)) {
      throw new \RuntimeException('Cannot locate an order, based on data from notification.');
    }

    $controller->handle($order);
    $controller->log();

    // Notify subscribed modules about notification.
    module_invoke_all("{$payment_method_id}_notification", strtoupper($event_name), $order, $controller->getData());
  }
  // Something went terribly wrong.
  catch (NotificationException $e) {
    $controller->log($e);
    $controller->error($e);

    drupal_exit();
  }
  // All other exceptions does not mean that we didn't handle notification.
  catch (\Exception $e) {
    $controller->log($e);
    $controller->terminate($e);
  }

  return $controller->getResponse();
}
