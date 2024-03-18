<?php 

namespace Ptdi\Mpub\Main;

use DOMDocument;

class XSIValidator extends CSDBValidator{

  /**
   * @param mixed $validatee adalah absolute path XML document atau \CSDBObject class
   * @return \XSIValidator
   */
  public function __construct(mixed $validatee)
  {
    $this->validationType = 'XSI';
    if(is_string($validatee)){
      $this->validatee = new CSDBObject("5.0");    
      $this->validatee->load($validatee);
    }
    elseif($validatee instanceof CSDBObject){
      $this->validatee = $validatee;
    }
    return $this;
  }

  public function validate() :bool
  {
    return $this->validateBySchema();
  }

  private function validateBySchema() :bool
  {
    if(!$this->validatee->isS1000DDoctype() OR !($this->validatee->document instanceof \DOMDocument)) {
      CSDBError::setError('validateBySchema', "There is no document to validate.");
      return false;
    };
    if($this->validatee->XSIValidationResult) return true;
    libxml_use_internal_errors(true);
    $validator = $this->validatee->document->documentElement->getAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'noNamespaceSchemaLocation');
    $validate = @$this->validatee->document->schemaValidate($validator, LIBXML_PARSEHUGE);
    $errors = libxml_get_errors();
    if (!$validate) {
      CSDBError::setError('validateBySchema', "error during validate by xsi in file " . $this->validatee->filename . ".");
      foreach ($errors as $err) {
        CSDBError::setError('validateBySchema', "line: {$err->line}; message: {$err->message}");
      }
      $this->validatee->XSIValidationResult = false;
      return false;
    } else {
      $this->validatee->XSIValidationResult = true;
      return true;
    }
  }
}