<?php

/**
 * @file
 * Elysia Cron integration.
 */

/**
 * Check statuses of all pending payments.
 *
 * @param string $payment_method_id
 *   An ID of payment method.
 * @param string $payment_method_instance_id
 *   An ID of payment method instance.
 *
 * @see commerce_payment_watcher_cronapi()
 *
 * @internal
 */
function commerce_payment_watcher_check_payment_statuses($payment_method_id, $payment_method_instance_id) {
  $payment_method = commerce_payment_method_instance_load($payment_method_instance_id);

  if (!empty($payment_method['settings']['payment_watcher'])) {
    /* @var \Drupal\commerce_payment_watcher\Entity\PaymentWatcherEntity $watcher */
    foreach (entity_load(COMMERCE_PAYMENT_WATCHER_ENTITY_TYPE, FALSE, ['payment_method_id' => $payment_method_id]) as $watcher) {
      $minutes_since_modification = $watcher->getMinutesSinceModification();
      $minutes_before_next_check = commerce_payment_watcher_calculate_next_status_check_gap($payment_method['settings'], $watcher->authorisation_checks);

      try {
        if ($watcher->getDaysSinceCreation() >= $payment_method['settings']['payment_watcher']['days_before_release']) {
          $watcher->delete();
        }
        elseif (
          // No attempts have been made. Do first one, to update time of
          // modification and thereby allow further checks.
          (0 === $minutes_since_modification && $watcher->getMinutesSinceCreation() >= $minutes_before_next_check) ||
          // An allowable amount of time has passed.
          $minutes_since_modification >= $minutes_before_next_check
        ) {
          $watcher->checkPaymentStatus();
        }
      }
      catch (\Exception $e) {
        watchdog_exception($payment_method_id, $e);
      }
    }
  }
}
