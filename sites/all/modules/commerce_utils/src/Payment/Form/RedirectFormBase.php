<?php

namespace Commerce\Utils\Payment\Form;

use Commerce\Utils\Payment\Authorisation\RequestBase;
use Commerce\Utils\Payment\Authorisation\ResponseBase;

/**
 * Base controller of redirection to gateway.
 */
abstract class RedirectFormBase {

  /**
   * Response from gateway.
   *
   * @var \Commerce\Utils\Payment\Authorisation\ResponseBase
   */
  private $response;

  /**
   * Form builder.
   *
   * @param \Commerce\Utils\Payment\Authorisation\RequestBase $request
   *   Authorisation request.
   * @param array $form
   *   Elements to POST to gateway. Usually there are "hidden" fields.
   * @param array $form_state
   *   A state of form.
   *
   * @see commerce_utils_redirect_form()
   */
  public function form(RequestBase $request, array &$form, array &$form_state) {
  }

  /**
   * Handler for cases when payment was declined on gateway.
   *
   * @see commerce_utils_redirect_form_back()
   */
  public function cancel() {

  }

  /**
   * Handler for cases when payment was successfully finished on gateway.
   *
   * @see commerce_utils_redirect_form_validate()
   */
  public function response() {
  }

  /**
   * Set response from gateway.
   *
   * @param \Commerce\Utils\Payment\Authorisation\ResponseBase $response
   *   Response from gateway.
   */
  public function setResponse(ResponseBase $response) {
    $this->response = $response;
  }

  /**
   * Returns response from gateway.
   *
   * @return \Commerce\Utils\Payment\Authorisation\ResponseBase|null
   *   Response from gateway or NULL if called in "from" method.
   */
  public function getResponse() {
    return $this->response;
  }

}
