<?php

namespace Ptdi\Mpub\Object;

use DOMXPath;
use Error;
use Exception;
use Ptdi\Mpub\CSDB;
use Ptdi\Mpub\Schema\Schema;

class DModule extends CSDB
{
  private \DOMDocument $DOMDocument;
  public array $schemaValidate;
  public string $prefix;

  /**
   * @param string $filename the name of the data module file, with format extension
   * @param string $prefix the data module name prefix, DMC or PMC.
   */
  public function __construct(string $filename, string $prefix = "DMC")
  {
    $doc = parent::load($filename);
    $this->DOMDocument = $doc->firstElementChild->nodeName == 'dmodule' ? $doc : new \DOMDocument();
    $this->schemaValidate = $this->validateToSchema($this->DOMDocument);
    $this->prefix = $prefix;
    return $this;
  }

  public static function getSchemaPath(\DOMElement $element)
  {
    $schema_path = $element->getAttribute("xsi:noNamespaceSchemaLocation");
    if (!$schema_path) {
      return false;
    } else {
      return $schema_path;
    }
  }

  public static function getSchemaName(\DOMElement $element)
  {
    $schema_path = self::getSchemaPath($element);
    preg_match('/[a-z]+(?=.xsd)/', $schema_path, $matches, PREG_OFFSET_CAPTURE, 0);
    if ($matches) {
      return $matches[0][0];
    } else {
      return false;
    }
  }

  /**
   * Validate DOMDocument by XSD
   * 
   * @param \DOMDocument $document The document should be validated
   * @param string $schemaName The xsd path. If null, it will used the internal schema which stated in DM attribute xsi:noNamespaceSchemaLocation
   */
  public static function validateToSchema(\DOMDocument $document, string $schemaName = null)
  {
    libxml_use_internal_errors(true);
    
    $schemaString = isset($schemaName) ? Schema::getSchemaString($schemaName) : Schema::getSchemaString(self::getSchemaPath($document->firstElementChild));
    $status = $document->schemaValidateSource($schemaString, LIBXML_PARSEHUGE);
    $errors = libxml_get_errors();
    return ["status"=> $status, "errors" => $errors];
  }

  public function getDOMDocument()
  {
    return $this->DOMDocument;
  }

  /**
   * @param DModule $dmodule is as source
   * @param int $dmType is data module type
   * 
   * @return string data module name
   */
  public static function resolveDMName(DModule $dmodule, int $dmType = 0)
  {
    require_once "dmType.php";
    if(!isset($resolveDMName[$dmType])){
      throw new Exception("No such dmType");      
    }
    $doc = $dmodule->getDOMDocument();
    $domXpath = new DOMXPath($doc);

    $query_dmCode = $resolveDMName[$dmType]['xpath']['dmCode'];
    $query_issueInfo = $resolveDMName[$dmType]['xpath']['issueInfo'];

    
    $dmCode = $domXpath->evaluate($query_dmCode);
    $dmCode = get_class($dmCode) == "DOMNodeList" ? $dmCode->item(0) : throw new Error("Data module name cannot be resolved");
    $issueInfo = $domXpath->evaluate($query_issueInfo);
    $issueInfo = get_class($issueInfo) == "DOMNodeList" ? $issueInfo->item(0) : throw new Error("Data module name cannot be resolved");

    $modelIdentCode = $dmCode->getAttribute('modelIdentCode');
    $systemDiffCode = $dmCode->getAttribute('systemDiffCode');
    $systemCode = $dmCode->getAttribute('systemCode');
    $subSystemCode = $dmCode->getAttribute('subSystemCode');
    $subSubSystemCode = $dmCode->getAttribute('subSubSystemCode');
    $assyCode = $dmCode->getAttribute('assyCode');
    $disassyCode = $dmCode->getAttribute('disassyCode');
    $disassyCodeVariant = $dmCode->getAttribute('disassyCodeVariant');
    $infoCode = $dmCode->getAttribute('infoCode');
    $infoCodeVariant = $dmCode->getAttribute('infoCodeVariant');
    $itemLocationCode = $dmCode->getAttribute('itemLocationCode');

    $issueNumber = $issueInfo->getAttribute('issueNumber');
    $inWork = $issueInfo->getAttribute('inWork');

    $name = (($prefix = $resolveDMName[$dmType]["prefix"]) ? $prefix : $doc->prefix)."-".
    $modelIdentCode."-".$systemDiffCode."-".
    $systemCode."-".$subSystemCode.$subSubSystemCode."-".
    $assyCode."-".$disassyCode.$disassyCodeVariant."-".
    $infoCode.$infoCodeVariant."-".$itemLocationCode."_".
    $issueNumber."_".$inWork;

    return $name;
  }
}
