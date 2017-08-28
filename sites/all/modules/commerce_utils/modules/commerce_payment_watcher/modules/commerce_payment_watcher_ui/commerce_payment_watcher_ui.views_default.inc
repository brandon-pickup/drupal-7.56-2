<?php

/**
 * @file
 * Exported views.
 */

/**
 * Implements hook_views_default_views().
 */
function commerce_payment_watcher_ui_views_default_views() {
  $views = [];

  foreach (commerce_payment_watcher_payment_methods() as $payment_method_id => $payment_method) {
    $view = new \view();
    $view->tag = 'default';
    $view->name = commerce_payment_watcher_item_name($payment_method_id);
    $view->core = 7;
    $view->disabled = FALSE;
    $view->base_table = 'commerce_payment_transaction_pending_authorisations';
    $view->api_version = 3.0;

    $view->description = t('Lists pending authorisations of "@payment_method" payments.', [
      '@payment_method' => $payment_method['title'],
    ]);

    $view->human_name = t('Commerce @payment_method Payment Watchers', [
      '@payment_method' => $payment_method['title'],
    ]);

    /* Display: Master */
    $handler = $view->new_display('default', t('Master'), 'default');

    $handler->display->display_options['title'] = t('@payment_method pending authorisations', [
      '@payment_method' => $payment_method['title'],
    ]);

    $handler->display->display_options['use_more_always'] = FALSE;
    $handler->display->display_options['access']['type'] = 'perm';
    /* @see commerce_payment_watcher_permission() */
    $handler->display->display_options['access']['perm'] = "administer {$payment_method_id} payment watchers";
    $handler->display->display_options['cache']['type'] = 'none';
    $handler->display->display_options['query']['type'] = 'views_query';
    $handler->display->display_options['exposed_form']['type'] = 'basic';
    $handler->display->display_options['exposed_form']['options']['submit_button'] = t('Search');
    $handler->display->display_options['exposed_form']['options']['reset_button'] = TRUE;
    $handler->display->display_options['pager']['type'] = 'full';
    $handler->display->display_options['pager']['options']['items_per_page'] = 30;
    $handler->display->display_options['pager']['options']['offset'] = 0;
    $handler->display->display_options['pager']['options']['id'] = 0;
    $handler->display->display_options['pager']['options']['quantity'] = 9;
    $handler->display->display_options['style_plugin'] = 'table';
    $handler->display->display_options['style_options']['default_row_class'] = FALSE;
    $handler->display->display_options['style_options']['columns'] = array(
      'views_bulk_operations' => 'views_bulk_operations',
      'label' => 'label',
      'created' => 'created',
      'changed' => 'changed',
      'next_attempt' => 'next_attempt',
      'authorisation_checks' => 'authorisation_checks',
    );
    $handler->display->display_options['style_options']['default'] = 'changed';
    $handler->display->display_options['style_options']['info'] = array(
      'views_bulk_operations' => array(
        'align' => '',
        'separator' => '',
        'empty_column' => 0,
      ),
      'label' => array(
        'align' => '',
        'separator' => '',
        'empty_column' => 0,
      ),
      'created' => array(
        'sortable' => 1,
        'default_sort_order' => 'desc',
        'align' => '',
        'separator' => '',
        'empty_column' => 0,
      ),
      'changed' => array(
        'sortable' => 1,
        'default_sort_order' => 'desc',
        'align' => '',
        'separator' => '',
        'empty_column' => 0,
      ),
      'next_attempt' => array(
        'sortable' => 1,
        'default_sort_order' => 'desc',
        'align' => '',
        'separator' => '',
        'empty_column' => 0,
      ),
      'authorisation_checks' => array(
        'sortable' => 1,
        'default_sort_order' => 'desc',
        'align' => '',
        'separator' => '',
        'empty_column' => 0,
      ),
    );

    /* No results behavior: Global: Text area */
    $handler->display->display_options['empty']['area']['id'] = 'area';
    $handler->display->display_options['empty']['area']['table'] = 'views';
    $handler->display->display_options['empty']['area']['field'] = 'area';
    $handler->display->display_options['empty']['area']['empty'] = TRUE;
    $handler->display->display_options['empty']['area']['content'] = t('System does not have payment watchers at this, indisputably happy moment.');
    $handler->display->display_options['empty']['area']['format'] = 'filtered_html';

    /* Relationship: Payment transaction watcher: Commerce order */
    $handler->display->display_options['relationships']['order_id']['id'] = 'order_id';
    $handler->display->display_options['relationships']['order_id']['table'] = $view->base_table;
    $handler->display->display_options['relationships']['order_id']['field'] = 'order_id';

    /* Field: Bulk operations: Payment transaction watcher */
    $handler->display->display_options['fields']['views_bulk_operations']['id'] = 'views_bulk_operations';
    $handler->display->display_options['fields']['views_bulk_operations']['table'] = $view->base_table;
    $handler->display->display_options['fields']['views_bulk_operations']['field'] = 'views_bulk_operations';
    $handler->display->display_options['fields']['views_bulk_operations']['label'] = '';
    $handler->display->display_options['fields']['views_bulk_operations']['element_label_colon'] = FALSE;
    $handler->display->display_options['fields']['views_bulk_operations']['element_default_classes'] = FALSE;
    $handler->display->display_options['fields']['views_bulk_operations']['vbo_settings']['display_type'] = 0;
    $handler->display->display_options['fields']['views_bulk_operations']['vbo_settings']['enable_select_all_pages'] = 1;
    $handler->display->display_options['fields']['views_bulk_operations']['vbo_settings']['row_clickable'] = 1;
    $handler->display->display_options['fields']['views_bulk_operations']['vbo_settings']['force_single'] = 0;
    $handler->display->display_options['fields']['views_bulk_operations']['vbo_settings']['entity_load_capacity'] = 10;
    $handler->display->display_options['fields']['views_bulk_operations']['vbo_operations'] = array(
      'action::views_bulk_operations_delete_item' => array(
        'selected' => 1,
        'postpone_processing' => 0,
        'skip_confirmation' => 0,
        'override_label' => 0,
        'label' => '',
      ),
      /* @see commerce_payment_watcher_ui_check_payment_status() */
      'action::commerce_payment_watcher_ui_check_payment_status' => array(
        'selected' => 1,
        'postpone_processing' => 0,
        'skip_confirmation' => 0,
        'override_label' => 0,
        'label' => '',
      ),
    );
    /* Field: Payment transaction watcher: Label */
    $handler->display->display_options['fields']['label']['id'] = 'label';
    $handler->display->display_options['fields']['label']['table'] = $view->base_table;
    $handler->display->display_options['fields']['label']['field'] = 'label';
    $handler->display->display_options['fields']['label']['label'] = t('Label');
    $handler->display->display_options['fields']['label']['alter']['make_link'] = TRUE;

    /* Field: Payment transaction watcher: Date of creation */
    $handler->display->display_options['fields']['created']['id'] = 'created';
    $handler->display->display_options['fields']['created']['table'] = $view->base_table;
    $handler->display->display_options['fields']['created']['field'] = 'created';
    $handler->display->display_options['fields']['created']['label'] = t('Date of creation');
    $handler->display->display_options['fields']['created']['element_label_colon'] = FALSE;
    $handler->display->display_options['fields']['created']['date_format'] = 'custom';
    $handler->display->display_options['fields']['created']['custom_date_format'] = 'l j, H:i:s, F d, Y';
    $handler->display->display_options['fields']['created']['second_date_format'] = 'long';

    /* Field: Payment transaction watcher: Date of modification */
    $handler->display->display_options['fields']['changed']['id'] = 'changed';
    $handler->display->display_options['fields']['changed']['table'] = $view->base_table;
    $handler->display->display_options['fields']['changed']['field'] = 'changed';
    $handler->display->display_options['fields']['changed']['label'] = t('Last attempt');
    $handler->display->display_options['fields']['changed']['element_label_colon'] = FALSE;
    $handler->display->display_options['fields']['changed']['date_format'] = 'custom';
    $handler->display->display_options['fields']['changed']['custom_date_format'] = 'l j, H:i:s, F d, Y';
    $handler->display->display_options['fields']['changed']['second_date_format'] = 'long';

    /* Field: Payment transaction watcher: Next attempt */
    $handler->display->display_options['fields']['next_attempt']['id'] = 'next_attempt';
    $handler->display->display_options['fields']['next_attempt']['table'] = $view->base_table;
    $handler->display->display_options['fields']['next_attempt']['field'] = 'next_attempt';
    $handler->display->display_options['fields']['next_attempt']['label'] = t('Next attempt');
    $handler->display->display_options['fields']['next_attempt']['date_format'] = 'custom';
    $handler->display->display_options['fields']['next_attempt']['payment_method_instance_id'] = $payment_method['instance_id'];
    $handler->display->display_options['fields']['next_attempt']['custom_date_format'] = 'l j, H:i:s, F d, Y';
    $handler->display->display_options['fields']['next_attempt']['second_date_format'] = 'long';

    /* Field: Payment transaction watcher: Authorisation_checks */
    $handler->display->display_options['fields']['authorisation_checks']['id'] = 'authorisation_checks';
    $handler->display->display_options['fields']['authorisation_checks']['table'] = $view->base_table;
    $handler->display->display_options['fields']['authorisation_checks']['field'] = 'authorisation_checks';
    $handler->display->display_options['fields']['authorisation_checks']['label'] = t('Attempts');
    $handler->display->display_options['fields']['authorisation_checks']['separator'] = '';

    /* Sort criterion: Payment transaction watcher: Commerce order */
    $handler->display->display_options['sorts']['order_id']['id'] = 'order_id';
    $handler->display->display_options['sorts']['order_id']['table'] = $view->base_table;
    $handler->display->display_options['sorts']['order_id']['field'] = 'order_id';
    $handler->display->display_options['sorts']['order_id']['order'] = 'DESC';
    $handler->display->display_options['sorts']['order_id']['expose']['label'] = t('Commerce order');

    /* Filter criterion: Payment transaction watcher: Payment method ID */
    $handler->display->display_options['filters']['payment_method_id']['id'] = 'payment_method_id';
    $handler->display->display_options['filters']['payment_method_id']['table'] = $view->base_table;
    $handler->display->display_options['filters']['payment_method_id']['field'] = 'payment_method_id';
    $handler->display->display_options['filters']['payment_method_id']['operator'] = 'in';
    $handler->display->display_options['filters']['payment_method_id']['value']['value'] = $payment_method_id;

    /* Filter criterion: Commerce Order: E-mail */
    $handler->display->display_options['filters']['mail']['id'] = 'mail';
    $handler->display->display_options['filters']['mail']['table'] = 'commerce_order';
    $handler->display->display_options['filters']['mail']['field'] = 'mail';
    $handler->display->display_options['filters']['mail']['operator'] = 'contains';
    $handler->display->display_options['filters']['mail']['relationship'] = 'order_id';
    $handler->display->display_options['filters']['mail']['exposed'] = TRUE;
    $handler->display->display_options['filters']['mail']['expose']['operator_id'] = 'mail_op';
    $handler->display->display_options['filters']['mail']['expose']['label'] = t('E-mail');
    $handler->display->display_options['filters']['mail']['expose']['operator'] = 'mail_op';
    $handler->display->display_options['filters']['mail']['expose']['identifier'] = 'mail';
    $handler->display->display_options['filters']['mail']['expose']['remember_roles'] = array();

    /* Filter criterion: Commerce Order: Order number */
    $handler->display->display_options['filters']['order_number']['id'] = 'order_number';
    $handler->display->display_options['filters']['order_number']['table'] = 'commerce_order';
    $handler->display->display_options['filters']['order_number']['field'] = 'order_number';
    $handler->display->display_options['filters']['order_number']['relationship'] = 'order_id';
    $handler->display->display_options['filters']['order_number']['operator'] = 'contains';
    $handler->display->display_options['filters']['order_number']['exposed'] = TRUE;
    $handler->display->display_options['filters']['order_number']['expose']['operator_id'] = 'order_number_op';
    $handler->display->display_options['filters']['order_number']['expose']['label'] = t('Order number');
    $handler->display->display_options['filters']['order_number']['expose']['operator'] = 'order_number_op';
    $handler->display->display_options['filters']['order_number']['expose']['identifier'] = 'order_number';
    $handler->display->display_options['filters']['order_number']['expose']['remember_roles'] = array();

    /* Display: Page */
    $handler = $view->new_display('page', t('Page'), 'page');
    /* @see commerce_payment_watcher_entity_info() */
    $handler->display->display_options['path'] = COMMERCE_PAYMENT_WATCHER_ADMIN_PATH . '/' . str_replace('_', '-', $payment_method_id);
    $handler->display->display_options['menu']['title'] = '';
    $handler->display->display_options['menu']['description'] = '';
    $handler->display->display_options['menu']['weight'] = 0;
    $handler->display->display_options['menu']['context'] = 0;
    $handler->display->display_options['menu']['context_only_inline'] = 0;

    $views[$view->name] = $view;
  }

  return $views;
}
