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

  /**
   * 
   * @param array $attributes for each $attributes[index] contains two/more array which are [0] = attribute name, [1] = attribute value. Example $attributes = [["name", "value"],["name1", "value2]]
   */
  public function __construct(array $attributes = [], array $inner = []){
    $reflex = new ReflectionClass($this);
    $this->nodeName = strtolower($reflex->getShortName());
    $this->attributes = $attributes;
    $this->inner = $inner; 
    return $this;
  }


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

  public function __set(string $name, mixed $value){
    $this->{$name} = $value;
  }

  /**
   * intended to get $attribute
   */
  public function __get(string $name){
    if (!$this->{$name}){
      foreach ($this->attributes as $attribute){
        if($attribute[0] == $name)
        return $attribute[1];
      }
    } else {
      return $this->{$name};
    }
  }



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