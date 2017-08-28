<?php

/**
 * @file
 * Beautifier of payment's settings form.
 */

const COMMERCE_UTILS_SETTINGS_FORM_ID = 'commerce_utils_settings_form';
const COMMERCE_UTILS_SETTINGS_FORM_PARENTS = [
  'parameter',
  'payment_method',
  'settings',
  'payment_method',
  'settings',
];

/**
 * Implements hook_form_FORM_ID_alter().
 *
 * {@inheritdoc}
 *
 * @internal
 */
function commerce_utils_form_rules_ui_edit_element_alter(array &$form, array &$form_state) {
  // Edit form can belong not just to payment method settings, so we have to
  // check what kind of rule we're going to modify.
  if (!empty($form_state['element_settings']['payment_method'])) {
    // Initially, when the form wasn't saved yet, here will be the payment
    // method ID.
    $method_id = $form_state['element_settings']['payment_method'];

    // Once form with payment settings have been saved at least once, then
    // here will be an array with "method_id" and "settings" keys.
    if (is_array($method_id)) {
      $method_id = $method_id['method_id'];
    }

    $payment_method = commerce_payment_method_load($method_id);

    // Do the tweaking only if payment method has a payment plugin.
    /* @see commerce_utils_menu() */
    /* @see commerce_utils_hook_info() */
    if (isset($payment_method['payment_plugin'])) {
      $payment_plugin = commerce_utils_get_payment_plugin($payment_method);
      $settings_form_class = $payment_plugin->getSettingsFormClass();

      $element =& drupal_array_get_nested_value($form, COMMERCE_UTILS_SETTINGS_FORM_PARENTS);

      if (!empty($form_state['values'])) {
        $settings =& drupal_array_get_nested_value($form_state['values'], COMMERCE_UTILS_SETTINGS_FORM_PARENTS);
      }

      if (empty($settings)) {
        $settings =& drupal_array_get_nested_value($form_state['element_settings'], ['payment_method', 'settings']);
      }

      /* @var \Commerce\Utils\Payment\Form\SettingsFormBase $settings_form */
      $settings_form = new $settings_form_class();
      $notifications_controller = $payment_plugin->getNotificationsControllerClass();

      if (!empty($form_state['input']['_triggering_element_name'])) {
        $settings_form->setAjaxParents(explode('__', $form_state['input']['_triggering_element_name']));
      }

      if (NULL !== $notifications_controller) {
        $basic_auth_url = 'https://www.drupal.org/project/basic_auth';
        $basic_auth_status = t('module does not exist');

        // Module enabled.
        if (function_exists('basic_auth_config_exists')) {
          $basic_auth_url = url('admin/config/system/basic-auth', ['absolute' => TRUE]);
          $basic_auth_status = t('currently not configured');

          if (basic_auth_config_exists($notifications_controller::NOTIFICATIONS_PATH, TRUE)) {
            $basic_auth_status = t('already configured');
          }
        }
        // Module exists within file system but disabled at the moment.
        elseif (!empty(drupal_get_path('module', 'basic_auth'))) {
          $basic_auth_url = url('admin/modules', ['absolute' => TRUE]);
          $basic_auth_status = t('module uninstalled');
        }

        $element['basic_auth'] = [
          '#type' => 'item',
          '#title' => t('Please configure your merchant account to send notifications to: %notification_url.', [
            '%notification_url' => url($notifications_controller::NOTIFICATIONS_PATH, ['absolute' => TRUE]),
          ]),
          '#description' => t('Also, it is recommended to set up <a href="@basic_auth_url" target="_blank">basic HTTP authorisation</a> (@basic_auth_status) to get rid of dummy messages in system log, when someone visit that URL specially or by accident.', [
            '@basic_auth_url' => $basic_auth_url,
            '@basic_auth_status' => $basic_auth_status,
          ]),
        ];
      }

      if (NULL === $settings) {
        $settings = [];
      }

      $settings_form->form($element, $settings);

      // When payment watcher defined it means that payment plugin
      // requires "commerce_payment_watcher" module to be enabled.
      /* @see \Commerce\Utils\PaymentPlugin::setPaymentWatcherEntityClass() */
      if (NULL !== $payment_plugin->getPaymentWatcherEntityClass()) {
        commerce_payment_watcher_settings_form($element, $settings, $method_id);
      }

      commerce_utils_settings_form_preprocess($element, $settings, []);

      $element['#prefix'] = '<div id="' . COMMERCE_UTILS_SETTINGS_FORM_ID . '">';
      $element['#suffix'] = '</div>';

      /* @see commerce_utils_settings_form_validate() */
      /* @see commerce_utils_settings_form_submit() */
      foreach (['validate', 'submit'] as $handler) {
        array_unshift($form['#' . $handler], COMMERCE_UTILS_SETTINGS_FORM_ID . '_' . $handler);
      }

      // Store handler for further usage.
      $form_state['#' . COMMERCE_UTILS_SETTINGS_FORM_ID] = $settings_form;
    }
  }
}

/**
 * Preprocess settings form.
 *
 * @param array $form
 *   Form elements definitions.
 * @param mixed $settings
 *   Saved settings.
 * @param string[] $parents
 *   Form element parents.
 *
 * @internal
 */
function commerce_utils_settings_form_preprocess(array &$form, &$settings, array $parents) {
  foreach (element_children($form) as $child) {
    if (isset($settings[$child])) {
      $form[$child]['#default_value'] = $settings[$child];
    }
    elseif (isset($form[$child]['#default_value'])) {
      $settings[$child] = $form[$child]['#default_value'];
    }

    if (isset($form[$child]['#ajax'])) {
      // Change button identifier from "op", to unique, recognizable name.
      if (!empty($form[$child]['#type']) && 'button' === $form[$child]['#type']) {
        $form[$child]['#name'] = implode('__', $parents) . '__' . $child;
      }

      $form[$child]['#ajax'] = [
        /* @see commerce_utils_settings_form_ajax() */
        'callback' => COMMERCE_UTILS_SETTINGS_FORM_ID . '_ajax',
        'wrapper' => COMMERCE_UTILS_SETTINGS_FORM_ID,
      ];
    }

    if (is_array($form[$child])) {
      // Handle cases when additional properties for elements of types "radios"
      // and/or "checkboxes" added.
      if (is_array($settings)) {
        $options =& $settings[$child];
      }
      else {
        $options =& $settings;
      }

      call_user_func_array(__FUNCTION__, [
        &$form[$child],
        &$options,
        array_merge($parents, [$child]),
      ]);
    }
  }
}

/**
 * {@inheritdoc}
 *
 * @internal
 */
function commerce_utils_settings_form_validate(array &$form, array &$form_state) {
  if (!empty($form_state['#' . COMMERCE_UTILS_SETTINGS_FORM_ID])) {
    $settings =& drupal_array_get_nested_value($form_state['values'], COMMERCE_UTILS_SETTINGS_FORM_PARENTS);
    $element =& drupal_array_get_nested_value($form, COMMERCE_UTILS_SETTINGS_FORM_PARENTS);

    $form_state['#' . COMMERCE_UTILS_SETTINGS_FORM_ID]->validate($element, $settings);
  }
}

/**
 * {@inheritdoc}
 *
 * @internal
 */
function commerce_utils_settings_form_submit(array $form, array &$form_state) {
  if (!empty($form_state['#' . COMMERCE_UTILS_SETTINGS_FORM_ID])) {
    $settings =& drupal_array_get_nested_value($form_state['values'], COMMERCE_UTILS_SETTINGS_FORM_PARENTS);
    $element =& drupal_array_get_nested_value($form, COMMERCE_UTILS_SETTINGS_FORM_PARENTS);

    $form_state['#' . COMMERCE_UTILS_SETTINGS_FORM_ID]->submit($element, $settings);
  }
}

/**
 * {@inheritdoc}
 *
 * @internal
 */
function commerce_utils_settings_form_ajax(array $form, array &$form_state) {
  $commands = [];
  $element = drupal_array_get_nested_value($form, COMMERCE_UTILS_SETTINGS_FORM_PARENTS);

  $commands[] = ajax_command_replace('#' . COMMERCE_UTILS_SETTINGS_FORM_ID, drupal_render($element));

  if (!empty(form_get_errors())) {
    $commands[] = ajax_command_prepend('#' . COMMERCE_UTILS_SETTINGS_FORM_ID, theme('status_messages'));
    form_clear_error();
  }

  return [
    '#type' => 'ajax',
    '#commands' => $commands,
  ];
}
