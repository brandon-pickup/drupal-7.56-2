<?php

namespace Commerce\Utils\Exception;

/**
 * An exception containing several reasons of the failure.
 */
class MultiErrorException extends \RuntimeException {

  /**
   * List of error messages.
   *
   * @var string[]
   */
  private $errors = [];

  /**
   * {@inheritdoc}
   */
  public function __construct($message = '', array $errors = []) {
    parent::__construct($message, 0, NULL);

    $this->errors = $errors;
  }

  /**
   * Returns list of error messages.
   *
   * @return string[]
   *   List of error messages.
   */
  public function getErrors() {
    return $this->errors;
  }

}
