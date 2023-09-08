<?php 

namespace Ptdi\Mpub\Object;

use DOMXPath;
use Exception;
use Ptdi\Mpub\Object\DModule;

class CCT extends DModule {
  use Applicability;
  
  public array $condType;
  public array $condList;

  public function __construct(string $filename)
  {
    parent::__construct($filename);
    if(DModule::getSchemaName($this->getDOMDocument()->firstElementChild) != 'condcrossreftable')
    {
      throw new Exception("The DModule is not type of CCT data module", 1);
    }
  }

  /**
   * @return \DOMElement 
   */
  public function getCondType(string $applicPropertyIdent){
    $domXpath = new DOMXPath($this->crossRefTable->getDOMDocument());
    $query_condTypeRefId = "//cond[@id = '{$applicPropertyIdent}']/@condTypeRefId";
    $condTypeRefId = $domXpath->evaluate($query_condTypeRefId);
    $condTypeRefId = $condTypeRefId[0]->value;

    $query_condType = "//condType[@id = '{$condTypeRefId}']";

    $condType = $domXpath->evaluate($query_condType);
    if($condType){
      return $condType[0] ? $condType[0] : null;
    } else {
      return null;
    }
  }

  // /**
  //  * @return string
  //  * @return null
  //  */
  // public function getValueDataType(string $applicPropertyIdent){
  //   $condType = $this->getCondType($applicPropertyIdent);
  //   $valueDataType = $condType ? $condType->getAttribute('valueDataType') : null;
  //   return $valueDataType;
  // }
}