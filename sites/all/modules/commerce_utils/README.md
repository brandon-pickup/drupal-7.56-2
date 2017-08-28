# Commerce Utilities

## How to alter the configuration of `payment_plugin`?

```php
/**
 * Implements hook_commerce_payment_method_info_alter().
 */
function MODULE_commerce_payment_method_info_alter(array &$payment_methods) {
  /* @var \Commerce\Utils\PaymentPlugin $payment_plugin */
  $payment_plugin = $payment_methods['PAYMENT_METHOD_ID']['payment_plugin'];
  $payment_plugin->setRefundTransactionClass(RefundTransaction::class);
}
```

## Tests

```bash
drush dl commerce views -y
drush en simpletest -y
php ./scripts/run-tests.sh --color --verbose 'Commerce Utils'
```
