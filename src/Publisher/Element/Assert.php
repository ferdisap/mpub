<?php 

namespace Ptdi\Mpub\Publisher\Element;

use DOMElement;
use DOMXPath;
use Ptdi\Mpub\Object\ACT;
use Ptdi\Mpub\Publisher\Element;
use Ptdi\Mpub\Publisher\Message;

class Assert extends Element {

  public function __construct(array $attributes = [], array $inner = [])
  {
    parent::__construct($attributes, $inner);
    return $this;
  }

  public function test(ACT $act)
  {
    // dd($this->applicPropertyType);
    switch($this->applicPropertyType){
      case 'prodattr':
        dd($this->prodattr($act));
        break;
      
      case 'condition':
        # code...
        break;
      
      default:
        Message::generate(300, 'no such applicPropertyType values.', __CLASS__, __LINE__);
        return false;
        break;
    }
  }

  // masih lanjut disini, baru sudah mencoba 3~5|10~15
  // coba juga segala kondisi misal: serial number 3 digit, 
  private function prodattr(ACT $act){
    $nominalValues = [];
    
    // set nominalValues from enumeration tag
    $domXpath = new DOMXPath($act->getDOMDocument());
    $query_enum = "//productAttribute[@id ='{$this->applicPropertyIdent}']/enumeration";
    $enums = $domXpath->evaluate($query_enum);
    foreach ($enums as $enum) {
      $value = $enum->getAttribute('applicPropertyValues'); // eg: per <enum>: "3-5|10-15"
      $values = $this->resolveAplicPropertyValues($value);
      foreach($values as $v){
        array_push($nominalValues, $v);
      }
    }

    // dump($nominalValues);
    
    // set nominalValue from valuePattern attribute (mungkin @valuePattern itu untuk ngecek saja, sama seperti @valueDataTypes)
    ### code

    $actualValues = [];
    $values = $this->resolveAplicPropertyValues($this->applicPropertyValues);
    foreach($values as $v){
      array_push($actualValues, $v);
    }

    $finalValues = [];
    foreach ($actualValues as $actualValue){
      if (in_array($actualValue, $nominalValues)){
        array_push($finalValues, $actualValue);
      } else {
        Message::generate(300,"it may have a fault in written rule of applicability.");
      }
    }
    $finish = ($this->appicDisplayClass ?? '').implode(', ', $finalValues);
    return $finish;
  }

  private function resolveAplicPropertyValues(string $applicPropertyValues){
    $regex = "/(?<=\|)?[A-Za-z0-9\/\-]+(?:~[0-9a-zA-Z\/\-]+)?(?=\|)?/";
    preg_match_all($regex, $applicPropertyValues, $matches, PREG_SET_ORDER, 0);
    $values = [];
    foreach ($matches as $match){
      $regex_tilde = "/[0-9]+(?=~)|(?<=~)[0-9]+/";
      // $matches adalah array total temuan atas nilai attribute applicPropertyValues eg: [ [0],[1] ],
      // $match adalah array. Dalam temuan diatas, isinya array lagi eg1: [0] = 3~5, eg2: [1] = 10~15
      // $match[0] adalah string temuannya, eg: 3~5
      preg_match_all($regex_tilde, $match[0], $matches_tilde, PREG_SET_ORDER, 0);
      $start = $matches_tilde[0][0];
      $end = $matches_tilde[1][0];

      foreach (range($start, $end) as $val) {
        array_push($values, $val);
      }
    }
    return $values;
  }
  
  
}