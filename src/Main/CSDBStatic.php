<?php

namespace Ptdi\Mpub\Main;

class CSDBStatic
{
  /**
   * @deprecated
   * diganti/dipindahkan ke class Transformer/Pdf
   * 
   * what masterName (@pmType) used currently of transformatting
   * mungkin nanti dipindahkan ke class CSDBObject saja
   */
  protected static string $PDF_MasterName = '';

  /**
   * @deprecated
   * diganti/dipindahkan ke class Transformer/Pdf
   */
  public static function get_PDF_MasterName()
  {
    return self::$PDF_MasterName;
  }

  /**
   * @deprecated
   * diganti/dipindahkan ke class Transformer/Pdf
   */
  public static function set_PDF_MasterName(string $text)
  {
    self::$PDF_MasterName = $text;
  }

  /**
   * @deprecated
   * dipindah ke Transformer\Pdf
   * digunakan agar tidak ada multiple masterName di xsl fo layout
   */
  protected static array $masterName = [];

  /**
   * @deprecated
   * dipindah ke Transformer\Pdf
   * digunakan sekalian untuk check apakah masterName sudah di tambahkan ke layout atau belum
   */
  public static function add_masterName(string $name)
  {
    if (!in_array($name, self::$masterName, true)) {
      self::$masterName[] = $name;
      return true;
    } else {
      return false;
    }
  }


  /**
   * @deprecated
   * dipindah ke Transformer\Pdf
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

  /**
   * @deprecated
   * dipindah ke Transformer\Pdf
   */
  public static function fillBookmark(string $destination, string $text, string $parent = '')
  {
    self::$bookmarks[$destination] = [
      'text' => $text,
      'parent' => $parent,
    ];
  }

  /**
   * @deprecated dipindah ke Transformer\Pdf
   * @return \DOMDocument
   */
  public static function transformBookmark_to_xml()
  {
    // dump(self::$bookmarks);
    if (empty(self::$bookmarks)) return '';
    $dom = new \DOMDocument;
    $bookmarkTree_el = $dom->createElementNS('http://www.w3.org/1999/XSL/Format', 'bookmark-tree');
    $dom->appendChild($bookmarkTree_el);

    while (!empty(self::$bookmarks)) {
      $keyfirst = array_key_first(self::$bookmarks);

      $parent = self::$bookmarks[$keyfirst]['parent'];

      $bookmark_el = $dom->createElementNS('http://www.w3.org/1999/XSL/Format', 'bookmark');
      $bookmarkTitle_el = $dom->createElementNS('http://www.w3.org/1999/XSL/Format', 'bookmark-title');
      $bookmark_el->setAttribute('internal-destination', $keyfirst);
      $bookmarkTitle_el->textContent = self::$bookmarks[$keyfirst]['text'];

      $bookmark_el->appendChild($bookmarkTitle_el);
      $bookmarkTree_el->appendChild($bookmark_el);

      if ($parent) {
        $domxpath = new \DOMXpath($dom);
        $domxpath->registerNamespace('fo', 'http://www.w3.org/1999/XSL/Format');
        $xpath_string = "//fo:bookmark[@id = '$parent']";
        $e = $domxpath->query($xpath_string)[0];
        if ($e) {
          $e->appendChild($bookmark_el);
        }
      } else {
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

  public static function resolve_ident($ident = null, $prefix = '', $format = '.xml')
  {
    if (!$ident) return '';
    if (is_array($ident)) {
      $ident = $ident[0];
    }
    $nodeName = $ident->nodeName;
    if($nodeName === 'dmIdent'||$nodeName === 'dmRef'|| $nodeName === 'dmRefIdent') return self::resolve_dmIdent($ident, $prefix === 'auto' ? 'DMC-' : '', $format);
    else if($nodeName === 'pmIdent'||$nodeName === 'pmRef'|| $nodeName === 'pmRefIdent') return self::resolve_pmIdent($ident, $prefix === 'auto' ? 'PMC-' : '', $format);
    else if($nodeName === 'externalPubRefIdent') return self::resolve_externalPubRefIdent($ident, $prefix);
    else if($nodeName === 'dmlIdent'||$nodeName === 'dmlRef'|| $nodeName === 'dmlRefIdent') return self::resolve_dmlIdent($ident, $prefix === 'auto' ? 'DML-' : '', $format);
    else if($nodeName === 'ddnIdent'||$nodeName === 'ddnRef'|| $nodeName === 'ddnRefIdent') return self::resolve_ddnIdent($ident, $prefix === 'auto' ? 'DDN-' : '', $format);
    else if($nodeName === 'commentIdent'||$nodeName === 'commentRef'|| $nodeName === 'commentRefIdent') return self::resolve_commentIdent($ident, $prefix === 'auto' ? 'COM-' : '', $format);
  }

  public static function resolve_title($title = null, $child = null)
  {
    if (!$title) return '';
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

  public static function resolve_commentIdent($commentIdent = null, $prefix = 'COM-', $format = '.xml')
  {
    if (empty($commentIdent)) return '';
    if (is_array($commentIdent)) {
      $commentIdent = $commentIdent[0];
    }
    $commentCode = self::resolve_commentCode($commentIdent->getElementsByTagName('commentCode')[0], $prefix);
    $languange = ($lg = self::resolve_languange($commentIdent->getElementsByTagName('language')[0])) ? "_" . $lg : '';;
    return strtoupper($commentCode . $languange) . $format;
  }

  public static function resolve_ddnIdent($ddnIdent = null, $prefix = 'DDN-', $format = '.xml')
  {
    if (empty($ddnIdent)) return '';
    if (is_array($ddnIdent)) {
      $ddnIdent = $ddnIdent[0];
    }
    $ddnCode = self::resolve_ddnCode($ddnIdent->getElementsByTagName('ddnCode')[0], $prefix);
    return strtoupper($ddnCode) . $format;
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
    if (empty($imfIdent)) return '';
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

    $name = $prefix .
      $modelIdentCode . "-" .
      $senderIdent . "-" .
      $dmlType . "-" .
      $yearOfDataIssue . "-" .
      $seqNumber;

    return strtoupper($name);
  }

  public static function resolve_commentCode($commentCode, string $prefix = 'COM-')
  {
    if (empty($commentCode)) return '';
    // untuk mengakomodir penggunaan fungsi di XSLT
    if (is_array($commentCode)) {
      $commentCode = $commentCode[0];
    }
    $modelIdentCode = $commentCode->getAttribute('modelIdentCode');
    $senderIdent = $commentCode->getAttribute('senderIdent');
    $yearOfDataIssue = $commentCode->getAttribute('yearOfDataIssue');
    $seqNumber = $commentCode->getAttribute('seqNumber');
    $commentType = $commentCode->getAttribute('commentType');

    $name = $prefix .
      $modelIdentCode . "-" .
      $senderIdent . "-" .
      $commentType . "-" .
      $yearOfDataIssue . "-" .
      $seqNumber;

    return strtoupper($name);
  }

  public static function resolve_ddnCode($commentCode, string $prefix = 'DDN-')
  {
    if (empty($commentCode)) return '';
    // untuk mengakomodir penggunaan fungsi di XSLT
    if (is_array($commentCode)) {
      $commentCode = $commentCode[0];
    }
    $modelIdentCode = $commentCode->getAttribute('modelIdentCode');
    $senderIdent = $commentCode->getAttribute('senderIdent');
    $receiverIdent = $commentCode->getAttribute('receiverIdent');
    $yearOfDataIssue = $commentCode->getAttribute('yearOfDataIssue');
    $seqNumber = $commentCode->getAttribute('seqNumber');

    $name = $prefix .
      $modelIdentCode . "-" .
      $senderIdent . "-" .
      $receiverIdent . "-" .
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

  public static function resolve_externalPubRefIdent($externalPubRefIdent, $codingScheme = '')
  {
    if (empty($externalPubRefIdent)) return '';
    if (is_array($externalPubRefIdent)) {
      $externalPubRefIdent = $externalPubRefIdent[0];
    }

    $externalPubCode = ($externalPubRefIdent->getElementsByTagName('externalPubCode')[0]);
    $externalPubTitle = ($externalPubRefIdent->getElementsByTagName('externalPubTitle')[0]);
    $externalPubIssueInfo = ($externalPubRefIdent->getElementsByTagName('externalPubIssueInfo')[0]);

    $ident =  $externalPubCode ? $externalPubCode->textContent .
      ($externalPubTitle ? "_" . $externalPubTitle->textContent .
        ($externalPubIssueInfo ? "_" . $externalPubIssueInfo->textContent : ''
        ) : ''
      ) : '';

    $extension = '';
    switch ($codingScheme) {
      case 'PDF':
        $extension = '.pdf';
        break;
    }
    return $ident . $extension;
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
  public static function parse_language(string $name): \DOMElement
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
   * @deprecated diganti checkLevelByPrefix
   * minimum value of level is 0 (zero)
   * @return int
   */
  public static function checkLevel(mixed $element, int $minimum = 0): int
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

  /**
   * @deprecated dipindah ke Transformator class
   */
  public static function checkLevelByPrefix(string $prefix = '')
  {
    return count(explode('.', $prefix));
  }

  /**
   * @deprecated dipindah ke Transformator class
   * checking index by sibling
   * @return int
   */
  public static function checkIndex(\DOMElement $element, int $minimum = 0): int
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
   * @deprecated dipindah ke Transformator class
   * @return int
   */
  public static function getPrefixNum(\DOMElement $element, $minimum = 0): string
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
      case 'DDN-':
        return self::decode_ddnIdent($filename, $ref);
        break;
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
        return self::decode_infoEntityIdent($filename);
        break;
      case 'IMF-':
        return self::decode_imfIdent($filename);
        break;
    }
    return array();
  }

  public static function decode_imfIdent(string $filename, $ref = false) :array
  {
    // IMF-0001Z-00011-001-01_000-01.xml
    $prefix = 'IMF-';
    if (substr($filename, 0, 4) === 'IMF-') {
      $imfIdentIcn = substr($filename, 4); // 0001Z-00014-002-01_000-01.xml gak ada lg prefix nya
    }
    $imfIdentIcn = str_replace('.xml', '', $imfIdentIcn); // 0001Z-00014-002-01_000-01 gak ada lagi extension atau format atau notasi nya
    list($imfIdentIcn, $issueInfo) = explode("_",$imfIdentIcn); // $imfIdentIcn = "0001Z-00014-002-01"; $issueInfo = "000-01";
    list($issueNumber, $inWork) =  explode("-", $issueInfo);
    $extension = '.xml';

    $code_array = explode('-', $imfIdentIcn);
    $data = [];

    $data['prefix'] = $prefix;
    $data['extension'] = $extension;
    $data['imfCode'] = [
      'imfIdentIcn' => $imfIdentIcn
    ];
    $data['issueInfo'] = [
      'issueNumber' => $issueNumber,
      'inWork' => $inWork,
    ];
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

    $ref = '';
    $xml_string = function ($data = []) use ($ref, $imfIdentIcn, $issueNumber, $inWork) {
      $d = [];
      array_walk($data['imfCode'], function ($v, $name) use (&$d) {
        $d[$name] = ($v != '') ? ("{$name}=" . '"' . "$v" . '"') : '';
      });
      array_walk($data['issueInfo'], function ($v, $name) use (&$d) {
        $d[$name] = ($v != '') ? ("{$name}=" . '"' . "$v" . '"') : '';
      });
      $ident = "<imf{$ref}Ident><imfCode{$ref}Ident imfIdentIcn={$imfIdentIcn}/>";      
      if($d['issueNumber'] && $d['inWork']){
        $ident .= '<issueInfo issueNumber="'.$issueNumber.'" inWork="'.$inWork.'"/>';
      }
      $ident .= "</imf{$ref}Ident>";
      return $ref ? "<imf{$ref}>$ident</imf{$ref}>" : $ident;
    };
    $data['xml_string'] = $xml_string($data);
    return $data;
  }

  /**
   * @return Array
   */
  public static function decode_infoEntityIdent(string $filename): array
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
  public static function decode_pmIdent(string $filename, $ref = true): array
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

      $ident = "<pm{$ref}Ident><pmCode {$d['modelIdentCode']} {$d['pmIssuer']} {$d['pmNumber']} {$d['pmVolume']}/>";
      if($d['issueNumber'] && $d['inWork']){
        $ident .= "<issueInfo {$d['issueNumber']} {$d['inWork']}/>";
      }
      if($d['languageIsoCode'] && $d['countryIsoCode']){
        $ident .= "<language {$d['languageIsoCode']} {$d['countryIsoCode']}/>";
      }
      $ident .= "</pm{$ref}Ident>";

      return $ref ? "<pm{$ref}>$ident</pm{$ref}>" : $ident;
    };

    $data['xml_string'] = $xml_string($data);
    $data['prefix'] = $prefix;

    return $data;
  }

  /**
   * @return Array
   */
  public static function decode_commentIdent(string $filename, $ref = true): array
  {
    $prefix = 'COM-';
    $f = str_replace($prefix, '', $filename);
    $f = preg_replace('/.xml/', '', $f);

    $f_array = explode('_', $f);
    $code = $f_array[0];
    // $issueInfo = $f_array[1] ?? '';
    $language = $f_array[1] ?? '';

    $code_array = explode('-', $code);
    // $issueInfo_array = explode('-', $issueInfo);
    $language_array = explode('-', $language);

    $ref = $ref ? 'Ref' : '';

    $data = [];
    $data['commentCode'] =  [
      "modelIdentCode" => $code_array[0],
      "senderIdent" => $code_array[1],
      "commentType" => $code_array[2],
      "yearOfDataIssue" => $code_array[3],
      "seqNumber" => $code_array[4],
    ];

    $data['prefix'] = $prefix;
    $data['language'] = [
      'languageIsoCode' => strtolower($language_array[0] ?? ''),
      'countryIsoCode' => $language_array[1] ?? '',
    ];

    $xml_string = function ($data = []) use ($ref) {
      $d = [];
      array_walk($data['commentCode'], function ($v, $name) use (&$d) {
        $d[$name] = ($v != '') ? ("{$name}=" . '"' . "$v" . '"') : '';
      });
      array_walk($data['language'], function ($v, $name) use (&$d) {
        $d[$name] = ($v != '') ? ("{$name}=" . '"' . "$v" . '"') : '';
      });

      $ident = "<comment{$ref}Ident><commentCode {$d['modelIdentCode']} {$d['senderIdent']} {$d['yearOfDataIssue']} {$d['seqNumber']} {$d['commentType']}/>";
      if($d['languageIsoCode'] && $d['countryIsoCode']){
        $ident .= "<language {$d['languageIsoCode']} {$d['countryIsoCode']}/>";
      }
      $ident .= "</comment{$ref}Ident>";

      return $ref ? "<comment{$ref}>$ident</comment{$ref}>" : $ident;
    };

    $data['xml_string'] = $xml_string($data);
    $data['prefix'] = $prefix;

    return $data;
  }
  /**
   * @return Array
   */
  public static function decode_ddnIdent(string $filename, $ref = true): array
  {
    $prefix = 'DDN-';
    // $f = str_replace($prefix, '', $filename);
    // $f = preg_replace('/.xml/', '', $f);
    $code = str_replace($prefix, '', $filename);
    $code = preg_replace('/.xml/', '', $code);

    // $f_array = explode('_', $f);
    // $code = $f_array[0];
    // $issueInfo = $f_array[1] ?? '';
    // $code = $f;

    $code_array = explode('-', $code);
    // $issueInfo_array = explode('-', $issueInfo);

    $ref = $ref ? 'Ref' : '';

    $data = [];
    $data['ddnCode'] =  [
      "modelIdentCode" => $code_array[0],
      "senderIdent" => $code_array[1],
      "receiverIdent" => strtolower($code_array[2]),
      "yearOfDataIssue" => $code_array[3],
      "seqNumber" => $code_array[4],
    ];

    $data['prefix'] = $prefix;
    // $data['issueInfo'] = [
    //   'issueNumber' => $issueInfo_array[0] ?? '',
    //   'inWork' => $issueInfo_array[1] ?? '',
    // ];

    $xml_string = function ($data = []) use ($ref) {
      $d = [];
      array_walk($data['ddnCode'], function ($v, $name) use (&$d) {
        $d[$name] = ($v != '') ? ("{$name}=" . '"' . "$v" . '"') : '';
      });

      $ident = <<<EOD
        <ddn{$ref}Ident>
          <ddnCode {$d['modelIdentCode']} {$d['senderIdent']} {$d['receiverIdent']} {$d['yearOfDataIssue']} {$d['seqNumber']} />
        </ddn{$ref}Ident>
      EOD;

      return $ref ? "<ddn{$ref}>$ident</ddn{$ref}>": $ident;
    };

    $data['xml_string'] = $xml_string($data);
    $data['prefix'] = $prefix;

    return $data;
  }
  /**
   * @return Array
   */
  public static function decode_dmlIdent(string $filename, $ref = true): array
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

      $ident = "<dml{$ref}Ident><dmlCode {$d['modelIdentCode']} {$d['senderIdent']} {$d['dmlType']} {$d['yearOfDataIssue']} {$d['seqNumber']} />";
      if($d['issueNumber'] && $d['inWork']){
        $ident .= "<issueInfo {$d['issueNumber']} {$d['inWork']}/>";
      }
      $ident .= " </dml{$ref}Ident>";

      return $ref ? "<dml{$ref}>$ident</dml{$ref}>" : $ident;
    };

    $data['xml_string'] = $xml_string($data);
    $data['prefix'] = $prefix;

    return $data;
  }

  /**
   * $xmlString dmIdent tidak 
   * @return Array
   */
  public static function decode_dmIdent(string $filename, $ref = true): array
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

      $ident = "<dm{$ref}Ident><dmCode {$d['modelIdentCode']} {$d['systemDiffCode']} {$d['systemCode']} {$d['subSystemCode']} {$d['subSubSystemCode']} {$d['assyCode']} {$d['disassyCode']} {$d['disassyCodeVariant']} {$d['infoCode']} {$d['infoCodeVariant']} {$d['itemLocationCode']} {$d['learnCode']} {$d['learnEventCode']}/>";
      if($d['issueNumber'] && $d['inWork']){
        $ident .= "<issueInfo {$d['issueNumber']} {$d['inWork']}/>";
      }
      if($d['languageIsoCode'] && $d['countryIsoCode']){
        $ident .= "<language {$d['languageIsoCode']} {$d['countryIsoCode']}/>";
      }

      $ident .=  "</dm{$ref}Ident>";

      return $ref ? "<dm{$ref}>$ident</dm{$ref}>" : $ident;
    };

    $data['xml_string'] = $xml_string($data);
    $data['prefix'] = $prefix;

    return $data;
  }

  /**
   * @return \DOMDocument
   */
  public static function document($path, string $filename): \DOMDocument
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
    if (!$filename) return new \DOMDocument;

    CSDBError::$processId = 'ignore';
    $CSDBObject = new CSDBObject("5.0");
    if (!file_exists($path . DIRECTORY_SEPARATOR . $filename)) {
      CSDBError::setError('opendocument', "No such $filename exists.");
      return new \DOMDocument;
    }
    $CSDBObject->load($path . DIRECTORY_SEPARATOR . $filename);
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
  public static function detectIMF(string $path, string $icnFilename): string
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
   * @deprecated dipindah ke Transformator class
   */
  public static function interpretDimension(string $unit): string
  {
    // <xsl:variable name="units" select="php:function('preg_replace', '/[0-9\.]+/' ,'', string(ancestor::tgroup/colspec[1]/@colwidth))"/>
    if (!$unit) return '';
    preg_match('/([0-9\.]+)(.)/', $unit, $matches);
    $n = $matches[1];
    $u = $matches[2];
    if (($n > 0) and ($n <= 1)) {
      $n = $n * 100;
    }
    if ($u === '*') {
      $u = '%';
    }
    return $n . $u;
  }


  /**
   * @deprecated, tidak dipakai lagi karena sudah pakai FOP
   * khusus footnote yang marking nya number, bukan asterisk atay alpha
   */
  public static $footnotePositionStore = [];

  /**
   * @deprecated, tidak dipakai lagi karena sudah pakai FOP
   */
  public static function next_footnotePosition(string $filename, bool $set = false): int
  {
    $totalIndex = count(self::$footnotePositionStore[$filename]);
    if ($totalIndex === 0) {
      if ($set) self::$footnotePositionStore[$filename][] = 1;
      return 1;
    };
    $no = self::$footnotePositionStore[$filename][$totalIndex - 1] + 1;
    if ($set) self::$footnotePositionStore[$filename][] = $no;
    return $no;
  }

  /**
   * @deprecated, tidak dipakai lagi karena sudah pakai FOP
   */
  public static function add_footnotePosition(string $filename, int $no): void
  {
    self::$footnotePositionStore[$filename][] = $no;
  }

  /**
   * DEPRECIATED (barudibuat tapi depreciated karena sudah ada fungsi decode_dmIdent)
   * awalnya diapakai di CSDBObject create_dml_identAndStatusSection
   * ini berbeda dengan fungsi decode_... karena decode_... outputnya ada xmlString
   * @return array contain attribute in dmCode,issueInfo,language
   */
  public static function explode_dm(string $filename)
  {
    $filename = strtoupper($filename);
    $filename = preg_replace('/.XML|DMC-/', '', $filename);
    $filenameIdent_array = explode('_', $filename);
    $dmCode = $filenameIdent_array[0];
    $issueInfo = $filenameIdent_array[1];
    $language = $filenameIdent_array[2];

    $dmCode_array = explode('-', $dmCode);
    $issueInfo_array = explode('-', $issueInfo);
    $language_array = explode('-', $language);

    $ret = [
      "modelIdentCode" => $dmCode_array[0],
      "systemDiffCode" => $dmCode_array[1],
      "systemCode" => $dmCode_array[2],
      "subSystemCode" => $dmCode_array[3][0],
      "subSubSystemCode" => $dmCode_array[3][1],
      "assyCode" => $dmCode_array[4],
      "disassyCode" => substr($dmCode_array[5], 0, 2),
      "disassyCodeVariant" => substr($dmCode_array[5], 2),
      "infoCode" => substr($dmCode_array[6], 0, 3),
      "infoCodeVariant" => substr($dmCode_array[6], 3),
      "itemLocationCode" => $dmCode_array[7],
    ];
    if (isset($dmCode_array[8])) {
      $ret['learnCode'] = strtoupper(substr($dmCode_array[8], 0, 3));
      $ret['learnEventCode'] = strtoupper(substr($dmCode_array[8], 4));
    } else {
      $ret['learnCode'] = '';
      $ret['learnEventCode'] = '';
    }

    $ret['issueNumber'] = $issueInfo_array[0];
    $ret['inWork'] = $issueInfo_array[1];

    $ret['languageIsoCode'] = strtolower($language_array[0]);
    $ret['countryIsoCode'] = $language_array[1];

    return $ret;
  }

  public static function simple_xml_to_json(\DOMDocument $DOMDocument){
    $arr = [];
    $childNodes = $DOMDocument->childNodes;
    $i = 0;
    while (isset($childNodes[$i])) {
      // jika ada child DOMElement
      if ($childNodes[$i]->nodeType === XML_ELEMENT_NODE) {
        self::simple_decode_element($childNodes[$i], $arr);
      }
      $i++;
    }
    return json_encode($arr);
  }

  public static function simple_decode_element($DOMElement, &$v){
    $arr = [];
    if ($DOMElement->hasAttributes()) {
      foreach ($DOMElement->attributes as $attr) {
        self::simple_decode_attribute($attr, $arr);
      }
    }
    $childNodes = $DOMElement->childNodes;
    $i = 0;
    while (isset($childNodes[$i])) {
      // jika ada child DOMElemen
      if ($childNodes[$i]->nodeType === XML_ELEMENT_NODE) {
        self::simple_decode_element($childNodes[$i], $arr);
      }
      // jika ada child DOMText
      elseif ($childNodes[$i]->nodeType === XML_TEXT_NODE) {
        $arr[] = self::decode_text($childNodes[$i]);
      }
      $i++;
    }
    if(array_is_list($arr) && count($arr) <= 1) $arr = join("",$arr);
    else {
      $keys = array_keys($arr);
      foreach($keys as $k){
        if(is_array($arr[$k]) && array_is_list($arr[$k]) && count($arr[$k]) <= 1) $arr[$k] = join("",$arr[$k]);
      }
    }
    $v[$DOMElement->nodeName] = $arr;  
  }
  
  private static function simple_decode_attribute($DOMAttr, &$v)
  {
    $v['at_' . $DOMAttr->nodeName] = $DOMAttr->nodeValue;
  }

  // private static function textToJson($DOMText, &$v)
  // {
  //   $nodeValue = trim($DOMText->nodeValue);
  //   if ($nodeValue) {
  //     $v[] = $nodeValue;
  //   }
  // }

  public static function xml_to_json(\DOMDocument $DOMDocument)
  {
    $arr = [];
    $childNodes = $DOMDocument->childNodes;
    $i = 0;
    while (isset($childNodes[$i])) {
      // jika ada child DOMElement
      if ($childNodes[$i]->nodeType === XML_ELEMENT_NODE) {
        self::decode_element($childNodes[$i], $arr[]);
      }
      // jika ada child DOMDoctype
      elseif ($childNodes[$i]->nodeType === XML_DOCUMENT_TYPE_NODE) {
        self::decode_doctype($childNodes[$i], $arr[]);
      }
      $i++;
    }
    return json_encode($arr);
  }
  private static function decode_doctype(\DOMDocumentType $DOMDoctype, &$v)
  {
    $name = $DOMDoctype->name;
    $re = '/(<\?xml[\s\S]+\?>)[\s\S](<!DOCTYPE\s' . $name . '[\s\S]+\]\>)([\s\S]+)/m';
    $str = ($DOMDoctype->parentNode->saveXML());
    $doctypeString = preg_replace($re, '${2}', $str);
    $v['DOCTYPE'] = [
      'name' => $DOMDoctype->nodeName,
      "systemId" => $DOMDoctype->systemId,
      "publicId" => $DOMDoctype->publicId,
      'string' => $doctypeString
    ];
  }
  public static function decode_element($DOMElement, &$v)
  {
    $arr = [];
    if ($DOMElement->hasAttributes()) {
      foreach ($DOMElement->attributes as $attr) {
        self::decode_attribute($attr, $arr[]);
      }
    }
    $childNodes = $DOMElement->childNodes;
    $i = 0;
    while (isset($childNodes[$i])) {
      // jika ada child DOMElemen
      if ($childNodes[$i]->nodeType === XML_ELEMENT_NODE) {
        self::decode_element($childNodes[$i], $arr[]);
      }
      // jika ada child DOMText
      elseif ($childNodes[$i]->nodeType === XML_TEXT_NODE) {
        if($text = self::decode_text($childNodes[$i])) $arr[] = $text;
      }
      $i++;
    }
    $v = [$DOMElement->nodeName => $arr];
  }
  private static function decode_attribute($DOMAttr, &$v)
  {
    $v = ['at_' . $DOMAttr->nodeName => $DOMAttr->nodeValue];
  }
  private static function decode_text($DOMText)
  {
    $nodeValue = trim($DOMText->nodeValue);
    if ($nodeValue) {
      return preg_replace("/\n|\r|\n\r|\s+/m"," ",$nodeValue);
    }
  }
  public static function json_to_xml(mixed $value, mixed $parentNode = null)
  {
    // dd($value);
    if (!$parentNode) $parentNode = new \DOMDocument();
    $arr = is_array($value) ? $value : (is_numeric($value) ? null : json_decode($value, true)); // json juga bisa di decode berdasarkan numeric value
    
    // jika array list (element xml), biasanya yang elemen pertama ada pada array list, karena saat transform to json, fungsi akan membaca doctype sebagai child document, jadi membuatnya menjadi array list
    if ($arr && array_is_list($arr)) {
      foreach ($arr as $node) {

        // asumsi parentNode adalah \DOMElement atau \DOMDocument
        $name = array_key_first($node);
        if ($name === 'DOCTYPE') {
          $str = <<<EOL
          <?xml version="1.0" encoding="UTF-8"?>
          EOL;
          $str .= $node[$name]['string']. '<'. $node[$name]['name'] .'/>'; // harus disertakan root elementnya atau error
          $parentNode->loadXML($str);
          $parentNode->firstElementChild->remove(); // di remove agar nanti tidak double
        } else {
          $DOMElement = $parentNode->ownerDocument ? $parentNode->ownerDocument->createElement($name) : $parentNode->createElement($name);
          $parentNode->appendChild($DOMElement);
          foreach ($node[$name] as $value) {
            self::json_to_xml($value, $DOMElement);
          }
        }
      }
    }
    // bisa DOMATTR atau DOMElement. Kalau attritbute tandanya $key nya diprefix simbol 'at_' 
    elseif ($arr) {
      foreach ($arr as $name => $node) {
        $type = substr($name, 0, 3) === 'at_' ? 'DOMAttr' : 'DOMElement'; // disini jika ingin membuat DOMDoctype
        if ($type === 'DOMAttr') {
          // asumsi parentNode adalah \DOMElement
          $parentNode->setAttribute(substr($name, 1), $node); // dibuang 'at_' nya
        } else {
          $DOMElement = $parentNode->ownerDocument ? $parentNode->ownerDocument->createElement($name) : $parentNode->createElement($name);
          $parentNode->appendChild($DOMElement);
          // handling nodeValue
          foreach ($node as $value) {
            self::json_to_xml($value, $DOMElement);
          }
        }
      }
    }
    // jika DOMText
    elseif (is_string($value) || is_numeric($value)) {
      // asumsi parentNode adalah \DOMElement
      $textNode = $parentNode->ownerDocument->createTextNode($value);
      $parentNode->appendChild($textNode);
    }
    return $parentNode;
  }
}
