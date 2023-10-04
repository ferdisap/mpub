<?php 

namespace Ptdi\Mpub\Publisher\Element;

use Ptdi\Mpub\Publisher\Element;

class DmlAddressItems extends Element {
  
  private string $schemaName;
  private array $childElement = ['issueDate'];

  public function __construct(string $schema)
  {
    parent::__construct();
  }
}