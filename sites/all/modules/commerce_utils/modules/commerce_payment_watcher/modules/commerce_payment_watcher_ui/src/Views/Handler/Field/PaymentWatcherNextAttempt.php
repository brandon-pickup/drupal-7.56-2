<?php

namespace Drupal\commerce_payment_watcher_ui\Views\Handler\Field;

/**
 * Handler for displaying time of next status check attempt.
 */
class PaymentWatcherNextAttempt extends \views_handler_field_date {

  /**
   * {@inheritdoc}
   */
  // @codingStandardsIgnoreStart
  public function option_definition() {
    // @codingStandardsIgnoreEnd
    $options = parent::option_definition();

    $options['payment_method_instance_id'] = ['default' => ''];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    // It's a custom field and it's not defined in table schema, so nothing to
    // query.
  }

  /**
   * {@inheritdoc}
   */
  // @codingStandardsIgnoreStart
  public function get_value($values, $field = NULL) {
    // @codingStandardsIgnoreEnd
    static $payment_methods = [];

    if (empty($this->options['payment_method_instance_id'])) {
      throw new \RuntimeException('You must programmatically set an ID of payment method this plugin belongs to.');
    }

    if (!isset($payment_methods[$this->options['payment_method_instance_id']])) {
      // Avoid loading the same data for every result.
      $payment_method_instance = commerce_payment_method_instance_load($this->options['payment_method_instance_id']);

      if (empty($payment_method_instance)) {
        throw new \RuntimeException(sprintf('The "%s" is not correct ID of payment method instance!', $this->options['payment_method_instance_id']));
      }

      $payment_methods[$this->options['payment_method_instance_id']] = $payment_method_instance;
    }

    /* @var \Drupal\commerce_payment_watcher\Entity\PaymentWatcherEntity[] $entities */
    list(, $entities) = $this->query->get_result_entities([$values], $this->relationship);

    if (empty($entities)) {
      // In a normal life this point will never be reached! This is only
      // possible if "\views_plugin_query_default" or its child will not
      // be used as a plugin for query.
      throw new \RuntimeException('Cannot load the entity!');
    }

    $entity = reset($entities);
    // Multiply gap by 60, since gap - is an amount of minutes and we are
    // looking for timestamp.
    $values->{$this->field_alias} = $entity->changed + 60 * commerce_payment_watcher_calculate_next_status_check_gap(
      $payment_methods[$this->options['payment_method_instance_id']]['settings'],
      $entity->authorisation_checks
    );

    return parent::get_value($values, $field);
  }

  /**
   * {@inheritdoc}
   */
  public function render($values) {
    $value = parent::render($values);

    // Add informativity for users, because a moment of next check has already
    // passed.
    if (REQUEST_TIME >= $values->{$this->field_alias}) {
      $value .= '<br />' . t('<sup>On next cron execution, since moment passed.</sup>');
    }

    return $value;
  }

}
