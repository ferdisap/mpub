<?php 

namespace Ptdi\Mpub\Publisher\Element;

use Ptdi\Mpub\Publisher\Element;
use Ptdi\Mpub\Publisher\Structure;

class IdentAndStatusSection extends Element {
  use Structure;
  private string $schemaName;

  private array $childElement_dmlXsd = ['dmlIdent', 'dmlAddressItems'];
  public array $attributes = [];
  private array $structure;

  /**
   * @param string $schemaName with extension
   */
  public function __construct(string $schemaName)
  {
    parent::__construct();
    $this->schemaName = $schemaName;
    switch ($schemaName) {
      case 'dml.xsd':
        $this->setStructure($this->childElement_dmlXsd);
        break;
    }
  }


}