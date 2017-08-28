<?php

namespace Drupal\commerce_paygate_payhost\Payment\Composition;

/**
 * An abstraction to build compositions on top of.
 */
abstract class BaseComposition {

  /**
   * List of values, representing a composition.
   *
   * @var array
   */
  protected $data = [];

  /**
   * BaseComposition constructor.
   */
  public function __construct() {
    $this->data = $this->getDefaultValues();
  }

  /**
   * Returns a list of default values for composition.
   *
   * @return array
   *   List of default values.
   */
  abstract protected function getDefaultValues();

  /**
   * Returns a list of field names which must have a value assigned.
   *
   * @return string[]
   *   List of field names.
   */
  protected function getRequiredFields() {
    return [];
  }

  /**
   * Returns validated composition representation.
   *
   * @return array
   *   Representation of a composition.
   */
  final public function toArray() {
    foreach ($this->getRequiredFields() as $field) {
      if (empty($this->data[$field])) {
        throw new \RuntimeException(sprintf('Property "%s" is required to be set for the "%s" class.', $field, static::class));
      }
    }

    return $this->data;
  }

}
