<?php 

namespace Ptdi\Mpub\Publisher;

use DOMDocument;
use DOMXPath;
use Exception;
use Ptdi\Mpub\Object\DModule;
use Ptdi\Mpub\Schema\Schema;
use ReflectionClass;

class Element {

  public $nodeName;
  public array $attributes; // [0] = attribute name, [1] = attribute value
  public array $inner;
  private \DOMElement $DOMElement;
  

  /**
   * 
   * @param array $attributes for each $attributes[index] contains two/more array which are [0] = attribute name, [1] = attribute value. Example $attributes = [["name", "value"],["name1", "value2]]
   */
  public function __construct(array $attributes = [], array $inner = []){
    $reflex = new ReflectionClass($this);
    $this->nodeName = lcfirst($reflex->getShortName());
    $this->attributes = $attributes;
    $this->inner = $inner; 
    return $this;
  }


  /**
   * @param mixed create Element by the allowable source such array, string xml, or DOMElement
   */
  public static function createElement(mixed $source)
  {
    $tagName = null;
    $attributes = []; 
    if(is_string($source)){
      $doc = new DOMDocument();
      $doc->loadXML($source);
      $el = $doc->firstElementChild;
      $domXpath = new DOMXPath($doc);

      $tagName = $el->tagName;
      $atts = $domXpath->evaluate("//".$tagName."/@*");
      foreach($atts as $att){
        array_push($attributes, [$att->nodeName, $att->nodeValue]);
      }

    } elseif($source instanceof \DOMElement){
      $el = $source;
      // dd($source);
      // dd($source->C14N());
      $tagName = $el->tagName;
      $atts = $el->attributes;
      foreach($atts as $att){
        array_push($attributes, [$att->nodeName, $att->nodeValue]);
      }

    } elseif(is_array($source)){
      $el = $source[0];
      $atts = $source[1];
      if (!is_array($atts)){
        throw new Exception("The source index 1 (attributes) should be array type");
      }
    }

    $inner = [];
    $child = $source->firstChild;
    if($child){
      array_push($inner, $child);
      while($next = $child->nextElementSibling){
        array_push($inner, $next);
        $child = $next;
      }    
    }

    $class_name = "Ptdi\Mpub\Publisher\Element\\".ucfirst($tagName);
    $element = (new $class_name($attributes, $inner));

    return $element;
  }

  public function __set($name, mixed $value){
    $this->{$name} = $value;
  }

  /**
   * intended to get $attribute
   */
  public function __get($name){
    // dd($name, __CLASS__,__LINE__  );
    if (!$this->{$name}){
      foreach ($this->attributes as $attribute){
        if($attribute[0] == $name)
        return $attribute[1];
      }
    } else {
      return $this->{$name};
    }
  }

  public function getNodeName(){
    $reflex = new ReflectionClass($this);
    return $reflex;
    // return $this->nodeName;
  }

  // public static function getSimpleStructureFromSchema(string $schema_name, string $node_name){
  //   $schema_string = Schema::getSchemaString($schema_name);
  //   $domSchema = (new DOMDocument())->loadXML($schema_string);

  //   $domXpath = new DOMXPath($domSchema);
  //   $node = $domXpath->evaluate("//xs:element[@name = '{$node_name}']");
  //   $node = $node->item(0);

  //   $attributes = [];
  //   $att = [];
  //   $node_name_elemType = $node_name."ElemType";

  //   if($node->getAttribute('type') == $node_name_elemType){
  //     $elemType = $domXpath->evaluate("//xs:complexType[@name='{$node_name_elemType}'");
  //     $elemType = $elemType->item(0);

  //     foreach($elemType->childNodes as $att){
  //       if($att->nodeName = "xs:attribute"){
  //         array_push($att[0], $att->getAttribute('ref') ?: $att->getAttribute('name'));
  //         array_push($att[1], null);
  //       } elseif ($att->nodeName == "xs:attributeGroup"){
  //         $attGroup = $domXpath->evaluate("//xs:attributeGroup[@name = '{$att->getAttribute('ref')}']");
  //         $attGroup = $attGroup->item(0);
  //         array_push($att[0], $attGroup)
  //       }
  //     }
  //   }
  // }



  // public function __construct(string $nodeName, array $attribute, $inner)
  // public function __construct(string $schema_name)
  // {
  //   $this->nodeName = $nodeName;
  //   $schemaString = Schema::getSchemaString($schemaName);
  // }

  // public static function createElementByDModule(DModule $dmodule, string $nodeName = null ,string $xpathQuery = null)
  // {
  //   $domXpath = new DOMXPath($dmodule->getDOMDocument());
    
  //   if($nodeName != null){
  //     $query_nodeName = "//".$nodeName;
  //   } else {
  //     $query_nodeName = $xpathQuery;
  //   }
  //   $nodeNames = $domXpath->evaluate($query_nodeName);
  //   // dd(get_class($nodeName));
  //   // dd(get_class($nodeNames));
  //   dd($nodeNames->item(0), __FUNCTION__);
  // }

  // public static function createElementByString(string $string)
  // {
  // }

  
}