<?php

namespace Commerce\Utils;

/**
 * Credit card validator and type detector.
 */
class CreditCard {

  /**
   * Card type definition.
   *
   * @var array[]
   */
  protected static $cards = [
    // Debit cards must come first, since they have more specific patterns
    // than their credit card equivalents.
    'visaelectron' => [
      'luhn' => TRUE,
      'pattern' => '/^4(026|17500|405|508|844|91[37])/',
      'length' => [
        'number' => [16],
        'cvc' => [3],
      ],
    ],
    'maestro' => [
      'luhn' => TRUE,
      'pattern' => '/^(5(018|0[23]|[68])|6(39|7))/',
      'length' => [
        'number' => [12, 13, 14, 15, 16, 17, 18, 19],
        'cvc' => [3],
      ],
    ],
    'forbrugsforeningen' => [
      'luhn' => TRUE,
      'pattern' => '/^600/',
      'length' => [
        'number' => [16],
        'cvc' => [3],
      ],
    ],
    'dankort' => [
      'luhn' => TRUE,
      'pattern' => '/^5019/',
      'length' => [
        'number' => [16],
        'cvc' => [3],
      ],
    ],
    // Credit cards.
    'visa' => [
      'luhn' => TRUE,
      'pattern' => '/^4/',
      'length' => [
        'number' => [13, 16],
        'cvc' => [3],
      ],
    ],
    'mastercard' => [
      'luhn' => TRUE,
      'pattern' => '/^(5[0-5]|2[2-7])/',
      'length' => [
        'number' => [16],
        'cvc' => [3],
      ],
    ],
    'amex' => [
      'luhn' => TRUE,
      'pattern' => '/^3[47]/',
      'length' => [
        'number' => [15],
        'cvc' => [3, 4],
      ],
    ],
    'dinersclub' => [
      'luhn' => TRUE,
      'pattern' => '/^3[0689]/',
      'length' => [
        'number' => [14],
        'cvc' => [3],
      ],
    ],
    'discover' => [
      'luhn' => TRUE,
      'pattern' => '/^6([045]|22)/',
      'length' => [
        'number' => [16],
        'cvc' => [3],
      ],
    ],
    'unionpay' => [
      'luhn' => FALSE,
      'pattern' => '/^(62|88)/',
      'length' => [
        'number' => [16, 17, 18, 19],
        'cvc' => [3],
      ],
    ],
    'jcb' => [
      'luhn' => TRUE,
      'pattern' => '/^35/',
      'length' => [
        'number' => [16],
        'cvc' => [3],
      ],
    ],
  ];

  /**
   * Number of a credit card.
   *
   * @var int
   */
  private $number;
  /**
   * Computed type of a credit card.
   *
   * @var string
   */
  private $type;
  /**
   * Technical information about a credit card.
   *
   * @var array
   */
  private $card;

  /**
   * CreditCard constructor.
   *
   * @param string|int $number
   *   Number of a credit card.
   */
  public function __construct($number) {
    $this->number = preg_replace('/[^0-9]/', '', $number);

    foreach (static::$cards as $type => $card) {
      if (preg_match($card['pattern'], $this->number)) {
        $this->type = $type;
        $this->card = $card;
        break;
      }
    }

    if (NULL === $this->type) {
      throw new \RuntimeException('Type of card cannot be recognized by its number.', 1);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return $this->type;
  }

  /**
   * Checks whether number of credit card valid.
   *
   * @return bool
   *   A state of check.
   */
  public function isValidNumber() {
    return $this->checkLength($this->number, 'number') && $this->isValidLuhn();
  }

  /**
   * Check whether CVC is valid for given credit card.
   *
   * @param int $cvc
   *   CVC code.
   *
   * @return bool
   *   A state of check.
   */
  public function isValidCvc($cvc) {
    return ctype_digit($cvc) && $this->checkLength($cvc, 'cvc');
  }

  /**
   * Check correctness of given credit card number.
   *
   * @return bool
   *   A state of check.
   *
   * @link https://en.wikipedia.org/wiki/Luhn_algorithm
   */
  protected function isValidLuhn() {
    if ($this->card['luhn']) {
      $checksum = 0;
      $length = strlen($this->number);

      for ($i = 2 - ($length % 2); $i <= $length; $i += 2) {
        $checksum += (int) $this->number{$i - 1};
      }

      for ($i = ($length % 2) + 1; $i < $length; $i += 2) {
        $digit = (int) $this->number{$i - 1} * 2;

        if ($digit >= 10) {
          $digit -= 9;
        }

        $checksum += $digit;
      }

      return 0 === $checksum % 10;
    }

    return TRUE;
  }

  /**
   * Checks length of credit card number of its CVC code.
   *
   * @param string $input
   *   Input data to validate.
   * @param string $type
   *   One of "cvc" or "number".
   *
   * @return bool
   *   A state of check.
   */
  protected function checkLength($input, $type) {
    return in_array(strlen($input), $this->card['length'][$type], TRUE);
  }

  /**
   * Update original data about cards.
   *
   * @param array $data
   *   Data to merge.
   */
  public static function setData(array $data) {
    static::$cards = array_merge(static::$cards, $data);
  }

}
