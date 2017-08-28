<?php

namespace Drupal\commerce_payment_watcher_ui\Views\Handler\Field;

/**
 * Handler for displaying entity labels.
 */
class EntityLabel extends \views_handler_field_entity {

  /**
   * {@inheritdoc}
   */
  // @codingStandardsIgnoreStart
  public function options_form(&$form, &$form_state) {
    // @codingStandardsIgnoreEnd
    parent::options_form($form, $form_state);

    $form['alter']['path']['#access'] = FALSE;
    $form['alter']['make_link']['#description'] = t('Output label of an entity as a link to its view.');
  }

  /**
   * {@inheritdoc}
   */
  public function render($values) {
    $entity = $this->get_value($values);
    $label = entity_label($this->entity_type, $entity);
    $uri = entity_uri($this->entity_type, $entity);

    if (empty($this->options['alter']['make_link']) || NULL === $uri) {
      return $label;
    }

    return l($label, $uri['path'], $uri['options']);
  }

}
