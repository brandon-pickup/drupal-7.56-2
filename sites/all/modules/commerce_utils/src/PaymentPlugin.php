<?php

namespace Commerce\Utils;

use Commerce\Utils\Payment\Capture\CaptureProcessorBase;
use Commerce\Utils\Payment\Authorisation\RequestBase;
use Commerce\Utils\Payment\Authorisation\ResponseBase;
use Commerce\Utils\Payment\Form\RedirectFormBase;
use Commerce\Utils\Payment\Form\SettingsFormBase;
use Commerce\Utils\Payment\Form\SubmitFormBase;
use Drupal\commerce_payment_watcher\Entity\PaymentWatcherEntity;

/**
 * Payment plugin builder.
 */
final class PaymentPlugin {

  /**
   * FQN of class, extending "SettingsFormBase".
   *
   * @var string
   */
  private $settingsFormClass;
  /**
   * FQN of class, extending "SubmitFormBase".
   *
   * @var string
   */
  private $submitFormClass;
  /**
   * FQN of class, extending "RedirectFormBase".
   *
   * @var string
   */
  private $redirectFormClass;
  /**
   * FQN of class, extending "RequestBase".
   *
   * @var string
   */
  private $authorisationRequestClass;
  /**
   * FQN of class, extending "ResponseBase".
   *
   * @var string
   */
  private $authorisationResponseClass;
  /**
   * FQN of class, extending "Transaction".
   *
   * @var string
   */
  private $paymentTransactionClass;
  /**
   * FQN of class, extending "Transaction".
   *
   * @var string
   */
  private $refundTransactionClass;
  /**
   * FQN of class, extending "NotificationControllerBase".
   *
   * @var string
   */
  private $notificationsControllerClass;
  /**
   * FQN of class, extending "PaymentWatcherEntity".
   *
   * @var string
   */
  private $paymentWatcherEntityClass;
  /**
   * FQN of class, extending "CaptureProcessorBase".
   *
   * @var string
   */
  private $paymentCaptureProcessorClass;

  /**
   * List of optional properties, which might be empty on compilation.
   */
  const OPTIONAL = [
    'notificationsControllerClass',
    'paymentCaptureProcessorClass',
    'paymentWatcherEntityClass',
  ];

  /**
   * {@inheritdoc}
   */
  public function setSettingsFormClass($class) {
    self::validateClass($class, SettingsFormBase::class);
    $this->settingsFormClass = $class;

    return $this;
  }

  /**
   * {@inheritdoc}
   *
   * @return string|\Commerce\Utils\Payment\Form\SettingsFormBase
   *   Fully-qualified class path.
   */
  public function getSettingsFormClass() {
    return $this->settingsFormClass;
  }

  /**
   * {@inheritdoc}
   */
  public function setSubmitFormClass($class) {
    self::validateClass($class, SubmitFormBase::class);
    $this->submitFormClass = $class;

    return $this;
  }

  /**
   * {@inheritdoc}
   *
   * @return string|\Commerce\Utils\Payment\Form\SubmitFormBase
   *   Fully-qualified class path.
   */
  public function getSubmitFormClass() {
    return $this->submitFormClass;
  }

  /**
   * {@inheritdoc}
   */
  public function setRedirectFormClass($class) {
    self::validateClass($class, RedirectFormBase::class);
    $this->redirectFormClass = $class;

    return $this;
  }

  /**
   * {@inheritdoc}
   *
   * @return string|\Commerce\Utils\Payment\Form\RedirectFormBase
   *   Fully-qualified class path.
   */
  public function getRedirectFormClass() {
    return $this->redirectFormClass;
  }

  /**
   * {@inheritdoc}
   */
  public function setAuthorisationRequestClass($class) {
    self::validateClass($class, RequestBase::class);
    $this->authorisationRequestClass = $class;

    return $this;
  }

  /**
   * {@inheritdoc}
   *
   * @return string|\Commerce\Utils\Payment\Authorisation\RequestBase
   *   Fully-qualified class path.
   */
  public function getAuthorisationRequestClass() {
    return $this->authorisationRequestClass;
  }

  /**
   * {@inheritdoc}
   */
  public function setAuthorisationResponseClass($class) {
    self::validateClass($class, ResponseBase::class);
    $this->authorisationResponseClass = $class;

    return $this;
  }

  /**
   * {@inheritdoc}
   *
   * @return string|\Commerce\Utils\Payment\Authorisation\ResponseBase
   *   Fully-qualified class path.
   */
  public function getAuthorisationResponseClass() {
    return $this->authorisationResponseClass;
  }

  /**
   * {@inheritdoc}
   */
  public function setPaymentTransactionClass($class) {
    self::validateClass($class, Transaction::class);
    $this->paymentTransactionClass = $class;

    return $this;
  }

  /**
   * {@inheritdoc}
   *
   * @return string|\Commerce\Utils\Transaction
   *   Fully-qualified class path.
   */
  public function getPaymentTransactionClass() {
    return $this->paymentTransactionClass;
  }

  /**
   * {@inheritdoc}
   */
  public function setRefundTransactionClass($class) {
    self::validateClass($class, Transaction::class);
    $this->refundTransactionClass = $class;

    return $this;
  }

  /**
   * {@inheritdoc}
   *
   * @return string|\Commerce\Utils\Transaction
   *   Fully-qualified class path.
   */
  public function getRefundTransactionClass() {
    return $this->refundTransactionClass;
  }

  /**
   * {@inheritdoc}
   *
   * NOTE: Only in a case, if payment gateway supports this type of information
   * distribution. Also, fully-qualified URL (including domain) should be
   * configured on third-party gateway itself.
   */
  public function setNotificationsController($class) {
    self::validateClass($class, NotificationControllerBase::class);

    if (empty($class::NOTIFICATIONS_PATH)) {
      throw new \InvalidArgumentException(sprintf('The "NOTIFICATIONS_PATH" constant of "%s" must contain system path to receive notifications on.', $class));
    }

    $this->notificationsControllerClass = $class;

    return $this;
  }

  /**
   * {@inheritdoc}
   *
   * @return string|\Commerce\Utils\NotificationControllerBase
   *   Fully-qualified class path.
   */
  public function getNotificationsControllerClass() {
    return $this->notificationsControllerClass;
  }

  /**
   * {@inheritdoc}
   */
  public function setPaymentWatcherEntityClass($class) {
    $dependency = 'commerce_payment_watcher';

    if (!module_exists($dependency) && (!module_enable([$dependency]) || !class_exists(PaymentWatcherEntity::class))) {
      throw new \RuntimeException(sprintf('You must have enabled the "%s" module first.', $dependency));
    }

    self::validateClass($class, PaymentWatcherEntity::class);
    $this->paymentWatcherEntityClass = $class;

    return $this;
  }

  /**
   * {@inheritdoc}
   *
   * @return string|\Drupal\commerce_payment_watcher\Entity\PaymentWatcherEntity
   *   Fully-qualified class path.
   */
  public function getPaymentWatcherEntityClass() {
    return $this->paymentWatcherEntityClass;
  }

  /**
   * {@inheritdoc}
   */
  public function setPaymentCaptureProcessor($class) {
    self::validateClass($class, CaptureProcessorBase::class);
    $this->paymentCaptureProcessorClass = $class;

    return $this;
  }

  /**
   * {@inheritdoc}
   *
   * @return string|\Commerce\Utils\Payment\Capture\CaptureProcessorBase
   *   Fully-qualified class path.
   */
  public function getPaymentCaptureProcessor() {
    return $this->paymentCaptureProcessorClass;
  }

  /**
   * Compile payment plugin.
   */
  public function compile() {
    foreach ($this as $property => $value) {
      if (NULL === $value && !in_array($property, static::OPTIONAL)) {
        throw new \RuntimeException(sprintf('"%s" is not set.', $property));
      }
    }

    return $this;
  }

  /**
   * Ensures that class exists and extends a base one.
   *
   * @param string $class
   *   Fully-qualified namespace of class.
   * @param string $base
   *   Fully-qualified namespace of base class.
   */
  private static function validateClass($class, $base) {
    if (!class_exists($class)) {
      throw new \InvalidArgumentException(sprintf('Class "%s" not found.', $class));
    }

    if (!is_subclass_of($class, $base)) {
      throw new \InvalidArgumentException(sprintf('Class "%s" must be subclass or implement "%s".', $class, $base));
    }

    if (!(new \ReflectionClass($class))->isInstantiable()) {
      throw new \InvalidArgumentException(sprintf('Class "%s" must be instantiable.', $class));
    }
  }

}
