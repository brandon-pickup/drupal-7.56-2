<?php

/**
 * @file
 * Entity API integration.
 */

/**
 * Implements hook_entity_property_info_alter().
 */
function commerce_utils_entity_property_info_alter(array &$info) {
  // The way of protection. Let's be fully sure that base data provided
  // by the "entity" module.
  if (!empty($info[COMMERCE_PAYMENT_WATCHER_ENTITY_TYPE]['properties'])) {
    $properties =& $info[COMMERCE_PAYMENT_WATCHER_ENTITY_TYPE]['properties'];

    $properties['order_id'] = array_merge($properties['order_id'], [
      'type' => 'commerce_order',
      'label' => t('Commerce order'),
      'description' => t('The order the watcher belongs to.'),
      'getter callback' => 'entity_property_verbatim_get',
      // No "setter callback" - write is not supported!
    ]);

    $properties['created'] = array_merge($properties['created'], [
      'type' => 'date',
      'label' => t('Date of creation'),
      'description' => t('The date the watcher was created.'),
      'getter callback' => 'entity_property_verbatim_date_get',
      // No "setter callback" - write is not supported!
    ]);

    $properties['changed'] = array_merge($properties['changed'], [
      'type' => 'date',
      'label' => t('Date of modification'),
      'description' => t('The date the watcher was last time modified.'),
      'getter callback' => 'entity_property_verbatim_date_get',
      // No "setter callback" - write is not supported!
    ]);

    $properties['payment_method_id'] = array_merge($properties['payment_method_id'], [
      // Type is correctly set to "token" here since this property is
      // a "bundle" entity key.
      'label' => t('Payment method ID'),
      'description' => t('An ID of payment method this watcher belongs to.'),
      'getter callback' => 'entity_property_verbatim_date_get',
      // No "setter callback" - write is not supported!
    ]);
  }
}
