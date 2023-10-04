<?php 

namespace Ptdi\Mpub\Publisher\Element;

use Ptdi\Mpub\Publisher\Element;

class DmlContent extends Element {
  
  private string $schemaName;
  private array $childElement_dmlXsd = [];

  public function __construct(string $schema)
  {
    parent::__construct();
  }
}