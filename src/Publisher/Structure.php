<?php 

namespace Ptdi\Mpub\Publisher;

trait Structure
{
  protected $representation = [
    0 => "required",
    1 => "optional", 
    2 => "textual",
    3 => "empty",
    4 => "only once",
    5 => "zero or one time",
    6 => "one or more times",
    7 => "zero or more times",
  ];

  public function setStructure(array $childElement)
  {
    $structure = [];
    foreach($childElement as $element){
      $el = new ("Ptdi\Mpub\Publisher\Element\\".ucfirst($element))('dml.xsd');
      $structure[$element] = $el;
    }
    $structure['attributes'] = $this->attributes;
    $this->structure = $structure;
  }

  public function getChildElement()
  {
    return $this->childElement;
  }

  public function getStructure()
  {
    return $this->structure;
  }
}