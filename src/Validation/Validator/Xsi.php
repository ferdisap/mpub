<?php 

namespace Ptdi\Mpub\Validation\Validator;

use Ptdi\Mpub\Main\CSDBError;
use Ptdi\Mpub\Validation\GeneralValidationInterface;

class Xsi implements GeneralValidationInterface 
{
  protected string $schema;
  protected bool $isReady = false;
  protected bool $result = false;
  public CSDBError $errors;

  public function __construct(public \DOMDocument $document)
  {
    if($document->documentElement){
      $this->schema = $this->document->documentElement->getAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'noNamespaceSchemaLocation');
      if(
        // str_contains($this->schema, 'http://www.s1000d.org/S1000D_5-0/xml_schema_flat/') 
        str_contains($this->schema, 'http://www.s1000d.org') 
        && substr($this->schema,-4) === '.xsd'
      ) $this->isReady = true;
    }
    $this->errors = new CSDBError();
  }

  public function isReady() :bool
  {
    return $this->isReady;    
  }

  public function validate()
  {
    if($this->isReady){
      libxml_use_internal_errors(true);
      $this->result = @$this->document->schemaValidate($this->schema, LIBXML_PARSEHUGE);
      $this->handleXmlErrors(libxml_get_errors());
    }
  }

  public function result()
  {
    return $this->result;    
  }

  protected function handleXmlErrors($errors)
  {
    $e = [];
    foreach ($errors as $err) {
      $e[] = "line: {$err->line}; message: {$err->message}";
    }
    $this->errors->set('xsi_validation', $e);
  }
}