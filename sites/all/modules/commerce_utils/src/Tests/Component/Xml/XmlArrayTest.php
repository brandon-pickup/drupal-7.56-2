<?php

namespace Drupal\commerce_utils\Tests\Component\Xml;

use Drupal\commerce_utils\Tests\UnitTest;
use Commerce\Utils\Component\Xml\XmlArray;

/**
 * Test conversion of arrays into proper XML.
 */
class XmlArrayTest extends UnitTest {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return static::info('Tests a XmlArray component.');
  }

  /**
   * Test array to XML conversion.
   */
  public function test() {
    $array = [
      'one' => [
        'two' => [
          'three' => [
            'four' => 4,
            'test' => '__test',
            'object' => (new XmlArray('<object />'))->addChild('object', [
              'one' => 1,
              'two' => [
                'three' => 3,
              ],
            ]),
          ],
        ],
      ],
      'two' => [
        'three' => 3,
      ],
      'three' => 3,
      'four' => [4, 3, 2],
    ];

    $xml = <<<XML
<test>
  <one>
    <two>
      <three>
        <four>4</four>
        <test>__test</test>
        <object>
          <one>1</one>
          <two>
            <three>3</three>
          </two>
        </object>
      </three>
    </two>
  </one>
  <two>
    <three>3</three>
  </two>
  <three>3</three>
  <four>4</four>
  <four>3</four>
  <four>2</four>
</test>
XML;

    $this->assertXml((new XmlArray('<test />'))->addChild('test', $array), $xml);

    $array = [
      'bla' => [
        'key' => 1,
      ],
    ];

    $xml = <<<XML
<namespace:test>
  <namespace:bla>
    <namespace:key>1</namespace:key>
  </namespace:bla>
</namespace:test>
XML;

    $this->assertXml((new XmlArray('<test />'))->addChild('test', $array, 'namespace'), $xml);
  }

  /**
   * Assert XML.
   *
   * @param \Commerce\Utils\Component\Xml\XmlArray $array
   *   The first value to check.
   * @param string $xml
   *   The second value to check.
   */
  protected function assertXml(XmlArray $array, $xml) {
    // Convert beautified XML into a single line because "XmlArray" forms
    // it in this format.
    $xml = trim(preg_replace('/>\s+</', '><', $xml));
    // On PHP less than 7 method "__toString()" for the "\SimpleXMLElement"
    // object is not called automatically.
    $array = trim((string) $array);

    $this->assertTrue($xml === $array, sprintf('Value %s is equal to %s.', $xml, $array));
  }

}
