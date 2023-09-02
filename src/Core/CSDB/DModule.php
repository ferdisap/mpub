<?php 

namespace Ptdi\Mpub\Core\CSDB;

use Ptdi\Mpub\Core\Contracts\Brex;
use Ptdi\Mpub\Core\CSDB;
use Ptdi\Mpub\Schema\Schema;

class DModule extends CSDB implements Brex{

  private \DOMDocument $DOMDocument;

  public function __construct(String $filename)
  {
    $doc = parent::load($filename);
    $this->DOMDocument = $doc->firstElementChild->nodeName == 'dmodule' ? $doc : new \DOMDocument();
    return $this;
  }

  public function validateToBrex()
  {
    dd('validateToBrex is under maintenance');
  }

  public function getDOMDocument()
  {
    return $this->DOMDocument;
  }

  public static function getSchemaPath(\DOMElement $element)
  {
    $schema_path = $element->getAttribute("xsi:noNamespaceSchemaLocation");
    if (!$schema_path){
      return false;
    } else {
      return $schema_path;
    }
  }

  public static function getSchemaName(\DOMElement $element)
  {
    $schema_path = self::getSchemaPath($element);
    preg_match('/[a-z]+(?=.xsd)/', $schema_path, $matches ,PREG_OFFSET_CAPTURE, 0);
    if ($matches){
      return $matches[0][0];
    } else {
      return false;
    }
  }

  /**
   * Validate DOMDocument by XSD
   * 
   * @param \DOMDocument $document The document should be validated
   * @param string $filename The xsd path
   */
  public static function validateToSchema(\DOMDocument $document, string $schemaName = null)
  {
    $schemaString = isset($schemaName) ? Schema::getSchemaString($schemaName) : Schema::getSchemaString(self::getSchemaPath($document->firstElementChild));
    $status = $document->schemaValidateSource($schemaString, LIBXML_PARSEHUGE);
    return $status;
  }
  
}