<?php 

namespace Ptdi\Mpub\Schema;

use Ptdi\Mpub\Core\CSDB;

class Schema {

  private static $separator = PHP_OS == "Windows" || PHP_OS == "WINNT" ? "\\" : "/";

  /**
   * @param string $schemaName An XSD filename without extension format
   * 
   * @return string xml string by C14N()
   */
  public static function getSchemaString(string $schemaName)
  {
    $xsdDocument = CSDB::load(__DIR__.self::$separator.$schemaName);
    // $xsdDocument = self::resolveXsImportPath($xsdDocument);
    return $xsdDocument->C14N();
  }

  private static function resolveXsImportPath(\DOMDocument $document)
  {
    foreach ($document->getElementsByTagNameNS("http://www.w3.org/2001/XMLSchema","import") as $importEl) {
      $schemaLocation = $importEl->getAttribute("schemaLocation");
      if($schemaLocation == "xlink.xsd" || $schemaLocation == "rdf.xsd"){
        $importEl->setAttribute('schemaLocation', __DIR__.self::$separator.$schemaLocation);
        $document->saveXML();
      }
    }
    return $document;
  } 

}