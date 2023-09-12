<?php 

namespace Ptdi\Mpub\Object;

use DOMXPath;
use Exception;
use Ptdi\Mpub\Object\DModule;

class ACT extends DModule {
  use Applicability;

  public $CCT;
  public $PCT;
  public array $productAttributes;

  // public function __construct(string $filename)
  // {
  //   parent::__construct($filename);
  //   if(DModule::getSchemaName($this->getDOMDocument()->firstElementChild) != 'appliccrossreftable')
  //   {
  //     throw new Exception("The DModule is not type of ACT data module", 1);
  //   }

  //   if ($CCT = new CCT($this->getCCTnPCT_name(2).".xml")){
  //     $this->CCT = $CCT;
  //   }
  //   if ($PCT = new PCT($this->getCCTnPCT_name(3).".xml")){
  //     $this->PCT = $PCT;
  //   }
  // }
  public function __construct($prefix, $modelIdentCode)
  {
    parent::__construct($prefix, $modelIdentCode);
  }

  public function setReferedCCTandPCT(){
    $cct = new CCT('DMC', $this->modelIdentCode);
    $cct->import_DOMDocument($this->getCCTnPCT_name(2).".xml", 'condcrossreftable.xsd');
    $this->CCT = $cct;

    $pct = new PCT('DMC', $this->modelIdentCode);
    $pct->import_DOMDocument($this->getCCTnPCT_name(3).".xml", 'prdcrossreftable.xsd');
    $this->PCT = $pct;
  }

  private function getCCTnPCT_name(int $dmType)
  {
    return parent::getDMName($this->getDOMDocument(), $dmType);
  }

  /**
   * @return \DOMElement 
   */
  public function getProductAttribute(string $applicPropertyIdent){
    $domXpath = new DOMXPath($this->getDOMDocument());
    $query_productAttribute = "//productAttribute[@id = '{$applicPropertyIdent}']";

    $productAttribute = $domXpath->evaluate($query_productAttribute);
    if($productAttribute){
      return $productAttribute[0] ? $productAttribute[0] : null;
    } else {
      return null;
    }
  }

  // /**
  //  * @return string
  //  * @return null
  //  */
  // public function getValueDataType(string $applicPropertyIdent){
  //   $productAttribute = $this->getProductAttribute($applicPropertyIdent);
  //   $valueDataType = $productAttribute ? $productAttribute->getAttribute('valueDataType') : null;
  //   return $valueDataType;
  // }
}