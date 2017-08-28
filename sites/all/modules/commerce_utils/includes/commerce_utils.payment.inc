<?php

/**
 * @file
 * Payment callbacks.
 */

require_once __DIR__ . '/payment/commerce_utils.payment.settings.inc';
require_once __DIR__ . '/payment/commerce_utils.payment.submit.inc';
require_once __DIR__ . '/payment/commerce_utils.payment.redirect.inc';

/**
 * Pre-render callback.
 *
 * @internal
 * @see commerce_utils_redirect_form()
 */
function commerce_utils_redirect_form_pre_render(array $form) {
  // Remove Drupal fields from a form (such as "form_token", "form_id" etc).
  // This needs to be done since gateways may generate signature using submitted
  // data and will expect only those fields which described in API. Any other
  // data will lead to wrong signature of payment request.
  foreach (array_diff(element_children($form), array_keys(iterator_to_array($form['#payment']))) as $name) {
    unset($form[$name]);
  }

  return $form;
}
