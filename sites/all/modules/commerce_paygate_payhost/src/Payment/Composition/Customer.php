<?php

namespace Drupal\commerce_paygate_payhost\Payment\Composition;

/**
 * Customer representation.
 */
class Customer extends BaseComposition {

  use AddressTrait;

  /**
   * {@inheritdoc}
   */
  protected function getDefaultValues() {
    // DO NOT CHANGE ORDERING OF THESE FIELDS!
    return [
      'Title' => '',
      'FirstName' => '',
      'MiddleName' => '',
      'LastName' => '',
      'Telephone' => '',
      'Mobile' => '',
      'Fax' => '',
      'Email' => '',
      'DateOfBirth' => '',
      'SocialSecurityNumber' => '',
      'Address' => NULL,
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getRequiredFields() {
    return ['FirstName', 'LastName', 'Email'];
  }

  /**
   * {@inheritdoc}
   *
   * @param string $title
   *   Customer's title.
   */
  public function setTitle($title) {
    $this->data['Title'] = $title;
  }

  /**
   * {@inheritdoc}
   *
   * @return string
   *   Title.
   */
  public function getTitle() {
    return $this->data['Title'];
  }

  /**
   * {@inheritdoc}
   *
   * @param string $first_name
   *   First name.
   */
  public function setFirstName($first_name) {
    $this->data['FirstName'] = $first_name;
  }

  /**
   * {@inheritdoc}
   *
   * @return string
   *   First name.
   */
  public function getFirstName() {
    return $this->data['FirstName'];
  }

  /**
   * {@inheritdoc}
   *
   * @param string $middle_name
   *   Middle name.
   */
  public function setMiddleName($middle_name) {
    $this->data['MiddleName'] = $middle_name;
  }

  /**
   * {@inheritdoc}
   *
   * @return string
   *   Middle name.
   */
  public function getMiddleName() {
    return $this->data['MiddleName'];
  }

  /**
   * {@inheritdoc}
   *
   * @param string $last_name
   *   Last name.
   */
  public function setLastName($last_name) {
    $this->data['LastName'] = $last_name;
  }

  /**
   * {@inheritdoc}
   *
   * @return string
   *   Last name.
   */
  public function getLastName() {
    return $this->data['LastName'];
  }

  /**
   * {@inheritdoc}
   *
   * @param string $email
   *   Email.
   */
  public function setEmail($email) {
    $this->data['Email'] = $email;
  }

  /**
   * {@inheritdoc}
   *
   * @return string
   *   Email.
   */
  public function getEmail() {
    return $this->data['Email'];
  }

  /**
   * {@inheritdoc}
   *
   * @param string $telephone
   *   Telephone.
   */
  public function setTelephone($telephone) {
    $this->data['Telephone'] = $telephone;
  }

  /**
   * {@inheritdoc}
   *
   * @return string
   *   Telephone.
   */
  public function getTelephone() {
    return $this->data['Telephone'];
  }

  /**
   * {@inheritdoc}
   *
   * @param string $mobile
   *   Mobile.
   */
  public function setMobile($mobile) {
    $this->data['Mobile'] = $mobile;
  }

  /**
   * {@inheritdoc}
   *
   * @return string
   *   Mobile.
   */
  public function getMobile() {
    return $this->data['Mobile'];
  }

  /**
   * {@inheritdoc}
   *
   * @param string $fax
   *   Fax.
   */
  public function setFax($fax) {
    $this->data['Fax'] = $fax;
  }

  /**
   * {@inheritdoc}
   *
   * @return string
   *   Fax.
   */
  public function getFax() {
    return $this->data['Fax'];
  }

  /**
   * {@inheritdoc}
   *
   * @param string|int $date
   *   Date of birth.
   */
  public function setDateOfBirth($date) {
    $this->data['DateOfBirth'] = date('Y-m-d', is_numeric($date) ? $date : strtotime($date));
  }

  /**
   * {@inheritdoc}
   *
   * @return string
   *   Date of birth.
   */
  public function getDateOfBirth() {
    return $this->data['DateOfBirth'];
  }

  /**
   * {@inheritdoc}
   *
   * @param string $social_security_number
   *   Social security number.
   */
  public function setSocialSecurityNumber($social_security_number) {
    $this->data['SocialSecurityNumber'] = $social_security_number;
  }

  /**
   * {@inheritdoc}
   *
   * @return string
   *   Social security number.
   */
  public function getSocialSecurityNumber() {
    return $this->data['SocialSecurityNumber'];
  }

}
