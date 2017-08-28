<?php

namespace Drupal\commerce_paygate_payhost\Payment\Form;

use Commerce\Utils\Payment\Form\SettingsFormBase;
use Drupal\commerce_paygate_payhost\Payment\Environment;
use Drupal\commerce_paygate_payhost\Payment\PaymentLocaleInterface;

/**
 * {@inheritdoc}
 */
class SettingsForm extends SettingsFormBase {

  /**
   * {@inheritdoc}
   */
  public function form(array &$form, array &$settings) {
    $settings += ['mode' => Environment::TEST];

    $settings += [$settings['mode'] => []];
    $settings[Environment::TEST] += [
      // Testing Currencies: ZAR, EUR and USD.
      'paygate_id' => '10011072130',
      'password' => 'test',
    ];

    $form['mode'] = [
      '#ajax' => TRUE,
      '#type' => 'radios',
      '#title' => t('Mode'),
      '#required' => TRUE,
      '#default_value' => Environment::TEST,
      '#options' => [
        Environment::TEST => t('Test'),
        Environment::LIVE => t('Live'),
      ],
    ];

    foreach ($form['mode']['#options'] as $mode => $label) {
      $form[$mode] = [
        '#type' => 'fieldset',
        '#title' => t('@label-mode credentials', ['@label' => $label]),
        '#access' => $settings['mode'] === $mode,
        '#attributes' => [
          'class' => ['credentials'],
        ],
      ];

      $form[$mode]['paygate_id'] = [
        '#type' => 'textfield',
        '#title' => t('Account'),
        '#required' => TRUE,
      ];

      $form[$mode]['password'] = [
        '#type' => 'textfield',
        '#title' => t('Password'),
        '#required' => TRUE,
      ];
    }

    /* @see commerce_paygate_payhost_convert_transaction_status() */
    $form['auto_settlement_disabled'] = [
      '#type' => 'checkbox',
      '#title' => t('Disable auto-settlement'),
      '#description' => \implode('<br /><br />', [
        t('With this option enabled, you do not need to send a capture request for an approved authorisation. As soon as the bank approves the authorisation, PayGate immediately and automatically creates the capture transaction on your behalf. This option is enabled by default.'),
        t('<b>WARNING!</b> Always remember that just enabling/disabling the "auto-settlement" option on a Drupal side is not enough action to get it working. The PayGate support MUST be contacted with a request to do the same action on their side for your merchant account.'),
      ]),
    ];

    $form['shopper_locale'] = [
      '#type' => 'select',
      '#title' => t('Shopper locale'),
      '#options' => array_map('t', PaymentLocaleInterface::LOCALES),
      '#required' => TRUE,
      '#default_value' => 'en-us',
    ];

    $form['#attached']['css'][] = [
      'data' => '.credentials > .fieldset-wrapper > * {float: left; margin-right: 20px !important;}',
      'type' => 'inline',
    ];
  }

}
