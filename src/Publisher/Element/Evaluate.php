<?php 

namespace Ptdi\Mpub\Publisher\Element;

use DOMElement;
use DOMXPath;
use Exception;
use Ptdi\Mpub\Object\ACT;
use Ptdi\Mpub\Publisher\Element;
use Ptdi\Mpub\Publisher\Message;
use Ptdi\Mpub\Resolver\Applicability;

class Evaluate extends Applic {
  // use Applicability;
  // public $parentNodeName;

  public function __construct(array $attributes = [], array $inner = [])
  {
    parent::__construct($attributes, $inner);
    // $parent = parent::__construct($attributes, $inner);
    // dd($parent == $this);
    // dd($parent,__CLASS__,__LINE__);
    return $this;
  }

  /**
   * @return string|false 
   */
  public function test(ACT $act)
  {
    // $this->act = $act;
    dd(__CLASS__,__LINE__);
    // return $this->resolve_applicPropertyType($this->applicPropertyType);
  }  

    /**
   * this function should be done if the element Applic has been validate by the xsd schema, or will failure (false).
   * 
   * @return bool 
   */
  // public function resolve(ACT $act){
    // foreach($this->inner as $inner){
    //   if($inner instanceof \DOMElement){
    //     switch ($inner->nodeName) {
    //       case 'assert':
    //         return $this->assert($inner, $act);
    //         break;
    //       case 'evaluate':
    //         return $this->evaluate($inner, $act);
    //       default:
    //         throw new Exception("Nothing to resolve");            
    //         break;
    //     }
    //   }
    // }
  // }
}