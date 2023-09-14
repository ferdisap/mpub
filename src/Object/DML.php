<?php 

namespace Ptdi\Mpub\Object;

use DOMDocument;
use DOMXPath;
use Exception;
use Ptdi\Mpub\Publisher\Element\DmlEntry;
use Ptdi\Mpub\Publisher\Element\IdentAndStatusSection;
use Ptdi\Mpub\Publisher\Structure;

class DML extends DModule {
  use Structure;

  protected array $childElement = [
    'identAndStatusSection' => "required|one time|", 
    'dmlContent'
  ];
  public array $attributes = [];
  private array $structure;

  public function __construct(string $modelIdentCode = '')
  {
    parent::__construct('DML', $modelIdentCode);
  }

  public function add_dmlEntry()
  {
    $this->dmlEntry = new DmlEntry();
    $this->dmlEntry->setAttributes('n');
    $dmlEntry = $this->dmlEntry->generate_DOMElement(); // string xml
    // dd($dmlEntry);

    $doc = $this->getDOMDocument();
    $doc->formatOutput = true;
    $doc->saveXML();

    $dmlEntry = $doc->importNode($dmlEntry);

    $dmlContent = $doc->getElementsByTagName("dmlContent")->item(0);
    return $dmlContent->appendChild($dmlEntry);
  }

  public function add_dmlContent(){
    if($this->getDOMDocument()->getElementsByTagName('dmlContent')->item(0)){
      return false;      
    }
    $doc = new DOMDocument("1.0", "UTF-8");
    $dmlContent = $doc->createElement('dmlContent');
    $doc->appendChild($dmlContent);
    
    foreach($this->attributes as $name => $value){
      $dmlContent->setAttribute($name, $value);
    }

    $doc = $this->getDOMDocument();
    $doc->formatOutput = true;
    $doc->saveXML();

    $dmlContent = $doc->importNode($dmlContent);

    $dml = $doc->getElementsByTagName("dml")->item(0);
    return $dml->appendChild($dmlContent);
  }

  
}