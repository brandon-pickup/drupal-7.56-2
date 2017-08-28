<?php

namespace Drupal\commerce_paygate_payhost\Payment;

use Commerce\Utils\Component\Xml\XmlArray;
use Commerce\Utils\Exception\MultiErrorException;

/**
 * Payment environment, depending on configuration.
 */
class Environment {

  const SOAP_NAMESPACE = 'ns1';
  const BASE_URL = 'paygate.co.za';
  const TEST = 'test';
  const LIVE = 'live';

  /**
   * Environment constructor.
   *
   * @param int $environment
   *   Environment type. Must be one of class constants ("TEST" or "LIVE").
   */
  public function __construct($environment) {
    if (!in_array($environment, [static::TEST, static::LIVE], TRUE)) {
      throw new \InvalidArgumentException('You may select between test and live environments.');
    }
  }

  /**
   * Instantiate environment from settings of payment method.
   *
   * @param array $settings
   *   Payment method settings.
   *
   * @return static
   */
  public static function createFromSettings(array $settings) {
    if (!isset($settings['mode'])) {
      $settings['mode'] = static::TEST;
    }

    return new static($settings['mode']);
  }

  /**
   * Returns URL to PayHost WSDL.
   *
   * @return string
   *   PayHost WSDL.
   */
  public function getPayHostWsdl() {
    return sprintf('https://secure.%s/PayHost/process.trans?wsdl', static::BASE_URL);
  }

  /**
   * Perform API request.
   *
   * @param string $method
   *   One of API methods.
   * @param array $data
   *   Data for API call.
   *
   * @return \stdClass
   *   API response.
   */
  public function request($method, array $data) {
    $client = new \SoapClient($this->getPayHostWsdl(), [
      'trace' => TRUE,
      'exceptions' => TRUE,
    ]);

    // On PHP less than 7 method "__toString()" for the "\SimpleXMLElement"
    // object is not called automatically.
    /* @see \Drupal\commerce_utils\Tests\Component\Xml\XmlArrayTest::assertXml() */
    $xml = (string) (new XmlArray("<$method />"))
      ->addChild($method . 'Request', $data, static::SOAP_NAMESPACE);

    try {
      $response = $client->__soapCall($method, [
        new \SoapVar($xml, XSD_ANYXML),
      ]);
    }
    catch (\SoapFault $e) {
      if (isset($e->detail) && !empty($e->detail->error)) {
        // In a case of single error there'll be only a string.
        // Otherwise - an array of strings.
        throw new MultiErrorException($e->getMessage(), (array) $e->detail->error);
      }
    }

    if (empty($response)) {
      throw new \RuntimeException(t('as'));
    }

    return $response;
  }

}
