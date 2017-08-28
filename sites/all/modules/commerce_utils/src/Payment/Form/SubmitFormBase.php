<?php

namespace Commerce\Utils\Payment\Form;

use Commerce\Utils\OrderPayment;

/**
 * Base controller of payment submission.
 *
 * Redirect form come in action once this form will be submitted (if exists).
 */
abstract class SubmitFormBase {

  use OrderPayment;

  /**
   * SubmitFormBase constructor.
   *
   * @param \stdClass $order
   *   Commerce order.
   * @param array $payment_method
   *   Payment method information.
   */
  public function __construct(\stdClass $order, array $payment_method) {
    $this->setOrder($order);
    $this->setPaymentMethod($payment_method);
  }

  /**
   * Form builder.
   *
   * @param array $form
   *   Base elements of the given form.
   * @param array $values
   *   Stored values (after AJAX submission or coming back to checkout step).
   * @param array $checkout_pane
   *   Definition of checkout pane.
   *
   * @see commerce_utils_submit_form()
   */
  public function form(array &$form, array &$values, array &$checkout_pane) {

  }

  /**
   * Form validation.
   *
   * Errors are reported by throwing exceptions.
   *
   * @param array $form
   *   Built form which have been submitted.
   * @param array $values
   *   Submitted input of form elements.
   *
   * @see commerce_utils_submit_form_validate()
   */
  public function validate(array $form, array &$values) {

  }

  /**
   * Store the values of form.
   *
   * Errors are reported by throwing exceptions.
   *
   * @param array $form
   *   Built form which have been submitted.
   * @param array $values
   *   Submitted input of form elements.
   * @param array $balance
   *   Order balance.
   *
   * @see commerce_utils_submit_form_submit()
   */
  public function submit(array $form, array &$values, array &$balance) {

  }

}
