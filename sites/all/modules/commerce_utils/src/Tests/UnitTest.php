<?php

namespace Drupal\commerce_utils\Tests;

/**
 * Base unit test.
 */
abstract class UnitTest extends \CommerceBaseTestCase {

  /**
   * A set of modules to enable.
   */
  const MODULES_SET = '';

  /**
   * Returns an information about the test.
   *
   * @param string $description
   *   Test description.
   *
   * @return array
   *   An information about the test.
   */
  protected static function info($description) {
    $parts = explode('\\', static::class);

    return [
      // Use class name as name of the test.
      'name' => end($parts),
      // The "1" will always contain the name of module.
      'group' => $parts[1],
      'description' => $description,
    ];
  }

  /**
   * {@inheritdoc}
   *
   * Handle the "PDOException: SQLSTATE[42000]: Syntax error or access
   * violation: 1059 Identifier name '%s' is too long". It happens due
   * to the "commerce_payment_transaction_pending_authorisations" name
   * of table.
   *
   * @see commerce_payment_watcher_schema()
   */
  protected function prepareDatabasePrefix() {
    // 51 characters in the name + 10 of "simpletest" + 3 of "mt_rand()".
    // 64 in total, which is a maximum allowed length of table names in MySQL.
    // We cannot change the "simpletest" word here!
    /* @see drupal_valid_test_ua() */
    $this->databasePrefix = 'simpletest' . mt_rand(99, 999);

    // As soon as the database prefix is set, the test might start to execute.
    // All assertions as well as the SimpleTest batch operations are associated
    // with the testId, so the database prefix has to be associated with it.
    db_update('simpletest_test_id')
      ->fields(['last_prefix' => $this->databasePrefix])
      ->condition('test_id', $this->testId)
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    if (!module_enable(['autoload'])) {
      throw new \RuntimeException('Cannot enable the "autoload" module!');
    }

    // Autoload 2.x.
    function_exists('autoload') && autoload(TRUE);

    $modules = static::getModulesToEnable();

    if ('' !== static::MODULES_SET) {
      $modules = $this->setUpHelper(static::MODULES_SET, $modules);
    }

    parent::setUp($modules);
  }

  /**
   * Returns list of modules to enable for testing.
   *
   * @return string[]
   *   List of modules.
   */
  public static function dependencies() {
    return ['commerce_utils'];
  }

  /**
   * Returns list of modules to enable for this test.
   *
   * @return string[]
   *   List of modules.
   */
  protected static function getModulesToEnable() {
    /* @var string|static $class */
    $class = static::class;
    $modules = [];

    do {
      if (
        method_exists($class, 'dependencies') &&
        (
          // Method of this class.
          static::class === $class ||
          // Method of child class.
          (new \ReflectionMethod($class, 'dependencies'))->class === $class
        )
      ) {
        $modules[$class] = $class::dependencies();
      }
    } while ($class = get_parent_class($class));

    // Modules have been collected in reverse class hierarchy order; modules
    // defined by base classes should be sorted first. Then, merge the results
    // together.
    return call_user_func_array('array_merge_recursive', array_reverse($modules));
  }

  /**
   * Configure instance of payment method.
   *
   * @param string $method_id
   *   An ID of payment method.
   * @param string $instance_id
   *   An instance of payment method.
   * @param array $settings
   *   Payment method settings.
   *
   * @return array
   *   An instance of payment method.
   */
  protected function getPaymentMethod($method_id, $instance_id, array $settings = []) {
    commerce_utils_set_payment_settings($method_id, $settings);

    return commerce_payment_method_instance_load($instance_id);
  }

  /**
   * Loads non-cached entity.
   *
   * @param string $entity_type
   *   The type of entity.
   * @param int $id
   *   Entity ID.
   *
   * @return object|null
   *   Entity object.
   */
  protected function loadEntity($entity_type, $id) {
    $entities = entity_load($entity_type, [$id], [], TRUE);

    return isset($entities[$id]) ? $entities[$id] : NULL;
  }

  /**
   * Create an order with products as line items.
   *
   * @param \stdClass $creator
   *   Order author.
   * @param \stdClass[] $products
   *   List of commerce products.
   *
   * @return \stdClass
   *   Commerce order.
   */
  protected function createOrder(\stdClass $creator, array $products) {
    $order = commerce_order_new($creator->uid);
    /* @see commerce_payment_redirect_pane_checkout_form() */
    $order->data['payment_redirect_key'] = drupal_hash_base64(REQUEST_TIME);
    // Save to generate an ID.
    commerce_order_save($order);

    $order_wrapper = entity_metadata_wrapper('commerce_order', $order);

    foreach ($products as $product) {
      $line_item = commerce_product_line_item_new($product, 1, $order->order_id);
      commerce_line_item_save($line_item);

      $order_wrapper->commerce_line_items[] = $line_item;
    }

    // Save the order again to update its line items.
    commerce_order_save($order);

    return $order;
  }

}
