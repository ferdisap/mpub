<?php

namespace Ptdi\Mpub\Main;

class CSDBStatic
{
  protected static string $PDF_MasterName = '';

  public static function get_PDF_MasterName()
  {
    return self::$PDF_MasterName;
  }

  public static function set_PDF_MasterName(string $text)
  {
    self::$PDF_MasterName = $text;
  }

  
  /**
   * [
   *  'id-000' => [ 
   *    'text' => 'lorem ipsum',
   *    'parent' => '',
   *  ],
   *  'id-001' => [
   *    'text' => 'lorem ipsum 2',
   *    'parent' => 'id-001'
   *  ]
   * ]
   */
  protected static array $bookmarks = [];

  public static function fillBookmark(string $destination, string $text, string $parent = '')
  {
   self::$bookmarks[$destination] = [
    'text' => $text,
    'parent' => $parent,
   ];
  }

  /**
   * @return \DOMDocument
   */
  public static function transformBookmark_to_xml()
  {
    // dump(self::$bookmarks);
    if(empty(self::$bookmarks)) return '';
    $dom = new \DOMDocument;
    $bookmarkTree_el = $dom->createElementNS('http://www.w3.org/1999/XSL/Format', 'bookmark-tree');
    $dom->appendChild($bookmarkTree_el);

    $randomNS = hash('md2',rand(0,10000));
    $randomNS = "aaa" . substr($randomNS, 0,10);
    // $randomNS = 'randomNS123';
    // $randomNS = 'c8ed21db4f';
    // dd('aaa', $randomNS);
    
    while(!empty(self::$bookmarks)){
      $keyfirst = array_key_first(self::$bookmarks);
      
      $parent = self::$bookmarks[$keyfirst]['parent'];

      $bookmark_el = $dom->createElementNS('http://www.w3.org/1999/XSL/Format', 'bookmark');
      $bookmark_el->setAttributeNS("$randomNS", "$randomNS:id", $keyfirst);
      $bookmarkTitle_el = $dom->createElementNS('http://www.w3.org/1999/XSL/Format', 'bookmark-title');
      $bookmark_el->setAttribute('internal-destination', $keyfirst);
      $bookmarkTitle_el->textContent = self::$bookmarks[$keyfirst]['text'];

      $bookmark_el->appendChild($bookmarkTitle_el);
      $bookmarkTree_el->appendChild($bookmark_el);
      
      if($parent){
        $domxpath = new \DOMXpath($dom);
        $domxpath->registerNamespace('fo','http://www.w3.org/1999/XSL/Format');
        $domxpath->registerNamespace("randomNS","randomNS");
        $xpath_string = "//fo:bookmark[@$randomNS:id = '$parent']";
        $e = $domxpath->query($xpath_string)[0];
        if($e){
          $e->appendChild($bookmark_el);
        }
      }
      else {
        $dom->appendChild($bookmarkTree_el);
      }
      unset(self::$bookmarks[$keyfirst]);
    }
    return $dom;
  }


  public static function directory_separator()
  {
    return DIRECTORY_SEPARATOR;
  }

  public static function resolve_ident($ident = null, $prefix = 'DMC-', $format = '.xml')
  {
    if(!$ident) return '';
    if (is_array($ident)) {
      $ident = $ident[0];
    }
    switch ($ident->nodeName) {
      case 'dmIdent':
        return self::resolve_dmIdent($ident, $prefix, $format);
        break;
      case 'dmRefIdent':
        return self::resolve_dmIdent($ident, $prefix, $format);
        break;
      case 'pmIdent':
        return self::resolve_pmIdent($ident, $prefix, $format);
        break;      
      case 'pmRefIdent':
        return self::resolve_pmIdent($ident, $prefix, $format);
        break;      
      case 'externalPubRefIdent':
        return self::resolve_externalPubRefIdent($ident, $prefix, $format);
        break;      
      case 'dmlIdent':
        return self::resolve_dmlIdent($ident, $prefix, $format);
        break;
      default:
        # code...
        break;
    }
  }

  public static function resolve_title($title = null, $child = null)
  {
    if(!$title) return '';
    if (is_array($title)) {
      $title = $title[0];
    }
    switch ($title->nodeName) {
      case 'dmTitle':
        return self::resolve_dmTitle($title, $child);
        break;
      case 'pmTitle':
        return self::resolve_pmTitle($title);
        break;
      default:
        # code...
        break;
    }
  }

  public static function resolve_dmIdent($dmIdent = null, $prefix = 'DMC-', $format = '.xml')
  {
    if (empty($dmIdent)) return '';
    if (is_array($dmIdent)) {
      $dmIdent = $dmIdent[0];
    }
    $dmCode = self::resolve_dmCode($dmIdent->getElementsByTagName('dmCode')[0], $prefix);
    $issueInfo = ($if = self::resolve_issueInfo($dmIdent->getElementsByTagName('issueInfo')[0])) ? "_" . $if : '';
    $languange = ($lg = self::resolve_languange($dmIdent->getElementsByTagName('language')[0])) ? "_" . $lg : '';

    return strtoupper($dmCode . $issueInfo . $languange) . $format;
  }

  public static function resolve_pmIdent($pmIdent = null, $prefix = 'PMC-', $format = '.xml')
  {
    if (empty($pmIdent)) return '';
    if (is_array($pmIdent)) {
      $pmIdent = $pmIdent[0];
    }
    $pmCode = self::resolve_pmCode($pmIdent->getElementsByTagName('pmCode')[0], $prefix);
    $issueInfo = ($if = self::resolve_issueInfo($pmIdent->getElementsByTagName('issueInfo')[0])) ? "_" . $if : '';
    $languange = ($lg = self::resolve_languange($pmIdent->getElementsByTagName('language')[0])) ? "_" . $lg : '';

    return strtoupper($pmCode . $issueInfo . $languange) . $format;
  }

  public static function resolve_dmlIdent($dmlIdent = null, $prefix = 'DML-', $format = '.xml')
  {
    if (empty($dmlIdent)) return '';
    if (is_array($dmlIdent)) {
      $dmlIdent = $dmlIdent[0];
    }
    $dmlCode = self::resolve_dmlCode($dmlIdent->getElementsByTagName('dmlCode')[0], $prefix);
    $issueInfo = ($if = self::resolve_issueInfo($dmlIdent->getElementsByTagName('issueInfo')[0])) ? "_" . $if : '';
    return strtoupper($dmlCode . $issueInfo) . $format;
  }

  public static function resolve_infoEntityIdent($infoEntityIdent = null, $prefix = '', $format = '')
  {
    if (empty($infoEntityIdent)) return '';
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

  public static function resolve_imfIdent($imfIdent = null, $prefix = 'IMF-', $format = '.xml')
  {
    if (empty($idents)) return '';
    if (is_array($imfIdent)) {
      $imfIdent = $imfIdent[0];
    }
    $imfCode = $imfIdent->getElementsByTagName('imfCode')[0]->getAttribute('imfIdentIcn');
    $issueInfo = ($if = self::resolve_issueInfo($imfIdent->getElementsByTagName('issueInfo')[0])) ? "_" . $if : '';

    return strtoupper($prefix . $imfCode . $issueInfo) . $format;
  }

  public static function resolve_dmCode($dmCode, string $prefix = 'DMC-')
  {
    // untuk mengakomodir penggunaan fungsi di XSLT
    if (empty($dmCode)) return '';
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
    if (empty($pmCode)) return '';
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
    if (empty($dmlCode)) return '';
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
    if (empty($issueInfo)) return '';

    if (is_array($issueInfo)) {
      $issueInfo = $issueInfo[0];
    }

    $issueNumber = $issueInfo->getAttribute('issueNumber');
    $inWork = $issueInfo->getAttribute('inWork');
    return $issueNumber . "-" . $inWork;
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

  public static function resolve_issueType($issueType, string $option = '')
  {
    if (empty($issueType)) return '';
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

  public static function resolve_externalPubRefIdent($externalPubRefIdent)
  {
    if (empty($externalPubRefIdent)) return '';
    if (is_array($externalPubRefIdent)) {
      $externalPubRefIdent = $externalPubRefIdent[0];
    }

    // $externalPubCode = isset($externalPubRefIdent->getElementsByTagName('externalPubCode')[0]) ? 'Dummy Ext Pub Code' : null;
    // $externalPubTitle = isset($externalPubRefIdent->getElementsByTagName('externalPubTitle')[0]) ? 'Dummy Ext Pub Title' : null;
    // $externalPubIssueInfo = isset($externalPubRefIdent->getElementsByTagName('externalPubIssueInfo')[0]) ? 'Dummy Ext Pub Issue Info' : null;
    $externalPubCode = ($externalPubRefIdent->getElementsByTagName('externalPubCode')[0]);
    $externalPubTitle = ($externalPubRefIdent->getElementsByTagName('externalPubTitle')[0]);
    $externalPubIssueInfo = ($externalPubRefIdent->getElementsByTagName('externalPubIssueInfo')[0]);

    return $externalPubCode ? $externalPubCode->textContent . 
    ($externalPubTitle ? "_" . $externalPubTitle->textContent . 
    ($externalPubIssueInfo ? "_" . $externalPubIssueInfo->textContent : ''
    ) : ''
    ) : '';
  }

  public static function resolve_languange($languange = null)
  {
    if (empty($languange)) return '';
    if (is_array($languange)) {
      $languange = $languange[0];
    }
    $languangeIsoCode = $languange->getAttribute('languageIsoCode');
    $countryIsoCode = $languange->getAttribute('countryIsoCode');
    return $languangeIsoCode . "-" . $countryIsoCode;
  }

  public static function resolve_pmTitle($pmTitle, $shortPmTitle = null)
  {
    if (empty($pmTitle)) return '';
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

  public static function resolve_dmTitle($dmTitle, $child = '')
  {
    if (empty($dmTitle)) return '';
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

  public static function resolve_externalPubRefTitle()
  {
    return '';
  }

  // /**
  //  * resolving applic element
  //  * @param DOMElement $doc berupa applic
  //  * @param int $useDisplayText 0,1,2. jika satu itu string HARUS pakai display Text. Jika dua itu optional. Artinya jika displayText tidak ada, akan mengambil assert untuk test
  //  * @return string
  //  */
  // public static function resolve_applic(mixed $applic, bool $keppOneByOne = false, bool $useDisplayName = true ,int $useDisplayText = 2)
  // {
  //   if (empty($applic)) return '';
  //   if (is_array($applic)) {
  //     $applic = $applic[0];
  //   }

  //   if($useDisplayText){
  //     if($applic->firstElementChild->tagName === 'displayText'){
  //       $displayText = '';
  //       foreach($applic->firstElementChild->childNodes as $simplePara){
  //         $displayText .= ', ' . $simplePara->textContent;
  //       }
  //       return ltrim($displayText, ', ');
  //     }
  //     if($useDisplayText === 1){
  //       return '';
  //     }
  //   }

  //   // untuk assert
  //   $arrayify = Helper::arrayify_applic($applic, $keppOneByOne, $useDisplayName);
  //   unset($arrayify['displayText']);
  //   $arrayify[array_key_first($arrayify)]['text'] = ltrim($arrayify[array_key_first($arrayify)]['text'], "(");
  //   $arrayify[array_key_first($arrayify)]['text'] = rtrim($arrayify[array_key_first($arrayify)]['text'], ")");
  //   $text = $arrayify[array_key_first($arrayify)]['text'];
  //   // $message = array_filter($arrayify[array_key_first($arrayify)]['children'], fn($c) => $c['%MESSAGE'] ?? false);
  //   // output = $message = [
  //   //   "%MESSAGE" => "ERROR: 'serialnumber' only contains N001-N004, N006-N010, N012-N015 and does not contains such N011, N016-N020",
  //   //   "text" => "",
  //   //   "%STATUS" => "fail",
  //   //   "%APPLICPROPERTYTYPE" => "prodattr",
  //   //   "%APPLICPROPERTYIDENT" => "serialnumber",
  //   //   "%APPLICPROPERTYVALUES" => "N001~N004|N006~N020",
  //   // ];
  //   // output harusnya nanti bisa dibuat/ditambah ke static error CSDB;
  //   // dd($text);
  //   return $text; // return string "text". eg.: $arrayify = ["evaluate" => ["text" => string, 'andOr' => String, 'children' => array]];
  // }


  /**
   * @return \DOMElement $issueInfo
   */
  public static function parse_issueInfo(string $name): \DOMElement
  {
    $doc = new \DOMDocument();
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
   * @return \DOMElement $dmCode
   */
  public static function parse_dmCode(string $name): \DOMElement
  {
    $doc = new \DOMDocument();
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
   * @return \DOMElement
   */
  public static function parse_language(string $name) :\DOMElement
  {
    $doc = new \DOMDocument();
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
   * depreciated. diganti checkLevelByPrefix
   * minimum value of level is 0 (zero)
   * @return int
   */
  public static function checkLevel(mixed $element, int $minimum = 0) :int
  {
    if (empty($element)) return -1;
    if (is_array($element)) {
      $element = $element[0];
    }
    $tagName = $element->tagName;
    $level = $minimum;
    while (($parent = $element->parentNode)->nodeName == $tagName) {
      $element = $parent;
      $level += 1;
    }
    return ($level < 0) ? (int) $minimum : (int) $level;
  }

  public static function checkLevelByPrefix(string $prefix = '')
  {
   return count(explode('.',$prefix));
  }

  /**
   * checking index by sibling
   * @return int
   */
  public static function checkIndex(\DOMElement $element, int $minimum = 0) :int
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
   * @return int
   */
  public static function getPrefixNum(\DOMElement $element, $minimum = 0) :string
  {
    $tagName = $element->tagName;
    $index = self::checkIndex($element) + $minimum;
    $prefixnum = array($index);

    while (($parent = $element->parentNode)->nodeName == $tagName) {
      $index = self::checkIndex($parent) + $minimum;
      array_push($prefixnum, $index);
      $element = $parent;
    }
    $prefixnum = array_reverse($prefixnum);
    return (string) join(".", $prefixnum);
  }


  /**
   * #return 
   * for ICN, it is based on Cage code named
   */
  public static function decode_ident(string $filename, bool $ref = true)
  {
    $prefix = substr($filename, 0, 4);
    switch ($prefix) {
      case 'DMC-':
        return self::decode_dmIdent($filename, $ref);
        break;
      case 'DML-':
        return self::decode_dmlIdent($filename, $ref);
        break;
      case 'PMC-':
        return self::decode_pmIdent($filename, $ref);
        break;
      case 'ICN-':
        return self::decode_infoEntityIdent($filename, $ref);
        break;
      default:
        return  '';
        break;
    }
  }
  
  /**
   * @return Array
   */
  public static function decode_infoEntityIdent(string $filename) :array
  {
    $prefix = 'ICN-';
    if (substr($filename, 0, 4) === 'ICN-') {
      $f = substr($filename, 4); // 0001Z-00014-002-01.jpg gak ada lg prefix nya
    }
    $f = preg_replace("/\.\w+$/", '', $f); // 0001Z-00014-002-01 gak ada lagi extension atau format atau notasi nya
    $extension = str_replace($prefix . $f, '', $filename); // .jpg

    $code_array = explode('-', $f);
    $data = [];

    $data['prefix'] = $prefix;
    $data['extension'] = $extension;

    $data['xml_string'] = <<<EOL
      <infoEntityRef infoEntityRefIdent="{$filename}"/>
    EOL;

    $data['prefix'] = $prefix;

    if (($l = count($code_array)) == 4) {
      $data['infoEntityIdent'] =  [
        "cageCode" => $code_array[0],
        "uniqueIdentifier" => $code_array[1],
        "issueNumber" => $code_array[2],
        "securityClassification" => $code_array[3],
      ];
    } elseif ($l == 9) {
      $data['infoEntityIdent'] = [
        "modelIdentCode" => $code_array[0],
        "systemDiffCode" => $code_array[1],
        "snsCode" => $code_array[2],
        "responsiblePartnerCompanyCode" => $code_array[3],
        "originatorCompanyCode" => $code_array[4],
        "uniqueIdentifier" => $code_array[5],
        'variantCode' => $code_array[6],
        'issueNumber' => $code_array[7],
        "securityClassification" => $code_array[8],
      ];
    }
    return $data;
  }

  /**
   * @return Array
   */
  public static function decode_pmIdent(string $filename, $ref = true) :array
  {
    $prefix = 'PMC-';
    $f = str_replace($prefix, '', $filename); // MALE-K0378-A0001-00_000-01_EN-EN.xml
    $f = preg_replace('/.xml/', '', $f);

    $f_array = explode('_', $f);
    $code = $f_array[0];
    $issueInfo = $f_array[1] ?? '';
    $language = $f_array[2] ?? '';

    $code_array = explode('-', $code);
    $issueInfo_array = explode('-', $issueInfo);
    $language_array = explode('-', $language);

    $ref = $ref ? 'Ref' : '';

    $data = [];
    $data['pmCode'] =  [
      "modelIdentCode" => $code_array[0],
      "pmIssuer" => $code_array[1],
      "pmNumber" => $code_array[2],
      "pmVolume" => $code_array[3],
    ];

    $data['prefix'] = $prefix;
    $data['issueInfo'] = [
      'issueNumber' => $issueInfo_array[0] ?? '',
      'inWork' => $issueInfo_array[1] ?? '',
    ];

    $data['language'] = [
      'languageIsoCode' => strtolower($language_array[0] ?? ''),
      'countryIsoCode' => $language_array[1] ?? '',
    ];

    $xml_string = function ($data = []) use ($ref) {
      $d = [];
      array_walk($data['pmCode'], function ($v, $name) use (&$d) {
        $d[$name] = ($v != '') ? ("{$name}=" . '"' . "$v" . '"') : '';
      });
      array_walk($data['issueInfo'], function ($v, $name) use (&$d) {
        $d[$name] = ($v != '') ? ("{$name}=" . '"' . "$v" . '"') : '';
      });
      array_walk($data['language'], function ($v, $name) use (&$d) {
        $d[$name] = ($v != '') ? ("{$name}=" . '"' . "$v" . '"') : '';
      });

      $ident = <<<EOD
        <pm{$ref}Ident>
          <pmCode {$d['modelIdentCode']} {$d['pmIssuer']} {$d['pmNumber']} {$d['pmVolume']}/>
          <language {$d['languageIsoCode']} {$d['countryIsoCode']}/>
          <issueInfo {$d['issueNumber']} {$d['inWork']}/>
        </pm{$ref}Ident>
      EOD;

      if ($ref) {
        return
          <<<EOL
        <pm{$ref}>
          $ident        
        </pm{$ref}>
        EOL;
      } else {
        return $ident;
      }
    };

    $data['xml_string'] = $xml_string($data);
    $data['prefix'] = $prefix;

    return $data;
  }

  /**
   * @return Array
   */
  public static function decode_dmlIdent(string $filename, $ref = true) :array
  {
    $prefix = 'DML-';
    $f = str_replace($prefix, '', $filename); // MALE-K0378-P-2024-00003_001-00.xml
    $f = preg_replace('/.xml/', '', $f);

    $f_array = explode('_', $f);
    $code = $f_array[0];
    $issueInfo = $f_array[1] ?? '';

    $code_array = explode('-', $code);
    $issueInfo_array = explode('-', $issueInfo);

    $ref = $ref ? 'Ref' : '';

    $data = [];
    $data['dmlCode'] =  [
      "modelIdentCode" => $code_array[0],
      "senderIdent" => $code_array[1],
      "dmlType" => strtolower($code_array[2]),
      "yearOfDataIssue" => $code_array[3],
      "seqNumber" => $code_array[4],
    ];

    $data['prefix'] = $prefix;
    $data['issueInfo'] = [
      'issueNumber' => $issueInfo_array[0] ?? '',
      'inWork' => $issueInfo_array[1] ?? '',
    ];

    $xml_string = function ($data = []) use ($ref) {
      $d = [];
      array_walk($data['dmlCode'], function ($v, $name) use (&$d) {
        $d[$name] = ($v != '') ? ("{$name}=" . '"' . "$v" . '"') : '';
      });
      array_walk($data['issueInfo'], function ($v, $name) use (&$d) {
        $d[$name] = ($v != '') ? ("{$name}=" . '"' . "$v" . '"') : '';
      });

      $ident = <<<EOD
        <dml{$ref}Ident>
          <dmlCode {$d['modelIdentCode']} {$d['senderIdent']} {$d['dmlType']} {$d['yearOfDataIssue']} {$d['seqNumber']} />
          <issueInfo {$d['issueNumber']} {$d['inWork']}/>
        </dml{$ref}Ident>
      EOD;

      if ($ref) {
        return
          <<<EOL
        <dml{$ref}>
          $ident        
        </dml{$ref}>
        EOL;
      } else {
        return $ident;
      }
    };

    $data['xml_string'] = $xml_string($data);
    $data['prefix'] = $prefix;

    return $data;
  }

  /**
   * $xmlString dmIdent tidak 
   * @return Array
   */
  public static function decode_dmIdent(string $filename, $ref = true) :array
  {
    $prefix = 'DMC-'; // DMC-,
    $f = str_replace($prefix, '', $filename); // MALE-SNS-Disscode-infoCode,
    // $f = substr($filename,4); 
    $f = preg_replace('/.xml/', '', $f);

    $f_array = explode('_', $f);
    $code = $f_array[0];
    $issueInfo = $f_array[1] ?? '';
    $language = $f_array[2] ?? '';

    $code_array = explode('-', $code);
    $issueInfo_array = explode('-', $issueInfo);
    $language_array = explode('-', $language);

    if (count($code_array) < 8) return [];
    $ref = $ref ? 'Ref' : '';

    $data = [];
    $data['dmCode'] =  [
      "modelIdentCode" => $code_array[0],
      "systemDiffCode" => $code_array[1],
      "systemCode" => $code_array[2],
      "subSystemCode" => $code_array[3][0],
      "subSubSystemCode" => $code_array[3][1],
      "assyCode" => $code_array[4],
      "disassyCode" => substr($code_array[5], 0, 2),
      "disassyCodeVariant" => substr($code_array[5], 2),
      "infoCode" => substr($code_array[6], 0, 3),
      "infoCodeVariant" => substr($code_array[6], 3),
      "itemLocationCode" => $code_array[7],
    ];
    if (isset($dmCode_array[8])) {
      $data['dmCode']['learnCode'] = strtoupper(substr($dmCode_array[8], 0, 3));
      $data['dmCode']['learnEventCode'] = strtoupper(substr($dmCode_array[8], 4));
    } else {
      $data['dmCode']['learnCode'] = '';
      $data['dmCode']['learnEventCode'] = '';
    }

    $data['prefix'] = $prefix;
    $data['issueInfo'] = [
      'issueNumber' => $issueInfo_array[0] ?? '',
      'inWork' => $issueInfo_array[1] ?? '',
    ];

    $data['language'] = [
      'languageIsoCode' => strtolower($language_array[0] ?? ''),
      'countryIsoCode' => $language_array[1] ?? '',
    ];


    $xml_string = function ($data = []) use ($ref) {
      $d = [];
      array_walk($data['dmCode'], function ($v, $name) use (&$d) {
        $d[$name] = ($v != '') ? ("{$name}=" . '"' . "$v" . '"') : '';
      });
      array_walk($data['issueInfo'], function ($v, $name) use (&$d) {
        $d[$name] = ($v != '') ? ("{$name}=" . '"' . "$v" . '"') : '';
      });
      array_walk($data['language'], function ($v, $name) use (&$d) {
        $d[$name] = ($v != '') ? ("{$name}=" . '"' . "$v" . '"') : '';
      });

      $ident = <<<EOD
        <dm{$ref}Ident>
          <dmCode {$d['modelIdentCode']} {$d['systemDiffCode']} {$d['systemCode']} {$d['subSystemCode']} {$d['subSubSystemCode']} {$d['assyCode']} {$d['disassyCode']} {$d['disassyCodeVariant']} {$d['infoCode']} {$d['infoCodeVariant']} {$d['itemLocationCode']} {$d['learnCode']} {$d['learnEventCode']}/>
          <issueInfo {$d['issueNumber']} {$d['inWork']}/>
          <language {$d['languageIsoCode']} {$d['countryIsoCode']}/>
        </dm{$ref}Ident>
      EOD;

      if ($ref) {
        return
          <<<EOL
        <dm{$ref}>
          $ident        
        </dm{$ref}>
        EOL;
      } else {
        return $ident;
      }

      return
        <<<EOL
      <dm{$ref}>
        <dm{$ref}Ident>
          <dmCode {$d['modelIdentCode']} {$d['systemDiffCode']} {$d['systemCode']} {$d['subSystemCode']} {$d['subSubSystemCode']} {$d['assyCode']} {$d['disassyCode']} {$d['disassyCodeVariant']} {$d['infoCode']} {$d['infoCodeVariant']} {$d['itemLocationCode']} {$d['learnCode']} {$d['learnEventCode']}/>
          <issueInfo {$d['issueNumber']} {$d['inWork']}/>
          <language {$d['languageIsoCode']} {$d['countryIsoCode']}/>
        </dm{$ref}Ident>
      </dm{$ref}>
      EOL;
    };

    $data['xml_string'] = $xml_string($data);
    $data['prefix'] = $prefix;

    return $data;
  }

  /**
   * @return \DOMDocument
   */
  public static function document($path, string $filename) :\DOMDocument
  {
    if (is_array($path)) {
      $path = $path[0];
    }
    if ($path instanceof \DOMDocument) {
      $path = Helper::analyzeURI($path->baseURI)['path'];
    }
    if (substr($filename, 0, 3) === 'ICN') {
      $filename = self::detectIMF($path, $filename);
    }
    if(!$filename) return new \DOMDocument;
    
    CSDBError::$processId = 'ignore';
    $CSDBObject = new CSDBObject("5.0");
    if(!file_exists($path. DIRECTORY_SEPARATOR. $filename)){
      CSDBError::setError('opendocument', "No such $filename exists.");
      return new \DOMDocument;
    }
    $CSDBObject->load($path. DIRECTORY_SEPARATOR . $filename);
    CSDBError::getErrors(true, 'ignore'); // remove error
    if ($CSDBObject->document) {
      return $CSDBObject->document;
    } else {
      return new \DOMDocument;
    }
  }

  /**
   * search IMF file by using latest issueNumber and inWork number
   * @return string filename IMF
   */
  public static function detectIMF(string $path, string $icnFilename) :string
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
        $filterBy = array_filter($c, (fn ($v) => ($v[0] == $in_max AND $v[1] == $iw_max)));
      }
      $issueInfo = join("-", $filterBy[0]);
      $filename .= "_" . $issueInfo . ".xml";
      return $filename;
    };
    $filename = $searchImf($path);
    return $filename;
  }

  public static function interpretDimension(string $unit) :string
  {
    // <xsl:variable name="units" select="php:function('preg_replace', '/[0-9\.]+/' ,'', string(ancestor::tgroup/colspec[1]/@colwidth))"/>
    if(!$unit) return '';
    preg_match('/([0-9\.]+)(.)/', $unit, $matches);
    $n = $matches[1];
    $u = $matches[2];
    if(($n > 0) AND ($n <= 1)){
      $n = $n*100;
    }
    if($u === '*'){
      $u = '%';
    }
    return $n.$u;
  }


  /**
   * khusus footnote yang marking nya number, bukan asterisk atay alpha
   */
  public static $footnotePositionStore = [];
  public static function next_footnotePosition(string $filename, bool $set = false) :int
  {
    $totalIndex = count(self::$footnotePositionStore[$filename]);
    if($totalIndex === 0) {
      if($set) self::$footnotePositionStore[$filename][] = 1;
      return 1;
    };
    $no = self::$footnotePositionStore[$filename][$totalIndex-1] + 1;
    if($set) self::$footnotePositionStore[$filename][] = $no;
    return $no;
  }
  public static function add_footnotePosition(string $filename, int $no) : void
  {
    self::$footnotePositionStore[$filename][] = $no;
  }

  
}
