<?php 

namespace Ptdi\Mpub\Main;

class CSDBValidator{

  protected CSDBObject $validatee;
  protected CSDBObject $validator;
  protected string $validationType = '';
  protected array $params;
  protected string $storage_path = '';

  public function __construct(string $validationType = '', $params = [])
  {
    $this->validationType = $validationType;
    $this->params = $params;
  }

  public function setStoragePath(string $absolutePath)
  {
    $this->storage_path = $absolutePath;
  }

  public function validate() :bool
  {
    if(!$this->validationType) return false;
    elseif($this->validationType === 'ICNName') return $this->validateICNName();
    else return false;
  }

  /**
   * @param string $filename not include path
   * @param string $codeBased 'modelIdent' or 'cage'
   * @return string $filename if true 
   * @return boolean false
   */
  private function validateICNName(string $rule = 'PTDI') :bool
  {
    $decodedFilename = CSDBStatic::decode_infoEntityIdent($this->params['validatee']);
    if($rule === 'PTDI'){
      if($this->usePTDIICNNamingFileRule($decodedFilename['infoEntityIdent'], $decodedFilename['prefix'], $decodedFilename['extension'])){
        return true;
      }
      return false;
    }
    return true;
  }

  /**
   * @param array $infoEntityIdent ,gunakan fungsi decode_infoEntityIdent, dan masukkan array key infoEntityIdent 
   * @return boolean true or false
   */
  private function usePTDIICNNamingFileRule(array $infoEntityIdent, $prefix = 'ICN-', $extension = '') :bool
  {
    if(!$this->storage_path){
      CSDBError::setError('', 'Can not save the icn object.');
      return false;
    };
    if(!$extension) {
      CSDBError::setError('', "Extension file of '". $prefix . join("-", $infoEntityIdent). $extension . "' should be exist.");
      return false;
    }
    if(!(count($infoEntityIdent) === 9 OR count($infoEntityIdent) === 4)){
      CSDBError::setError('', "Naming file '". $prefix . join("-", $infoEntityIdent). $extension . "' is uncomply with PTDI rule.");
      return false;
    }
    return true;

    // tidak ada validasi uniqueIdentifier disini, melainkan jika perlu, harus dilakukan di laravel controller
    // securityClassification tidak perlu di validasi karena itu belum fix cara penamaan di PTDI

    // #1 validasi uniqueIdentifier
    // $f = array_filter(scandir($this->storage_path),fn($filename) => str_contains($filename, $infoEntityIdent['uniqueIdentifier']));
    // $f = array_pop($f);
    // if($f) {
    //   CSDBError::setError('', "The unique identifier of ICN name is same with {$f}");
    //   return false;
    // }

    // #2 validasi securityClassification. Min 1, max 5
    // if(((int)$infoEntityIdent['securityClassification'] < 1) AND ((int)$infoEntityIdent['securityClassification'] > 5))
    // {
    //   CSDBError::setError('', "Security Classification value must be 1 through 5");
    //   return false;
    // }

    return true;
    ###### dibawah ini adalah aturan khusus untuk penamaan ICN 47 character ######
    // masukkan validasi seusai aturan penamaan ICN (seperti yang diberikan pak Hendro);
    // masukkan CSDBError jika ada aturan yang tidak dipenuhi
    // sementara ini rule hanya sebatas menggunakan modelIdent base, bukan cagecode base
    if(count($infoEntityIdent) != 9){
      CSDBError::setError('', "Naming file '". $prefix . join("-", $infoEntityIdent). $extension . "' is uncomply with PTDI rule.");
      return false;
    }
    return true;    
  }
  
}