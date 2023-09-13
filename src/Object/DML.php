<?php 

namespace Ptdi\Mpub\Object;

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

  
}