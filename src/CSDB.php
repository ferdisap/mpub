<?php 

namespace Ptdi\Mpub;

use DOMElement;
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
    if(!$mime){
      return false;
    }
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
    $doc->load($filename, LIBXML_PARSEHUGE);
   
    return $doc;
    // try {
    // } catch (\Throwable $th) {
    //   return false;
    // }
  }

  public function setCsdbPath(string $app_path ,string $modelIdentCode = null)
  {
    if($modelIdentCode){
      return $this->CSDB_path = ($app_path ?? '')."\ietp_". strtolower($modelIdentCode) . "\csdb\\";
    }
    return $this->modelIdentCode ? $this->CSDB_path = ($app_path ?? '')."\ietp_". strtolower($this->modelIdentCode). "\csdb\\" : null;
  }

  // tambahan saat buat pdf
  /**
   * @param string $absolute_path for publication module, if empty string, it call the $xml_string
   * @param string $xml_string of publication module
   */
  public static function importDocument(mixed $absolute_path = '', string $xml_string = '', string $rootname = '')
  {
    $DOMDocument = null;
    if($absolute_path != ''){
      $dom = CSDB::load($absolute_path);
      $DOMDocument = $dom;
      if($DOMDocument){
        if ($DOMDocument->firstElementChild->tagName != "{$rootname}") {
          throw new \Exception("The root element must be <{$rootname}>", 1);
        }
      }
    } else {
      $dom = new \DOMDocument();
      $dom->loadXML($xml_string);
      $DOMDocument = $dom;
      if($DOMDocument){
        if ($DOMDocument->firstElementChild->tagName != "{$rootname}") {
          throw new \Exception("The root element must be <{$rootname}>", 1);
        }
      }
    }
    return $DOMDocument;
  }

  /**
   * minimum value of level is 0 (zero)
   * @return int
   */
  public static function checkLevel(\DOMElement $element, int $minimum = 0){
    $tagName = $element->tagName;
    $level = $minimum;
    while(($parent = $element->parentNode)->nodeName == $tagName){
      $element = $parent;
      $level += 1;
    }
    return ($level < 0) ? $minimum : (int) $level;
  }

  /**
   * checking index by sibling
   * @return int
   */
  public static function checkIndex(\DOMElement $element, int $minimum = 0){
    $tagName = $element->tagName;
    $parent = $element->parentNode;
    $index = $minimum;
    if($parent){
      while($prev_el = $element->previousElementSibling){
        if($prev_el->tagName == $tagName){
          $index += 1;
        }
        $element = $prev_el;
      }
      return (int) $index;
    }
  }

  /**
   * @return string
   */
  public static function getPrefixNum(\DOMElement $element, $minimum = 0){
    $tagName = $element->tagName;
    $index = CSDB::checkIndex($element) + $minimum;
    $prefixnum = array($index);

    while(($parent = $element->parentNode)->nodeName == $tagName){
      $index = CSDB::checkIndex($parent) + $minimum;
      array_push($prefixnum, $index);
      $element = $parent;
    }
    $prefixnum = array_reverse($prefixnum);
    return (string) join(".",$prefixnum);
  }

  public static function children(\DOMElement $element, string $nodeType = "element"){
    $arr = array();
    foreach ($element->childNodes as $childNodes){
      switch ($nodeType) {
        case 'element':
          $childNodes instanceof \DOMElement ? array_push($arr, $childNodes) : null;    
          break;
        case '#text':
          dd($$childNodes, __CLASS__, __LINE__);
          break;
      }
    }
    return $arr;
  }

  public static function call($fn){
    // return (CSDB::class)::tesfungsi;
    // $c = __DIR__.DIRECTORY_SEPARATOR.CSDB::class;
    // dd($c);
    $c = CSDB::class;
    // dd(__DIR__.DIRECTORY_SEPARATOR.$c);
    return "$c::$fn";
  }

  // public static function resolve_dmCode_forXSLT($dmCode)
  // {
    // dd($dmCode[0]);
    // dd($el);
    // return $el[0];
    // dd($el);
    // return $el[0];
    // return $el->nodeValue;
    // return 'sedang tesfungsi';
  // }

  public static function resolve_issueType($issueType){
    // untuk mengakomodir penggunaan fungsi di XSLT
    if(is_array($issueType)){
      $issueType = $issueType[0];
    }
    $it = $issueType->nodeValue;
    
    switch ($it) {
      case 'new':
        return'N';
        break;
      case 'changed':
        return 'C';
        break;
      case 'deleted':
        return 'D';
        break;
      case 'revised':
        return 'R';
        break;
      case 'status':
        return 'S';
        break;
      case 'rinstate-changed':
        return 'RC';
        break;
      case 'rinstate-revised':
        return 'RR';
        break;
      case 'rinstate-status':
        return 'RS';
        break;
    }
  }

  public static function get_childrenElement(\DOMElement $element)
  {
    $arr = [];
    foreach ($element->childNodes as $childNodes) {
      $childNodes instanceof \DOMElement ? array_push($arr, $childNodes) : null;
    }
    return $arr;
  }  

  public static function resolve_issueDate($issueDate, $format = "M-d-Y"){
    // untuk mengakomodir penggunaan fungsi di XSLT
    if(is_array($issueDate)){
      $issueDate = $issueDate[0];
    }
    $y = $issueDate->getAttribute("year");
    $m = $issueDate->getAttribute("month");
    $d = $issueDate->getAttribute("day");

    return date($format, mktime(0,0,0, $m, $d, $y));
  }

  public static function get_applic_display_text($applic, $separator = '|'){
    // untuk mengakomodir penggunaan fungsi di XSLT
    if(is_array($applic)){
      $applic = $applic[0];
    }

    $displayText = $applic->getElementsByTagName('displayText')[0];
    $simpleParas = self::get_childrenElement($displayText);
    $txt = '';
    $c = count($simpleParas);
    for ($i=0; $i < $c; $i++) { 
      $txt .= $simpleParas[$i]->nodeValue;
      if($i != ($c-1)) $txt .= $separator;
    }
    return $txt;
    
  }

  public static function resolve_dmTitle($dmTitle, string $child = ''){
    // untuk mengakomodir penggunaan fungsi di XSLT
    if(is_array($dmTitle)){
      $dmTitle = $dmTitle[0];
    }

    $techname = $dmTitle->firstElementChild->nodeValue;
    $infoname = (isset($dmTitle->firstElementChild->nextElementSibling) ? $dmTitle->firstElementChild->nextElementSibling->nodeValue : '');
    $infoNameVariant = (isset($dmTitle->firstElementChild->nextElementSibling->nextElementSibling) ? $dmTitle->firstElementChild->nextElementSibling->nextElementSibling : '');

    switch ($child) {
      case 'techname':
        return $techname;
        break;
      case 'infoname':
        return $infoname;
        break;
      case 'infoNameVariant':
        return $infoNameVariant;
        break;
      default:
        return $techname."-".$infoname."-".$infoNameVariant;
        break;
    }
  }

  
  public static function resolve_dmCode($dmCode, string $prefix = 'DMC-')
  {
    // untuk mengakomodir penggunaan fungsi di XSLT
    if(is_array($dmCode)){
      $dmCode = $dmCode[0];
    }

    $modelIdentCode = $dmCode->getAttribute('modelIdentCode');
    $systemDiffCode = $dmCode->getAttribute('systemDiffCode');
    $systemCode = $dmCode->getAttribute('systemCode');
    $subSystemCode = $dmCode->getAttribute('subSystemCode');
    $subSubSystemCode = $dmCode->getAttribute('subSubSystemCode');
    $assyCode = $dmCode->getAttribute('assyCode');
    $disassyCode = $dmCode->getAttribute('disassyCode');
    $disassyCodeVariant = $dmCode->getAttribute('disassyCodeVariant');
    $infoCode = $dmCode->getAttribute('infoCode');
    $infoCodeVariant = $dmCode->getAttribute('infoCodeVariant');
    $itemLocationCode = $dmCode->getAttribute('itemLocationCode');

    $name = $prefix.
    $modelIdentCode."-".$systemDiffCode."-".
    $systemCode."-".$subSystemCode.$subSubSystemCode."-".
    $assyCode."-".$disassyCode.$disassyCodeVariant."-".
    $infoCode.$infoCodeVariant."-".$itemLocationCode;

    return $name;
  }

  public static function resolve_pmCode(\DOMElement $pmCode, string $prefix = 'PMC-')
  {
    $modelIdentCode = $pmCode->getAttribute('modelIdentCode');
    $pmIssuer = $pmCode->getAttribute('pmIssuer');
    $pmNumber = $pmCode->getAttribute('pmNumber');
    $pmVolume = $pmCode->getAttribute('pmVolume');

    $name = $prefix.
    $modelIdentCode."-".
    $pmIssuer."-".
    $pmNumber."-".
    $pmVolume;

    return $name;
  }

  public static function resolve_issueInfo(\DOMElement $issueInfo = null)
  {
    if(!$issueInfo){
      return '';
    }
    $issueNumber = $issueInfo->getAttribute('issueNumber');
    $inWork = $issueInfo->getAttribute('inWork');
    return $issueNumber."-".$inWork;
  }

  public static function resolve_languange(\DOMElement $languange = null)
  {
    if(!$languange) {
      return '';
    }
    $languangeIsoCode = $languange->getAttribute('languageIsoCode');
    $countryIsoCode = $languange->getAttribute('countryIsoCode');
    return $languangeIsoCode."-".$countryIsoCode;
  }
}