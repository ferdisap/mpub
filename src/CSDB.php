<?php

namespace Ptdi\Mpub;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Exception;
use Ptdi\Mpub\Pdf2\PMC_PDF;

// abstract class CSDB {
//  untuk keperluan program baru, ini dibuat tidak abstact
class CSDB
{
  use Validation;

  protected string $modelIdentCode;
  protected string $CSDB_path;
  public static bool $CSDB_use_internal_error = true;
  protected static array $errors = array();
  public static string $processid = '';
  protected static $object_to_export = [];
  public const SCHEMA_PATH = __DIR__ . DIRECTORY_SEPARATOR . "Schema";

  public static function get_object_to_export()
  {
    return self::$object_to_export;
  }

  public static function set_object_to_export(mixed $val)
  {
    if (is_array($val)) {
      self::$object_to_export = array_unique(array_merge($val, self::$object_to_export));
    } elseif (is_numeric($val) or is_string($val)) {
      self::$object_to_export[] = $val;
    }
  }

  public static function getSchemaUsed($doc, $option = 'file')
  {
    if (!$doc or !($doc instanceof \DOMDocument)) return '';
    // untuk mengakomodir penggunaan fungsi di XSLT
    if (is_array($doc)) {
      $doc = $doc[0];
    }
    $schema = $doc->documentElement->getAttributeNS('http://www.w3.org/2001/XMLSchema-instance', "noNamespaceSchemaLocation");
    preg_match("/\w+.xsd/", $schema, $schema);
    if (!empty($schema)) $schema = $schema[0];

    if ($option == 'file') {
      $file = self::importDocument(__DIR__ . "/Schema\/", $schema, null, '');
      return $file;
    } else {
      return $schema;
    }
  }

  public static function setError(string $processId = null, string $message)
  {
    if ($processId) {
      self::$errors[$processId][] = $message;
    } else {
      self::$errors[] = $message;
    }
  }

  public static function resolve_securityClassification(\DOMDocument $doc)
  {
    $sc = $doc->getElementsByTagName('security')[0]->getAttribute('securityClassification');
    switch ($sc) {
      case '01':
        return 'unclassified';
        break;
      case '02':
        return 'restricted';
        break;
      case '03':
        return 'confidential';
        break;
      case '04':
        return 'secret';
        break;
      case '05':
        return 'top secret';
        break;
      default:
        return ' ';
        break;
    }
  }

  public static function resolve_DocIdent(\DOMDocument $doc)
  {
    $docType = $doc->firstElementChild->tagName;
    if ($docType == 'pm') {
      $docIdent = $doc->getElementsByTagName('pmIdent')[0];
      $docIdent = self::resolve_pmIdent($docIdent);
    } elseif ($docType == 'dmodule') {
      $docIdent = $doc->getElementsByTagName('dmIdent')[0];
      $docIdent = self::resolve_dmIdent($docIdent);
    } elseif ($docType == 'icnMetadataFile') {
      $docIdent = $doc->getElementsByTagName('imfIdent')[0];
      $docIdent = CSDB::resolve_imfIdent($docIdent);
    } else {
      $docIdent = '';
    }
    return $docIdent;
  }

  public static function resolve_DocTitle(\DOMDocument $doc)
  {
    $docType = $doc->firstElementChild->tagName;
    switch ($docType) {
      case 'dmodule':
        $title = $doc->getElementsByTagName('dmTitle')[0];
        $title = CSDB::resolve_dmTitle($title);
        break;
      case 'pm':
        $title = $doc->getElementsByTagName('pmTitle')[0];
        $title = CSDB::resolve_pmTitle($title);
        break;
      case 'icnMetadataFile':
        $title = new \DOMXPath($doc);
        $title = $title->evaluate("//icnInfoItem[@icnInfoItemType = 'iiit54']")[0];
        $title = $title->textContent ?? '';
        break;
      default:
        $title = '';
        break;
    }
    return $title;
  }

  public static function resolve_imfIdent($imfIdent = null, array $idents = [], $prefix = 'PMC-', $format = '.xml')
  {
    if (empty($idents)) {
      if (is_array($imfIdent)) {
        $imfIdent = $imfIdent[0];
      }
      $imfCode = $imfIdent->getElementsByTagName('imfCode')[0]->getAttribute('imfIdentIcn');
      $issueInfo = ($if = self::resolve_issueInfo($imfIdent->getElementsByTagName('issueInfo')[0])) ? "_" . $if : '';
    } else {
      $imfCode = $idents[0];
      $issueInfo = isset($idents[1]) ? "_" . $idents[1] : '';
      $languange = isset($idents[2]) ? "_" . $idents[2] : '';
    }

    return strtoupper($prefix .$imfCode . $issueInfo ) . $format;
  }

  // nggak dipakai karena fungsi importDocument tidak lagi memakai ini
  // fungsi ini di pakai / di override di ICNDocument.php
  // /**
  //  * Load CSDB object
  //  * fungsi ini depreciated
  //  * @param string $filename The csdb object
  //  */
  // public static function load(string $filename, $tes = false)
  // {
  //   if (!file_exists($filename)) {
  //     preg_match("/(?<=\/|\\\\)(DMC|ICN|PMC).+/", $filename, $matches);
  //     self::$errors['file_exists'][] = "{$matches[0]} is not exist";
  //     return false;
  //   }
  //   $mime = mime_content_type($filename);
  //   if (!$mime) {
  //     return false;
  //   }
  //   switch ($mime) {
  //     case 'text/xml':
  //       return self::loadXmlDoc($filename);
  //     case 'text/plain':
  //       return file_get_contents($filename, true);
  //       break;
  //     case 'image/jpeg':
  //       return [file_get_contents($filename, true), $mime];
  //     default:
  //       throw new Exception("No such object of csdb");
  //       break;
  //   }
  // }

  // /**
  //  * Load xml document from local
  //  * 
  //  * @param string $filename The xml file
  //  * 
  //  * @return \DOMDocument document or false
  //  */
  // private static function loadXmlDoc(String $filename)
  // {
  //   $doc = new \DOMDocument();
  //   $doc->load($filename, LIBXML_PARSEHUGE);

  //   return $doc;
  // }

  private static function display_xml_error($error)
  {
    $return = '--- ';

    switch ($error->level) {
      case LIBXML_ERR_WARNING:
        $return .= "Warning $error->code: ";
        break;
      case LIBXML_ERR_ERROR:
        $return .= "Error $error->code: ";
        break;
      case LIBXML_ERR_FATAL:
        $return .= "Fatal Error $error->code: ";
        break;
    }

    $message = preg_replace("/file:\S+(?=\/|\\\\)/m",'',$error->message);
    $return .= trim($message) .
    // $return .= trim($error->message) .

      ". Line: $error->line" .
      ". Column: $error->column";

    if ($error->file) {
      $return .= ". File: $error->file";
    }

    return "$return ---";
  }

  public function setCsdbPath(string $app_path, string $modelIdentCode = null)
  {
    if ($modelIdentCode) {
      return $this->CSDB_path = ($app_path ?? '') . "\ietp_" . strtolower($modelIdentCode) . "\csdb\\";
    }
    return $this->modelIdentCode ? $this->CSDB_path = ($app_path ?? '') . "\ietp_" . strtolower($this->modelIdentCode) . "\csdb\\" : null;
  }

  // tambahan saat buat pdf
  /**
   * untuk absolute path, tambahkan back slash "/" di akhir string
   * @param string $absolute_path for publication module, if empty string, it call the $xml_string
   * @param string $xml_string of publication module
   */
  public static function importDocument(mixed $absolute_path = null, $filename = '', string $xml_string = null, string $rootname = '', $tes = false)
  {
    libxml_use_internal_errors(true);
    $absolute_path = !empty($absolute_path) ? $absolute_path.DIRECTORY_SEPARATOR : '';
    if (!empty($absolute_path) or !empty($xml_string)) {
      if (
        !empty($absolute_path) and
        (!file_exists($absolute_path . $filename) or !is_file($absolute_path . $filename))
      ) {
        $m = "{$filename} does not exist.";
        self::$processid ? (self::$errors[self::$processid][] = $m) : (self::$errors['file_exists'][] = $m);
        return false;
      }
      $text = $xml_string ?? '';
      $mime = file_exists($absolute_path . $filename) ? mime_content_type($absolute_path . $filename) : '';
      if (str_contains($mime, 'text') or $text) {
        $obj = new DOMDocument();
        if ($text) {
          $obj->loadXML(trim($text));
        } else {
          $obj->load($absolute_path . $filename); // pakai load supaya ada base URI sehingga bisa relative path kalau perlu operasi import/include di xml nya. 
        }

        if ((isset($obj->firstElementChild))
          and !empty($rootname)
          and ($obj->firstElementChild->tagName != "{$rootname}")
        ) {
          if (!self::$CSDB_use_internal_error) {
            throw new \Exception("The root element must be <{$rootname}>", 1);
          } else {
            self::$processid ? (self::$errors[self::$processid][] = "The root element must be <{$rootname}>") : (self::$errors[] = "The root element must be <{$rootname}>");
            return false;
          }
        }
        $obj->absolute_path = $absolute_path;

        // checking error of libxml_get_errors
        $errors = libxml_get_errors();
        foreach($errors as $e ){
          self::$processid ? (self::$errors[self::$processid][] = self::display_xml_error($e)) : (self::$errors[] = self::display_xml_error($e));
        }
        if(!empty($errors)){
          return false;
        }

        return $obj;
      } else {
        $icn =  new ICNDocument();
        $icn->load($absolute_path, $filename);
        return $icn;
        // $obj = [file_get_contents($absolute_path . $filename), $mime];
        // return $obj;
      }
    } else {
      self::$processid ? (self::$errors[self::$processid][] = "there is no data to be document.") : (self::$errors[] = "there is no data to be document.");
      return false;
    }



    # script lama
    //   libxml_use_internal_errors(true);
    //   // $DOMDocument = null;
    //   $DOMDocument = new DOMDocument();
    //   if (!empty($absolute_path)){
    //     $DOMDocument = CSDB::load($absolute_path.$filename,$tes);
    //   } elseif (!empty($xml_string)) {
    //     $DOMDocument->loadXML($xml_string);
    //   } else {
    //     return false;
    //   }

    //   // jika bukan XML, misal mime= text/plain atau image
    //   if(!($DOMDocument instanceof \DOMDocument)){
    //     return $DOMDocument;
    //   }

    //   // jika tidak ada firstElementChild, return false
    //   if (!isset($DOMDocument->firstElementChild)) {
    //     // jika rootname ada DAN firstElementChild tidak sama dengan rootname, return false atau error
    //     return false;
    //   }
    //   if (!empty($rootname) and ($DOMDocument->firstElementChild->tagName != "{$rootname}")) {
    //     if (!self::$CSDB_use_internal_error) {
    //       throw new \Exception("The root element must be <{$rootname}>", 1);
    //     } else {
    //       if (self::$processid) {
    //         self::$errors[self::$processid][] = "The root element must be <{$rootname}>";
    //       } else {
    //         self::$errors[] = "The root element must be <{$rootname}>";
    //       }
    //       return false;
    //     }
    //   }

    //   $DOMDocument->absolute_path = $absolute_path;
    //   return $DOMDocument;
  }

  public static function decode_dmCode(string $name)
  {
    $doc = new DOMDocument();
    $doc->loadXML("<dmCode/>");

    $dmCode = $doc->documentElement;
    $arr = explode("-", $name);
    $att = ['modelIdentCode', 'systemDiffCode', 'systemCode', 'subSystemCode', 'assyCode', 'disassyCode', 'infoCode', 'itemLocationCode'];
    foreach ($arr as $k => $v) {
      switch ($att[$k]) {
        case 'subSystemCode':
          $subSystemCode = substr($arr[$k], 0, 1);
          $subSubSystemCode = substr($arr[$k], 1);
          $dmCode->setAttribute('subSystemCode', $subSystemCode);
          $dmCode->setAttribute('subSubSystemCode', $subSubSystemCode);
          break;
        case 'disassyCode':
          $disassyCodeVariant = substr($arr[$k], 2);
          $disassyCode = rtrim($arr[$k], $disassyCodeVariant);
          $dmCode->setAttribute('disassyCode', $disassyCode);
          $dmCode->setAttribute('disassyCodeVariant', $disassyCodeVariant);
          break;
        case 'infoCode':
          // dd($name,$att, $k, $arr);
          $infoCodeVariant = substr($arr[$k], 3);
          $infoCode = rtrim($arr[$k], $infoCodeVariant);
          $dmCode->setAttribute('infoCode', $infoCode);
          $dmCode->setAttribute('infoCodeVariant', $infoCodeVariant);
          break;
        default:
          $dmCode->setAttribute($att[$k], $v);
          break;
      }
    }
    return $dmCode->cloneNode();
  }

  public static function decode_issueInfo(string $name)
  {
    $doc = new DOMDocument();
    $doc->loadXML("<issueInfo/>");

    $issueInfo = $doc->documentElement;
    $arr = explode("-", $name);
    $att = ['issueNumber', 'inWork'];
    foreach ($arr as $k => $v) {
      $issueInfo->setAttribute($att[$k], $v);
    }
    return $issueInfo->cloneNode();
  }

  public static function decode_language(string $name)
  {
    $doc = new DOMDocument();
    $doc->loadXML("<language/>");

    $language = $doc->documentElement;
    $arr = explode("-", $name);
    $att = ['languageIsoCode', 'countryIsoCode'];
    foreach ($arr as $k => $v) {
      switch ($att[$k]) {
        case 'languageIsoCode':
          $language->setAttribute($att[$k], strtolower($v));
          break;
        case 'countryIsoCode':
          $language->setAttribute($att[$k], $v);
          break;
      }
    }
    return $language->cloneNode();
  }


  /**
   * @return array
   */
  public static function get_errors(bool $deleteErrors = true, string $processid = '')
  {
    if ($processid and isset(self::$errors[$processid])) {
      $e = self::$errors[$processid];
      if ($deleteErrors) {
        unset(self::$errors[$processid]);
      }
      return $e;
    } elseif ($processid and !isset(self::$errors[$processid])) {
      return null;
    } elseif (!$processid) {
      $e = self::$errors;
      if ($deleteErrors) {
        self::$errors = array();
      }
      array_walk($e, function(&$v) {
        if(is_array($v)){
          return $v = array_unique($v);
        } else {
          return $v;
        }
      });
      // array_walk($e, 
      // fn (&$v) => 
      // $v = array_unique($v));
      return $e;
    }

    // if (!$deleteErrors) {
    //   if ($processid and isset(self::$errors[$processid])) {
    //     return self::$errors[$processid];
    //   } else {
    //     return null;
    //   }
    // } elseif ($processid and isset(self::$errors[$processid])) {
    //   $errors = self::$errors[$processid];
    //   self::$errors[$processid] = array();
    //   return $errors;
    // } else {
    //   $errors = self::$errors;
    //   self::$errors = array();
    //   return $errors;
    // }
  }

  /**
   * minimum value of level is 0 (zero)
   * @return int
   */
  public static function checkLevel(\DOMElement $element, int $minimum = 0)
  {
    $tagName = $element->tagName;
    $level = $minimum;
    while (($parent = $element->parentNode)->nodeName == $tagName) {
      $element = $parent;
      $level += 1;
    }
    return ($level < 0) ? $minimum : (int) $level;
  }

  /**
   * checking index by sibling
   * @return int
   */
  public static function checkIndex(\DOMElement $element, int $minimum = 0)
  {
    $tagName = $element->tagName;
    $parent = $element->parentNode;
    $index = $minimum;
    if ($parent) {
      while ($prev_el = $element->previousElementSibling) {
        if ($prev_el->tagName == $tagName) {
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
  public static function getPrefixNum(\DOMElement $element, $minimum = 0)
  {
    $tagName = $element->tagName;
    $index = CSDB::checkIndex($element) + $minimum;
    $prefixnum = array($index);

    while (($parent = $element->parentNode)->nodeName == $tagName) {
      $index = CSDB::checkIndex($parent) + $minimum;
      array_push($prefixnum, $index);
      $element = $parent;
    }
    $prefixnum = array_reverse($prefixnum);
    return (string) join(".", $prefixnum);
  }

  public static function children(\DOMElement $element, string $nodeType = "element")
  {
    $arr = array();
    foreach ($element->childNodes as $childNodes) {
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

  public static function call($fn)
  {
    // return (CSDB::class)::tesfungsi;
    // $c = __DIR__.DIRECTORY_SEPARATOR.CSDB::class;
    // dd($c);
    $c = CSDB::class;
    // dd(__DIR__.DIRECTORY_SEPARATOR.$c);
    return "$c::$fn";
  }

  public static function resolve_issueType($issueType, string $option = '')
  {
    if (!$issueType) return '';
    // untuk mengakomodir penggunaan fungsi di XSLT
    if (is_array($issueType)) {
      $issueType = $issueType[0];
    }
    $it = $issueType->nodeValue;

    switch ($option) {
      case 'uppercase':
        return strtoupper($it);
        break;
      case 'lowercase':
        return strtolower($it);
        break;
      case 'sentencecase':
        return ucfirst($it);
        break;
    }

    switch ($it) {
      case 'new':
        return 'N';
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

  public static function get_childrenElement(\DOMElement $element, $excludeElement = '')
  {
    $arr = [];
    if (is_string($excludeElement)) {
      $excludeElement = array($excludeElement);
    }
    foreach ($element->childNodes as $childNodes) {
      if (($childNodes instanceof \DOMElement) and !in_array($childNodes->nodeName, $excludeElement)) {
        $arr[] = $childNodes;
      }
      // (($childNodes instanceof \DOMElement) and ($childNodes->nodeName != $excludeElement)) ? array_push($arr, $childNodes) : null;
    }
    return $arr;
  }

  public static function get_modelIdentCode(\DOMDocument $doc)
  {
    $dmCode = $doc->getElementsByTagName('dmCode')[0];
    $modelIdentCode = $dmCode->getAttribute('modelIdentCode');
    return $modelIdentCode;
  }

  /**
   * @return string
   */
  public static function resolve_responsiblePartnerCompany($responsiblePartnerCompany, $option = 'enterpriseName')
  {
    // untuk mengakomodir penggunaan fungsi di XSLT
    if (is_array($responsiblePartnerCompany)) {
      $responsiblePartnerCompany = $responsiblePartnerCompany[0];
    }

    if ($option == 'enterpriseName' and $$option = $responsiblePartnerCompany->firstElementChild) {
      return $$option->nodeValue;
    } elseif ($option == 'enterpriseCode' and $$option = $responsiblePartnerCompany->getAttribute($option)) {
      return $$option;
    } elseif (
      $option == 'both' and
      (($enterpriseName = $responsiblePartnerCompany->firstElementChild) or $enterpriseCode = $responsiblePartnerCompany->getAttribute('enterpriseCode'))
    ) {
      return ($enterpriseName ? $enterpriseName->nodeValue : '') . "," . ($enterpriseCode ?? '');
    }
  }

  public static function resolve($path, $filename, $csdbFunction)
  {
    if (is_array($path) and ($path[0] instanceof \DOMDocument)) {
      $doc = $path[0];
      $path = Helper::analyzeURI($doc->documentURI)['path'];
    } else {
      $doc = self::importDocument($path, $filename);
    }
    if (!$doc) return null;
    $docxpath = new DOMXPath($doc);
    switch ($csdbFunction) {
      case 'resolve_dmTitle':
        $dmTitle = $docxpath->evaluate('//identAndStatusSection/descendant::dmTitle');
        return self::resolve_dmTitle($dmTitle[0] ?? '');
        break;
      case 'resolve_pmTitle':
        $pmTitle = $docxpath->evaluate('//identAndStatusSection/descendant::pmTitle');
        return self::resolve_pmTitle($pmTitle[0] ?? '');
        break;
      case 'resolve_issueDate':
        $issueDate = $docxpath->evaluate('//identAndStatusSection/descendant::issueDate');
        return self::resolve_issueDate($issueDate[0] ?? '');
        break;
      case 'getApplicability':
        $params = array_values(array_filter(func_get_args(), (fn ($v, $i) => $i > 2), ARRAY_FILTER_USE_BOTH));
        $pmc = new PMC_PDF($path);
        $pmc->ignore_error = $params[0] ?? $pmc->ignore_error;
        $pmc->setDocument($doc);
        return $pmc->getApplicability('', 'first');
        break;
        // case 'resolve_issueType':
        // $params = array_values(array_filter(func_get_args(),(fn($v, $i) => $i > 2), ARRAY_FILTER_USE_BOTH));
        // $issueType = self::resolve_issueType();
        // break;
      default:
        break;
    }

    // dd($params);
    // dd(self::class."::$csdbFunction");
    // $res = call_user_func(self::class."::$csdbFunction", $params);
    // dd($res);
    // dd($doc, $path, $filename);
  }

  public static function resolve_issueDate($issueDate, $format = "M-d-Y")
  {
    // untuk mengakomodir penggunaan fungsi di XSLT
    if (is_array($issueDate)) {
      $issueDate = $issueDate[0];
    }
    $y = $issueDate->getAttribute("year");
    $m = $issueDate->getAttribute("month");
    $d = $issueDate->getAttribute("day");

    return date($format, mktime(0, 0, 0, $m, $d, $y));
  }

  public static function get_applic_display_text($applic, $separator = ', ')
  {
    // untuk mengakomodir penggunaan fungsi di XSLT
    if (is_array($applic)) {
      $applic = $applic[0];
    }

    $displayText = $applic->getElementsByTagName('displayText')[0];
    $simpleParas = self::get_childrenElement($displayText);
    $txt = '';
    $c = count($simpleParas);
    for ($i = 0; $i < $c; $i++) {
      $txt .= $simpleParas[$i]->nodeValue;
      if ($i != ($c - 1)) $txt .= $separator;
    }
    // dd($txt);
    return $txt;
  }

  public static function test($par)
  {
    return $par;
  }

  public static function resolve_dmTitle($dmTitle, string $child = '')
  {
    if (!$dmTitle) return '';
    // untuk mengakomodir penggunaan fungsi di XSLT
    if (is_array($dmTitle)) {
      $dmTitle = $dmTitle[0];
    }

    $techName = trim($dmTitle->firstElementChild->nodeValue);
    $infoname = trim((isset($dmTitle->firstElementChild->nextElementSibling) ? $dmTitle->firstElementChild->nextElementSibling->nodeValue : ''));
    $infoNameVariant = trim((isset($dmTitle->firstElementChild->nextElementSibling->nextElementSibling) ? $dmTitle->firstElementChild->nextElementSibling->nextElementSibling : ''));

    switch ($child) {
      case 'techName':
        return $techName;
        break;
      case 'infoname':
        return $infoname;
        break;
      case 'infoNameVariant':
        return $infoNameVariant;
        break;
      default:
        // return $techName."-".$infoname."-".$infoNameVariant;
        return $techName . ($infoname ? " - " . $infoname : '') . ($infoNameVariant ? " - " . $infoNameVariant : '');
        break;
    }
  }



  /**
   * shortPmTitle tidak bisa di dapatkan dari nextElementSibling jika dari XSL
   */
  public static function resolve_pmTitle($pmTitle, $shortPmTitle = null)
  {
    if (!$pmTitle) return '';
    // untuk mengakomodir penggunaan fungsi di XSLT
    if (is_array($pmTitle)) {
      $pmTitle = $pmTitle[0];
    }
    if (is_array($shortPmTitle)) {
      $shortPmTitle = $shortPmTitle[0];
    }
    $shortPmTitle = $shortPmTitle ?? $pmTitle->nextElementSibling;
    return $pmTitle->nodeValue . ($shortPmTitle ? " - " . $shortPmTitle->nodeValue : '');
  }

  public static function resolve_dmCode($dmCode, string $prefix = 'DMC-')
  {
    // untuk mengakomodir penggunaan fungsi di XSLT
    if (is_array($dmCode)) {
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

    $name = $prefix .
      $modelIdentCode . "-" . $systemDiffCode . "-" .
      $systemCode . "-" . $subSystemCode . $subSubSystemCode . "-" .
      $assyCode . "-" . $disassyCode . $disassyCodeVariant . "-" .
      $infoCode . $infoCodeVariant . "-" . $itemLocationCode;

    return $name;
  }

  public static function resolve_pmCode($pmCode, string $prefix = 'PMC-')
  {
    if (!$pmCode) return '';
    // untuk mengakomodir penggunaan fungsi di XSLT
    if (is_array($pmCode)) {
      $pmCode = $pmCode[0];
    }

    $modelIdentCode = $pmCode->getAttribute('modelIdentCode');
    $pmIssuer = $pmCode->getAttribute('pmIssuer');
    $pmNumber = $pmCode->getAttribute('pmNumber');
    $pmVolume = $pmCode->getAttribute('pmVolume');

    $name = $prefix .
      $modelIdentCode . "-" .
      $pmIssuer . "-" .
      $pmNumber . "-" .
      $pmVolume;

    return $name;
  }

  public static function resolve_dmlCode($dmlCode, string $prefix = 'DML-')
  {
    if (!$dmlCode) return '';
    // untuk mengakomodir penggunaan fungsi di XSLT
    if (is_array($dmlCode)) {
      $dmlCode = $dmlCode[0];
    }
    // <dmlCode modelIdentCode="MALE" senderIdent="0001Z" dmlType="p" yearOfDataIssue="2023" seqNumber="00001" />
    // <issueInfo issueNumber="000" inWork="01" />
    $modelIdentCode = $dmlCode->getAttribute('modelIdentCode');
    $senderIdent = $dmlCode->getAttribute('senderIdent');
    $dmlType = $dmlCode->getAttribute('dmlType');
    $yearOfDataIssue = $dmlCode->getAttribute('yearOfDataIssue');
    $seqNumber = $dmlCode->getAttribute('seqNumber');

    $name = $prefix .
      $modelIdentCode . "-" .
      $senderIdent . "-" .
      $dmlType . "-" .
      $yearOfDataIssue . "-" .
      $seqNumber;

    return strtoupper($name);
  }

  public static function resolve_issueInfo($issueInfo = null)
  {
    if (!$issueInfo) return '';

    if (is_array($issueInfo)) {
      $issueInfo = $issueInfo[0];
    }

    $issueNumber = $issueInfo->getAttribute('issueNumber');
    $inWork = $issueInfo->getAttribute('inWork');
    return $issueNumber . "-" . $inWork;
  }

  public static function resolve_languange($languange = null)
  {
    if (!$languange) return '';

    if (is_array($languange)) {
      $languange = $languange[0];
    }
    $languangeIsoCode = $languange->getAttribute('languageIsoCode');
    $countryIsoCode = $languange->getAttribute('countryIsoCode');
    return $languangeIsoCode . "-" . $countryIsoCode;
  }

  public static function getStatus($children = ['applic', 'qualityAssurance'], \DOMDocument $doc = null, string $absolute_path_csdbInput = '')
  {
    foreach ($children as $child) {
      switch ($child) {
        case 'applic':
          return self::getApplicability($doc, $absolute_path_csdbInput);
          break;
        case 'qualityAssurance': // return json
          $type = $doc->firstElementChild->tagName;
          $qas = $doc->getElementsByTagName("{$type}Status")[0]->getElementsByTagName($child);
          $r = [];
          foreach ($qas as $qa) {
            $applicRefId = $qa->getAttribute('applicRefId');
            $stt = $qa->firstElementChild;
            $verificationType = $stt->getAttribute('verificationType');
            $r[] = [
              'applicRefId' => $applicRefId,
              'status' => $stt->tagName,
              'verificationType' => $verificationType,
            ];
          }
          return json_encode($r);
      }
    }
  }

  public static function resolve_externalPubRefIdent($externalPubRefIdent)
  {
    if (is_array($externalPubRefIdent)) {
      $externalPubRefIdent = $externalPubRefIdent[0];
    }

    $externalPubCode = isset($externalPubRefIdent->getElementsByTagName('externalPubCode')[0]) ? 'Dummy Ext Pub Code' : null;
    $externalPubTitle = isset($externalPubRefIdent->getElementsByTagName('externalPubTitle')[0]) ? 'Dummy Ext Pub Title' : null;
    $externalPubIssueInfo = isset($externalPubRefIdent->getElementsByTagName('externalPubIssueInfo')[0]) ? 'Dummy Ext Pub Issue Info' : null;

    return $externalPubCode ? $externalPubCode . "_" . ($externalPubTitle ? $externalPubTitle . "_" . ($externalPubIssueInfo ?? ''
    ) : ''
    ) : '';
  }

  public static function resolve_dmIdent($dmIdent = null, array $idents = [], $prefix = 'DMC-', $format = '.xml')
  {

    if (empty($idents)) {
      if (is_array($dmIdent)) {
        if(empty($dmIdent)) return '';
        $dmIdent = $dmIdent[0];
      }
      $dmCode = self::resolve_dmCode($dmIdent->getElementsByTagName('dmCode')[0], $prefix);
      $issueInfo = ($if = self::resolve_issueInfo($dmIdent->getElementsByTagName('issueInfo')[0])) ? "_" . $if : '';
      $languange = ($lg = self::resolve_languange($dmIdent->getElementsByTagName('language')[0])) ? "_" . $lg : '';
    } else {
      $dmCode = $idents[0];
      $issueInfo = isset($idents[1]) ? "_" . $idents[1] : '';
      $languange = isset($idents[2]) ? "_" . $idents[2] : '';
    }

    return strtoupper($dmCode . $issueInfo . $languange) . $format;
  }

  public static function resolve_pmIdent($pmIdent = null, array $idents = [], $prefix = 'PMC-', $format = '.xml')
  {
    if (empty($idents)) {
      if (is_array($pmIdent)) {
        $pmIdent = $pmIdent[0];
      }
      $pmCode = self::resolve_pmCode($pmIdent->getElementsByTagName('pmCode')[0], $prefix);
      $issueInfo = ($if = self::resolve_issueInfo($pmIdent->getElementsByTagName('issueInfo')[0])) ? "_" . $if : '';
      $languange = ($lg = self::resolve_languange($pmIdent->getElementsByTagName('language')[0])) ? "_" . $lg : '';
    } else {
      $pmCode = $idents[0];
      $issueInfo = isset($idents[1]) ? "_" . $idents[1] : '';
      $languange = isset($idents[2]) ? "_" . $idents[2] : '';
    }

    return strtoupper($pmCode . $issueInfo . $languange) . $format;
  }

  public static function resolve_dmlIdent($dmlIdent = null, array $idents = [], $prefix = 'DML-', $format = '.xml')
  {
    if (empty($idents)) {
      if (is_array($dmlIdent)) {
        $dmlIdent = $dmlIdent[0];
      }
      $dmlCode = self::resolve_dmlCode($dmlIdent->getElementsByTagName('dmlCode')[0], $prefix);
      $issueInfo = ($if = self::resolve_issueInfo($dmlIdent->getElementsByTagName('issueInfo')[0])) ? "_" . $if : '';
    } else {
      $dmlCode = $idents[0];
      $issueInfo = isset($idents[1]) ? "_" . $idents[1] : '';
    }
    return strtoupper($dmlCode . $issueInfo) . $format;
  }

  public static $countest = 0;
  public static function getApplicability(\DOMDocument $doc, string $absolute_path_csdbInput = '')
  {
    self::$countest += 1;
    $CSDB = new self();

    $domxpath = new DOMXPath($doc);
    $dmRefIdent = $domxpath->evaluate("//identAndStatusSection/descendant::applicCrossRefTableRef/descendant::dmRefIdent")[0];
    if(!$dmRefIdent){
      return '';
    }
    // $ACTdoc = self::importDocument($absolute_path_csdbInput . DIRECTORY_SEPARATOR, self::resolve_dmIdent($dmRefIdent), null, 'dmodule');
    $ACTdoc = self::importDocument($absolute_path_csdbInput . '/', self::resolve_dmIdent($dmRefIdent), null, 'dmodule');
    if (!$ACTdoc) {
      $error = CSDB::get_errors(true, 'file_exists') ?? CSDB::get_errors();
      $error = array_map((fn($v) => is_array($v) ? ($v = join(", ", $v)) : $v), $error);
      array_unshift($error, "Error inside ".Helper::analyzeURI($doc->baseURI)['filename']);
      CSDB::setError(__FUNCTION__, implode(", ", $error));
      return false;
    }

    $CSDB->ACTdoc = $ACTdoc;

    $actdomxpath = new DOMXPath($ACTdoc);
    $dmRefIdent = $actdomxpath->evaluate("//content/applicCrossRefTable/condCrossRefTableRef/descendant::dmRefIdent")[0];
    $CCTdoc =  self::importDocument($absolute_path_csdbInput . DIRECTORY_SEPARATOR, self::resolve_dmIdent($dmRefIdent), null, 'dmodule');
    $CSDB->CCTdoc = $CCTdoc;

    // PCT tidak di gunakan untuk mendapatkan applicability, melainkan untuk filter saja
    // $dmRefIdent = $actdomxpath->evaluate("//content/applicCrossRefTable/productCrossRefTableRef/descendant::dmRefIdent")[0];
    // $PCTdoc =  self::importDocument($absolute_path_csdbInput . DIRECTORY_SEPARATOR, self::resolve_dmIdent($dmRefIdent), null, 'dmodule');
    // $CSDB->PCTdoc = $PCTdoc;

    $CSDB->applics = array();
    $applics = $domxpath->evaluate("//applic");
    $result = [];

    $resolve = function ($childApplic, $resolve_fn) use ($CSDB) {
      switch ($childApplic->tagName) {
        case 'assert':
          $assert = $childApplic;
          $test =  $CSDB->assertTest($assert);
          // dd($assert->parentNode->nodeName);
          if ($assert->parentNode->nodeName == 'evaluate') {
            return $test;
          } else {
            if ($test[array_key_first($test)]['STATUS'] == 'fail') {
              $applicPropertyIdent = array_key_first($test);
              $values = array_filter($test[$applicPropertyIdent], (fn ($v, $i) => is_numeric($i)), ARRAY_FILTER_USE_BOTH);
              $values = join(", ", $values);
              $filename = self::resolve_DocIdent($assert->ownerDocument);
              throw new Exception("Error processing applicability inside $filename. For '$applicPropertyIdent' does not contains such $values", 1);
            } else {
              unset($test[array_key_first($test)]['STATUS']);
              return $test;
            }
          }
          break;
        case 'evaluate':
          $evaluate = $childApplic;
          $children = self::get_childrenElement($evaluate);
          $results = [];
          foreach ($children as $child) {
            $results[] = $resolve_fn($child, $resolve_fn);
          }
          $andOr = $evaluate->getAttribute('andOr') ?? null;
          // buat semua kayak 'and'
          switch ($andOr) {
            case 'and':
              $res1 = $results[0];
              $res2 = $results[1];
              if (array_key_first($res1) == array_key_first($res2)) { // jika $applicPropertyIdent nya sama
                $r[array_key_first($res1)] = array_merge($res1[array_key_first($res1)], [", "], $res2[array_key_first($res2)]);
                $results = $r;
              } else {
                $results = array_merge($res1, $res2);
              }
              foreach ($results as $applicPropertyIdent => $values) {
                if ($results[$applicPropertyIdent]['STATUS'] == 'fail') {
                  $xpath = new DOMXPath($evaluate->ownerDocument);
                  $dmIdent = $xpath->evaluate("//identAndStatusSection/descendant::dmIdent")[0];
                  $dmIdent = self::resolve_dmIdent($dmIdent);

                  throw new Exception("Error processing applicability inside $dmIdent.", 1);
                }
                unset($results[$applicPropertyIdent]['STATUS']);
              }
              // dd('bbb', $results);
              break;
            case 'or':
              $res1 = $results[0];
              $res2 = $results[1];
              if ($res1[array_key_first($res1)]['STATUS'] != 'fail') {
                unset($res1[array_key_first($res1)]['STATUS']);
                $r[array_key_first($res1)] = $res1[array_key_first($res1)];
                $results = $r;
              } elseif ($res2[array_key_first($res2)]['STATUS'] != 'fail') {
                unset($res2[array_key_first($res2)]['STATUS']);
                $r[array_key_first($res2)] = $res2[array_key_first($res2)];
                $results = $r;
              } else {
                $xpath = new DOMXPath($evaluate->ownerDocument);
                $dmIdent = $xpath->evaluate("//identAndStatusSection/descendant::dmIdent")[0];
                $dmIdent = self::resolve_dmIdent($dmIdent);
                throw new Exception("Error processing applicability inside $dmIdent.", 1);
              }
              break;
          }
          return $results;
      }
    };

    $applicability = [];
    foreach ($applics as $applic) {
      $id = $applic->getAttribute('id');
      foreach (self::get_childrenElement($applic, 'displayText') as $child) {
        $result = [];
        $r = $resolve($child, $resolve);
        // dd($r,__CLASS__,__LINE__);
        foreach ($r as $applicPropertyIdent => $testedValues) {
          $result[$applicPropertyIdent] = $testedValues[0];
          unset($testedValues[0]);
          foreach ($testedValues as $conf => $val) {
            $result[$conf] = $val;
          }
          // tidak dipakai karena akan menjoin semua value element (ada 'STATUS', 'APPLICPROPERTYTYPE);
          // $result[$applicPropertyIdent] = join('',$testedValues); // setiap testedValues sudah ada separator nya
        }
      }
      ($id) ? ($applicability[$id] = $result) : $applicability[]  = $result;
    }
    return [
      'applicability' => $applicability,
      'CSDB' => $CSDB,
    ];
  }

  private function assertTest(\DOMElement $assert)
  {
    foreach ($assert->attributes as $att) {
      if (!in_array($att->nodeName, ['applicPropertyIdent', 'applicPropertyType', 'applicPropertyValues'])) {
        return false;
      }
    }

    if ($assert->firstChild instanceof \DOMText) {
      return ['text' => $assert->firstChild->nodeValue];
    }

    $applicPropertyIdent = $assert->getAttribute('applicPropertyIdent');
    $applicPropertyType = $assert->getAttribute('applicPropertyType');
    $applicPropertyValues = $assert->getAttribute('applicPropertyValues');

    // $this->applicPropertyValues = $applicPropertyValues;

    // validation CCTdoc
    if ($applicPropertyType == 'condition' and !$this->CCTdoc) {
      CSDB::setError('getApplicability', join(", ", CSDB::get_errors(true, 'file_exists')));
      return false;
    }

    // #1 getApplicPropertyValuesFromCrossRefTable
    $crossRefTable = ($applicPropertyType == 'prodattr') ? $this->ACTdoc : $this->CCTdoc;
    $crossRefTableDomXpath = new DOMXPath($crossRefTable);
    if (str_contains(($schema = $crossRefTable->firstElementChild->getAttribute('xsi:noNamespaceSchemaLocation')), 'appliccrossreftable.xsd')) {
      $query_enum = "//enumeration[parent::*/@id = '{$applicPropertyIdent}']/@applicPropertyValues";
      $valueDataType = $crossRefTableDomXpath->evaluate("//productAttribute[@id = '{$applicPropertyIdent}']");
      $valueDataType = (count($valueDataType) > 0) ? ($valueDataType[0]->getAttribute('valueDataType') ?? null) : null;
    } elseif (str_contains(($schema = $crossRefTable->firstElementChild->getAttribute('xsi:noNamespaceSchemaLocation')), 'condcrossreftable.xsd')) {
      $query_condTypeRefId = "//cond[@id = '{$applicPropertyIdent}']/@condTypeRefId";
      $condTypeRefId = $crossRefTableDomXpath->evaluate($query_condTypeRefId);
      $condTypeRefId = $condTypeRefId[0]->value;
      $query_enum = "//enumeration[parent::*/@id = '{$condTypeRefId}']/@applicPropertyValues";

      $valueDataType = $crossRefTableDomXpath->evaluate("//condType[@id = '{$condTypeRefId}']");
      $valueDataType = (count($valueDataType) > 0) ? ($valueDataType[0]->getAttribute('valueDataType') ?? null) : null;
    } else {
      return false;
    }
    $enums = $crossRefTableDomXpath->evaluate($query_enum);
    $applicPropertyValuesFromCrossRefTable = '';
    $pattern = $crossRefTableDomXpath->evaluate("//@valuePattern[parent::*/@id = '$applicPropertyIdent']");
    $pattern = (count($pattern) > 0) ? $pattern[0]->nodeValue : null;
    if (count($enums) == 0) {
      // isexistValuePattern()
      if ($pattern) {
        $propertyValue = trim($pattern);
        $propertyValue = substr_replace($propertyValue, "", 0, 1);
        $propertyValue = substr_replace($propertyValue, "", strlen($propertyValue) - 1, 1);
        $applicPropertyValuesFromCrossRefTable = $propertyValue;
      }
    } else {
      $applicPropertyValuesFromCrossRefTable = $enums[0]->value;
    }

    // #2 generateValue for Nominal and Prodcued/actual value
    $generateValue = function (string $applicPropertyValues) use ($valueDataType, $pattern) {
      $values_generated = array();
      // breakApplicPropertyValues()
      // $applicPropertyValues = "N071|N001N005`N010|N015throughN020|N020|N030~N035|N001~N005~N010";
      // $regex[0] untuk match ->N030~N035<- ->N001~N005~N010<-
      // $regex[1] untuk match ->N071<- ->N015throughN020<- ->N020<-
      // semua value yang akan di cek terhadap @valuePattern (jika @valueDataType is string) ada dalam match-group ke 1(index ke 1) atau 2 atau 3
      // jika range (tilde) maka $start = group 1; $end = group 2
      // jika singe value maka group 3
      $regex = ["([A-Za-z0-9\-\/]+)~([A-Za-z0-9\-\/]+)(?:[~`!@#$%^&*()\-_+={}\[\]\\;:'" . '",<.>\/? A-Za-z0-9]+)*', "|", "(?<![`~!@#$%^&*()-_=+{}\[\]\\;;'" . '",<.>\/? ])([A-Za-z0-9\-\/]+)(?![`~!@#$%^&*()-_=+{}\[\]\\;;' . "',<.>\/? ])"]; // https://regex101.com/r/vKhlJB/3 account ferdisaptoko@gmail.com
      $regex = "/" . implode($regex) . "/";
      preg_match_all($regex, $applicPropertyValues, $matches, PREG_SET_ORDER, 0); // matches1 = "N003~N005", matches2 = "N010~N015"
      foreach ($matches as $values) {
        // get start value for iterating
        $start = null;
        $end = null;
        $singleValue = null;
        if ($valueDataType != 'string') {
          $start = $values[1];
          $end = $values[2];
          $singleValue = (isset($values[3]) and $values[3]) ? $values[3] : null;
        } else {
          if (!empty($pattern)) { // jika mau di iterate
            preg_match_all($pattern, $values[1], $matches, PREG_SET_ORDER);
            $start = isset($matches[0][0]) ? $matches[0][1] : null;
            preg_match_all($pattern, $values[2], $matches, PREG_SET_ORDER);
            $end = isset($matches[0][0]) ? $matches[0][1] : null;
            if ((isset($values[3]) and $values[3])) {
              preg_match_all($pattern, $values[2], $matches, PREG_SET_ORDER);
              $singleValue = isset($matches[0][0]) ? $matches[0][1] : null;
            }
          }
        }
        if ($start and $end) {
          $range = range($start, $end);
          foreach ($range as $v) ($values_generated[] = $v);
        }
        if ($singleValue) {
          $values_generated[] = $singleValue;
        }
      }
      return $values_generated;
    };

    $nominalValues = $generateValue($applicPropertyValuesFromCrossRefTable);
    $producedValues = $generateValue($applicPropertyValues);

    $testedValues = array();
    if (!empty($nominalValues) and !empty($producedValues)) {
      $status = 'success';
      foreach ($producedValues as $value) {
        // if(in_array($value, $nominalValues)){
        //   $testedValues[] = $value; // berbeda dengan script awal. Yang ini, walaupun aday ang ga match antara produced dan nominal values, tidak membuat semuanya false
        // }
        $testedValues[] = $value;
        if (!in_array($value, $nominalValues)) $status = 'fail'; // jika ada yang tidak sama, maka dikasi status fail, tapi tetap masuk ke testedValue. Intinya testedValues = produced Values
      }

      if (in_array($applicPropertyIdent, ['SERIALNUMBER', 'Serialnumber', 'serialnumber', 'serialNumber', 'SerialNumber', 'SERIAL_NUMBER', 'Serial_umber', 'serial_number', 'serial_Number', 'Serial_Number'])) {
        $keepOneByOne = false; // ubah keep nya jika ingin oneByOne atau tidak
        // $keepOneByOne = true; // ubah keep nya jika ingin oneByOne atau tidak
        $oneByOne = false;
        $length = count($testedValues);
        $s = [];
        $i = 0;
        $span = ' ~ ';
        while (isset($testedValues[$i])) {
          $s[] = $testedValues[$i];
          if ($keepOneByOne and ($i < $length - 1)) $s[] = ', ';

          if (
            isset($testedValues[$i + 1]) and
            (($testedValues[$i + 1] - $testedValues[$i]) >= 1)
          ) {
            if ((count($s) > 1) and !$oneByOne) {
              array_pop($s);
              if ($keepOneByOne) $s[] = ', ';
              $oneByOne = false;
            } else {
              // $keepOneByOne ? null : ($s[] = ' through ');
              $keepOneByOne ? null : ($s[] = ' ~ ');
            }
            if (($testedValues[$i + 1] - $testedValues[$i]) >= 2) {
              if (!$keepOneByOne) $s[] = $testedValues[$i];
              if (!$keepOneByOne) $s[] = ', ';
              $oneByOne =  true;
            } else {
              $oneByOne = ($keepOneByOne) ? true : false;
            }
          }
          $i++;
        }
        foreach ($s as $k => $v) {
          if ($v == $span) {
            if (abs($s[$k + 1] - $s[$k - 1]) == 1) {
              $s[$k] = ', ';
            }
          }
        }
        if ($pattern) {
          $regex = "/.*(\(.*\)).*/"; // akan match dengan yang didalam kurungnya /N(219)/ akan match dengan 219
          preg_match_all($regex, $pattern, $structure, PREG_SET_ORDER, 0);
        }
        foreach ($s as $n => $v) {
          if (!is_string($v)) {
            $s[$n] = sprintf('%03d', $s[$n]);
            if ($pattern) {
              if ($structure) {
                $newValue = str_replace($structure[0][1], $s[$n], $structure[0][0]); // $newValue = "/N001/"
                $newValue = trim($newValue);
                $newValue = substr_replace($newValue, "", 0, 1); // delete "/" di depan
                $newValue = substr_replace($newValue, "", strlen($newValue) - 1, 1); // delete "/" dibelakang
                $s[$n] = $newValue;
              }
            }
          }
        }
        $testedValues = [];
        $s = (join("", $s));
        $testedValues[] = $s;
      } else {
        $r = join(", ", $testedValues);
        $testedValues = [];
        $testedValues[] = $r;
      }
      $testedValues['STATUS'] = $status;
      $testedValues['%APPLICPROPERTYTYPE'] = $applicPropertyType;
    }
    $ret = array($applicPropertyIdent => $testedValues);
    return $ret;
  }

  public static function document($path, string $filename)
  {
    if(is_array($path)){
      $path = $path[0];
    }
    if($path instanceof \DOMDocument){
      $path = Helper::analyzeURI($path->baseURI)['path'];
    }
    if(substr($filename, 0, 3) == 'ICN'){
      $filename = self::detectIMF($path, $filename);
    } 
    CSDB::$processid = 'ignore';
    $dom = CSDB::importDocument($path."/", $filename);
    $errors = CSDB::get_errors(true, 'ignore');

    if($dom){
      return $dom;
    } else {
      return new DOMDocument();
    }
  }

  /**
   * @return string filename IMF
   */
  public static function detectIMF($path, $icnFilename)
  {
    $icnFilename_withoutFormat = preg_replace("/.\w+$/",'',$icnFilename);
    $icnFilename_array = explode("-",$icnFilename_withoutFormat);
    
    $imfFilename_array = $icnFilename_array;
    $imfFilename_array[0] = 'IMF';

    // mencari dengan issueNumber dan/atau inWork terbesar
    $searchImf = function($path) use($imfFilename_array) {
      $filename = join("-", $imfFilename_array);

      $dir = array_diff(scandir($path));
      $collection = [];
      foreach($dir as $file){
        if(str_contains($file, $filename)){
          $collection[] = $file;
        }
      }
      $c = array_map(function($v){
        $v = preg_replace("/IMF-[\w-]+_/", '',$v);
        $v = preg_replace("/.xml/", '',$v);
        $v = explode("-",$v);
        return $v;
      }, $collection);

      if(empty($c)){
        return '';
      }

      $in = array_map((fn($v) => (int)($v[0])), $c);
      $iw = array_map((fn($v) => (int)($v[1])), $c);

      $in_max = str_pad(max($in), 3, '0', STR_PAD_LEFT);
      $iw_max = str_pad(max($iw), 3, '0', STR_PAD_LEFT);

      $filterBy = array_filter($c, (fn($v) => $v[0] == $in_max));
      if(count($filterBy) > 1){
        $filterBy = array_filter($c, (fn($v) => ($v[0] == $in_max AND $v[1] == $iw_max)));
      }
      $issueInfo = join("-", $filterBy[0]);
      $filename .= "_".$issueInfo.".xml";
      return $filename;
    };
    $filename = $searchImf($path);
    return $filename;
  }

  /**
   * @return string infoEntityIdent eg: ICN...-01.jpeg,hot-001 atau (tanpa hot-001) eg: ICN...-01.jpeg
   * @return Array ['ICN....-01.jpeg', 'hot-001'];
   * fungsi ini lebih diperuntukkan untuk hot
   * example: input $id = fig-001-gra-001-hot-001 (menggunakan hotspot dari IMF) 
   * pada data module: 
   * <figure id="fig-001">
   *    <graphic id="fig-001-gra-001" infoEntityIdent="..."/>
   * </figure> 
   * <internalRef internalRefTargetType="irtt51" internalRefId="fig-001-gra-001-hot-001">tes hotspot</internalRef>
   */
  public static function getEntityIdentFromId($doc, string $id, $return = 'string')
  {
    if(is_array($doc)){
      $doc = $doc[0];
    }
    $domXpath = new \DOMXPath($doc);
    $res = $domXpath->evaluate("//*[@id = '{$id}']");
    // evaluasi id secara langsung, return
    if($res->length > 0){
      $infoEntityIdent = $res[0]->getAttribute('infoEntityIdent');
      return $return == 'string' ? $infoEntityIdent : [$infoEntityIdent, ''];
    } 
    else {
      $id_array = explode('-', $id); //ex: [gra,001,hot,001] 

      // jika ganjil maka return ''. Harusnya genap karena ada dash pada setiap id fig-001-gra-001
      if(($length_id_array = count($id_array)) % 2){ 
        return '';
      }

      // jika > 2 artinya levelled internalRefid="fig-001-gra-001" (cari graphic 1 yang parentnya fig-001);
      elseif($length_id_array > 2){
        $descendant_id = [$id_array[$length_id_array-2], $id_array[$length_id_array-1]];
        unset($id_array[$length_id_array-2]);
        unset($id_array[$length_id_array-1]);

        $try_id = join('-', $id_array);
        $res = $domXpath->evaluate("//*[@id = '{$try_id}']");        
        if($res->length > 0){
          $infoEntityIdent = $res[0]->getAttribute('infoEntityIdent');
          return $return == 'string' ? ($infoEntityIdent . ','. join('-',$descendant_id)) : [$infoEntityIdent, join('-',$descendant_id)];
        }
      }

      /**
       * jika cara penamaan id pada <graphic> tidak levelled maka xpath = "//descendant::*[@id = 'fig-001'/descendant::*[@id = 'gra-001']]"
       * <graphic id="gra-001" infoEntityIdent="..."/>
      */
      $xpath = '/';
      array_filter($id_array, function($v,$k) use($id_array, &$xpath){
        if($k % 2){
          $id_array[$k-1] .= "-{$v}";
          $xpath .= "/descendant::*[@id = '{$id_array[$k-1]}']";
          unset($id_array[$k]);
        }
      },ARRAY_FILTER_USE_BOTH); // ex: $id_array = [gra-001,hot-001] 
      $res = $domXpath->evaluate($xpath);
      if($res->length <= 0){
        return '';
      }
      $infoEntityIdent = $res[0]->getAttribute('infoEntityIdent');

      // jika ada $descendant_id artinya levelled id
      if($return == 'string'){
        return  isset($descendant_id) ? ($infoEntityIdent . ','. join('-',$descendant_id)) : ($infoEntityIdent);
      } else {
        return  isset($descendant_id) ? [$infoEntityIdent, $descendant_id] : [$infoEntityIdent, ''];
      }
    }
  }
}
