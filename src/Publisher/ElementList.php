<?php 

namespace Ptdi\Mpub\Publisher;

class ElementList {

  public $length;
  protected array $elementList;

  public function count(){
    return count($this->elementList);
  }
  
  public function item(int $index){
    return $this->elementList[$index];
  }

  public static function createList(array $list) {
    $self = new self();
    $elementList = [];
    foreach($list as $domElement){
      if ($domElement instanceof \DOMElement){
        $element = Element::createElement($domElement);
        array_push($elementList, $element);
      }
    }
    $self->length = count($elementList);
    $self->elementList = $elementList;
    return $self;
  }
}