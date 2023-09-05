<?php 

namespace Ptdi\Mpub\Publisher\Element;

use DOMElement;
use Ptdi\Mpub\Object\ACT;
use Ptdi\Mpub\Publisher\Element;

class Applic extends Element {

  public function __construct(array $attributes = [], array $inner = [])
  {
    parent::__construct($attributes, $inner);
    return $this;
  }

  /**
   * @return bool 
   */
  public function resolve(ACT $act){
    foreach($this->inner as $inner){
      if($inner instanceof \DOMElement){
        switch ($inner->nodeName) {
          case 'assert':
            return $this->assert($inner, $act);
            break;
          default:
            break;
        }
      }
    }
  }

  private function assert(DOMElement $element, ACT $act){
    $assert = Assert::createElement($element);
    $assert->test($act);
    // return $assert;
    dd($assert,__CLASS__,__LINE__);
  }
}