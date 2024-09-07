<?php

namespace Ptdi\Mpub\Validation\Validator;

use JsonSerializable;
use Ptdi\Mpub\Main\CSDBObject;
use Ptdi\Mpub\Main\CSDBStatic;
use Ptdi\Mpub\Main\Helper;
use Ptdi\Mpub\Validation\CSDBValidatee;
use Ptdi\Mpub\Validation\CSDBValidator;
use Ptdi\Mpub\Validation\GeneralValidationInterface;

// tes.php
// require __DIR__.'/../vendor/autoload.php';
// $validator = new CSDBObject();
// $validator->load("../storage/csdb/OAyKA/DMC-S1000D-G-04-10-0301-00A-022A-D_001-00_EN-US.XML");
// $validator = new CSDBValidator($validator);
// // dd($validator);

// $validatee = new CSDBObject();
// $validatee->load("../storage/csdb/OAyKA/DMC-MALE-A-15-00-01-00A-018A-A_000-01_EN-EN.xml");
// $validatee = new CSDBValidatee($validatee);

// $validation = new Brex($validator, $validatee);
// $validation->validate();
// dd($validation->result());

class Brex implements GeneralValidationInterface, JsonSerializable
{
  protected bool $isReady = false;
  protected array $result = [];

  protected mixed $currentValidatorDoc = null;
  protected mixed $currentValidateeDoc = null;

  public function __construct(public CSDBValidator $validator, public CSDBValidatee $validatee)
  {
    if ($this->validator->isReady() && $this->validatee->isReady()) $this->isReady = true;
  }

  public function validate()
  {
    if ($this->isReady) {
      $l_validatee = count($this->validatee);
      $l_validator = count($this->validator);
      for ($i = 0; $i < $l_validatee; $i++) {
        $this->currentValidateeDoc = ($this->validatee[$i] instanceof CSDBObject ? $this->validatee[$i]->document : $this->validatee[$i]);
        for ($j = 0; $j < $l_validator; $j++) {
          $this->currentValidatorDoc = ($this->validator[$j] instanceof CSDBObject ? $this->validator[$j]->document : $this->validator[$j]);
          $l = count($this->result);
          $this->result[$l] = [];
          $this->result[$l]['results'] = $this->evaluate();
          if(!$this->result[$l]['results']) unset($this->result[$l]);
          $this->result[$l]['validator'] = $this->validator[$j]->filename;
          $this->result[$l]['validatee'] = $this->validatee[$i]->filename;
        }
      }
    }
  }

  /**
   * @return Array
   */
  public function result(string $structuralObjectRuleId = '')
  {
    return array_filter($structuralObjectRuleId ? $this->result[$structuralObjectRuleId] : $this->result, fn ($v) => $v);
  }

  /**
   * baru cover validasion contextRules, belum untuk snsRules dan nonContextRules 
   * @return mixed can be array or false
   */
  private function evaluate() :mixed
  {
    $result = [];
    // evaluate by snsRules here
    // TBD

    // evaluate by contextRules here
    $DOMXpath = new \DOMXpath($this->currentValidatorDoc);
    $schema = $this->currentValidateeDoc->documentElement->getAttribute("xsi:noNamespaceSchemaLocation"); // kalau document di loadXML, tidak bisa pakai fungsi getAttributeNS().
    $result['contextRules'] = $this->byContextRules([...$DOMXpath->evaluate("//contextRules[not(@rulesContext)] | //contextRules[@rulesContext = '{$schema}']")]);

    // evaluate by nonContextRules here
    // TBD

    return !empty($result) ? $result : false;
  }

  /**
   * resultnya adalah hasil yang false (yang salah atau yang tidak sesuai brex)
   * * @return Array
   */
  private function byContextRules(array $contextRules) :Array
  {
    $result = [];
    $l = count($contextRules);
    for ($i = 0; $i < $l; $i++) {
      // for structural
      $result['structureObjectRuleGroup'] = $result['structureObjectRuleGroup'] ?? [];
      $structureObjectRuleGroup = $contextRules[$i]->firstElementChild;
      if ($structureObjectRuleGroup && $structureObjectRule = $structureObjectRuleGroup->firstElementChild) {
        if ($r = $this->byStructureObjectRule($structureObjectRule)) $result['structureObjectRuleGroup'][] = $r;
        while ($structureObjectRule = $structureObjectRule->nextElementSibling) {
          if ($r = $this->byStructureObjectRule($structureObjectRule)) $result['structureObjectRuleGroup'][] = $r;
        }
      }

      // sesuai definisi @allowedNotationFlag, jika ada spesific/explisit context rules is given, maka notation yang ada di general/implisit tidak di validasi
      $result['notationRuleList'] = $result['notationRuleList'] ?? [];
      $notationRuleList = $contextRules[$i]->firstElementChild->nextElementSibling;
      if ($notationRuleList && $notationRule = $notationRuleList->firstElementChild) {
        // check notation rule
        if ($this->isBase($notationRule, $contextRules) && ($r = $this->byNotationRule($notationRule))) $result['notationRuleList'][] = $r;
        while ($notationRule = $notationRule->nextElementSibling) {
          // check notation rule
          if ($this->isBase($notationRule, $contextRules) && ($r = $this->byNotationRule($notationRule))) $result['notationRuleList'][] = $r;
        }
      }
    }
    return $result;
  }

  /**
   * $result adalah array yang berisi failed/unseccess validated object
   * * @return mixed
   */
  private function byStructureObjectRule(\DOMElement $structureObjectRule) :mixed
  {
    $result = [];
    // handle objectPath
    $objectPath = $structureObjectRule->getElementsByTagName('objectPath')[0]; // DOM Element
    CSDBStatic::simple_decode_element($objectPath, $opath);
    $this->evaluateObjectPath($opath['objectPath']['0'], $objectPathResult);

    // handle objectValue    
    $unmatched = $this->unmatchWithObjectValues($objectPathResult, [...$structureObjectRule->getElementsByTagName('objectValue')]); // output value yang ga match dengan ObjectValue

    switch ($opath['objectPath']['at_allowedObjectFlag']) {
      case '0':
        if (!empty($unmatched)) return null; // true
        break;
      case '1' || '2':
        // kalau ada yang unmatch ?
        if (!empty($unmatched)) {
          $resultIndex = count($result);
          // handle brDecisionRef
          $this->handleBrDecisionRef($structureObjectRule, $result);

          $result['brSeverityLevel'] = $structureObjectRule->getAttribute('brSeverityLevel');
          $result['objectPath'] = $opath['objectPath'];
          $use = $structureObjectRule->getElementsByTagName('objectUse')[0]; // DOM element
          if ($use) $result['use'] = $use->nodeValue;
          $result['lines'] = [];
          foreach ($unmatched as $index) {
            $line = @$objectPathResult[$index]->getLineNo() ?? '';
            $result['lines'][] = $line;
          }
        } else return null; // true
    }
    return !empty($result) ? $result : false;
  }

  /**
   * @return Array
   */
  private function evaluateObjectPath(string $xpath, &$result) :Array
  {
    $DOMXpath = new \DOMXpath($this->currentValidateeDoc);
    $evaluate = $DOMXpath->evaluate($xpath);
    return $result = is_iterable($evaluate) ? [...$evaluate] : [$evaluate];
  }

  /**
   * check wether the result of objectpath evaluation value is match or not
   * @return Array contain unmatched index of objectPathResult
   */
  private function unmatchWithObjectValues(array $objectPathResult, $objectValues) :Array
  {
    if (empty($objectValues)) return [];
    else {
      array_walk($objectValues, function (&$v) {
        $v = [
          'id' => $v->getAttribute('id'),
          'valueForm' => $v->getAttribute('valueForm'), // single, range, pattern
          'valueAllowed' => $v->getAttribute('valueAllowed'), // string value of @valueForm
          'valueTailoring' => $v->getAttribute('valueTailoring'), // restrictable, lexical, closed
          'valueText' => $v->nodeValue,
        ];
      });
    }
    $matching = function ($ov, $op) {
      if (isset($ov['valueForm']) && isset($ov['valueAllowed'])) {
        if ($op instanceof \DOMNode) $op = (string) $op->nodeValue;
        switch ($ov['valueForm']) {
          case 'single':
            return ($op === $ov['valueAllowed']);
          case 'pattern':
            $regex = '/' . preg_quote($ov['valueAllowed']) . '/m';
            preg_match_all($regex, $op, $matches);
            $m = function ($matches, $m) {
              $k = 0;
              $l = count($matches);
              while (is_array($matches) && $k < $l) {
                if (is_array($matches[$k]) && !empty($matches[$k])) {
                  return $m($matches[$k], $m);
                } elseif (!empty($matches[$k])) {
                  return false;
                }
                $k++;
              }
              return true;
            };
            return $m($matches, $m);
          case 'range':
            $range = Helper::range($ov['valueAllowed']);
            return in_array($op, $range);
        }
      }
      return false;
    };
    $l_op = count($objectPathResult);
    $l_ov = count($objectValues);
    $unmatched = [];
    for ($i_op = 0; $i_op < $l_op; $i_op++) {
      $matched = false;
      for ($i_ov = 0; $i_ov < $l_ov; $i_ov++) {
        if ($matched = $matching($objectValues[$i_ov], $objectPathResult[$i_op])) break;
      }
      if (!$matched) $unmatched[] = $i_op;
    }

    return $unmatched;
  }

  /**
   * attach brDecisionRef to the $result
   * @param \DOMElement $structureObjectRule
   * @param Array
   */
  private function handleBrDecisionRef(\DOMElement $structureObjectRule, &$result) :void
  {
    $brDecisionRefs = $structureObjectRule->getElementsByTagName('brDecisionRef'); // DOM Element    
    $l_brDecisionRefs = count($brDecisionRefs);
    if ($l_brDecisionRefs > 0) {
      $result['brDecisionRef'] = [];
      for ($i = 0; $i < $l_brDecisionRefs; $i++) {
        if ($brDecisionIdentNumber = $brDecisionRefs[$i]->getAttribute('brDecisionIdentNumber')) $result['brDecisionRef']['brDecisionIdentNumber'] = $brDecisionIdentNumber;
        if ($id = $brDecisionRefs[$i]->getAttribute('id')) $result['brDecisionRef']['id'] = $id;
        if ($refs = $brDecisionRefs[$i]->firstElementChild) {
          $refs = Helper::children($refs);
          $l_refs = count($refs);
          for ($lr = 0; $lr < $l_refs; $lr++) {
            $result['brDecisionRef'] = [
              'filename' => CSDBStatic::resolve_ident($refs[$lr]),
              'id' => $refs[$lr]->getAttribute('id'),
            ];
          }
        }
      }
    }
  }

  /**
   * ketika di brexDoc ada spesific contextRule[@rulesContext] maka allowedNotationFlag='1' current notationRule akan menjadi 'sunnah', otherwise it MUST be used
   * @return Array
   * @return bool
   */
  private function byNotationRule(\DOMElement $notationRule) :mixed
  {
    $result = [];
    $notationNameElement = $notationRule->getElementsByTagName('notationName')[0]; // DOM Element
    $notationName = $notationNameElement->nodeValue;
    // $id = $notationNameElement->getAttribute('id');

    $allowedNotationFlag = (string) $notationNameElement->getAttribute('allowedNotationFlag');
    if (!$allowedNotationFlag) $allowedNotationFlag = '2';

    $matchEntityName = [];
    $matchSystemId = [];
    $entities = $this->currentValidateeDoc->doctype->entities;
    $l_e = count($entities);
    for ($i = 0; $i < $l_e; $i++) {
      $actual_notationName = strtolower($entities[$i]->notationName);
      if (strtolower($actual_notationName) === strtolower($notationName)) {
        $matchEntityName[] = $entities[$i]->nodeName;
        $matchSystemId[] = $entities[$i]->systemId;
      }
    }
    // check DOCTYPE entity nya csdb object. 
    // jika UNMATCH dengan notationRule ini maka notation MUST NOT be used
    // jika MATCH dengan notationRule ini maka notation MUST be used.
    if (($allowedNotationFlag === '0' && !empty($matchEntityName))
      || $allowedNotationFlag === '1' && empty($matchEntityName)
    ) {
      $this->handleBrDecisionRef($notationRule, $result);
      if ($securityLevel = $notationRule->getAttribute('brSeverityLevel')) $result['brSeverityLevel'] = $securityLevel;
      $result['notationName'] = $notationName;
      $result['entityName'] = $matchEntityName;
      $result['entitySystemId'] = $matchSystemId;
      $result['allowedNotationFlag'] = $allowedNotationFlag;
    }
    return !empty($result) ? $result : false;
  }

  /**
   * to check wheter the notation rule is base rule or not
   * @return bool if true, then the notationRule is base as validator, otherwise not used as validator
   */
  private function isBase(\DOMElement $notationRule, array $notationRules) :bool
  {
    $notationName = (($notationName = ($notationRule->getElementsByTagName('notationName')[0] ?? null)) ?  $notationName->nodeValue : null);
    if (!$notationName) return false;

    $schema = $notationRule->parentElement->parentElement->getAttribute('rulesContext'); //if false, then this notation is general/implicit context
    // jika ini adalah notation implicit / general context maka cari yang explisit/spesific context
    if (!$schema) {
      $l = count($notationRules);
      for ($i = 0; $i < $l; $i++) {
        if (($schema = $notationRules[$i]->getAttribute('rulesContext'))
          && ($notationNameExplisit = (($notationNameExplisit = ($notationRule->getElementsByTagName('notationName')[0] ?? null)) ?  $notationNameExplisit->nodeValue : null))
          && (strtolower($notationNameExplisit) === strtolower($notationName))
        ) {
          return false; // return false jika notationRule adalah implict DAN ada explisti context
        }
      }
      return true; // return true jika notationRule adalah implicit DAN tidak ada explist context
    }
    return true; // return ture jika notationRule adalah explisti context
  }

  public function jsonSerialize(): mixed
  {
    return [
      'validator' => $this->validator,
      'validatee,' => $this->validatee,
      'results' => $this->result(),
    ];
  }
}
