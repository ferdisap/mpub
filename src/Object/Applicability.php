<?php 

namespace Ptdi\Mpub\Object;

use DOMXPath;
use Ptdi\Mpub\Publisher\Message;
use ReflectionClass;

/**
 * Shall be used in CCT or ACT class
 */
trait Applicability
{
  /**
   * @return string
   * @return null
   */
  public function getValueDataType(string $applicPropertyIdent){
    $reflex = new ReflectionClass($this);
    switch($reflex->getShortName()){
      case 'ACT':
        $productAttribute = $this->getProductAttribute($applicPropertyIdent);
        $valueDataType = $productAttribute ? $productAttribute->getAttribute('valueDataType') : null;
        return $valueDataType;
        break;
      case 'CCT':
        $condType = $this->getCondType($applicPropertyIdent);
        $valueDataType = $condType ? $condType->getAttribute('valueDataType') : null;
        return $valueDataType;
        break;
    }
  }

  /**
   * 1. fungsi ini digunakan jika valueDataType == 'string' atau jika <enumeration> di ACT/CCT tidak ada, pakai fungsi ini dan force $valueDataType ke string.
   * 2. Untuk menggunakan fungsi ini, pastikan attribute @valuePattern di data module memiliki capturing Group untuk di iterate/tidak. 
   * Misal /(N219)/ match with string "N219", otherwise if not grouped, it will abandoned even regex is matched.
   * 
   * @return mixed value for ranging/iterate. Bisa integer bisa juga string untuk di iterate
   * @return false jika pattern tidak ada padahal wajib ada atau jika subject tidak sesuai dengan pattern
   */
  public function validateTowardsPattern(string $applicPropertyIdent, string $subject, $valueDataType = 'string'){
    $valueDataType = $valueDataType ?: 'string';
    if ($valueDataType != 'string') {
      return $subject;
    }
    $valuePattern = $this->isexistValuePattern($applicPropertyIdent);
    if($valuePattern){
      preg_match_all($valuePattern, $subject, $matches, PREG_SET_ORDER);
      $match = $matches[0][0];
      $value = $matches[0][1] ?: $match;
      if ($match) {
        return $value;
      } else {
        Message::generate(300, "@applicPropertyValues is not comply with @valuePattern");
        return false;
      }
    }
    Message::generate(300, "attribute @valuePattern should be exist");
    return false;
  }

  /**
   * @return string
   * @return false
   */
  public function isexistValuePattern(string $applicPropertyIdent){
    $domXpath = new DOMXPath($this->getDOMDocument());
    $query_valuePattern = "//@valuePattern[parent::*/@id = '{$applicPropertyIdent}']";

    $valuePattern = $domXpath->evaluate($query_valuePattern);
    if($valuePattern){
      return $valuePattern[0] ? $valuePattern[0]->value : false;
    } else {
      return false;
    }
  }

  /**
   * @return string
   * @return false
   */
  public function getApplicPropertyValuesFromCrossRefTable(string $applicPropertyIdent){
    $reflex = new ReflectionClass($this);
    $domXpath = new DOMXPath($this->getDOMDocument());
    switch($reflex->getShortName()){
      case 'ACT':
        $query_enum = "//enumeration[parent::*/@id = '{$applicPropertyIdent}']/@applicPropertyValues";
        break;
      case 'CCT':
        $query_condTypeRefId = "//cond[@id = '{$applicPropertyIdent}']/@condTypeRefId";
        $condTypeRefId = $domXpath->evaluate($query_condTypeRefId);
        $condTypeRefId = $condTypeRefId[0]->value;

        $query_enum = "//enumeration[parent::*/@id = '{$condTypeRefId}']/@applicPropertyValues";
        break;
    }
    $enums = $domXpath->evaluate($query_enum);

    //  if enums is not exist, use valuePattern as propertyValues
    if(!$enums || count($enums) == 0){
      $pattern = $this->isexistValuePattern($applicPropertyIdent);
      if($pattern){
        $propertyValue = trim($pattern);
        $propertyValue = substr_replace($propertyValue, "", 0,1);
        $propertyValue = substr_replace($propertyValue, "", strlen($propertyValue)-1,1); 
        return $propertyValue;
      }
      return false;
    }

    return $enums[0]->value;
  }
}
