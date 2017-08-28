<?php

namespace Drupal\commerce_paygate_payhost\Payment\Composition;

/**
 * Account representation.
 */
class Account extends BaseComposition {

  /**
   * {@inheritdoc}
   */
  protected function getDefaultValues() {
    // DO NOT CHANGE ORDERING OF THESE FIELDS!
    return [
      'PayGateId' => '',
      'Password' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getRequiredFields() {
    return ['PayGateId', 'Password'];
  }

  /**
   * Set PayGate account ID.
   *
   * @param string $paygate_id
   *   PayGate account ID.
   */
  public function setPayGateId($paygate_id) {
    $this->data['PayGateId'] = $paygate_id;
  }

  /**
   * Returns PayGate account ID.
   *
   * @return string
   *   PayGate account ID.
   */
  public function getPayGateId() {
    return $this->data['PayGateId'];
  }

  /**
   * Set password for PayGate account.
   *
   * @param string $password
   *   Password for PayGate account.
   */
  public function setPassword($password) {
    $this->data['Password'] = $password;
  }

  /**
   * Returns password for PayGate account.
   *
   * @return string
   *   Password for PayGate account.
   */
  public function getPassword() {
    return $this->data['Password'];
  }

  /**
   * Create an instance of PayGate account from payment method settings.
   *
   * @param array $settings
   *   Settings of payment method.
   *
   * @return static
   */
  public static function createFromSettings(array $settings) {
    if (empty($settings['mode']) || empty($settings[$settings['mode']]['password'])) {
      throw new \InvalidArgumentException('Settings of payment method are not correct.');
    }

    $account = new static();
    $account->setPayGateId($settings[$settings['mode']]['paygate_id']);
    $account->setPassword($settings[$settings['mode']]['password']);

    return $account;
  }

}
