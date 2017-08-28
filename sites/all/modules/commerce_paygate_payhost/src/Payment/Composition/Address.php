<?php

namespace Drupal\commerce_paygate_payhost\Payment\Composition;

use League\ISO3166\ISO3166;

/**
 * Address representation.
 */
class Address extends BaseComposition {

  /**
   * {@inheritdoc}
   */
  protected function getDefaultValues() {
    // DO NOT CHANGE ORDERING OF THESE FIELDS!
    return [
      'AddressLine' => [],
      'City' => '',
      'Country' => '',
      'State' => '',
      'Zip' => '',
    ];
  }

  /**
   * Add an address line (up to 3).
   *
   * @param string $address_line
   *   A line of an address.
   */
  public function addAddressLine($address_line) {
    if (count($this->data['AddressLine']) >= 3) {
      throw new \LogicException('An address can have up to 3 address lines, not more.');
    }

    $this->data['AddressLine'][] = $address_line;
  }

  /**
   * Returns a line of an address.
   *
   * @param string $number
   *   Number of address line.
   *
   * @return string
   *   A line of an address.
   */
  public function getAddressLine($number) {
    if (empty($this->data['AddressLine'][$number])) {
      throw new \InvalidArgumentException(sprintf('Address line %d is not set.', $number));
    }

    return $this->data['AddressLine'][$number];
  }

  /**
   * {@inheritdoc}
   *
   * @param string $city
   *   City.
   */
  public function setCity($city) {
    $this->data['City'] = $city;
  }

  /**
   * {@inheritdoc}
   *
   * @return string
   *   City.
   */
  public function getCity() {
    return $this->data['City'];
  }

  /**
   * {@inheritdoc}
   *
   * @param string $country_code
   *   Country code.
   *
   * @see https://en.wikipedia.org/wiki/ISO_3166-1_alpha-3
   */
  public function setCountry($country_code) {
    $this->data['Country'] = strtoupper((new ISO3166())->alpha2($country_code)['alpha3']);
  }

  /**
   * {@inheritdoc}
   *
   * @return string
   *   Country code (ISO 3166-1 Alpha 3).
   */
  public function getCountry() {
    return $this->data['Country'];
  }

  /**
   * {@inheritdoc}
   *
   * @param string $state
   *   State.
   */
  public function setState($state) {
    $this->data['State'] = $state;
  }

  /**
   * {@inheritdoc}
   *
   * @return string
   *   State.
   */
  public function getState() {
    return $this->data['State'];
  }

  /**
   * {@inheritdoc}
   *
   * @param string $postal_code
   *   Postal code.
   */
  public function setPostalCode($postal_code) {
    $this->data['Zip'] = $postal_code;
  }

  /**
   * {@inheritdoc}
   *
   * @return string
   *   Postal code.
   */
  public function getPostalCode() {
    return $this->data['Zip'];
  }

  /**
   * Create an instance of address from "commerce_customer_profile" entity.
   *
   * @param \EntityDrupalWrapper $profile
   *   Commerce customer profile.
   *
   * @return static
   */
  public static function createFromCustomerProfile(\EntityDrupalWrapper $profile) {
    if ('commerce_customer_profile' !== $profile->type() || empty($profile->commerce_customer_address)) {
      throw new \InvalidArgumentException(sprintf('You have to pass a correct customer profile entity to "%s".', __METHOD__));
    }

    return static::createFromAddressField($profile->commerce_customer_address);
  }

  /**
   * Create an instance of address from "addressfield" field.
   *
   * @param \EntityStructureWrapper $address
   *   Address field.
   *
   * @return static
   */
  public static function createFromAddressField(\EntityStructureWrapper $address) {
    if ('addressfield' !== $address->type()) {
      throw new \InvalidArgumentException(sprintf('You have to pass a correct address field to "%s".', __METHOD__));
    }

    $self = new static();
    $data = $address->value();

    if (isset($data['country'])) {
      $self->setCountry($data['country']);
    }

    if (isset($data['administrative_area'])) {
      $self->setState($data['administrative_area']);
    }

    if (isset($data['locality'])) {
      $self->setCity($data['locality']);
    }

    if (isset($data['postal_code'])) {
      $self->setPostalCode($data['postal_code']);
    }

    // Address line 1.
    if (isset($data['thoroughfare'])) {
      $self->addAddressLine($data['thoroughfare']);
    }

    // @todo Address line 2.

    // Address line 3.
    if (isset($data['premise'])) {
      $self->addAddressLine($data['premise']);
    }

    return $self;
  }

}
