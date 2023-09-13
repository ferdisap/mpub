<?php 

namespace Ptdi\Mpub;

use Exception;

abstract class CSDB {
  
  protected string $modelIdentCode;
  protected string $CSDB_path;
  
  /**
   * Load CSDB object
   * 
   * @param string $filename The csdb object
   */
  public static function load(string $filename)
  {
    $mime = mime_content_type($filename);
    switch ($mime) {
      case 'text/xml':
        return self::loadXmlDoc($filename);
      case 'image/jpeg':
        return;
      default:
        throw new Exception("No such object of csdb");
        break;
    }
  }

  /**
   * Load xml document from local
   * 
   * @param string $filename The xml file
   * 
   * @return \DOMDocument document or false
   */
  private static function loadXmlDoc(String $filename)
  {
    $doc = new \DOMDocument();
    try {
      $doc->load($filename, LIBXML_PARSEHUGE);
      return $doc;
    } catch (\Throwable $th) {
      return false;
    }
  }

  public function setCsdbPath(string $app_path ,string $modelIdentCode = null)
  {
    if($modelIdentCode){
      return $this->CSDB_path = ($app_path ?? '')."\ietp_". strtolower($modelIdentCode) . "\csdb\\";
    }
    return $this->modelIdentCode ? $this->CSDB_path = ($app_path ?? '')."\ietp_". strtolower($this->modelIdentCode). "\csdb\\" : null;
  }
}