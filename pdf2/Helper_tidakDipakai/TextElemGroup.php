<?php

namespace Ptdi\Mpub\Pdf2\Helper;

use TCPDF;

class TextElemGroup
{

  public static function resolve(TCPDF $pdf, \DOMElement $element)
  {
    switch ($element->tagName) {
      case 'identNumber':
        // ada change mark nya
        break;
      case 'indexFlag':
        // tidak ada change mark nya
        // kayaknya tidak dipakai, tambahkan di brex
        break;
      case 'changeInline':
        // ada change marknya
        // ada child elementnya TextElemGroup
        break;
      case 'emphasis':
        $txt = self::emphasis($element);
        $pdf->WriteHTML($txt, false, false, true, false, 'J');
        break;
      case 'symbol':
        // tidak ada change mark nya
        // sepertinya symbol tidak digunakan, tambahkan di brex
        break;
      case 'subScript':
        $pdf->WriteHTML("<sub>{$element->nodeValue}</sub>", false, false, true, false, 'J');
        break;
      case 'superScript':
        $pdf->WriteHTML("<sup>{$element->nodeValue}</sup>", false, false, true, false, 'J');
        break;
      case 'internalRef':
        // ada change mark nya
        // child element berupa subScript dan superScript, bisa pakai class TextElemGroup
        break;
      case 'dmRef':
        // ada change marknya
        break;
      case 'pmRef':
        // ada change marknya
        break;
      case 'sourceDocRef':
        // ada change marknya
        // kayaknya tidak digunakan, tambahkan di brex
        break;
      case 'footnote':
        // ada change marknya
        // pakai font kecil, 6 atau 7 saja
        // child elemennya adalah para, jadi bisa pakai class ini saja
        break;
      case 'footnoteRef':
        // ada change marknya
        // nanti textnya berisi link page dimana footnote di render. Setiap elemen yang punya id, harus ditambahkan ke array class pdf nya.
        break;
      case 'acronym':
        // ada change marknya
        // ini seperti abbreviation, jadi child element <acronymDefinition> bisa ditambahkan ke array propertis di pdf, nanti di generate kayak TOC
        // child element berupa superscript dan subscript saja, bisa pakai class TextElemGroup
        break;
      case 'captionGroup':
        // ada change marknya
        // ini seperti table, jadi $ln = true
        break;
      case 'caption':
        // ada change marknya
        // ini seperti cell kecil dan inline $ln = false
        break;
      case 'verbatimText':
        // ada change marknya
        // sepertinya tidak digunakan untuk data module manual            
      default:
        # code...
        break;
    }
  }

  // /**
  //  * @return string
  //  */
  // public static function resolve(\DOMElement $element){
  //   $text = '';
  //   if(($subScript = $element)->nodeName == 'subScript'){
  //     $html = <<<EOD
  //     <sub>{$subScript->nodeValue}</sub>
  //     EOD;
  //     $text = $text. $html;
  //   } 
  //   elseif(($superScript = $element)->nodeName == 'superScript'){
  //     $html = <<<EOD
  //     <sup>{$superScript->nodeValue}</sup>
  //     EOD;
  //     $text = $text. $html;
  //   }
  //   elseif(($emphasis = $element)->nodeName == 'emphasis'){
  //     $text = $text. TextElemGroup::emphasis($emphasis);
  //   }
  //   return (string)$text;
  // }

  // /**
  //  * @return string
  //  */
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
