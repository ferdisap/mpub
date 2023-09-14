<?php 

namespace Ptdi\Mpub\Publisher\Element;

use DOMAttr;
use DOMDocument;
use Exception;
use Ptdi\Mpub\Publisher\Element;
use Ptdi\Mpub\Schema\Schema;

class DmlEntry extends Element {
  
  private string $schemaName;
  private array $childElement_dmlXsd = [];
  public array $attributes;

  // jika element dmlEntry ada attribute yang wajib, maka masuk ke __construct()
  public function __construct(string $schemaName = 'dml.xsd')
  {
    parent::__construct();
    $this->schemaName = $schemaName;
  }

  public function setAttributes(string $dmlEntryType = '', string $issueType = '')
  {
    if($dmlEntryType){
      if(!in_array($dmlEntryType, require "config/dmlEntryType.php")){
        throw new Exception("No such dmlEntryType {$dmlEntryType}", 1);
      }
      $this->attributes['dmlEntryType'] = $dmlEntryType;
    }
    
    if($issueType){
      if(!in_array($issueType, require "config/issueType.php")){
        throw new Exception("No such issueType {$issueType}", 1);
      }
      $this->attributes['issueType'] = $issueType;
    }
  }

  public function generate_DOMElement(){
    $doc = new DOMDocument("1.0", "UTF-8");
    $el = $doc->createElement($this->nodeName);
    $doc->appendChild($el);
    
    foreach($this->attributes as $name => $value){
      $el->setAttribute($name, $value);
    }
    return $doc->documentElement;
  }
}