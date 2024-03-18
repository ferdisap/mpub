<?php

namespace PTDI\Mpub\Main;

use Ptdi\Mpub\Main\CSDBError;
use Ptdi\Mpub\Main\CSDBStatic;
use Ptdi\Mpub\Main\CSDBValidator;
use Ptdi\Mpub\Main\XSIValidator;

/**
 * Cara pakainya yaitu
 * 1. create instance DMRLValidator();
 * 2. setEntries();
 * 3. setDoctypes();
 * 4. validate();
 */
class DMRLValidator extends CSDBValidator
{

  /**
   * array contained string filename
   */
  protected array $entries = [];

  /**
   * doctype yang divalidasi berupa 'dmodule', 'pm', 'infoEntity', 'comment', dan/atau 'dml';
   */
  protected array $doctypes = [];

  /**
   * @param mixed $validatee adalah absolute path XML document atau \CSDBObject class
   * @param mixed $validator adalah absolute path XML document atau \CSDBObject class
   * @return \BREXValidator
   */
  public function __construct(mixed $validator)
  {
    $this->validationType = 'DMRL';
    if (is_string($validator)) {
      $this->validator = new CSDBObject("5.0");
      $this->validator->load($validator);
    } elseif ($validator instanceof CSDBObject) {
      $this->validator = $validator;
    }
    return $this;
  }

  public function setEntries($entries = []): void
  {
    $this->entries = $entries;
  }

  public function setDoctypes($doctypes = []): void
  {
    $this->doctypes = $doctypes;
  }

  /**
   * jika $object type tidak termasuk bagian yang perlu di validasi dmrl, maka dianggap true
   */
  public function validate(): bool
  {
    return $this->validateByDMRL();
  }

  private function validateByDMRL(): bool
  {
    if (!in_array($this->doctypes, ['dmodule', 'pm', 'infoEntity', 'comment', 'dml'])) {
      return true;
    }
    if (!$this->validator->document) return [false, 'No such DMRL', 'dmrl'];
    $XSIValidator = new XSIValidator($this->validator);
    $validation = $XSIValidator->validate();
    if (!$validation) {
      $err = CSDBError::getErrors(true, 'validateBySchema');
      array_unshift($err, "DMRL must be comply to dml.xsd");
      CSDBError::setError('validateByDMRL', join(", ", $err), 'dmrl');
      return false;
    }
    
    $xpath = new \DOMXPath($this->validator->document);
    $dmlEntries = $xpath->evaluate("//dmlEntry");
    $nominal_idents = array();
    foreach ($dmlEntries as $dmlEntry) {
      $ident = str_replace("Ref", '', $dmlEntry->firstElementChild->tagName);
      if ($dmlEntry->firstElementChild->tagName == 'infoEntityRef') {
        $nominal_idents[] = $dmlEntry->firstElementChild->getAttribute('infoEntityRefIdent');
      } else {
        $nominal_idents[] = call_user_func_array(CSDBStatic::class . "::resolve_{$ident}Ident", [$dmlEntry->getElementsByTagName("{$ident}RefIdent")[0]]);
      }
    }
    // $actual_ident = preg_replace("/_\d{3,5}-\d{2}|_[A-Za-z]{2,3}-[A-Z]{2}/", '', $entryFilename); // untuk membersihkan inwork dan issue number pada filename
    $actual_idents = array_map((fn($entryFilename) => preg_replace("/_\d{3,5}-\d{2}|_[A-Za-z]{2,3}-[A-Z]{2}/", '', $entryFilename)),$this->entries); // untuk membersihkan inwork dan issue number pada filename
    foreach($actual_idents as $actual_ident){
      if (!in_array($actual_ident, $nominal_idents)) {
        $actual_ident = preg_replace('/\.\w+$/', '', $actual_ident);
        CSDBError::setError('validateByDMRL', "{$actual_ident} is not required by the DMRL.");
      }
    }
    if(CSDBError::getErrors(false, 'validateByDMRL')){
      return false;
    } else {
      return true;
    }

  }
}
