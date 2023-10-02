<?php

namespace Ptdi\Mpub\Pdf2;

use Ptdi\Mpub\CSDB;

class PM
{
  public string $name;
  public array $config;
  public $DOMDocument;
  protected bool $validate = true;

  public function __construct(string $name, array $config)
  {
    $this->name = $name;
  }

  // import content
  public function import_file(string $absolute_path)
  {
    $dom = CSDB::load($absolute_path);
    $this->DOMDocument = $dom;
    if ($this->DOMDocument->firstElementChild->tagName == 'pm') {
      throw new \Exception("The root element must be <pm>", 1);
    }
  }
  public function import(string $xmlString)
  {
    $dom = new \DOMDocument();
    $dom->loadXML($xmlString);
    $this->DOMDocument = $dom;
    if ($this->DOMDocument->firstElementChild->tagName == 'pm') {
      throw new \Exception("The root element must be <pm>", 1);
    }
  }

  public function render()
  {
    if (!$this->validate) {
      return false;
    }
    $pmType = $this->DOMDocument->firstElementChild->getAttribute('pmType');

    switch ($pmType) {
      case 'pt51':
        // pmType = afm
        return $this->pt51($this->name, $this->config);
        break;
      
      default:
        // pmType with no header
        break;
    }
  }
  
  private function pt51(string $name,array $config)
  {
    
  }
}
