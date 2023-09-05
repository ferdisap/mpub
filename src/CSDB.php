<?php 

namespace Ptdi\Mpub;

use Exception;

abstract class CSDB {
  
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
}