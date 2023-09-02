<?php 

namespace Ptdi\Mpub\Core\CSDB\TypeDModule;

use Exception;
use Ptdi\Mpub\Core\Contracts\TypeDModule;
use Ptdi\Mpub\Core\CSDB\DModule;

class Descript extends DModule implements TypeDModule{

  private \DOMElement $DOMElement;

  public function __construct(String $filename)
  {
    $doc = parent::load($filename);
    $schemaName = parent::getSchemaName($doc->firstElementChild);
    if ($schemaName != 'descript'){
      throw new Exception("The file is not description type.");      
    }

    $this->DOMElement = $doc->firstElementChild;
    return $this;
  }

  public function getDOMElement()
  {
    return $this->DOMElement;
  }
}