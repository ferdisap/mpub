<?php 

namespace Ptdi\Mpub\Validation\Validator;

use Ptdi\Mpub\Validation\GeneralValidationInterface;

class Xsi implements GeneralValidationInterface 
{
  protected string $schema;
  protected bool $isReady = false;
  protected bool $result = false;

  public function __construct(public \DOMDocument $document)
  {
    if($document->documentElement){
      $this->schema = $document->documentElement->getAttribute('xsi:noNamespaceSchemaLocation');
      if(
        str_contains($this->schema, 'http://www.s1000d.org/S1000D_5-0/xml_schema_flat/') 
        && substr($this->schema,-4) === '.xsd'
      ) $this->isReady = true;
    }
  }

  public function isReady() :bool
  {
    return $this->isReady;    
  }

  public function validate()
  {
    if($this->isReady){
      $this->result = $this->document->schemaValidate($this->schema);
    }
  }

  public function result()
  {
    return $this->result;    
  }
}