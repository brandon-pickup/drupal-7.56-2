<?php

/**
 * @file
 * Menu pages.
 *
 * @see commerce_utils_test_menu()
 */

/**
 * {@inheritdoc}
 */
function commerce_utils_test_test_gateway_page($instance_id) {
  $page = [];

  $page['title'] = [
    '#markup' => '<h1>' . $instance_id . '</h1>',
  ];

  $page['POST'] = [
    '#markup' => '<pre>' . var_export($_POST, TRUE) . '</pre>',
  ];

  return $page;
}
