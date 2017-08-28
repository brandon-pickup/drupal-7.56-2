<?php

namespace Drupal\commerce_utils\Tests;

/**
 * Tests the "hook_hook_info()".
 */
class HookInfoTest extends UnitTest {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return static::info('Tests the "hook_hook_info()".');
  }

  /**
   * {@inheritdoc}
   */
  public static function dependencies() {
    return ['commerce_utils_test'];
  }

  /**
   * Test.
   */
  public function test() {
    $actual = commerce_utils_hook_info();
    $expected = [
      'payment_with_plugin_payment_authorisation_request_alter' => [
        'group' => 'commerce',
      ],
      'payment_with_plugin_payment_authorisation_response_alter' => [
        'group' => 'commerce',
      ],
      'commerce_payment_capture_rule_alter' => [
        'group' => 'rules_defaults',
      ],
      'commerce_payment_capture_occurred' => [
        'group' => 'commerce',
      ],
    ];

    ksort($actual);
    ksort($expected);

    $this->assertTrue($actual === $expected, 'All hooks scanned successfully.');
  }

}
