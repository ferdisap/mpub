<?php 

namespace Ptdi\Mpub\Pdf;

class TextElemGroup {

  /**
   * @return string
   */
  public static function resolve(\DOMElement $element){
    $text = '';
    if(($subScript = $element)->nodeName == 'subScript'){
      $html = <<<EOD
      <sub>{$subScript->nodeValue}</sub>
      EOD;
      $text = $text. $html;
    } 
    elseif(($superScript = $element)->nodeName == 'superScript'){
      $html = <<<EOD
      <sup>{$superScript->nodeValue}</sup>
      EOD;
      $text = $text. $html;
    }
    elseif(($emphasis = $element)->nodeName == 'emphasis'){
      $text = $text. TextElemGroup::emphasis($emphasis);
    }
    return (string)$text;
  }

  /**
   * @return string
   */
  public static function emphasis(\DOMElement $emphasis){
    $html = '';
    $attributes = $emphasis->attributes;
    if(isset($attributes[0])){
      foreach($attributes as $attribute){
        if(($emphasisType = $attribute)->nodeName == 'emphasisType'){
          if($emphasisType->nodeValue == 'em01'){
            $html = <<<EOD
            <b>{$emphasis->nodeValue}</b>
            EOD;
          }
          elseif($emphasisType->nodeValue == 'em02'){
            $html = <<<EOD
            <i>{$emphasis->nodeValue}</i>
            EOD;
          }
          elseif($emphasisType->nodeValue == 'em03'){
            $html = <<<EOD
            <u>{$emphasis->nodeValue}</u>
            EOD;
          }
          elseif($emphasisType->nodeValue == 'em05'){
            $html = <<<EOD
            <del{$emphasisType->nodeValue}</del>
            EOD;
          }
          else {
            $html = $emphasisType->nodeValue;
          }
        }
        // no more attribute except emphasisType
      }
    }
    else {
      $html = $emphasis->nodeValue;
    }

    return $html;
  }
}