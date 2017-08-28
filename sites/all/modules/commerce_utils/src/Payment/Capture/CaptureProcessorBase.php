<?php

namespace Commerce\Utils\Payment\Capture;

use Commerce\Utils\OrderPayment;

/**
 * Base processor for payment captures.
 */
abstract class CaptureProcessorBase {

  use OrderPayment;

  /**
   * Stores a state whether "perform()" method was called during HTTP request.
   *
   * @var bool
   */
  private $performed = FALSE;
  /**
   * Stores a state of capturing.
   *
   * @var null|bool
   */
  private $captured;
  /**
   * Stores a message to display.
   *
   * @var string
   */
  private $message = '';

  /**
   * CaptureProcessorBase constructor.
   *
   * @param \EntityDrupalWrapper|\stdClass|string|int $order
   *   Entity wrapper of "commerce_order" entity, entity itself or entity ID.
   * @param array $payment_method
   *   An instance of the payment method.
   */
  public function __construct($order, array $payment_method) {
    $this->setOrder($order);
    $this->setPaymentMethod($payment_method);
  }

  /**
   * Performs a capture request.
   */
  final public function capture() {
    if (FALSE === $this->performed) {
      $this->perform();
      $this->performed = TRUE;
    }
  }

  /**
   * Returns a state whether "perform()" method was called during HTTP request.
   *
   * @return bool
   *   A state whether "perform()" method was called.
   */
  final public function isPerformed() {
    return $this->performed;
  }

  /**
   * Returns a state of capturing.
   *
   * @return bool|null
   *   A state of capturing or "NULL" if not set. In a case of "NULL", order
   *   save will fail by throwing an exception. Every capture processor MUST
   *   use the "setCaptured()" method inside of the "perform()" in order to
   *   indicate a state of capturing.
   *
   * @see commerce_utils_commerce_order_presave()
   */
  final public function isCaptured() {
    return $this->captured;
  }

  /**
   * Returns a message to display.
   *
   * @return string
   *   A message to display.
   */
  final public function getMessage() {
    return $this->message;
  }

  /**
   * Sets a state of capture request.
   *
   * @param bool $status
   *   A state of capture request.
   * @param string $message
   *   A message to display. Will be "warning" in a case if order's status
   *   change is delayed, "error" - if "$status" is "FALSE" and "status"
   *   otherwise.
   *
   * @see commerce_utils_commerce_order_presave()
   */
  final protected function setCaptured($status, $message) {
    $this->captured = (bool) $status;
    $this->message = $message;
  }

  /**
   * Indicates whether order's status can be changed to "completed" immediately.
   *
   * In a case of successful capture, you may want to disallow immediate order
   * status change until something will happen. For instance, capture request
   * is not synchronous and payment gateway sends a notification informing
   * about the status. If notification will tell that capture is unsuccessful
   * you'll need to REMOVE the "commerce_utils_order_completed_invoked" marker
   * from the "$order->data" in order to allow repeat action triggering.
   *
   * @example
   * @code
   * class NotificationController extends NotificationControllerBase {
   *   public function handle(\stdClass $order) {
   *     if ('CAPTURE_FAILED' === $this->data['STATUS']) {
   *       commerce_utils_allow_capture_reinitialization($order);
   *     }
   *   }
   * }
   * @endcode
   *
   * @return bool
   *   Indicator state.
   */
  abstract public function isStatusChangeDelayed();

  /**
   * Performs a capture.
   *
   * IMPORTANT! This method MUST USE the "setCaptured()" method inside in
   * order to indicate a state of capturing.
   *
   * @see commerce_utils_payment_capture()
   */
  abstract protected function perform();

}
