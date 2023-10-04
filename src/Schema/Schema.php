<?php 

namespace Ptdi\Mpub\Schema;

use DOMXPath;
use Ptdi\Mpub\CSDB;

class Schema {

  private static $separator = PHP_OS == "Windows" || PHP_OS == "WINNT" ? "\\" : "/";

  /**
   * @param string $schemaName An XSD filename without extension format
   * 
   * @return string xml string by C14N()
   */
  public static function getSchemaString(string $schemaName)
  {
    $xsdDocument = CSDB::load(__DIR__.self::$separator.$schemaName);
    // $xsdDocument = self::resolveXsImportPath($xsdDocument);
    return $xsdDocument->C14N();
  }

    /**
   * @param string $schemaName An XSD filename with extension format
   * 
   * @return \DOMDocument
   */
  public static function getSchemaDoc(string $schemaName){
    return  CSDB::load(__DIR__.self::$separator.$schemaName);
  }

  private static function resolveXsImportPath(\DOMDocument $document)
  {
    foreach ($document->getElementsByTagNameNS("http://www.w3.org/2001/XMLSchema","import") as $importEl) {
      $schemaLocation = $importEl->getAttribute("schemaLocation");
      if($schemaLocation == "xlink.xsd" || $schemaLocation == "rdf.xsd"){
        $importEl->setAttribute('schemaLocation', __DIR__.self::$separator.$schemaLocation);
        $document->saveXML();
      }
    }
    return $document;
  } 

  public static function getXS(array $names , \DOMDocument $doc){
    $domXpath = new DOMXPath($doc);
    $structure = [];
    foreach ($names as $name) {
      $query = "//@name[. = '{$name}']/parent::*";
      $xs = ($domXpath->evaluate($query))->item(0);
      if($xs->nodeName == 'xs:element'){
        array_push($structure, self::xsElement($xs, $doc));
      }
      elseif($xs->nodeName == 'xs:attribute'){
        array_push($structure, self::xsAttribute($xs, $doc));
      }
      elseif ($xs->nodeName == 'xs:complexType'){
        array_push($structure, self::xsComplexType($xs, $doc));
      }
      elseif ($xs->nodeName == 'xs:simpleType'){
        foreach ($xs->childNodes as $child){
          if($child->nodeName == 'xs:restriction'){
            return $arr = [$child->getAttribute('base'), self::xsRestriction($child)];
          }
        }
      }
      elseif($xs->nodeName == 'xs:group'){
        array_push($structure, self::xsGroup($xs, $doc));
      }
    }
    return $structure;
  }

  public static function xsComplexType(\DOMElement $xsComplexType, \DOMDocument $doc){
    $name = $xsComplexType->getAttribute('name');
    $structure[$name] = [];
    $structure[$name]['attributes'] = [];
    $structure[$name]['mixed'] = false;
    if($mixed = $xsComplexType->getAttribute('mixed')){
      $structure[$name]['mixed'] = (boolean)$mixed;
    }
    foreach ($xsComplexType->childNodes as $child) {
      if($child->nodeName == "xs:attribute"){
        array_push($structure[$name]['attributes'], self::xsAttribute($child, $doc));
      }
      elseif($child->nodeName == 'xs:attributeGroup'){
        $attgroup = self::xsAttGroup($child, $doc);
        foreach ($attgroup as $attribute) {
          array_push($structure[$name]['attributes'], self::xsAttribute($child, $doc));
        }
      }
      elseif($child->nodeName =='xs:sequence'){
        $structure[$name]['sequence'] = isset($structure[$name]['sequence']) ? $structure[$name]['sequence'] : [];
        $structure[$name]['sequence'] = self::xsSequence($child, $doc);
      }
      elseif($child->nodeName =='xs:choice'){
        $structure[$name]['choice'] = isset($structure[$name]['choice']) ? $structure[$name]['choice'] : [];
        $structure[$name]['choice'] = self::xsChoice($child, $doc);
      }
    }
    return $structure[$name];
  }

  /**
   * @return string
   */
  private static function occurenceIndicator(\DOMElement $element){
    $occurence = [];
    $maxOccurs = (string)$element->getAttribute('maxOccurs');
    $minOccurs = (string)$element->getAttribute('minOccurs');
    $maxOccurs ? array_push($occurence, "maxOccurs:{$maxOccurs}") : '';
    $minOccurs >= 0  ? array_push($occurence, "minOccurs:{$minOccurs}") : '';
    
    $occurence = implode("|",$occurence);
    return $occurence;    
  }

  public static function xsGroup(\DOMElement $xsGroup, \DOMDocument $doc){
    $group = [];
    $name = $xsGroup->getAttribute('name');
    $group[$name] = [];
    foreach($xsGroup->childNodes as $child){
      if($child instanceof \DOMElement){
        if($child->nodeName == 'xs:choice'){
          $group[$name]['choice'] = self::xsChoice($child, $doc);
        }
        elseif($child->nodeName == 'xs:sequence'){
          $group[$name]['sequence'] = self::xsSequence($child, $doc);
        }
      }
    }
    return $group;
  }

  public static function xsSequence(\DOMElement $xsSequence, \DOMDocument $doc){
    $sequence = [];
    $sequence['inner'] = [];
    $sequence['rules'] = "";
    
    $occurence = self::occurenceIndicator($xsSequence);
    $occurence ? ($sequence['rules'] = $sequence['rules'] . '|' . $occurence): '';

    foreach($xsSequence->childNodes as $child){
      if(!$child instanceof \DOMText){
      // if($child->nodeName == 'xs:element'){
        $el = self::xsElement($child, $doc); // expected there is attribute ref=
        array_push($sequence['inner'], $el);
      }
    }
    return $sequence;
  }
  
  public static function xsChoice(\DOMElement $xsChoice, \DOMDocument $doc){
    $choice = [];
    $choice['inner'] = [];
    $choice['rules'] = "";

    $occurence = self::occurenceIndicator($xsChoice);
    $occurence ? ($choice['rules'] = $choice['rules'] . '|' . $occurence): '';

    foreach ($xsChoice->childNodes as $child){
      if(!$child instanceof \DOMText){
      // if($child->nodeName == 'xs:element'){
        $el = self::xsElement($child, $doc);
        array_push($choice['inner'], $el);
      }
      // elseif($child->nodeName == 'xs:group'){
        
      // }
    }
    return $choice;
  }

  public static function xsElement(\DOMElement $xsEl, \DOMDocument $doc){
    $element = [];
    $rules = [];    
    $name = $xsEl->getAttribute('name');
    $element[$name] = [];
    $occurence = self::occurenceIndicator($xsEl);

    array_push($rules, $occurence);
    
    foreach($xsEl->attributes as $att){
      if($att->nodeName == 'type'){
        if($type = self::is_elemType($att->value)){
          $elemType = self::getXS([$type."ElemType"], $doc);
          foreach($elemType as $complexElem){
            foreach($complexElem as $elemType_name => $inner){
              $element[$name][$elemType_name] = $inner;
            }
          }
        } 
        elseif($type = self::is_xsType($att->value)){
          $element[$name]['type'] = $att->value;
        }
      }
      elseif ($att->nodeName == 'ref'){
        $name = $att->value;
        $element[$name] = [];
        $element[$name]['rules'] = implode("|",$rules);
      }
    }
    return $element;
  }

  public static function xsAttribute(\DOMElement $xsAtt, \DOMDocument $doc){
    $attribute = [];
    foreach ($xsAtt->attributes as $att) {
      if($att->nodeName == 'type'){
        $attribute[2] = $att->value;
        if($type = self::is_attType($attribute[2])){
          $attType = self::getXS([$type."AttType"], $doc);
          $attribute[2] = $attType[0];
          $attribute[3] = $attType[1];
        }
      } 
      elseif ($att->nodeName == 'name'){
        $attribute[0] = $att->value;
      }
      elseif($att->nodeName == 'use'){
        $attribute[1] = $att->value;
      }
      elseif($att->nodeName == 'ref'){
        $domXpath = new DOMXPath($doc);
        $el = ($domXpath->evaluate("//xs:attribute[@name = '{$att->value}']"))->item(0);
        $name = $el->getAttribute('name');
        $type = $el->getAttribute('type');
        array_push($attribute, $name);
        array_push($attribute, $type);
      }
    }
    return $attribute;
  }

  public static function xsAttGroup(\DOMElement $xsAttGroup, \DOMDocument $doc){
    $group = [];
    foreach ($xsAttGroup->childNodes as $child){
      if($child->nodeName == 'xs:attribute'){
        array_push($group, self::xsAttribute($child, $doc));
      }
    }
    return $group;
  }
  
  public static function xsRestriction(\DOMElement $xsRestriction){
    return ['enum:allowed', 'enum:built', 'pattern:/xxx/'];
  }

  private static function is_xsType(string $value){
    $xs = substr_replace($value,"",3);
    return $xs == 'xs:' ? true : false;
  }
 
  
  private static function is_elemType(string $type){
    $re = '/([A-Za-z]+)ElemType$/';
    $replacement = '${1}';
    $name = preg_replace($re, $replacement, $type);
    // dd($name, $type);
    if($name == $type) { // jika $name == $type, berarti tidak di replace atau regex tidak matched
      return false;
    }
    return $name;
  }
  private static function is_attType(string $type){
    $re = '/([A-Za-z]+)AttType$/';
    $replacement = '${1}';
    $name = preg_replace($re, $replacement, $type);
    if($name == $type) { // jika $name == $type, berarti tidak di replace atau regex tidak matched
      return false;
    }
    return $name;
  }

  // // ini dibutuhkan jika kita ingin membuat DML atau lainnya, perlu ada structure
  // /**
  //  * @param string $nodeType which element or attribute.
  //  * @param string $schemaName xml file with extension.xsd
  //  */
  // public static function getStructureFromSchema(string $nodeName, string $schemaName)
  // {
  //   $doc = CSDB::load(__DIR__.self::$separator.$schemaName);
  //   return self::evaluate_name($nodeName,$doc);
  // }

  // private static function evaluate_name($name, $doc){
  //   $domXpath = new \DOMXPath($doc);
  //   $query = "//@name[. = '{$name}']/parent::*";
  //   $node = ($domXpath->evaluate($query))->item(0);
  //   return self::getStructure($node, $doc);
  // }

  // private static function getStructure(\DOMElement $node, $doc){
  //   switch ($node->nodeName) {
  //     case 'xs:element':
  //       self::element($node, $doc);
  //       break;
  //     case 'xs:attribute':
  //       self::attribute($node, $doc);
  //       break;
  //     case 'xs:complexType':
  //       self::complexType($node, $doc);
  //       break;
  //     case 'xs:sequence':
  //       self::complexType($node, $doc);
  //       break;
  //   }
  // }

  // private static function attribute(\DOMElement $xsAttribute, $doc){
  //   if($name = self::is_attType($xsAttribute->getAttribute('type'))){
  //     self::evaluate_name($name.'AttType', $doc);
  //   } elseif ($ref = $xsAttribute->getAttribute('ref')) {
  //     // jika xs:attribute kosong (type xs:...)
  //     return [$ref => ''];
  //   }
  // }

  // private static function element(\DOMElement $xsElement, $doc){
  //   if($name = self::is_elemType($xsElement->getAttribute('type'))){
  //     self::evaluate_name($name.'ElemType', $doc);
  //   } else {
  //     // jika xs:element innernya berisi child atau kosong (type xs:...)
  //   }
  // }

  // private static function is_attType(string $type){
  //   $re = '/([A-Za-z]+)AttType$/';
  //   $replacement = '${1}';
  //   $name = preg_replace($re, $replacement, $type);
  //   if($name == $type) { // jika $name == $type, berarti tidak di replace atau regex tidak matched
  //     return false;
  //   }
  //   return $name;
  // }


  // private static function complexType($xsComplexTypeElement, $doc){
  //   while($elementChild = $xsComplexTypeElement->firstElementChild){
  //     self::getStructure($elementChild, $doc);
  //   }
  // }

  // private static function element(string $elementName, \DOMDocument $doc)
  // {
    // $domXpath = new \DOMXPath($doc);
    // $query = "//xs:element[@name = '{$elementName}']";
    // $xsElement = ($domXpath->evaluate($query))->item(0);

    // if(!self::is_xsType($xsElement->getAttribute('type'))){
    //   $inner = [];
    //   while($child = $xsElement->firstElementChild){
    //     array_push($inner, $child);
    //   }
    //   foreach($inner as $node){
    //     switch ($node->nodeName) {
    //       case 'xs:choose':
    //         self::choose();
    //         break;
    //       case 'xs:sequence':
    //         self::sequence();
    //         break;
          
    //       default:
    //         # code...
    //         break;
    //     }
    //   }
    // }
  // }


  // private static function is_xsType(string $value)
  // {
  //   $xs = substr_replace($value,"",3);
  //   return $xs == 'xs:' ? true : false;
  // }

}