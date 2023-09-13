<?php 

namespace Ptdi\Mpub\Publisher\Element;

use Ptdi\Mpub\Publisher\Element;

class DmlAddress extends Element {
  
  private string $schemaName;
  private array $childElement = [];

  public function __construct(string $schema)
  {
    parent::__construct();
  }
}