<?php 

namespace Ptdi\Mpub\Publisher\Element;

use DOMElement;
use Exception;
use LDAP\Result;
use Ptdi\Mpub\Object\ACT;
use Ptdi\Mpub\Publisher\Element;
use Ptdi\Mpub\Publisher\ElementList;

class Applic extends Element {
  public $applicability;

  public function __construct(array $attributes = [], array $inner = [])
  {
    parent::__construct($attributes, $inner);
    return $this;
  }

  /**
   * this function should be done if the element Applic has been validate by the xsd schema, or will failure (false).
   * 
   * @return bool 
   */
  public function resolve(ACT $act){
    foreach($this->inner as $key => $inner){
      if($inner instanceof \DOMElement){
        switch ($inner->nodeName) {
          case 'assert':
            $this->assert($inner, $act);
            break;
          case 'evaluate':
            $this->evaluate($inner, $act);
          case 'displayText':
            break;
          default:
            throw new Exception("Nothing to resolve");            
            break;
        }
      }
    }
  }

  /**
   * assert dijalankan oleh Element Assert atau Element Evaluate
   * $this bisa Element Assert atau Evaluate
   * fungsi ini akan menyetel $this->applicability
   * jika applicabilit sesuai dengan $testedValue
   */
  private function assert(DOMElement $element, ACT $act){
    $assert = Assert::createElement($element);
    // $result = $assert->test($act);
    $result = $assert->test($assert->applicPropertyType == 'prodattr' ? $act : $act->CCT);
    // dump($this->nodeName,$this->andOr,'foo');
    foreach($result as $applicPropertyIdent => $testedValues){ //$values is array
      switch($this->andOr){
        case 'and':
          // dump('and', $this->applicability);
          if($this->applicability[$applicPropertyIdent]){
            foreach($testedValues as $value){
              array_push($this->applicability[$applicPropertyIdent], $value);
            }
          } else {
            $this->applicability[$applicPropertyIdent] = $testedValues;
          }
          break;
        case 'or':
          $this->applicability[$applicPropertyIdent] = $this->applicability[$applicPropertyIdent] ?: $testedValues;
          break;
        default:
          $this->applicability[$applicPropertyIdent] = $testedValues;
      }
    }
  }

  private function evaluate(DOMElement $element, ACT $act){
    $evaluate = Evaluate::createElement($element);
    $evaluate->resolve($act);
    $this->applicability = $evaluate->applicability;
  }
}