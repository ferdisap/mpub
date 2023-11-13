<?php 

namespace Ptdi\Mpub;

use DOMElement;
use DOMNodeList;
use DOMXPath;

/**
 * 
 */
trait Validation
{
  public static function validate(string $type = null, \DOMDocument $doc, $validator = null, $absolute_path = '')
  {
    if($type == 'BREX'){
      return self::validateByBrex($doc, $validator, $absolute_path);
    }
  }

  /**
   * @return false with errors that you can get by function get_errors
   * @return true
   */
  private static function validateByBrex(\DOMDocument $doc, $validator = null, $absolute_path = null)
  {
    $domXpath = new DOMXPath($doc);
    $brexDoc = $domXpath->evaluate("//identAndStatusSection/descendant::brexDmRef");
    if($brexDoc->length == 0){
      $docIdent = CSDB::resolve_DocIdent($doc);
      CSDB::setError('validateByBrex', "element <brexDmRe> cannot found in identAndStatusSection of {$docIdent}");
      return false;
    } else {
      $brexDoc = $brexDoc[0];
      $brexDoc = CSDB::resolve_dmIdent($brexDoc);
      $path = !empty($absolute_path) ? $absolute_path : $doc->absolute_path;
      $brexDoc = CSDB::importDocument($path."/{$brexDoc}");
    }

    $schema = CSDB::getSchemaUsed($brexDoc,'filename');
    $domXpath = new DOMXPath($brexDoc);
    $contexRules = $domXpath->evaluate("//contextRules[not(@rulesContext)] | //contextRules[@rulesContext = '{$schema}']");

    foreach($contexRules as $contextRule){
      $structureObjectRuleGroup = $contextRule->firstElementChild;
      $structureObjectRules = CSDB::get_childrenElement($structureObjectRuleGroup,'');
      foreach ($structureObjectRules as $structureObjectRule){
        self::validateByStructureObjectRule($doc, $structureObjectRule);
      }
    }
    
    $errors = CSDB::get_errors(false,'validateByStructureObjectRule');
    return empty($errors) ? true: false;
  }

  /**
   * php tidak bisa pakai fungsi xpath //applic/child::*\/name(), melainkan local-name(//applic/child::*)
   */
  private static function validateByStructureObjectRule(\DOMDocument $doc, \DOMElement $structureObjectRule)
  {
    $docIdent = CSDB::resolve_DocIdent($doc);

    $id = $structureObjectRule->getAttribute(('id'));

    $brDecisionRef = $structureObjectRule->getElementsByTagName('brDecisionRef'); // DOM Element
    $brDecisionIdentNumber = array();
    foreach ($brDecisionRef as $value) {
      $brDecisionIdentNumber[] = $value->getAttribute('brDecisionIdentNumber');
    }
    $brDecisionIdentNumber = join(", ", $brDecisionIdentNumber);

    $objectPath = $structureObjectRule->getElementsByTagName('objectPath')[0]; // DOM Element
    $allowedObjectFlag = $objectPath->getAttribute('allowedObjectFlag');

    $objectUse = $structureObjectRule->getElementsByTagName('objectUse')[0] ?? new DOMElement('foo'); // DOM element

    $objectValues = $structureObjectRule->getElementsByTagName('objectValue'); // nodeList
    $values = array();
    foreach ($objectValues as $v) {
      $valueForm = $v->getAttribute('valueForm');
      $valueAllowed = $v->getAttribute('valueAllowed');
      $valueText = $v->nodeValue;

      $values[] = [
        'valueForm' => $valueForm,
        'valueAllowed' => $valueAllowed,
        'valueText' => $valueText,
      ];
    }
    $objectValues = $values;
    unset($values);
    
    // jika objectPath result boolean
    $domXpath = new DOMXPath($doc);
    $results = $domXpath->evaluate($objectPath->nodeValue);

    // jika result boolean
    // jika result bernilai benar dan diperbolehkan, maka aman
    if(is_bool($results) AND $results == true AND $allowedObjectFlag == 1){
      return; // kalau true dan $allowedObject=1, misal //originator/@enterpriseCode='0001Z' berarti ga ada masalah (udah sesuai dengan rule)
    }
    // jika result bernilai benar, tapi tidak diperbolehkan, maka fail
    elseif(is_bool($results) AND $results == true AND $allowedObjectFlag == 0) {
      CSDB::setError('validateByBrex', "Document: {$docIdent}, the value does not match. id:{$id}, BR number:{$brDecisionIdentNumber}. Object Use: {$objectUse->nodeValue}");
      return;
    } 
    // jika result bernilai false, maka fail karena harusnya diperbolehkan (harus true)
    elseif(is_bool($results) AND $results == false AND $allowedObjectFlag == 1) {
      CSDB::setError('validateByBrex', "Document: {$docIdent}, the value does not match. id:{$id}, BR number:{$brDecisionIdentNumber}. Object Use: {$objectUse->nodeValue}");
      return;
    } 
    // jika result bernilai false dan juga tidak diperbolehkan, maka aman
    elseif(is_bool($results) AND $results == false AND $allowedObjectFlag == 0) {
      return;
    } 
    // jika tidak ada yang ditemukan, berarti aman (tidak perlu di validasi)
<<<<<<< HEAD
    // elseif (count($results) == 0){
    elseif (is_array($results) AND count($results) == 0){
=======
    elseif (count($results) == 0){
    // elseif (is_bool($results) AND count($results) == 0){
>>>>>>> d72ae2c0f9e2a0288e084bd5e5a6761bc5b43261
      return;
    }

    $res = array();
    if(is_iterable($results) AND $results instanceof \DOMNodeList){
      foreach($results as $r){
        $res[]['value'] = $r->nodeValue;
      }
    } else {
      $res[]['value'] = $results;
    }
    $results = $res;
    unset($res);
    // dd($results);

    // configurasi
    $type = '';
    if($allowedObjectFlag == 0 AND !empty($objectValues)){
      // jika ada objectValue yang match maka fail
      $type = 'value_is_not_allowed';
    }
    elseif($allowedObjectFlag == 0 AND empty($objectValues)){
      // berarti jika ada result yang match, maka fail
      $type = 'objectPath_is_not_allowed';
    }
    elseif($allowedObjectFlag == 1 AND !empty($objectValues)){
      // berarti jika Xpath pada result match tapi tidak satupun value match, maka fail
      $type = 'value_is_allowed';
    }
    elseif($allowedObjectFlag == 1 AND empty($objectValues)){
      // jika results itu null, maka fail. Karena @allowedObjectFlag=1 berarti HARUS match
      $type = 'objectPath_is_allowed';
    }

    if($type == 'objectPath_is_not_allowed'){
      if(count($results) > 0){
        CSDB::setError('validateByBrex', "Document: {$docIdent}, the value does not match. id:{$id}, BR number:{$brDecisionIdentNumber}. Object Use: {$objectUse->nodeValue}");
      }
    }
    elseif($type == 'objectPath_is_allowed'){
      // jika results itu null, maka fail. Karena @allowedObjectFlag=1 berarti HARUS match
      if(count($results) == 0){
        CSDB::setError('validateByBrex', "Document: {$docIdent}, the value does not match. id:{$id}, BR number:{$brDecisionIdentNumber}. Object Use: {$objectUse->nodeValue}");
      }
    }

    // validate type
    $lengthV = count($objectValues);
    foreach($results as $result){
      foreach ($objectValues as $key => $value) {
        if($type == 'value_is_not_allowed'){
          if(isset($value['valueForm']) AND isset($value['valueAllowed'])){
            if($value['valueForm'] == 'single'){
              if($result['value'] != $value['valueAllowed']){
                CSDB::setError('validateByBrex', "Document: {$docIdent}, the value does not match. id:{$id}, BR number:{$brDecisionIdentNumber}. Object Use: {$objectUse->nodeValue} Value Text: {$value['valueText']}");
              }
            }
            elseif($value['valueForm'] == 'pattern'){
              preg_match_all($value['valueAllowed'], $result['value'], $matches);
  
              // untuk check jika ada value yang tidak kosong (matched) maka fail
              $m = function($matches, $m) use ($docIdent, $id, $brDecisionIdentNumber, $objectUse, $value){
                $k = 0;
                $l = count($matches);
                while(is_array($matches) AND $k < $l){
                  if(is_array($matches[$k]) AND !empty($matches[$k])){
                    $m($matches[$k], $m);
                  }
                  elseif(!empty($matches[$k])){
                    CSDB::setError('validateByBrex', "Document: {$docIdent}, the value does not match. id:{$id}, BR number:{$brDecisionIdentNumber}. Object Use: {$objectUse->nodeValue} Value Text: {$value['valueText']}");
                  }
                  $k++;
                }
              };
              $m($matches, $m);
            }
            elseif($value['valueForm'] == 'range'){
              $range = explode('~', $value['valueAllowed']);
              $range = range($range[0], $range[1]);
              // jika nodeValue ada di range, maka fail (karena @allowedObjectFlag = 0)
              if(!in_array($result['value'], $range)){
                CSDB::setError('validateByBrex', "Document: {$docIdent}, the value does not match. id:{$id}, BR number:{$brDecisionIdentNumber}. Object Use: {$objectUse->nodeValue} Value Text: {$value['valueText']}");
              }
            }
          } 
        }
        elseif($type == "value_is_allowed"){
          if(isset($value['valueForm']) AND isset($value['valueAllowed'])){
            if($value['valueForm'] == 'single'){
              if($value['valueAllowed'] == $result['value']){
                break; // berarti udah ada yang match
              }
            }
            elseif($value['valueForm'] == 'pattern'){
              preg_match_all($value['valueAllowed'], $result['value'], $matches);

              // untuk check jika ada value match maka break. artinya udah ada yang benar
              $m = function($matches) use ($docIdent, $id, $brDecisionIdentNumber, $value){
                $k = 0;
                $l = count($matches);
                while(is_array($matches) AND $k < $l){
                  if(!empty($matches[$k])){
                    break; // berarti udah ada yang match.
                  }
                  $k++;
                }
              };
              $m($matches);
            }
            elseif($value['valueForm'] == 'range'){
              $range = explode('~', $value['valueAllowed']);
              $range = range($range[0], $range[1]);
              if(in_array($result['value'], $range)){
                break; // berarti udah ada yang match.
              }
            }
            // jika sampai value terakhir masih tidak ada yang match, berarti fail karena harus ada yang match
            elseif($key >= ($lengthV-1)){
              CSDB::setError('validateByBrex', "Document: {$docIdent}, the value does not match. id:{$id}, BR number:{$brDecisionIdentNumber}. Object Use: {$objectUse->nodeValue} Value Text: {$value['valueText']}");
            }
          }
        }
      }
    }
  }
}
