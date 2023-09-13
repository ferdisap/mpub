<?php 

namespace Ptdi\Mpub\Publisher\Element;

use Ptdi\Mpub\Publisher\Element;
use Ptdi\Mpub\Publisher\Structure;

class DmlIdent extends Element {
  use Structure;
  private string $schemaName;

  private array $childElement = ['dmlCode', 'issueInfo'];
  public array $attributes = [];
  private array $structure;

  public function __construct(string $schemaName)
  {
    parent::__construct();
    $this->schemaName = $schemaName;
  }


}