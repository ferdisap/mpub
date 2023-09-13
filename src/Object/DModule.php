<?php

namespace Ptdi\Mpub\Object;

use DOMXPath;
use Error;
use Exception;
use Ptdi\Mpub\Object\ACT;
use Ptdi\Mpub\CSDB;
use Ptdi\Mpub\Csdb\Element\Dmodule as ElementDmodule;
use Ptdi\Mpub\Publisher\Element;
use Ptdi\Mpub\Publisher\ElementList;
use Ptdi\Mpub\Schema\Schema;

class DModule extends CSDB
{
  private \DOMDocument $DOMDocument;
  public array $schemaValidate = ["status"=> false, "errors" => null];
  public string $prefix;
  public string $filename;

  /**
   * @param string $filename the name of the data module file, with format extension
   * @param string $prefix the data module name prefix, DMC or PMC. Used to resolve DM Name
   */
  public function __construct(string $prefix = "DMC", string $modelIdentCode = '')
  {
    $this->prefix = $prefix;
    $this->modelIdentCode = $modelIdentCode;
  }

  /**
   * @param string $schemaName is with extension .xsd
   * @return \DOMDocument
   */
  public function create_blankDocument(string $schemaName)
  {
    $doc = new \DOMDocument('1.0', "UTF-8");
    switch ($schemaName) {
      case 'dml.xsd':
        $dmodule = $doc->createElement('dml', '');
        break;
      
      default:
        $dmodule = $doc->createElement('dmodule', '');
        break;
    }
    $doc->appendChild($dmodule);
    
    $noNamespaceSchemaLocation = $doc->createAttributeNS("http://www.w3.org/2001/XMLSchema-instance", 'xsi:noNamespaceSchemaLocation');
    $noNamespaceSchemaLocation->value = "../mpub/src/Schema/dml.xsd";

    $dmodule->appendChild($noNamespaceSchemaLocation);
    return $this->DOMDocument = $doc;
  }

  /**
   * @param string $filename is output file with extension
   */
  public function writeToFile(string $app_path, string $filename)
  {
    switch ($this->getSchemaName($this->getDOMDocument()->firstElementChild)) {
      case 'dml.xsd':
        $objectType = "data_management_list";
        break;
      
      default:
        $objectType = "data_module";
        break;
    }
    $path = $this->setCsdbPath($app_path).($this->schemaValidate['status'] ? '' : $objectType.'\unvalidated\\').$filename;
    file_put_contents($path, $this->getDOMDocument());
  }

  /**
   * @param string $filename with extension
   * @param string $schemaName with extension .xsd
   */
  public function import_DOMDocument(string $filename, string $schemaName = null)
  {
    $doc = $this->load($filename);
    // $this->DOMDocument = $doc->firstElementChild->nodeName == 'dmodule' ? $doc : new \DOMDocument();
    $this->DOMDocument = $doc;
    if($schemaName){
      if($schemaName != DModule::getSchemaName($this->DOMDocument->firstElementChild)){
        throw new Exception("The DModule is not type of schema {$schemaName}", 1);
      }
    }
  }

  public function validateTowardsXSI(string $schemaName = null)
  {
    if($document = $this->DOMDocument){
      libxml_use_internal_errors(true);
      $schemaString = isset($schemaName) ? Schema::getSchemaString($schemaName) : Schema::getSchemaString(self::getSchemaName($document->firstElementChild));
      $status = $document->schemaValidateSource($schemaString, LIBXML_PARSEHUGE);
      $errors = libxml_get_errors();
      return $this->schemaValidate = ["status"=> $status, "errors" => $errors];
    }
    return $this->schemaValidate = ["status"=> false, "errors" => null];
  }
  // public function __construct(string $filename, string $prefix = "DMC")
  // {
  //   $doc = parent::load($filename);
  //   $this->filename = ($doc ? $filename : null);
  //   $this->DOMDocument = $doc->firstElementChild->nodeName == 'dmodule' ? $doc : new \DOMDocument();
  //   // dd($this->DOMDocument->firstElementChild);
  //   // dd($this->getSchemaName($this->DOMDocument->firstElementChild), 'aaa');
  //   $this->schemaValidate = $this->validateToSchema($this->DOMDocument, $this->getSchemaName($this->DOMDocument->firstElementChild));
  //   $this->prefix = $prefix;
  //   return $this;
  // }

  /**
   * @return string schema path with file name extension
   */
  public static function getSchemaPath(\DOMElement $element)
  {
    $schema_path = $element->getAttribute("xsi:noNamespaceSchemaLocation");
    if (!$schema_path) {
      return false;
    } else {
      return $schema_path;
    }
  }

  /**
   * @return string schemaName with extension .xsd
   * @return false
   */
  public static function getSchemaName(\DOMElement $element = null)
  {
    $schema_path = self::getSchemaPath($element);
    preg_match('/[a-z]+(?=.xsd)/', $schema_path, $matches, PREG_OFFSET_CAPTURE, 0);
    if ($matches) {
      return $matches[0][0].".xsd";
    } else {
      return false;
    }
  }

  // /**
  //  * Validate DOMDocument by XSD
  //  * 
  //  * @param \DOMDocument $document The document should be validated
  //  * @param string $schemaName The xsd path. If null, it will used the internal schema which stated in DM attribute xsi:noNamespaceSchemaLocation
  //  */
  // public static function validateToSchema(\DOMDocument $document, string $schemaName = null)
  // {
  //   libxml_use_internal_errors(true);
  //   $schemaString = isset($schemaName) ? Schema::getSchemaString($schemaName.".xsd") : Schema::getSchemaString(self::getSchemaName($document->firstElementChild).".xsd");
  //   $status = $document->schemaValidateSource($schemaString, LIBXML_PARSEHUGE);
  //   $errors = libxml_get_errors();
  //   return ["status"=> $status, "errors" => $errors];
  // }

  public function getDOMDocument()
  {
    return $this->DOMDocument;
  }

  /**
   * @param \DOMDocument $doc is as source
   * @param int $dmType is data module type that want to get
   * 
   * @return string data module name
   */
  // public static function getDMName(DModule $dmodule, int $dmType = 0)
  public static function getDMName(\DOMDocument $doc, int $dmType = 0)
  {
    require "dmType.php";
    // dump(DModule::getSchemaName($doc->firstElementChild),$getDMName);
    if(!isset($getDMName[$dmType])){
      throw new Exception("No such dmType");      
    }
    $domXpath = new DOMXPath($doc);

    $query_dmCode = $getDMName[$dmType]['xpath']['dmCode'];
    $query_issueInfo = $getDMName[$dmType]['xpath']['issueInfo'];
    
    $dmCode = $domXpath->evaluate($query_dmCode);
    $dmCode = get_class($dmCode) == "DOMNodeList" ? $dmCode->item(0) : throw new Error("Data module name cannot be resolved");
    $issueInfo = $domXpath->evaluate($query_issueInfo);
    $issueInfo = get_class($issueInfo) == "DOMNodeList" ? $issueInfo->item(0) : throw new Error("Data module name cannot be resolved");

    // dump(DModule::getSchemaName($doc->firstElementChild), $dmCode, $dmType);
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

    $name = (($prefix = $getDMName[$dmType]["prefix"]) ? $prefix : $doc->prefix)."-".
    $modelIdentCode."-".$systemDiffCode."-".
    $systemCode."-".$subSystemCode.$subSubSystemCode."-".
    $assyCode."-".$disassyCode.$disassyCodeVariant."-".
    $infoCode.$infoCodeVariant."-".$itemLocationCode."_".
    $issueNumber."_".$inWork;

    return $name;
  }

  public function resolve(array $objectResolves = ['applicability'])
  {
    foreach ($objectResolves as $objectResolve)
    {
      $this->{"resolve".ucfirst($objectResolve)}();
    }
  }

  private function resolveApplicability()
  {
    // dd($this->getSchemaName($this->getDOMDocument()->firstElementChild));
    // $actdm_filename = DModule::getDMName($this->getDOMDocument(),1).".xml";
    // dd($this->modelIdentCode);
    $act = new ACT('DMC', $this->modelIdentCode);
    $act->import_DOMDocument(DModule::getDMName($this->getDOMDocument(),1).".xml", 'appliccrossreftable.xsd');
    $act->setReferedCCTandPCT();
    
    // dd($act);

    $applics = $this->getElementList("//applic");
    
    // dd($applics->item(0),__LINE__);
    // $id = $applics->item(0)->id;
    // dd($id);

    for ($i=0; $i < $applics->count(); $i++) { 
      $applics->item($i)->resolve($act);
    }
    dd($applics);
    // $applics->item(0)->resolve($act);
    // dd($applics->item(0)->resolve($act), $applics->item(0));
    dd($this,__CLASS__, __LINE__);
    // lanjut di sini
    
  }

  public function getElementList(string $xpathQuery)
  {
    $domXpath = new DOMXPath($this->getDOMDocument());

    $result = $domXpath->evaluate($xpathQuery);

    $list_elements = [];
    foreach ($result as $node) {
      if($node instanceof \DOMElement){
        array_push($list_elements, $node);
      }
    }

    return ElementList::createList($list_elements); 
  }


}
