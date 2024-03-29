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
  public static bool $preserveWhiteSpace = false;
  public static bool $formatOutput = false;

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

  /**
   * $option = ('file'|anything)
   */
  public static function getSchemaUsed($doc, $option = 'file')
  {
    if (!$doc or !($doc instanceof \DOMDocument)) return '';
    // untuk mengakomodir penggunaan fungsi di XSLT
    if (is_array($doc)) {
      $doc = $doc[0];
    }
    // kalau document di loadXML, tidak bisa pakai getAttributeNS.
    // $schema = $doc->documentElement->getAttributeNS('http://www.w3.org/2001/XMLSchema-instance', "noNamespaceSchemaLocation");
    $schema = $doc->documentElement->getAttribute("xsi:noNamespaceSchemaLocation");
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

  /**
   * $return == (number,integer, or text (default else));
   */
  public static function resolve_securityClassification($doc, $return = 'text')
  {
    if(!$doc) return '';
    if(is_array(($doc))){
      $doc = $doc[0];
    }
    if($doc instanceof \DOMDocument){
      // $initial = $doc->documentElement->tagName;
      // if ($initial == 'dmodule') $initial = 'dm';
      $domXpath = new \DOMXpath($doc);
      // $sc = $domXpath->evaluate("string(//{$initial}Status/security/@securityClassification)");
      $sc = $domXpath->evaluate("string(//identAndStatusSection/descendant::security/@securityClassification)");
    }
    elseif($doc instanceof \DOMElement){
      $sc = $doc->getAttribute('securityClassification');
    }

    if ($return == 'number') {
      return $sc;
    } elseif ($return == 'integer') {
      return (int) $sc;
    } else {
      $a = [
        '01' => 'Unclassified',
        '02' => 'Restricted',
        '03' => 'Confidential',
        '04' => 'Secret',
        '05' => 'Top Secret',
      ];
      return $a[$sc] ?? '';
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
    } elseif ($docType == 'dml') {
      $docIdent = $doc->getElementsByTagName('dmlIdent')[0];
      $docIdent = CSDB::resolve_dmlIdent($docIdent);
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

  public static function resolve_imfIdent($imfIdent = null, array $idents = null, $prefix = 'PMC-', $format = '.xml')
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

    return strtoupper($prefix . $imfCode . $issueInfo) . $format;
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

    $message = preg_replace("/file:\S+(?=\/|\\\\)/m", '', $error->message);
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
    $absolute_path = !empty($absolute_path) ? $absolute_path . DIRECTORY_SEPARATOR : '';
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
        $obj->preserveWhiteSpace = self::$preserveWhiteSpace;
        $obj->formatOutput = self::$formatOutput;
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
        foreach ($errors as $e) {
          self::$processid ? (self::$errors[self::$processid][] = self::display_xml_error($e)) : (self::$errors[] = self::display_xml_error($e));
        }
        if (!empty($errors)) {
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

  /**
   * depreciated. Akan diganti dengan Helper::decode_ident
   * @return \DOMElement $dmCode
   */
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

  /**
   * depreciated. Akan diganti dengan Helper::decode_ident
   * @return \DOMElement $issueInfo
   */
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

  /**
   * depreciated. Akan diganti dengan Helper::decode_ident
   * @return \DOMElement $language
   */
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
      array_walk($e, function (&$v) {
        if (is_array($v)) {
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
          // dd($$childNodes, __CLASS__, __LINE__);
          array_push($arr, $childNodes->textContent);
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


  /**
   * depreciated. Diganti oleh Helper::children()
   */
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
    if (empty($issueDate)) return '';
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

  // belum support karena element <externalPubTitle> mengandung child DOMText, dan DOMElement (<indexFlag>, <subScript>, dll);
  public static function resolve_externalPubRefTitle()
  {
    return '';
  }

  public static function resolve_externalPubIssueDate($issueDate, $format = "M-d-Y")
  {
    if (empty($issueDate)) return '';
    if (is_array($issueDate)) {
      $issueDate = $issueDate[0];
    }
    $issueDate_text = $issueDate->textContent;
    if (!$issueDate_text) return self::resolve_issueDate($issueDate, $format);
    return $issueDate_text;
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

    if (strtolower($dmlType) == 's') ($prefix = 'CSL-');
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

  public static function resolve_dmIdent($dmIdent = null, array $idents = null, $prefix = 'DMC-', $format = '.xml')
  {
    if (empty($idents)) {
      if (is_array($dmIdent)) {
        if (empty($dmIdent)) return '';
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

  public static function resolve_pmIdent($pmIdent = null, array $idents = null, $prefix = 'PMC-', $format = '.xml')
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

  public static function resolve_dmlIdent($dmlIdent = null, array $idents = null, $prefix = 'DML-', $format = '.xml')
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

  /**
   * untuk sementara $ident diabaikan. Ini dijadikan parameter agar sama dengan fungsi lainnya. Fungsi lainnya pun tidak begitu berguna $ident. Tapi karna terpakai di xsl, jadi belum bisa di hapus parameter ini 
   * @return string
   */
  public static function resolve_infoEntityIdent($infoEntityIdent = null, array $ident = null, $prefix = '', $format = '')
  {
    if (is_array($infoEntityIdent)) {
      $infoEntityIdent = $infoEntityIdent[0];
    }
    $ident = $infoEntityIdent->getAttribute('infoEntityRefIdent');
    if ($prefix) {
      $ident = str_replace('ICN-', $prefix, $ident);
    }
    if ($format) {
      $ident = preg_replace("/\.\w+$/", $format, $ident);
    }
    return $ident;
  }

  /**
   * resolving applic element
   * @param DOMElement $doc berupa applic
   * @param int $useDisplayText 0,1,2. jika satu itu string HARUS pakai display Text. Jika dua itu optional. Artinya jika displayText tidak ada, akan mengambil assert untuk test
   * @return string
   */
  public static function resolve_applic(mixed $applic, bool $keppOneByOne = false, bool $useDisplayName = true ,int $useDisplayText = 2)
  {
    if (empty($applic)) return '';
    if (is_array($applic)) {
      $applic = $applic[0];
    }

    $useDisplayText = 0;
    // untuk displayText ($useDisplayText 1 or 2)
    if($useDisplayText){
      if($applic->firstElementChild->tagName === 'displayText'){
        $displayText = '';
        foreach($applic->firstElementChild->childNodes as $simplePara){
          $displayText .= ', ' . $simplePara->textContent;
        }
        return rtrim($displayText, ', ');
      }
      if($useDisplayText === 1){
        return '';
      }
    }

    // untuk assert
    $arrayify = Helper::arrayify_applic($applic, $keppOneByOne, $useDisplayName);
    unset($arrayify['displayText']);
    $arrayify[array_key_first($arrayify)]['text'] = ltrim($arrayify[array_key_first($arrayify)]['text'], "(");
    $arrayify[array_key_first($arrayify)]['text'] = rtrim($arrayify[array_key_first($arrayify)]['text'], ")");
    $text = $arrayify[array_key_first($arrayify)]['text'];
    // $message = array_filter($arrayify[array_key_first($arrayify)]['children'], fn($c) => $c['%MESSAGE'] ?? false);
    // output = $message = [
    //   "%MESSAGE" => "ERROR: 'serialnumber' only contains N001-N004, N006-N010, N012-N015 and does not contains such N011, N016-N020",
    //   "text" => "",
    //   "%STATUS" => "fail",
    //   "%APPLICPROPERTYTYPE" => "prodattr",
    //   "%APPLICPROPERTYIDENT" => "serialnumber",
    //   "%APPLICPROPERTYVALUES" => "N001~N004|N006~N020",
    // ];
    // output harusnya nanti bisa dibuat/ditambah ke static error CSDB;
    return $text; // return string "text". eg.: $arrayify = ["evaluate" => ["text" => string, 'andOr' => String, 'children' => array]];
  }

  /**
   * ini untuk mendapatkan applicability berdasarkan ID pada applic
   */
  public static function resolve_applicRefId()
  {
  }


  /**
   * depreciated
   */
  public static function getApplicability(\DOMDocument $doc, $absolute_path_csdbInput = '')
  {

    if (!$absolute_path_csdbInput) {
      $analyzeURI = Helper::analyzeURI($doc->baseURI);
      $absolute_path_csdbInput = $analyzeURI['path'];
    }
    $CSDB = new self();

    $domxpath = new DOMXPath($doc);
    $dmRefIdent = $domxpath->evaluate("//identAndStatusSection/descendant::applicCrossRefTableRef/descendant::dmRefIdent")[0];
    if (!$dmRefIdent) {
      return '';
    }
    $ACTdoc = self::importDocument($absolute_path_csdbInput, self::resolve_dmIdent($dmRefIdent), null, 'dmodule');
    // if (!$ACTdoc) {
    //   CSDB::get_errors(true,'file_exists'); // menghapus error karena mau dicoba import lagi dengan path yang berbeda
    //   $absolute_path_csdbInput = preg_replace("/(\/|\\\\)__[\w\d\.-]+$/",'',$absolute_path_csdbInput); // untuk menghilangkan /__unsued
    //   $ACTdoc = self::importDocument($absolute_path_csdbInput, self::resolve_dmIdent($dmRefIdent), null, 'dmodule');
    // }
    if (!$ACTdoc) {
      $error = CSDB::get_errors(true, 'file_exists') ?? CSDB::get_errors();
      $error = array_map((fn ($v) => is_array($v) ? ($v = join(", ", $v)) : $v), $error);
      array_unshift($error, "Error inside " . Helper::analyzeURI($doc->baseURI)['filename']);
      CSDB::setError(__FUNCTION__, implode(", ", $error)); // menghapus error karena mau dicoba import lagi dengan path yang berbeda
      return false;
    }

    $CSDB->ACTdoc = $ACTdoc;

    $actdomxpath = new DOMXPath($ACTdoc);
    $dmRefIdent = $actdomxpath->evaluate("//content/applicCrossRefTable/condCrossRefTableRef/descendant::dmRefIdent")[0];
    $CCTdoc =  self::importDocument($absolute_path_csdbInput, self::resolve_dmIdent($dmRefIdent), null, 'dmodule');
    // if (!$CCTdoc) {
    //   CSDB::get_errors(true,'file_exists');
    //   $absolute_path_csdbInput = preg_replace("/(\/|\\\\)__[\w\d\.-]+$/",'',$absolute_path_csdbInput); // untuk menghilangkan /__unsued
    //   $$CCTdoc = self::importDocument($absolute_path_csdbInput, self::resolve_dmIdent($dmRefIdent), null, 'dmodule');
    // }
    $CSDB->CCTdoc = $CCTdoc;

    // PCT tidak di gunakan untuk mendapatkan applicability, melainkan untuk filter saja
    // $dmRefIdent = $actdomxpath->evaluate("//content/applicCrossRefTable/productCrossRefTableRef/descendant::dmRefIdent")[0];
    // $PCTdoc =  self::importDocument($absolute_path_csdbInput . DIRECTORY_SEPARATOR, self::resolve_dmIdent($dmRefIdent), null, 'dmodule');
    // $CSDB->PCTdoc = $PCTdoc;

    $CSDB->applics = array();
    $applics = $domxpath->evaluate("//applic");
    $result = [];

    // $output = [
    //   'aircraft' => [
    //     '0' => 'MALE, Amphibi',
    //     '&APPLICPROPERTYTYPE' => 'prodattr'
    //   ]
    // ]
    $resolve = function ($childApplic, $resolve_fn) use ($CSDB) {
      // dd($childApplic->parentNode->getAttribute('id'));
      switch ($childApplic->tagName) {
        case 'displayText':
          $simpleParas = $childApplic->childNodes;
          $str = [];
          foreach ($simpleParas as $simplePara) {
            $str[] = $simplePara->nodeValue;
          }
          // sengaja return index '0' agar disesuaikan outputnya dengan yang child lain (eg: assert)
          return [
            '%DISPLAYTEXT' => [
              '0' => join(", ", $str),
              '%STATUS' => 'success'
            ],
          ];
          break;
        case 'assert':
          $assert = $childApplic;
          $test =  $CSDB->assertTest($assert);
          // dd($assert->parentNode->nodeName);
          if ($assert->parentNode->nodeName == 'evaluate') {
            return $test;
          } else {
            if ($test[array_key_first($test)]['%STATUS'] == 'fail') {
              $applicPropertyIdent = array_key_first($test);
              $values = array_filter($test[$applicPropertyIdent], (fn ($v, $i) => is_numeric($i)), ARRAY_FILTER_USE_BOTH);
              $values = join(", ", $values);
              // $filename = self::resolve_DocIdent($assert->ownerDocument);
              $test[array_key_first($test)]['%MESSAGE'] = "ERROR: For '$applicPropertyIdent' does not contains such $values";
              // dd($test);
              // return $test;
              // self::setError(__FUNCTION__,"Error processing applicability inside $filename. For '$applicPropertyIdent' does not contains such $values");
              // throw new Exception("Error processing applicability inside $filename. For '$applicPropertyIdent' does not contains such $values", 1);
              // dd($test);
              // return [];
            }
            // else {
            // unset($test[array_key_first($test)]['%STATUS']);
            // return $test;
            // }
            return $test;
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
              // dd($r);
              foreach ($results as $applicPropertyIdent => $values) {
                if ($results[$applicPropertyIdent]['%STATUS'] == 'fail') {
                  $xpath = new DOMXPath($evaluate->ownerDocument);
                  $dmIdent = $xpath->evaluate("//identAndStatusSection/descendant::dmIdent")[0];
                  $dmIdent = self::resolve_dmIdent($dmIdent);

                  throw new Exception("Error processing applicability inside $dmIdent.", 1);
                }
                unset($results[$applicPropertyIdent]['%STATUS']);
              }
              // dd('bbb', $results);
              break;
            case 'or':
              $res1 = $results[0];
              $res2 = $results[1];
              if ($res1[array_key_first($res1)]['%STATUS'] != 'fail') {
                unset($res1[array_key_first($res1)]['%STATUS']);
                $r[array_key_first($res1)] = $res1[array_key_first($res1)];
                $results = $r;
              } elseif ($res2[array_key_first($res2)]['%STATUS'] != 'fail') {
                unset($res2[array_key_first($res2)]['%STATUS']);
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
      // $childApplic = self::get_childrenElement($applic, 'displayText');
      $childApplic = self::get_childrenElement($applic);
      $result = [];
      foreach ($childApplic as $child) {
        $r = $resolve($child, $resolve);
        // dump($r);
        foreach ($r as $applicPropertyIdent => $testedValues) {
          $result[$applicPropertyIdent] = $testedValues[0];
          unset($testedValues[0]);
          foreach ($testedValues as $conf => $val) {
            $result[$conf] = $val;
          }
          // tidak dipakai karena akan menjoin semua value element (ada '%STATUS', 'APPLICPROPERTYTYPE);
          // $result[$applicPropertyIdent] = join('',$testedValues); // setiap testedValues sudah ada separator nya
        }
      }
      // dd($result);
      ($id) ? ($applicability[$id] = $result) : $applicability[]  = $result;
    }
    return [
      'applicability' => $applicability,
      'CSDB' => $CSDB,
    ];
  }

  /**
   * DEPRECIATED. Diganti oleh Helper@test_assert
   * assert harus ada attribute 'applicPropertyIdent', 'applicPropertyType', dan 'applicPropertyValues'
   */
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
      $testedValues['%STATUS'] = $status;
      $testedValues['%APPLICPROPERTYTYPE'] = $applicPropertyType;
    }
    $ret = array($applicPropertyIdent => $testedValues);
    return $ret;
  }

  public static function document($path, string $filename)
  {
    if (is_array($path)) {
      $path = $path[0];
    }
    if ($path instanceof \DOMDocument) {
      $path = Helper::analyzeURI($path->baseURI)['path'];
    }
    if (substr($filename, 0, 3) == 'ICN') {
      $filename = self::detectIMF($path, $filename);
    }
    CSDB::$processid = 'ignore';
    $dom = CSDB::importDocument($path . "/", $filename);
    $errors = CSDB::get_errors(true, 'ignore');

    if ($dom) {
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
    $icnFilename_withoutFormat = preg_replace("/.\w+$/", '', $icnFilename);
    $icnFilename_array = explode("-", $icnFilename_withoutFormat);

    $imfFilename_array = $icnFilename_array;
    $imfFilename_array[0] = 'IMF';

    // mencari dengan issueNumber dan/atau inWork terbesar
    $searchImf = function ($path) use ($imfFilename_array) {
      $filename = join("-", $imfFilename_array);

      $dir = array_diff(scandir($path));
      $collection = [];
      foreach ($dir as $file) {
        if (str_contains($file, $filename)) {
          $collection[] = $file;
        }
      }
      $c = array_map(function ($v) {
        $v = preg_replace("/IMF-[\w-]+_/", '', $v);
        $v = preg_replace("/.xml/", '', $v);
        $v = explode("-", $v);
        return $v;
      }, $collection);

      if (empty($c)) {
        return '';
      }

      $in = array_map((fn ($v) => (int)($v[0])), $c);
      $iw = array_map((fn ($v) => (int)($v[1])), $c);

      $in_max = str_pad(max($in), 3, '0', STR_PAD_LEFT);
      $iw_max = str_pad(max($iw), 2, '0', STR_PAD_LEFT);

      $filterBy = array_filter($c, (fn ($v) => $v[0] == $in_max));
      if (count($filterBy) > 1) {
        $filterBy = array_filter($c, (fn ($v) => ($v[0] == $in_max and $v[1] == $iw_max)));
      }
      $issueInfo = join("-", $filterBy[0]);
      $filename .= "_" . $issueInfo . ".xml";
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
    if (is_array($doc)) {
      $doc = $doc[0];
    }
    $domXpath = new \DOMXPath($doc);
    $res = $domXpath->evaluate("//*[@id = '{$id}']");
    // evaluasi id secara langsung, return
    if ($res->length > 0) {
      $infoEntityIdent = $res[0]->getAttribute('infoEntityIdent');
      return $return == 'string' ? $infoEntityIdent : [$infoEntityIdent, ''];
    } else {
      $id_array = explode('-', $id); //ex: [gra,001,hot,001] 

      // jika ganjil maka return ''. Harusnya genap karena ada dash pada setiap id fig-001-gra-001
      if (($length_id_array = count($id_array)) % 2) {
        return '';
      }

      // jika > 2 artinya levelled internalRefid="fig-001-gra-001" (cari graphic 1 yang parentnya fig-001);
      elseif ($length_id_array > 2) {
        $descendant_id = [$id_array[$length_id_array - 2], $id_array[$length_id_array - 1]];
        unset($id_array[$length_id_array - 2]);
        unset($id_array[$length_id_array - 1]);

        $try_id = join('-', $id_array);
        $res = $domXpath->evaluate("//*[@id = '{$try_id}']");
        if ($res->length > 0) {
          $infoEntityIdent = $res[0]->getAttribute('infoEntityIdent');
          return $return == 'string' ? ($infoEntityIdent . ',' . join('-', $descendant_id)) : [$infoEntityIdent, join('-', $descendant_id)];
        }
      }

      /**
       * jika cara penamaan id pada <graphic> tidak levelled maka xpath = "//descendant::*[@id = 'fig-001'/descendant::*[@id = 'gra-001']]"
       * <graphic id="gra-001" infoEntityIdent="..."/>
       */
      $xpath = '/';
      array_filter($id_array, function ($v, $k) use ($id_array, &$xpath) {
        if ($k % 2) {
          $id_array[$k - 1] .= "-{$v}";
          $xpath .= "/descendant::*[@id = '{$id_array[$k - 1]}']";
          unset($id_array[$k]);
        }
      }, ARRAY_FILTER_USE_BOTH); // ex: $id_array = [gra-001,hot-001] 
      $res = $domXpath->evaluate($xpath);
      if ($res->length <= 0) {
        return '';
      }
      $infoEntityIdent = $res[0]->getAttribute('infoEntityIdent');

      // jika ada $descendant_id artinya levelled id
      if ($return == 'string') {
        return  isset($descendant_id) ? ($infoEntityIdent . ',' . join('-', $descendant_id)) : ($infoEntityIdent);
      } else {
        return  isset($descendant_id) ? [$infoEntityIdent, $descendant_id] : [$infoEntityIdent, ''];
      }
    }
  }

  /**
   * belum mengakomodir element <answer>
   * @param string $name adalah filename sebuah csdb object
   * @return array contains all dmlEntry or array contain decode dmlEntry result
   */
  public static function identifyDmlEntries(\DOMDocument $dom, string $name = '')
  {
    $dmlEntries = $dom->getElementsByTagName('dmlEntry');
    $entries = [];
    foreach ($dmlEntries as $position => $dmlEntry) {
      $dmlEntryType = $dmlEntry->getAttribute('dmlEntryType');
      $issueType = $dmlEntry->getAttribute('issueType');

      $initial = str_replace("Ref", '', $dmlEntry->firstElementChild->tagName);
      $code = call_user_func_array(self::class . "::resolve_{$initial}Ident", [$dmlEntry->firstElementChild]);
      if ($name and (str_contains($name, $code) or str_contains($code, $name))) $found = true; // untuk ngecek $name, extension filename tidak dipermasalahkan karena dicheck dua arah. Jika filename ICN-...jpeg dicheck terhadap ICN-...png, $found = false;
      preg_match('/\.\w+$/', $code, $matches);
      $code_extension = $matches[0] ?? '';
      $code = str_replace($code_extension, '', $code);

      $security = $dmlEntry->getElementsByTagName('security')[0];
      $securityClassification = ($security ? $security->getAttribute('securityClassification') : '');
      $commercialClassification = ($security ? $security->getAttribute('commercialClassification') : '');
      $caveat = ($security ? $security->getAttribute('caveat') : '');
      $derivativeClassificationRefId = ($security ? $security->getAttribute('derivativeClassificationRefId') : '');

      $responsiblePartnerCompany = $dmlEntry->getElementsByTagName('responsiblePartnerCompany')[0];
      $enterpriseCode = $responsiblePartnerCompany->getAttribute('enterpriseCode');
      $enterpriseName = $responsiblePartnerCompany->firstElementChild->nodeValue;

      $remarks = $dmlEntry->getElementsByTagName('remarks')[0];
      if ($remarks) {
        $simpleParas = $remarks->childNodes;
        $remark = [];
        foreach ($simpleParas as $simplePara) {
          $remark[] = $simplePara->nodeValue;
        }
        $remark = join(" ", $remark);
      }

      $ret = [
        'code' => $code,
        'extension' => $code_extension,
        'position' => $position,
        // 'objects' => $objects,
        'dmlEntryType' => $dmlEntryType,
        'issueType' => $issueType,
        'security' => [
          'securityClassification' => $securityClassification,
          'commercialClassification' => $commercialClassification,
          'caveat' => $caveat,
          'derivativeClassificationRefId' => $derivativeClassificationRefId,
        ],
        'responsiblePartnerCompany' => [
          'enterpriseCode' => $enterpriseCode,
          'enterpriseName' => $enterpriseName,
        ],
        'remark' => $remark ?? ''
      ];

      if (isset($found) and $found) return $ret;
      $entries[] = $ret;
    }
    return $entries;
  }

  public static function commit(\DOMDocument $dom)
  {
    $validated = self::validateRootname($dom);
    if (!$validated) return false; // error sudah di set di function @validateRootname

    $domxpath = new \DOMXPath($dom);
    $issueInfo = $domxpath->evaluate("//identAndStatusSection/{$validated[3]}Address/{$validated[3]}Ident/issueInfo")[0];
    $inWork = (int)$issueInfo->getAttribute('inWork');
    if ($inWork == 0) {
      CSDB::setError('commit', "{$validated[1]} cannot be commited due to the current inWork is '00'.");
      return false;
    }
    if ($inWork == 99) ($inWork = 'AA');
    else ($inWork++);
    $inWork = str_pad($inWork, 2, '0', STR_PAD_LEFT);
    $issueInfo->setAttribute('inWork', $inWork);
    $dom->saveXML();
    return $dom;
  }

  /**
   * ICN yang disupport berdasarkan penamaan cagecode
   * prefix-cagecode-uniqueIdentifier-issueNumber-sc
   * $attribute adalah 'uniqueIdentifier' atau 'issueNumber'. Jika '', maka latestFile
   * @return string
   */
  public static function getLatestICNFile($path, $cageCode, $attributeStop = '')
  {
    $prefix = 'ICN';
    $dir = array_filter(scandir(storage_path("csdb")), fn ($v) => substr($v, 0, 3) == 'ICN');

    // uniqueIdentifier terbesar
    $uniqueIdentifier = array_map((fn ($v) => $v = explode("-", $v)[2]), $dir);
    uasort($uniqueIdentifier, (fn ($a, $b) => ($a == $b ? 0 : (($a < $b) ? 1 : -1)))); // descendant () value terbesar akan di index#0;
    if (empty($uniqueIdentifier)) return '';
    $uniqueIdentifier = $uniqueIdentifier[array_key_first($uniqueIdentifier)];
    $dir = array_filter($dir, fn ($v) => str_contains($v, "{$prefix}-{$cageCode}-{$uniqueIdentifier}"));
    uasort($dir, function ($a, $b) {
      $a = preg_replace("/\.\w+$/", '', $a);
      $b = preg_replace("/\.\w+$/", '', $b); // untuk menghilangkan format (extension) file agar tidak mempengaruhi sortingan
      if ($a == $b) return 0;
      return ($a < $b) ? -1 : 1;
    }); // descendant () value terbesar akan di index#0;
    if ($attributeStop == 'uniqueIdentifier') {
      return $dir[array_key_first($dir)];
    }

    // issueNumber terbesar
    $issueNumber = array_map((fn ($v) => $v = explode("-", $v)[3]), $dir);
    uasort($issueNumber, (fn ($a, $b) => ($a == $b ? 0 : (($a < $b) ? 1 : -1)))); // descendant () value terbesar akan di index#0;
    $issueNumber = $issueNumber[array_key_first($issueNumber)];
    $dir = array_filter($dir, fn ($v) => str_contains($v, "{$prefix}-{$cageCode}-{$uniqueIdentifier}-{$issueNumber}"));
    uasort($dir, function ($a, $b) {
      $a = preg_replace("/-[0-9]{2}\.\w+$/", '', $a);
      $b = preg_replace("/-[0-9]{2}\.\w+$/", '', $b); // untuk menghilangkan sc dan format (extension) file agar tidak mempengaruhi sortingan
      if ($a == $b) return 0;
      return ($a < $b) ? -1 : 1;
    }); // descendant () value terbesar akan di index#0;
    if ($attributeStop == 'issueNumber') {
      return $dir[array_key_first($dir)];
    }

    return $dir[array_key_first($dir)];
  }
}
