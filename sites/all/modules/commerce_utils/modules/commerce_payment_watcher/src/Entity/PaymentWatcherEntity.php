<?php

namespace Drupal\commerce_payment_watcher\Entity;

/**
 * Payment watcher entity.
 *
 * @property-read $id
 * @property-read $order_id
 * @property-read $created
 * @property-read $changed
 * @property-read $remote_id
 * @property-read $payment_method_id
 * @property-read $authorisation_checks
 *
 * @see commerce_payment_watcher_schema()
 */
abstract class PaymentWatcherEntity extends \Entity {

  const ENTITY_TYPE = COMMERCE_PAYMENT_WATCHER_ENTITY_TYPE;
  const READONLY = [
    'id',
    'order_id',
    'created',
    'changed',
    'remote_id',
    'payment_method_id',
    'authorisation_checks',
  ];

  /**
   * {@inheritdoc}
   *
   * @var int
   */
  public $id;
  /**
   * {@inheritdoc}
   *
   * @var int
   */
  // @codingStandardsIgnoreStart
  public $order_id;
  // @codingStandardsIgnoreEnd
  /**
   * {@inheritdoc}
   *
   * @var int
   */
  public $created = REQUEST_TIME;
  /**
   * {@inheritdoc}
   *
   * @var int
   */
  public $changed = REQUEST_TIME;
  /**
   * {@inheritdoc}
   *
   * @var string
   */
  // @codingStandardsIgnoreStart
  public $remote_id = '';
  // @codingStandardsIgnoreEnd
  /**
   * {@inheritdoc}
   *
   * @var string
   */
  // @codingStandardsIgnoreStart
  public $payment_method_id = '';
  // @codingStandardsIgnoreEnd
  /**
   * {@inheritdoc}
   *
   * @var int
   */
  // @codingStandardsIgnoreStart
  public $authorisation_checks = 0;
  // @codingStandardsIgnoreEnd
  /**
   * An associative array of properties and their default values.
   *
   * @var array
   */
  private $originalValues = [];
  /**
   * Commerce order.
   *
   * @var \stdClass
   */
  protected $order;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $values = []) {
    parent::__construct($values, static::ENTITY_TYPE);

    // Store original values of readonly properties.
    foreach (static::READONLY as $property) {
      if (property_exists($this, $property)) {
        $this->originalValues[$property] = $this->{$property};
      }
    }

    // Watcher cannot live without an order.
    /* @see commerce_payment_watcher_commerce_order_delete() */
    $this->order = entity_load_single('commerce_order', $this->order_id);
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    return entity_label('commerce_order', $this->order) . ' (' . $this->order->mail . ')';
  }

  /**
   * {@inheritdoc}
   */
  public function uri() {
    // Payment watcher has no view, but it represents an order it belongs to.
    return entity_uri('commerce_order', $this->order);
  }

  /**
   * Checks whether entity is new.
   *
   * @return bool
   *   A state of check.
   */
  public function isNew() {
    return NULL === $this->id;
  }

  /**
   * Checks payment status, creates a transaction and dies.
   */
  abstract public function checkPaymentStatus();

  /**
   * {@inheritdoc}
   */
  public function save() {
    foreach ($this->originalValues as $property => $value) {
      if ($value !== $this->{$property}) {
        throw new \RuntimeException(sprintf('Property "%s" of "%s" is readonly.', $property, static::class));
      }
    }

    $this->changed = REQUEST_TIME;

    parent::save();
  }

  /**
   * Save authorisation attempt.
   */
  protected function saveAttempt() {
    $this->originalValues['authorisation_checks'] = ++$this->authorisation_checks;

    $this->save();
  }

  /**
   * Set remote ID of a payment.
   *
   * @param string $remote_id
   *   Payment remote ID.
   */
  public function setRemoteId($remote_id) {
    $this->originalValues['remote_id'] = $this->remote_id = $remote_id;
  }

  /**
   * Returns number of days since creation of this watcher.
   *
   * @return float|int
   *   Days since creation.
   */
  public function getDaysSinceCreation() {
    return abs(REQUEST_TIME - $this->created) / 86400;
  }

  /**
   * Returns number of minutes since creation of this watcher.
   *
   * @return float|int
   *   Minutes since creation.
   */
  public function getMinutesSinceCreation() {
    return abs(REQUEST_TIME - $this->created) / 60;
  }

  /**
   * Returns number of minutes since last modification of this watcher.
   *
   * @return float|int
   *   Minutes since last modification.
   */
  public function getMinutesSinceModification() {
    return abs($this->changed - $this->created) / 60;
  }

  /**
   * Create payment status watcher for given order.
   *
   * @param \stdClass $order
   *   Commerce order.
   * @param string $payment_method_id
   *   An ID of payment method.
   * @param string $remote_id
   *   Payment remote ID.
   */
  public static function createForOrder(\stdClass $order, $payment_method_id, $remote_id = NULL) {
    $watcher = static::loadForOrder($order, $payment_method_id);

    // Save a watcher if it's new one or if its remote ID differs from new one.
    if ($watcher->isNew() || (NULL !== $remote_id && $watcher->remote_id !== $remote_id)) {
      $watcher->setRemoteId($remote_id);
      $watcher->save();
    }
  }

  /**
   * Delete payment status watcher from given order.
   *
   * @param \stdClass $order
   *   Commerce order.
   * @param string $payment_method_id
   *   An ID of payment method.
   */
  public static function deleteForOrder(\stdClass $order, $payment_method_id) {
    $watcher = static::loadForOrder($order, $payment_method_id);

    if (!$watcher->isNew()) {
      $watcher->delete();
    }
  }

  /**
   * Load or create a new instance of payment status watcher for given order.
   *
   * @param \stdClass $order
   *   Commerce order.
   * @param string $payment_method_id
   *   An ID of payment method.
   *
   * @return static
   *   An instance of watcher.
   */
  public static function loadForOrder(\stdClass $order, $payment_method_id) {
    $values = [
      'order_id' => $order->order_id,
      'payment_method_id' => $payment_method_id,
    ];

    $watchers = entity_load(static::ENTITY_TYPE, FALSE, $values);

    return empty($watchers) ? new static($values) : reset($watchers);
  }

  /**
   * Saves attempt and returns an exception.
   *
   * @return \RuntimeException
   *   Error exception.
   */
  protected function paymentNotFound() {
    $this->saveAttempt();

    return new \RuntimeException(t('Payment for "@label" not found on gateway.', [
      '@label' => $this->label(),
    ]));
  }

}
