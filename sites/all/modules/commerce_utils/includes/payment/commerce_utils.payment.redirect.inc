<?php

/**
 * @file
 * Payment redirect.
 */

use Commerce\Utils\Exception\MultiErrorException;

/**
 * Implements PAYMENT_METHOD_BASE_redirect_form().
 *
 * {@inheritdoc}
 *
 * @see commerce_utils_commerce_payment_method_info_alter()
 * @see commerce_payment_redirect_pane_checkout_form()
 *
 * @internal
 */
function commerce_utils_redirect_form(array $form, array &$form_state, \stdClass $order, array $payment_method) {
  try {
    $plugin = commerce_utils_get_payment_plugin($payment_method);
    $request_class = $plugin->getAuthorisationRequestClass();
    $redirect_form_class = $plugin->getRedirectFormClass();

    /* @var \Commerce\Utils\Payment\Authorisation\RequestBase $request */
    $request = new $request_class($order, $payment_method);
    /* @var \Commerce\Utils\Payment\Form\RedirectFormBase $redirect_form */
    $redirect_form = new $redirect_form_class();
    $redirect_form->form($request, $form, $form_state);

    drupal_alter($payment_method['method_id'] . '_payment_authorisation_request', $request, $order, $payment_method);
    // Sign the request to verify it later.
    $request->signRequest();

    foreach ($request as $name => $value) {
      $form[$name] = [
        '#type' => 'hidden',
        '#value' => $value,
      ];
    }

    $form['#action'] = $request->getEndpoint();
    $form['#payment'] = $request;
    /* @see commerce_utils_redirect_form_pre_render() */
    $form['#pre_render'][] = 'commerce_utils_redirect_form_pre_render';
  }
  catch (\Exception $e) {
    watchdog_exception($payment_method['method_id'], $e);
    commerce_utils_convert_exception_to_messages($e);
    // We don't want to infinitely do an unsuccessful redirect so changing
    // review step to previous one.
    commerce_payment_redirect_pane_previous_page($order);
  }

  return $form;
}

/**
 * Implements PAYMENT_METHOD_BASE_redirect_form_back().
 *
 * {@inheritdoc}
 *
 * @see commerce_utils_commerce_payment_method_info_alter()
 * @see commerce_payment_redirect_pane_checkout_form()
 *
 * @internal
 */
function commerce_utils_redirect_form_back(\stdClass $order, array $payment_method) {
  try {
    $plugin = commerce_utils_get_payment_plugin($payment_method);
    $response_class = $plugin->getAuthorisationResponseClass();
    $redirect_form_class = $plugin->getRedirectFormClass();

    /* @var \Commerce\Utils\Payment\Authorisation\ResponseBase $response */
    $response = new $response_class(commerce_utils_get_transaction_instance($payment_method, 'payment', $order), $payment_method);
    /* @var \Commerce\Utils\Payment\Form\RedirectFormBase $redirect_form */
    $redirect_form = new $redirect_form_class();
    $redirect_form->setResponse($response);
    $redirect_form->cancel();
  }
  catch (\Exception $e) {
    watchdog_exception($payment_method['method_id'], $e);
    commerce_utils_convert_exception_to_messages($e);
  }
}

/**
 * Implements PAYMENT_METHOD_BASE_redirect_form_validate().
 *
 * {@inheritdoc}
 *
 * @see commerce_utils_commerce_payment_method_info_alter()
 * @see commerce_payment_redirect_pane_checkout_form()
 *
 * @internal
 */
function commerce_utils_redirect_form_validate(\stdClass $order, array $payment_method) {
  // GET request should be here.
  try {
    $plugin = commerce_utils_get_payment_plugin($payment_method);
    $response_class = $plugin->getAuthorisationResponseClass();
    $redirect_form_class = $plugin->getRedirectFormClass();

    /* @var \Commerce\Utils\Payment\Authorisation\ResponseBase $response */
    $response = new $response_class(commerce_utils_get_transaction_instance($payment_method, 'payment', $order), $payment_method);
    /* @var \Commerce\Utils\Payment\Form\RedirectFormBase $redirect_form */
    $redirect_form = new $redirect_form_class();
    $redirect_form->setResponse($response);
    $redirect_form->response();

    drupal_alter($payment_method['method_id'] . '_payment_authorisation_response', $response, $order, $payment_method);
  }
  catch (\Exception $e) {
    watchdog_exception($payment_method['method_id'], $e);
    commerce_utils_convert_exception_to_messages($e);
    // If we exit we're failed, then customer cannot proceed to the next page.
    commerce_payment_redirect_pane_previous_page($order);

    // We should return boolean indicator since this is unusual form validation.
    return FALSE;
  }

  return TRUE;
}

/**
 * Implements PAYMENT_METHOD_BASE_redirect_form_submit().
 *
 * {@inheritdoc}
 *
 * @see commerce_utils_commerce_payment_method_info_alter()
 * @see commerce_payment_redirect_pane_checkout_form()
 *
 * @internal
 */
function commerce_utils_redirect_form_submit(\stdClass $order, array $payment_method) {
}

/**
 * Convert a thrown exception to system errors/warnings.
 *
 * @param \Exception $e
 *   Thrown exception.
 *
 * @internal
 */
function commerce_utils_convert_exception_to_messages(\Exception $e) {
  // For exceptions of type "\Exception" will be used "warning"
  // status of a message. In all other cases - "error".
  $type = is_subclass_of($e, \Exception::class) ? 'error' : 'warning';
  $errors = [];

  /* @var \Commerce\Utils\Exception\MultiErrorException $e */
  if (is_a($e, MultiErrorException::class)) {
    $errors = $e->getErrors();
  }

  array_unshift($errors, $e->getMessage());

  foreach ($errors as $error) {
    drupal_set_message($error, $type);
  }
}
