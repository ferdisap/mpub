<?php 

namespace Ptdi\Mpub\Core;

use Exception;

abstract class CSDB {

  public $verificator;
  public $validator;
  public $techpub;

  /**
   * Load CSDB object
   * 
   * @param string $filename The csdb object
   */
  public static function load(string $filename)
  {
    // dd($filename);
    $mime = mime_content_type($filename);
    // echo $mime;
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

  private static function loadXmlDoc(String $filename)
  {
    // dd(__DIR__, __NAMESPACE__);
    $doc = new \DOMDocument();
    $doc->load($filename, LIBXML_PARSEHUGE);
    return $doc;
  }  
}