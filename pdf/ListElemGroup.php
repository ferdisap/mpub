<?php 

namespace Ptdi\Mpub\Pdf;

class ListElemGroup {

  /**
   * @return string
   */
  public static function resolve(\DOMElement $element){
    $text = '';
    if(($sequentialList = $element)->nodeName == 'sequentialList'){
      ListElemGroup::sequentialList($element);
    } 
    elseif(($randomList = $element)->nodeName == 'randomList'){
    }
    elseif(($definitionList = $element)->nodeName == 'definitionList'){
    }
    return (string)$text;
  }

  public static function sequentialList(\DOMElement $sequentialList){
    // $children = 
  }
}