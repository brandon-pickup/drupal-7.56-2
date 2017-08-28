<?php

namespace Commerce\Utils\Component\Xml;

/**
 * A component to covert nested array into XML markup.
 */
class XmlArray extends \SimpleXMLElement {

  /**
   * {@inheritdoc}
   */
  public function addChild($name, $value = NULL, $namespace = NULL) {
    if (empty($value)) {
      return $this;
    }

    if (NULL !== $namespace) {
      $name = "$namespace:$name";
    }

    if (is_object($value) && method_exists($value, 'toArray')) {
      $value = $value->toArray();
    }

    if (is_array($value)) {
      $children = array_keys($value);
      // If all keys are numeric - create a sequence of elements, named as
      // a parent.
      $element = array_filter($children, 'is_numeric') === $children ? $this : parent::addChild($name, NULL, $namespace);

      foreach ($value as $key => $item) {
        $element->addChild(is_numeric($key) ? $name : $key, $item);
      }

      return $this;
    }

    return parent::addChild($name, $value, $namespace);
  }

  /**
   * Convert XML into an array.
   *
   * @return array
   *   Array representation of XML.
   */
  public function toArray() {
    // We need to forcibly cast the "string" type to provide operability on
    // PHP 5.6. By some reason, without casting a type, it returns "FALSE".
    // On PHP 7+ it works well even without.
    return json_decode(json_encode(simplexml_load_string((string) $this, static::class, LIBXML_NOCDATA)), TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    $root = $this->getName();
    // First line is always XML header, like <?xml version="1.0">.
    $value = implode("\n", array_slice(explode("\n", $this->asXML()), 1));
    $regexps = [];

    // Remove "xmlns:ns="ns"" attributes.
    foreach ($this->getDocNamespaces(TRUE) as $namespace) {
      $regexps[] = '\s+' . preg_quote(sprintf('xmlns:%s="%1$s"', $namespace));
    }

    return preg_replace(
      [
        "/^<$root></",
        "/><\/$root>$/",
        '/' . implode('|', $regexps) . '/',
      ],
      [
        '<',
        '>',
        '',
      ],
      $value
    );
  }

}
