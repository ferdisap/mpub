<?php

use Ptdi\Mpub\Main\CSDBError;
use Ptdi\Mpub\Main\CSDBObject;
use Ptdi\Mpub\Main\CSDBStatic;
use Ptdi\Mpub\Main\CSDBValidator;
use Ptdi\Mpub\Main\Helper;

class BREXValidator extends CSDBValidator {

  /**
   * @param mixed $validatee adalah absolute path XML document atau \CSDBObject class
   * @param mixed $validator adalah absolute path XML document atau \CSDBObject class
   * @return \BREXValidator
   */
  public function __construct(mixed $validatee, mixed $validator)
  {
    $this->validationType = 'BREX';
    if(is_string($validatee)){
      $this->validatee = new CSDBObject("5.0");    
      $this->validatee->load($validatee);
    }
    elseif($validatee instanceof CSDBObject){
      $this->validatee = $validatee;
    }
    if(is_string($validator)){
      $this->validator = new CSDBObject("5.0");    
      $this->validator->load($validator);
    }
    elseif($validator instanceof CSDBObject){
      $this->validator = $validator;
    }
    return $this;
  }

  public static function initiate(string $validatee)
  {
    $ValidateeCSDBObject = new CSDBObject("5.0");
    $ValidateeCSDBObject->load($validatee);

    $domXpath = new \DOMXPath($ValidateeCSDBObject->document);
    $validatorElement = $domXpath->evaluate("//identAndStatusSection/descendant::brexDmRef")[0];
    if(!$validatorElement) return false;
    $ValidatorCSDBObject = new CSDBObject("5.0");
    $ValidatorCSDBObject->loadByElement($validatorElement, 'dmodule');

    if($ValidateeCSDBObject AND $ValidatorCSDBObject){
      return new self($ValidateeCSDBObject, $ValidatorCSDBObject);
    }

  } 
  
  public function validate() :bool
  {
    return $this->validateByBrex();
  }

  public function validateByBrex()
  {
    if(!($this->validatee->document instanceof \DOMDocument) OR 
      !$this->validatee->document->documentElement OR
      !($this->validator->document instanceof \DOMDocument) OR
      !$this->validator->document->documentElement
    ) {
      CSDBError::setError('validateBySchema', "There is no document to validate.");
      return false;
    };

    if($this->validatee->BREXValidationResult) return true;

    $schema = $this->validatee->getSchema('filename');
    $domXpath = new DOMXPath($this->validator->document);
    $contexRules = $domXpath->evaluate("//contextRules[not(@rulesContext)] | //contextRules[@rulesContext = '{$schema}']");

    foreach ($contexRules as $contextRule) {
      $structureObjectRuleGroup = $contextRule->firstElementChild;
      $notationRuleList = $structureObjectRuleGroup->nextElementSibling;
      $structureObjectRules = Helper::children($structureObjectRuleGroup);
      foreach ($structureObjectRules as $structureObjectRule) {
        self::validateByStructureObjectRule($structureObjectRule, $schema);
      }
      foreach ($notationRuleList as $notationRule) {
        self::validateByNotationRule($notationRule);
      }
    }
    $errors = CSDBError::getErrors(false, 'validateByBrex');
    if(empty($errors)){
      $this->validatee->XSIValidationResult = true;
      return true;
    } else {
      $this->validatee->XSIValidationResult = false;
      return false;
    }
  }

  /**
   * belum di uji
   * jika tidak ada attribute @allowedNotationFlag maka tidak akan di validasi
   */
  private function validateByNotationRule(\DOMElement $notationRule)
  {
    $notationName = $notationRule->getElementsByTagName('notationName')[0];
    $allowedNotationFlag = $notationName->getAttribute('allowedNotationFlag');
    if ($allowedNotationFlag) {
      $entities = $this->validatee->document->doctype->entities;
      foreach ($entities as $entity) {
        // $entity->systemId hanyalah sebuhah filename/relative path.
        // nanti pengecekan notation tidak lagi menggunakan filename/relative path tapi menggunakan mime_content_type. 
        // Resikonya adalah jika mime tidak dikenal php, maka akan validasi selalu salah
        // jadi sebaiknya selalu gunakan extension/format pada filename
        if ($allowedNotationFlag === '1') {
          if(strtolower($entity->notationName) !== strtolower($notationName)){
              CSDBError::setError('validateByBrex', "the notationName of {$entity->systemId} shall included {$notationName->nodeValue}");
          }
        } elseif ($allowedNotationFlag === '0') {
          if(strtolower($entity->notationName) !== strtolower($notationName)){
            CSDBError::setError('validateByBrex', "the notationName of {$entity->systemId} shall not included the {$notationName->nodeValue}");
          }
        }
      }
    }
  }

  /**
   * php tidak bisa pakai fungsi xpath //applic/child::*\/name(), melainkan local-name(//applic/child::*)
   * hindari objectPath yang bernilai boolean karena jika dari setiap result ada yang true, padahal yang lain false, maka hasilnya true
   * 8Mar2024, Perbaiki lagi nanti. Ini tidak efisien. Kalau bisa jangan pakai static function lagi nanti;
   */
  private function validateByStructureObjectRule(\DOMElement $structureObjectRule, $schema)
  {
    // $docIdent = CSDB::resolve_DocIdent($doc);
    $id = $structureObjectRule->getAttribute(('id'));
    $brDecisionRefs = $structureObjectRule->getElementsByTagName('brDecisionRef'); // DOM Element
    $brDecisionIdentNumber = array();
    foreach ($brDecisionRefs as $brDecisionRef) {
      $brDecisionIdentNumber[] = $brDecisionRef->getAttribute('brDecisionIdentNumber');
    }
    $brDecisionIdentNumber = join(", ", $brDecisionIdentNumber);

    // validasi apakah schema nya termasuk di contextRule atau tidak
    $allowedSchema = [];
    foreach ($brDecisionRefs as $brDecisionRef) {
      $brDecisionIdentNumber = $brDecisionRef->getAttribute('brDecisionIdentNumber');
      if ($brDecisionRef->firstElementChild) {
        $refs = $brDecisionRef->firstElementChild;
        if ($refs->firstElementChild->tagName === 'dmRef') {
          // $BRDP_filename = CSDBStatic::resolve_dmIdent($refs->firstElementChild);
          // $dom = CSDB::importDocument($absolute_path . "/", $BRDP_filename);
          $CSDBObject = new CSDBObject("5.0");
          $CSDBObject->loadByElement($refs->firstElementChild, 'dmodule');
          if (!$CSDBObject->document) {
            CSDBError::setError('validateByBrex', "Document: {$CSDBObject->filename} is not available, referenced by structuralObjectRule@id='{$id}'.");
          } else {
            $domXpath = new \DOMXPath($CSDBObject->document);
            $res = $domXpath->evaluate("//brDecision[@brDecisionIdentNumber = '{$brDecisionIdentNumber}']/ancestor::brPara/descendant::s1000dSchemas/@*[. = 1]");
            foreach ($res as $schema) {
              $allowedSchema[] = str_replace('Xsd', '.xsd', $schema->nodeName);
            }
          }
        }
      }
    }
    if (!empty($allowedSchema)) {
      // jika tidak ada di allowable schema, maka tidak perlu di validasi (aman)
      if (!in_array($schema, $allowedSchema)) {
        return;
      }
    }


    $objectPath = $structureObjectRule->getElementsByTagName('objectPath')[0]; // DOM Element
    $allowedObjectFlag = $objectPath->getAttribute('allowedObjectFlag');

    $objectUse = $structureObjectRule->getElementsByTagName('objectUse')[0] ?? new DOMElement('none'); // DOM element

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
    $domXpath = new DOMXPath($this->validatee->document);
    $results = $domXpath->evaluate($objectPath->nodeValue);

    // jika result boolean
    // jika result bernilai benar dan diperbolehkan, maka aman
    if (is_bool($results) AND $results == true AND $allowedObjectFlag == 1) {
      return; // kalau true dan $allowedObject=1, misal //originator/@enterpriseCode='0001Z' berarti ga ada masalah (udah sesuai dengan rule)
    }
    // jika result bernilai benar, tapi tidak diperbolehkan, maka fail
    elseif (is_bool($results) AND $results == true AND $allowedObjectFlag == 0) {
      CSDBError::setError('validateByBrex', "Document: {$this->validatee->filename}, the value does not match. id:{$id}, BR number:{$brDecisionIdentNumber}. Object Use: {$objectUse->nodeValue}");
      return;
    }
    // jika result bernilai true, tapi sunnah, maka aman
    elseif (is_bool($results) AND $results == true AND $allowedObjectFlag == 2) {
      return;
    }
    // jika result bernilai false, maka fail karena harusnya diperbolehkan (harus true)
    elseif (is_bool($results) AND $results == false AND $allowedObjectFlag == 1) {
      CSDBError::setError('validateByBrex', "Document: {$this->validatee->filename}, the value does not match. id:{$id}, BR number:{$brDecisionIdentNumber}. Object Use: {$objectUse->nodeValue}");
      return;
    }
    // jika result bernilai false dan juga tidak diperbolehkan, maka aman
    elseif (is_bool($results) AND $results == false AND $allowedObjectFlag == 0) {
      return;
    }
    // jika result bernilai false, tapi sunnah, fail
    elseif (is_bool($results) AND $results == false AND $allowedObjectFlag == 2) {
      // harusnya return warning/caution/info saja. Nanti buat CSDB::setInfo()
      return;
    }
    // jika tidak ada yang ditemukan, berarti aman (tidak perlu di validasi)
    // elseif (count($results) == 0){
    // elseif (is_array($results) AND count($results) == 0){
    elseif (is_iterable($results) and count($results) == 0) {
      return;
    }

    $res = array();
    if (is_iterable($results) AND $results instanceof \DOMNodeList) {
      foreach ($results as $r) {
        $res[]['value'] = $r->nodeValue;
      }
    } else {
      $res[]['value'] = $results;
    }
    $results = $res;
    unset($res);

    // configurasi
    $type = '';
    if ($allowedObjectFlag == 0 AND !empty($objectValues)) {
      // jika ada objectValue yang match maka fail
      $type = 'value_is_not_allowed';
    } elseif ($allowedObjectFlag == 0 AND empty($objectValues)) {
      // berarti jika ada result yang match, maka fail
      $type = 'objectPath_is_not_allowed';
    } elseif ($allowedObjectFlag == 1 AND !empty($objectValues)) {
      // INI SALAH: berarti jika Xpath pada result match tapi tidak satupun value match, maka fail
      // jika ada result yang tidak match, maka fail      
      $type = 'value_is_allowed';
    } elseif ($allowedObjectFlag == 1 AND empty($objectValues)) {
      // jika results itu null, maka fail. Karena @allowedObjectFlag=1 berarti HARUS match
      $type = 'objectPath_is_allowed';
    }

    if ($type == 'objectPath_is_not_allowed') {
      if (count($results) > 0) {
        CSDBError::setError('validateByBrex', "Document: {$this->validatee->filename}, the value does not match. id:{$id}, BR number:{$brDecisionIdentNumber}. Object Use: {$objectUse->nodeValue}");
      }
    } elseif ($type == 'objectPath_is_allowed') {
      // jika results itu null, maka fail. Karena @allowedObjectFlag=1 berarti HARUS match
      if (count($results) == 0) {
        CSDBError::setError('validateByBrex', "Document: {$this->validatee->filename}, the value does not match. id:{$id}, BR number:{$brDecisionIdentNumber}. Object Use: {$objectUse->nodeValue}");
      }
    }

    if ($type == 'value_is_not_allowed') {
      foreach ($results as $result) {
        foreach ($objectValues as $value) {
          if (isset($value['valueForm']) and isset($value['valueAllowed'])) {
            if ($value['valueForm'] == 'single') {
              if ($result['value'] == $value['valueAllowed']) {
                CSDBError::setError('validateByBrex', "Document: {$this->validatee->filename}, the value does not match. id:{$id}, BR number:{$brDecisionIdentNumber}. Object Use: {$objectUse->nodeValue} Value Text: {$value['valueText']}");
              }
            } elseif ($value['valueForm'] == 'pattern') {
              preg_match_all($value['valueAllowed'], $result['value'], $matches);
              // untuk check jika ada value yang tidak kosong (matched) maka fail
              $m = function ($matches, $m) use ($id, $brDecisionIdentNumber, $objectUse, $value) {
                $k = 0;
                $l = count($matches);
                while (is_array($matches) and $k < $l) {
                  if (is_array($matches[$k]) and !empty($matches[$k])) {
                    $m($matches[$k], $m);
                  } elseif (!empty($matches[$k])) {
                    CSDBError::setError('validateByBrex', "Document: {$this->validatee->filename}, the value does not match. id:{$id}, BR number:{$brDecisionIdentNumber}. Object Use: {$objectUse->nodeValue} Value Text: {$value['valueText']}");
                  }
                  $k++;
                }
              };
              $m($matches, $m);
            } elseif ($value['valueForm'] == 'range') {
              $range = explode('~', $value['valueAllowed']);
              $range = range($range[0], $range[1]);
              // jika nodeValue ada di range, maka fail (karena @allowedObjectFlag = 0)
              if (in_array($result['value'], $range)) {
                CSDBError::setError('validateByBrex', "Document: {$this->validatee->filename}, the value does not match. id:{$id}, BR number:{$brDecisionIdentNumber}. Object Use: {$objectUse->nodeValue} Value Text: {$value['valueText']}");
              }
            }
          }
        }
      }
    } elseif ($type == "value_is_allowed") {
      $lengthV = count($objectValues);
      foreach ($results as $result) {
        $k = 0;
        while (isset($objectValues[$k]) AND $k < $lengthV) {
          $value = $objectValues[$k];
          if (isset($value['valueForm']) AND isset($value['valueAllowed'])) {
            if ($value['valueForm'] == 'single' AND $value['valueAllowed'] == $result['value']) {
              break; // berarti udah ada yang match.
            } elseif ($value['valueForm'] == 'pattern') {
              preg_match_all($value['valueAllowed'], $result['value'], $matches);
              // untuk check jika ada value match maka break. artinya udah ada yang benar
              $m = function ($matches) use ($id, $brDecisionIdentNumber, $objectUse, $value) {
                $k = 0;
                $l = count($matches);
                while (is_array($matches) AND $k < $l) {
                  if (!empty($matches[$k])) {
                    return true; // berarti udah ada yang match.
                  }
                  $k++;
                }
              };
              if ($m($matches)) {
                break; // akan break while
              };
            } elseif ($value['valueForm'] == 'range') {
              $range = explode('~', $value['valueAllowed']);
              $range = range($range[0], $range[1]);
              if (in_array($result['value'], $range)) {
                break; // berarti udah ada yang match.
              }
            }
          }
          $k++;
          if ($k == $lengthV) {
            CSDBError::setError('validateByBrex', "Document: {$this->validatee->filename}, the value does not match. id:{$id}, BR number:{$brDecisionIdentNumber}. Object Use: {$objectUse->nodeValue}");
          }
        }
      }
    }
  }
}