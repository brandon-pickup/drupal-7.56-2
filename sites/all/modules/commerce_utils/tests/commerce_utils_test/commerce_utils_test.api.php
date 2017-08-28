<?php

/**
 * @file
 * Commerce Utilities API (Test).
 *
 * All functions, prefixed by "hook_" will be scanned and added into
 * the "commerce" group.
 *
 * @see commerce_utils_hook_info()
 * @see \Drupal\commerce_utils\Tests\HookInfoTest::test()
 */

/**
 * This function MUST NOT BE added into "commerce" group.
 */
function commerce_utils_test_dummy2() {

}

/**
 * Implements hook_PAYMENT_METHOD_payment_authorisation_request_alter().
 *
 * @see hook_PAYMENT_METHOD_payment_authorisation_request_alter()
 */
function hook_payment_with_plugin_payment_authorisation_request_alter(\Drupal\commerce_utils_test\Payment\Authorisation\Request $payment, \stdClass $order, array $payment_method) {

}

/**
 * Implements hook_PAYMENT_METHOD_payment_authorisation_response_alter().
 *
 * @see hook_PAYMENT_METHOD_payment_authorisation_response_alter()
 */
function hook_payment_with_plugin_payment_authorisation_response_alter(\Drupal\commerce_utils_test\Payment\Authorisation\Response $payment, \stdClass $order, array $payment_method) {

}
