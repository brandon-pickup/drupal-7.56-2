<?php

namespace Drupal\commerce_utils\Tests;

use Commerce\Utils\CreditCard;

/**
 * Tests credit card validator.
 */
class CreditCardTest extends UnitTest {

  /**
   * Testing data.
   */
  const VALID_CARDS = [
    // Debit cards.
    'visaelectron' => [
      '4917300800000000',
    ],
    'maestro' => [
      '6759649826438453',
      '6799990100000000019',
    ],
    'forbrugsforeningen' => [
      '6007220000000004',
    ],
    'dankort' => [
      '5019717010103742',
    ],
    // Credit cards.
    'visa' => [
      '4111111111111111',
      '4012888888881881',
      '4222222222222',
      '4462030000000000',
      '4484070000000000',
    ],
    'mastercard' => [
      '5555555555554444',
      '5454545454545454',
      '2221000002222221',
    ],
    'amex' => [
      '378282246310005',
      '371449635398431',
      // American Express Corporate.
      '378734493671000',
    ],
    'dinersclub' => [
      '30569309025904',
      '38520000023237',
      '36700102000000',
      '36148900647913',
    ],
    'discover' => [
      '6011111111111117',
      '6011000990139424',
    ],
    'unionpay' => [
      '6271136264806203568',
      '6236265930072952775',
      '6204679475679144515',
      '6216657720782466507',
    ],
    'jcb' => [
      '3530111333300000',
      '3566002020360505',
    ],
  ];

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return static::info('Tests a credit card component.');
  }

  /**
   * Visa, MasterCard, AMEX, DinersClub, Discover and JCB.
   * @link https://www.paypalobjects.com/en_US/vhelp/paypalmanager_help/credit_card_numbers.htm
   *
   * VisaElectron, Maestro and Dankort.
   * @link http://support.worldpay.com/support/kb/bg/testandgolive/tgl5103.html
   */
  public function test() {
    foreach (static::VALID_CARDS as $type => $numbers) {
      foreach ($numbers as $number) {
        $card = new CreditCard($number);

        $this->assertTrue($card->getType() === $type, sprintf('The "%s" type of card has been recognized.', $type));
        $this->assertTrue($card->isValidNumber(), sprintf('The "%s" number of card is valid.', $number));

        $card = new CreditCard(substr($number, 0, 4));
        $this->assertTrue($card->getType() === $type, sprintf('The "%s" type of card has been recognized by only 4 first digits.', $type));
        $this->assertFalse($card->isValidNumber(), 'Card with 4 digits cannot be valid.');
      }
    }
  }

}
