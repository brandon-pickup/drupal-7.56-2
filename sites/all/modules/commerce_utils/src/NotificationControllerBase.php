<?php

namespace Commerce\Utils;

use Commerce\Utils\Payment\Exception\NotificationException;

/**
 * Base controller for handling notifications.
 *
 * @see commerce_utils_notification()
 */
abstract class NotificationControllerBase {

  /**
   * System path to send notifications to.
   */
  const NOTIFICATIONS_PATH = '';
  /**
   * A path to class to wrap incoming notification data.
   *
   * @see \Commerce\Utils\NotificationInterface
   */
  const NOTIFICATION_WRAPPER = '';

  /**
   * An ID of payment method.
   *
   * @var string
   */
  protected $paymentMethodId;
  /**
   * Data, that came in $_POST.
   *
   * @var \stdClass|\Commerce\Utils\NotificationInterface
   */
  protected $data;

  /**
   * NotificationControllerBase constructor.
   *
   * IMPORTANT! Do not throw any exceptions inside of constructor!
   *
   * @param string $payment_method_id
   *   An ID of payment method.
   */
  public function __construct($payment_method_id) {
    $this->paymentMethodId = $payment_method_id;
  }

  /**
   * Returns notification event.
   *
   * @return string
   *   Notification event.
   *
   * @throws \Exception
   *   If no event computed.
   */
  abstract public function getEvent();

  /**
   * Returns order number.
   *
   * @return string
   *   Order number, extracted from notification's data.
   */
  abstract public function locateOrder();

  /**
   * Process inbound data.
   *
   * @param \stdClass $order
   *   Commerce order, notification sent for.
   */
  abstract public function handle(\stdClass $order);

  /**
   * Reacts on critical error during notification handling.
   *
   * @param \Commerce\Utils\Payment\Exception\NotificationException $exception
   *   Thrown exception.
   */
  abstract public function error(NotificationException $exception);

  /**
   * Reacts on unexpected termination during notification handling.
   *
   * @param \Exception $exception
   *   Thrown exception.
   */
  abstract public function terminate(\Exception $exception);

  /**
   * Returns response.
   *
   * Usually, payment gateways just expect some plain string as response. If
   * so, "print" it and execute "drupal_exit()".
   *
   * @return int|string|array
   *   Handler response, acceptable by page delivery callback.
   *
   * @see drupal_deliver_page()
   */
  abstract public function getResponse();

  /**
   * Set inbound data.
   *
   * @param \stdClass $data
   *   Data, that came in $_POST.
   */
  public function setData(\stdClass $data) {
    $class = static::NOTIFICATION_WRAPPER;

    if (!empty($class) && class_exists($class) && is_subclass_of($class, NotificationInterface::class)) {
      $data = new $class($data);
    }

    $this->data = $data;
  }

  /**
   * Returns processed notification data.
   *
   * @return \stdClass
   *   Notification data.
   */
  public function getData() {
    return $this->data;
  }

  /**
   * Logs an exception.
   *
   * @param \Exception|null $exception
   *   An exception to store in log.
   */
  public function log(\Exception $exception = NULL) {
    $message = "Notification has been received. <pre>@data</pre>";
    $arguments = [
      '@data' => static::dump($this->data),
    ];

    if (NULL !== $exception) {
      $message = 'Notification has been handled' . ($exception instanceof NotificationException ? ' wrongly' : '') . ' and exception been thrown: <pre>@exception</pre><pre>@data</pre>';
      $arguments['@exception'] = static::dump($exception);
    }

    watchdog($this->paymentMethodId, $message, $arguments);
  }

  /**
   * Dumps any data.
   *
   * @param mixed $variable
   *   Variable to dump.
   *
   * @return string
   *   String representation of dumped data.
   */
  public static function dump($variable) {
    // Use bufferisation to prevent warnings about recursion.
    ob_start();
    var_dump($variable);

    return ob_get_clean();
  }

}
