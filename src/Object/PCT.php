<?php 

namespace Ptdi\Mpub\Object;

use Exception;
use Ptdi\Mpub\Object\DModule;

class PCT extends DModule {

  public array $condType;
  public array $condList;

  public function __construct(string $filename)
  {
    parent::__construct($filename);
    // if(DModule::getSchemaName($this->getDOMDocument()->firstElementChild) != 'prdcrossreftable')
    // {
    //   throw new Exception("The DModule is not type of PCT data module", 1);
    // }
  }
}