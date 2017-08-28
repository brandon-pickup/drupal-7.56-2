<?php

namespace Drupal\commerce_payment_watcher_ui\Entity\PaymentWatcher;

use Drupal\commerce_payment_watcher_ui\Views\Handler\Field\EntityLabel;
use Drupal\commerce_payment_watcher_ui\Views\Handler\Field\PaymentWatcherNextAttempt;

/**
 * Views controller for payment watchers.
 */
class ViewsController extends \EntityDefaultViewsController {

  /**
   * {@inheritdoc}
   */
  // @codingStandardsIgnoreStart
  public function views_data() {
    // @codingStandardsIgnoreEnd
    $data = parent::views_data();

    $data[$this->info['base table']]['label'] = [
      'title' => t('Label'),
      'help' => t('Display label of an entity.'),
      'field' => [
        'handler' => EntityLabel::class,
        'click sortable' => FALSE,
      ],
    ];

    $data[$this->info['base table']]['next_attempt'] = [
      'title' => t('Next attempt'),
      'help' => t('Date of next payment status check attempt.'),
      'field' => [
        'handler' => PaymentWatcherNextAttempt::class,
        'click sortable' => TRUE,
      ],
    ];

    return $data;
  }

}
