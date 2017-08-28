<?php

/**
 * @file
 * Payment submit.
 */

/**
 * Implements PAYMENT_METHOD_BASE_submit_form().
 *
 * {@inheritdoc}
 *
 * @see commerce_utils_commerce_payment_method_info_alter()
 * @see commerce_payment_pane_checkout_form()
 *
 * @internal
 */
function commerce_utils_submit_form(array $payment_method, array &$pane_values, array $checkout_pane, \stdClass $order) {
  $pane_form = [];

  try {
    $submit_form_class = commerce_utils_get_payment_plugin($payment_method)
      ->getSubmitFormClass();

    /* @var \Commerce\Utils\Payment\Form\SubmitFormBase $submit_form */
    $submit_form = new $submit_form_class($order, $payment_method);
    $submit_form->form($pane_form, $pane_values, $checkout_pane);
  }
  catch (\Exception $e) {
    drupal_set_message($e->getMessage(), 'error');
  }

  return $pane_form;
}

/**
 * Implements PAYMENT_METHOD_BASE_submit_form_validate().
 *
 * {@inheritdoc}
 *
 * @see commerce_utils_commerce_payment_method_info_alter()
 * @see commerce_payment_pane_checkout_form_validate()
 *
 * @internal
 */
function commerce_utils_submit_form_validate(array $payment_method, array &$pane_form, array &$pane_values, \stdClass $order) {
  $validation_passed = TRUE;

  try {
    $submit_form_class = commerce_utils_get_payment_plugin($payment_method)
      ->getSubmitFormClass();

    /* @var \Commerce\Utils\Payment\Form\SubmitFormBase $submit_form */
    $submit_form = new $submit_form_class($order, $payment_method);
    $submit_form->validate($pane_form, $pane_values);
  }
  catch (\Exception $e) {
    drupal_set_message($e->getMessage(), 'error');
    $validation_passed = FALSE;
  }

  return $validation_passed;
}

/**
 * Implements PAYMENT_METHOD_BASE_submit_form_submit().
 *
 * {@inheritdoc}
 *
 * @see commerce_utils_commerce_payment_method_info_alter()
 * @see commerce_payment_pane_checkout_form_submit()
 *
 * @internal
 */
function commerce_utils_submit_form_submit(array $payment_method, array $pane_form, array $pane_values, \stdClass $order, array $balance) {
  $validation_passed = TRUE;

  try {
    $submit_form_class = commerce_utils_get_payment_plugin($payment_method)
      ->getSubmitFormClass();

    /* @var \Commerce\Utils\Payment\Form\SubmitFormBase $submit_form */
    $submit_form = new $submit_form_class($order, $payment_method);
    $submit_form->submit($pane_form, $pane_values, $balance);
  }
  catch (\Exception $e) {
    drupal_set_message($e->getMessage(), 'error');
    $validation_passed = FALSE;
  }

  return $validation_passed;
}
