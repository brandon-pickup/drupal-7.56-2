<?php

namespace Drupal\commerce_payment_watcher\Entity;

/**
 * Controller allowing to have own entity class per bundle.
 */
class PaymentWatcherEntityController extends \EntityAPIController {

  /**
   * {@inheritdoc}
   */
  public function create(array $values = []) {
    if (empty($values[$this->entityInfo['entity keys']['bundle']])) {
      throw new \InvalidArgumentException(sprintf('You cannot %s the "%s" entity without specifying a bundle!', __FUNCTION__, $this->entityType));
    }

    $this->entityInfo['entity class'] = $this->getBundleEntityClass($values[$this->entityInfo['entity keys']['bundle']]);

    return parent::create($values);
  }

  /**
   * {@inheritdoc}
   */
  public function query($ids, $conditions, $revision_id = FALSE) {
    return $this
      ->buildQuery($ids, $conditions, $revision_id)
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function attachLoad(&$queried_entities, $revision_id = FALSE) {
    foreach ($queried_entities as $id => $entity) {
      list(,, $bundle) = entity_extract_ids($this->entityType, $entity);
      $class = $this->getBundleEntityClass($bundle);

      $queried_entities[$id] = new $class((array) $entity);
    }

    parent::attachLoad($queried_entities, $revision_id);
  }

  /**
   * Get bundle's entity class.
   *
   * @param string $bundle
   *   Machine name of a bundle.
   *
   * @return string|\Drupal\commerce_payment_watcher\Entity\PaymentWatcherEntity
   *   FQN of a class representing a particular bundle of the entity.
   *
   * @see \Commerce\Utils\PaymentPlugin::setPaymentWatcherEntityClass()
   */
  protected function getBundleEntityClass($bundle) {
    $property = 'entity class';
    $base_class = PaymentWatcherEntity::class;

    if (empty($this->entityInfo['bundles'][$bundle][$property])) {
      throw new \LogicException(sprintf('Every bundle of the "%s" entity must specify the "%s" property in its definition!', $bundle, $property));
    }

    $class = $this->entityInfo['bundles'][$bundle][$property];

    if (!class_exists($class)) {
      throw new \LogicException(sprintf('The value of the "%s" property of the "%s" bundle must be existent class.', $property, $bundle));
    }

    if (!is_subclass_of($class, $base_class)) {
      throw new \LogicException(sprintf('The entity class "%s" of the "%s" bundle must extend the "%s".', $class, $bundle, $base_class));
    }

    return $class;
  }

}
