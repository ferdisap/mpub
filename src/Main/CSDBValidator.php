<?php 

namespace Ptdi\Mpub\Main;

class CSDBValidator{

  protected CSDBObject $validatee;
  protected CSDBObject $validator;
  protected string $validationType = '';
  protected array $params;

  public function __construct(string $validationType = '', $params = [])
  {
    $this->validationType = $validationType;
    $this->params = $params;
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
    if(!$extension) {
      CSDBError::setError('', "Extension file of '". $prefix . join("-", $infoEntityIdent). $extension . "' should be exist.");
      return false;
    }
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