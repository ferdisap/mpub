<?php

namespace Ptdi\Mpub\Main;

use DOMDocument;

class CSDBObject
{

  protected string $version = "5.0";

  public bool $preserveWhiteSpace = false;
  public bool $formatOutput = false;

  protected string $filename = '';
  protected string $initial = '';
  protected string $path = '';
  protected array $breakDownURI = [];

  public bool $XSIValidationResult = false;
  public bool $BREXValidationResult = false;

  /**
   *  bisa berupa getID3 array or \DOMDocument
   */
  protected mixed $document = null;

  protected \DOMDocument $ACTdoc;
  protected \DOMDocument $CCTdoc;
  protected \DOMDocument $PCTdoc;

  protected string $pmEntryTitle = '';

  /**
   * @param string $filename include absolute path
   */
  public function __construct($version = "5.0")
  {
    $this->version = $version;
    $this->ACTdoc = new DOMDocument();
    $this->CCTdoc = new DOMDocument();
    $this->PCTdoc = new DOMDocument();
  }

  public function __get($props)
  {
    if ($props === 'filename') {
      return !empty($this->filename) ? $this->filename : $this->getFilename();
    }
    elseif($props === 'initial'){
      return !empty($this->initial) ? $this->initial : $this->getInitial();
    }
    elseif($props === 'path'){
      return !empty($this->path) ? $this->path : $this->getPath();
    }
    return $this->$props;
  }

  /**
   * Belum mencakup seluruh S1000D doctype. Tinggal tambahkan di array nya
   */
  public function isS1000DDoctype()
  {
    if(($this->document instanceof \DOMDocument) AND ($this->document->doctype) AND in_array($this->document->doctype->nodeName, ['dmodule', 'pm', 'dml', 'icnmetadata'])){
      return true;
    } else {
      CSDBError::setError(!empty(CSDBError::$processId) ? CSDBError::$processId : 's1000d_doctype', "document must be be S1000D standard type.");
      return false;
    }
  }

  /**
   * Set the object document, wheter it result is DOMDcoument or Array (if ICN file)
   * @param string $filename dengan absolute path 
   * @return bool true or false
   */
  public function load(string $filename = '')
  {
    libxml_use_internal_errors(true);
    $mime = file_exists($filename) ? mime_content_type($filename) : '';
    if (str_contains($mime, 'text')) {
      $dom = new \DOMDocument('1.0');
      $dom->preserveWhiteSpace = $this->preserveWhiteSpace;
      $dom->formatOutput = $this->formatOutput;
      @$dom->load($filename, LIBXML_PARSEHUGE);
      $errors = libxml_get_errors();
      foreach ($errors as $e) {
        CSDBError::setError(!empty(CSDBError::$processId) ? CSDBError::$processId : 'file_exist', CSDBError::display_xml_error($e));
      }
      if(!$dom->documentElement) return false;
      $this->document = $dom;
      return true;
    } else {
      $this->document = new ICNDocument();
      if($this->document->load($filename)) return true;
      return false;
    }
    return false;
  }

  public function loadByString(string $text)
  {
    libxml_use_internal_errors(true);
    $dom = new \DOMDocument('1.0');
    $dom->preserveWhiteSpace = $this->preserveWhiteSpace;
    $dom->formatOutput = $this->formatOutput;
    @$dom->loadXML($text, LIBXML_PARSEHUGE);
    $errors = libxml_get_errors();
    foreach ($errors as $e) {
      CSDBError::setError(!empty(CSDBError::$processId) ? CSDBError::$processId : 'file_exist', CSDBError::display_xml_error($e));
    }
    if(!$dom->documentElement) return false;
    $this->document = $dom;
    return true;
  }

  /**
   * @param \DOMElement $element
   * @param string $doctype berupa 'dmodule', 'dml', 'pm', 'infoEntity'
   */
  public function loadByElement(\DOMElement $element, string $doctype)
  {
    $path = Helper::analyzeURI($element->ownerDocument->baseURI)['path'];
    $filename = '';
    switch ($doctype) {
      case 'dmodule':
        $filename = CSDBStatic::resolve_dmIdent($element);
        break;
      case 'dml':
        $filename = CSDBStatic::resolve_dmlIdent($element);
        break;
      case 'infoEntity':
        $filename = CSDBStatic::resolve_infoEntityIdent($element);
        break;
      case 'pm':
        $filename = CSDBStatic::resolve_pmIdent($element);
        break;
    }
    if ($filename) {
      return $this->load($path . DIRECTORY_SEPARATOR . $filename);
    }
  }

  public function getSchema($option = '')
  {
    if ($this->document->doctype AND $this->document instanceof \DOMDocument) {
      if (!$option) {
        return $this->document->documentElement->getAttribute("xsi:noNamespaceSchemaLocation"); // kalau document di loadXML, tidak bisa pakai fungsi getAttributeNS().
      } elseif ($option === 'filename') {
        $schema = $this->document->documentElement->getAttribute("xsi:noNamespaceSchemaLocation"); // kalau document di loadXML, tidak bisa pakai fungsi getAttributeNS().
        preg_match("/\w+.xsd/", $schema, $schema);
        if (!empty($schema)) $schema = $schema[0];
        return $schema;
      }
    }
  }

  /**
   * get and set Filename
   * @return string
   */
  public function getFilename(): string
  {
    if ($this->document instanceof \DOMDocument) {
      $initial = $this->getInitial();
      $domXpath = new \DOMXPath($this->document);
      $ident = $domXpath->evaluate("identAndStatusSection/{$initial}Address/{$initial}Ident");
      if ($ident[0]) {
        // go to function resolve_dmlIdent, resolve_pmIdent, resolve_dmIdent, resolve_imfIdent
        $docIdent = call_user_func(CSDBStatic::class . "::resolve_" . $initial . "Ident", [$ident[0]]); //  argument#0 domElement / array, argument#1 prefix, argument#2 format
      }
      return $this->filename = $docIdent;
    } else {
      return $this->filename = $this->document['filename'];
    }
  }

  /**
   * get and set Initial
   * @return string
   */
  public function getInitial() :string
  {
    if ($this->document instanceof \DOMDocument) {
      $initial = $this->document->doctype->nodeName;
      $initial = $initial === 'dmodule' ? 'dm' : ($initial === 'icnmetadata' ? 'imf' : $initial);
      return $this->initial = $initial;
    }
  }
  
  /**
   * get and set path
   * @return string
   */
  public function getPath() :string
  {
    if(empty($this->breakDownURI)){
      $this->breakDownURI = Helper::analyzeURI($this->document->baseURI);
    }
    return $this->path = $this->breakDownURI['path'];
  }

  public function getSC($return = 'text'): string
  {
    if ($this->document instanceof \DOMDocument) {
      $domXpath = new \DOMXpath($this->document);
      $sc = $domXpath->evaluate("string(//identAndStatusSection/descendant::security/@securityClassification)");
    } elseif ($this->document instanceof \DOMElement) {
      $sc = $this->document->getAttribute('securityClassification');
    } else {
      return '';
    }

    if ($return === 'number') {
      return $sc;
    } elseif ($return === 'integer') {
      return (int) $sc;
    } elseif ($return === 'text') {
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

  public function getTitle($child = ''): string
  {
    if (!$this->document or !($this->document instanceof DOMDocument)) return '';
    $domXpath = new \DOMXPath($this->document);
    $title = '';
    $initial = '';
    switch ($this->document->doctype) {
      case 'dmodule':
        $initial = 'dm';
        break;
      case 'pm':
        $initial = 'pm';
        break;
      default:
        return '';
        break;
    }
    $title = $domXpath->evaluate("//identAndStatusSection/{$initial}Address/{$initial}Title")[0];
    $title = call_user_func_array(CSDBStatic::class . "::resolve_{$initial}Title", [$title, $child]);
    return $title;
  }

  public function getStatus($child = ''): string
  {
    switch ($child) {
      case 'applic':
        // return $this->getApplicability();
        return '';
        break;
      case 'qualityAssurance': // return json
        $doctype = $this->document->doctype;
        $doctype = $doctype === 'dmodule' ? 'dm' : $doctype;
        $qas = $this->document->getElementsByTagName("{$doctype}Status")[0]->getElementsByTagName($child);
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
      default:
        return '';
        break;
    }
  }

  public function getBrexDm() :self
  {
    if(!($this->document instanceof \DOMDocument) OR !$this->document->doctype){
      return new CSDBObject("5.0");
    }
    $domXpath = new \DOMXPath($this->document);
    $brexDmRef = $domXpath->evaluate("//identAndStatusSection/{$this->initial}Status/brexDmRef")[0];
    $brexDmRef = CSDBStatic::resolve_dmIdent($brexDmRef);
    if(!$brexDmRef) return new CSDBObject("5.0");
    
    $BREXObject = new CSDBObject("5.0");
    $BREXObject->load($this->path . DIRECTORY_SEPARATOR . $brexDmRef);
    return $BREXObject;
  }

  /**
   * resolving applic element
   * @param DOMElement $doc berupa applic
   * @param int $useDisplayText 0,1,2. jika satu itu string HARUS pakai display Text. Jika dua itu optional. Artinya jika displayText tidak ada, akan mengambil assert untuk test
   * @return string
   */
  public function getApplicability(mixed $applic, bool $keppOneByOne = false, bool $useDisplayName = true ,int $useDisplayText = 2) :string
  {
    if (empty($applic)) return '';
    if (is_array($applic)) {
      $applic = $applic[0];
    }
    if($useDisplayText){
      if($applic->firstElementChild->tagName === 'displayText'){
        $displayText = '';
        foreach($applic->firstElementChild->childNodes as $simplePara){
          $displayText .= ', ' . $simplePara->textContent;
        }
        return ltrim($displayText, ', ');
      }
      if($useDisplayText === 1){
        return '';
      }
    }

    // untuk assert
    $arrayify = $this->arrayify_applic($applic, $keppOneByOne, $useDisplayName);
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
    // dd($text);
    return $text; // return string "text". eg.: $arrayify = ["evaluate" => ["text" => string, 'andOr' => String, 'children' => array]];
  }

  /**
   * @return array
   */
  private function arrayify_applic(\DOMElement $applic, $keepOneByOne = false, $useDisplayName = true) :array
  {
    $doc = $this->document;
    $path = $this->getPath();
    $domXpath = new \DOMXPath($doc);
    $dmRefIdent = $domXpath->evaluate("//identAndStatusSection/descendant::applicCrossRefTableRef/descendant::dmRefIdent")[0];
    $ACTFilename = CSDBStatic::resolve_dmIdent($dmRefIdent);
    $this->ACTdoc = new \DOMDocument();
    if($this->ACTdoc->load($path . DIRECTORY_SEPARATOR . $ACTFilename)){
      $domxpath = new \DOMXPath($this->ACTdoc);
      $dmRefIdent = $domxpath->evaluate("//identAndStatusSection/descendant::applicCrossRefTableRef/descendant::dmRefIdent")[0];
      $CCTFilename = CSDBStatic::resolve_dmIdent($dmRefIdent);
      $this->CCTdoc->load($path . DIRECTORY_SEPARATOR . $CCTFilename);
    }

    $id = $applic->getAttribute('id');
    $childApplic = Helper::children($applic);
    $result = [];
    foreach ($childApplic as $child) {
      $result[$child->tagName] = $this->resolve_childApplic($child, $keepOneByOne, $useDisplayName);
    }
    return ($id) ? ($applicability[$id] = $result) : $applicability[] = $result;
  }

  /**
   * @return array containing ['text' => String, ...]
   */
  private function resolve_childApplic(\DOMElement $child, $keepOneByOne, $useDisplayName)
  {
    switch ($child->tagName) {
      case 'displayText':
        $displayText = '';
        foreach ($child->childNodes as $simplePara) {
          $displayText .= ', ' . $simplePara->textContent;
        }
        $displayText = rtrim($displayText, ", ");
        $displayText = ltrim($displayText, ", ");
        return ["text" => $displayText];
        break;
      case 'assert':
        return $this->test_assert($child, $keepOneByOne, $useDisplayName);
        break;
      case 'evaluate':
        return $this->test_evaluate($child, $keepOneByOne, $useDisplayName);
        break;
      default: 
        return '';        
    }
  }

  /**
   * saat ini, $PCT doc masih useless
   * kalau test fail, key 'text' akan di isi oleh <assert> text content dan status menjadi 'success'. Sehingga saat di <evaluate> akan true;
   * @param bool $keepOneByOne 
   * @return array ['text' => String, '%STATUS' => String ('success' or 'fail'), '%APPLICPROPERTYTYPE' => String, '%APPLICPROPERTYIDENT' => String, %APPLICPROPERTYVALUES' => String];
   */
  private function test_assert(\DOMElement $assert, bool $keepOneByOne = false, bool $useDisplayName = true) :array
  {
    foreach ($assert->attributes as $att) {
      if (!in_array($att->nodeName, ['applicPropertyIdent', 'applicPropertyType', 'applicPropertyValues'])) {
        return ['text' => $assert->textContent];
      }
    }

    $applicPropertyIdent = $assert->getAttribute('applicPropertyIdent');
    $applicPropertyType = $assert->getAttribute('applicPropertyType');
    $applicPropertyValues = $assert->getAttribute('applicPropertyValues');


    // #1 getApplicPropertyValuesFromCrossRefTable
    // validation CCTdoc
    $crossRefTable = ($applicPropertyType === 'prodattr') ? $this->ACTdoc : $this->CCTdoc;
    if (!$crossRefTable->documentElement) {
      $message = ($applicPropertyType === 'prodattr' ? "ACT " : "CCT")."document is not available in CSDB,";
      CSDBError::setError('getApplicability', $message);
      return ['text' => ''];
    }

    $crossRefTableDomXpath = new \DOMXPath($crossRefTable);
    if (str_contains(($schema = $crossRefTable->firstElementChild->getAttribute('xsi:noNamespaceSchemaLocation')), 'appliccrossreftable.xsd')) {
      $query_enum = "//enumeration[parent::*/@id = '{$applicPropertyIdent}']/@applicPropertyValues";
      $valueDataType = $crossRefTableDomXpath->evaluate("//productAttribute[@id = '{$applicPropertyIdent}']");
      $valueDataType = (count($valueDataType) > 0) ? ($valueDataType[0]->getAttribute('valueDataType') ?? null) : null;

      $displayName = $crossRefTableDomXpath->evaluate("//productAttribute[@id = '{$applicPropertyIdent}']/displayName");
      $displayName = isset($displayName[0]) ? $displayName[0]->textContent : '';
    } elseif (str_contains(($schema = $crossRefTable->firstElementChild->getAttribute('xsi:noNamespaceSchemaLocation')), 'condcrossreftable.xsd')) {
      $query_condTypeRefId = "//cond[@id = '{$applicPropertyIdent}']/@condTypeRefId";
      $condTypeRefId = $crossRefTableDomXpath->evaluate($query_condTypeRefId);
      $condTypeRefId = $condTypeRefId[0]->value;
      $query_enum = "//enumeration[parent::*/@id = '{$condTypeRefId}']/@applicPropertyValues";

      $valueDataType = $crossRefTableDomXpath->evaluate("//condType[@id = '{$condTypeRefId}']");
      $valueDataType = (count($valueDataType) > 0) ? ($valueDataType[0]->getAttribute('valueDataType') ?? null) : null;

      $displayName = $crossRefTableDomXpath->evaluate("//condType[@id = '{$condTypeRefId}']/displayName");
      $displayName = isset($displayName[0]) ? $displayName[0]->textContent : '';
    } else {
      return ['text' => ''];
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

    // #2 generateValue for Nominal and Produced/actual value
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
    $successValues = array();
    $failValues = array();
    if (!empty($nominalValues) and !empty($producedValues)) {
      $status = 'success';
      foreach ($producedValues as $value) {
        // walaupun aday ang ga match antara produced dan nominal values, tidak membuat semuanya false
        // $testedValues[] = $value;
        // if (!in_array($value, $nominalValues)) $status = 'fail'; // jika ada yang tidak sama, maka dikasi status fail, tapi tetap masuk ke testedValue. Intinya testedValues = produced Values

        // jika ada yang tidak sama, maka dikasi status fail. Value yang tidak sama akan di pisah;
        if (!in_array($value, $nominalValues)) {
          $status = 'fail';
          $failValues[] = $value;
        } else {
          $successValues[] = $value;
        }
      }

      if (in_array($applicPropertyIdent, ['SERIALNUMBER', 'Serialnumber', 'serialnumber', 'serialNumber', 'SerialNumber', 'SERIAL_NUMBER', 'Serial_umber', 'serial_number', 'serial_Number', 'Serial_Number'])) {
        $translator = function ($values) use ($keepOneByOne, $pattern) {
          // ubah keep nya jika ingin oneByOne atau tidak
          $oneByOne = false;
          $length = count($values);
          $s = [];
          $i = 0;
          $span = '-';
          while (isset($values[$i])) {
            $s[] = $values[$i];
            if ($keepOneByOne and ($i < $length - 1)) $s[] = ', ';

            if (
              isset($values[$i + 1]) and
              (($values[$i + 1] - $values[$i]) >= 1)
            ) {
              if ((count($s) > 1) and !$oneByOne) {
                array_pop($s);
                if ($keepOneByOne) $s[] = ', ';
                $oneByOne = false;
              } else {
                // $keepOneByOne ? null : ($s[] = ' through ');
                $keepOneByOne ? null : ($s[] = $span);
              }
              if (($values[$i + 1] - $values[$i]) >= 2) {
                if (!$keepOneByOne) $s[] = $values[$i];
                if (!$keepOneByOne) $s[] = ', ';
                $oneByOne =  true;
              } else {
                $oneByOne = ($keepOneByOne) ? true : false;
              }
            }
            $i++;
          }
          foreach ($s as $k => $v) {
            if ($v === $span) {
              // maksudnya jika N011 ~ N012, akan diganti menjadi N011, N012 (ganti tilde pakai comma);
              if (abs($s[$k + 1] - $s[$k - 1]) === 1) {
                $s[$k] = ', ';
              }
              // maksudnya jika N011 ~ N011, akan diganti menjadi N011, (hapus salah satu karena sama-sama N011);
              elseif (abs($s[$k + 1] - $s[$k - 1]) === 0) {
                $s[$k] = '';
                $s[$k + 1] = '';
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
          $s = (join("", $s));
          return $s;
        };
        $s = $translator($successValues);
        if ($status === 'fail') {
          $testedValues['%MESSAGE'] = "ERROR: '$applicPropertyIdent' only contains $s and does not contains such ";
          $s = $translator($failValues);
          $testedValues['%MESSAGE'] .= $s;
          $testedValues['text'] = '';
        } 
        else {
          $testedValues['text'] = $s;
        }
      } else {
        $r = join(", ", $successValues);
        $testedValues['text'] = $r;
        if ($status === 'fail') {
          $r = join(", ", $failValues);
          $testedValues['%MESSAGE'] = "ERROR: For '$applicPropertyIdent' does not contains such $r";
        }
      }

      if($useDisplayName AND $status === 'success'){
        $testedValues['text'] = $displayName ? ($displayName. ": " . $testedValues['text']) : $testedValues['text'];
      }
      if($status === 'fail'){
        $testedValues['text'] = !empty($assert->textContent) ? $assert->textContent: $testedValues['text'];
        $status = empty($testedValues['text']) ? $status : 'success';
      }
      $testedValues['%STATUS'] = $status;
      $testedValues['%APPLICPROPERTYTYPE'] = $applicPropertyType;
      $testedValues['%APPLICPROPERTYIDENT'] = $applicPropertyIdent;
      $testedValues['%APPLICPROPERTYVALUES'] = $applicPropertyValues;
    }
    // $ret = array($applicPropertyIdent => $testedValues);
    // return $ret;
    // dump($testedValues);
    return $testedValues;
  }

  /**
   * DEPRECIATED. Dipindah ke ./Main/Helper class
   * @return array ['text' => string, 'andOr' => String, 'children' => array contain evaluated child]
   */
  private function test_evaluate(\DOMElement $evaluate, bool $keepOneByOne = false, $useDisplayName = true)
  {
    $children = Helper::children($evaluate);
    foreach ($children as $child) {
      $resolved[] = $this->resolve_childApplic($child, $keepOneByOne, $useDisplayName);
    }
    $andOr = $evaluate->getAttribute('andOr');
    $text = '';
    $loop = 0;
    $failtext = '';

    if ($andOr === 'and') {
      $isFail = array_filter($resolved, (fn($r) => isset($r['%STATUS']) AND $r['%STATUS']  === 'fail' ? $r : false));
      if (!empty($isFail)) {
        return ['text' => '', 'andOr' => $andOr, 'children' => $resolved];
      }
    }

    $evaluatedElement = [];
    while (isset($resolved[$loop])) {
      if (count($resolved) > 2) {
        $separator = isset($resolved[$loop + 1]) ? ', ' : ", {$andOr} ";
      } else {
        $separator = " {$andOr} ";
      }

      if ($andOr === 'or') {
        if ($resolved[$loop]['%STATUS'] === 'success') {
          if ($resolved[$loop]['text']) {
            $text .= $separator . $resolved[$loop]['text'];
          }
        } else {
          if ($resolved[$loop]['text']) {
            $failtext .= $separator . $resolved[$loop]['text'];
          }
        }
      } else {
        $text .= $separator . $resolved[$loop]['text'];
      }
      $evaluatedElement[] = $resolved[$loop];
      $loop++;
    }

    $text = ltrim($text, $separator);
    return ['text' => '('.$text.')', 'andOr' => $andOr, 'children' => $evaluatedElement];
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
  public function getEntityIdentFromId(string $id, $return = 'string') :array
  {
    if(!$this->document) return [];
    $domXpath = new \DOMXPath($this->document);
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
   * @return Array contains all dmlEntry or array contain decode dmlEntry result
   */
  public function identifyDmlEntries(string $name = '') :array
  {
    if(!$this->document) return [];
    $dmlEntries = $this->document->getElementsByTagName('dmlEntry');
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

  /**
   * @return bool
   */
  public function commit() :bool
  {
    if(!$this->document) return false;
    $initial = $this->document->doctype;
    $initial = $initial === 'dmodule' ? 'dm' : ($initial === 'icnmetadata' ? "imf" : '');
    $domxpath = new \DOMXPath($this->document);
    $issueInfo = $domxpath->evaluate("//identAndStatusSection/{$initial}Address/{$initial}Ident/issueInfo")[0];
    $inWork = (int)$issueInfo->getAttribute('inWork');
    if ($inWork == 0) {
      CSDBError::setError('commit', "{$this->filename} cannot be commited due to the current inWork is '00'.");
      return false;
    }
    if ($inWork == 99) ($inWork = 'AA');
    else ($inWork++);
    $inWork = str_pad($inWork, 2, '0', STR_PAD_LEFT);
    $issueInfo->setAttribute('inWork', $inWork);
    return true;
  }

  /**
   * helper function untuk crew.xsl
   * ini tidak bisa di pindah karena bukan static method
   * * sepertinya bisa dijadikan static, sehingga fungsinya lebih baik ditaruh di CsdbModel saja
   */
  public function setLastPositionCrewDrillStep(int $num)
  {
    $this->lastPositionCrewDrillStep = $num;
  }

  /**
   * helper function untuk crew.xsl
   * ini tidak bisa di pindah karena bukan static method
   * sepertinya bisa dijadikan static, sehingga fungsinya lebih baik ditaruh di CsdbModel saja
   */
  public function getLastPositionCrewDrillStep()
  {
    return $this->lastPositionCrewDrillStep ?? 0;
  }

  /**
   * @param string $xslFile is absolute path of xsl file
   * @param array $params is associative array where is inclusion for XSL processor
   * @param string $output is 'html', 'pdf'
   * @return string
   */
  public function transform_to_xml(string $xslFile, array $params = [], string $output = 'html') :string
  {
    $xsl = new \DOMDocument();
    if(!$xsl->load($xslFile)){
      return '';
    }
    $xsltproc = new \XSLTProcessor();
    $xsltproc->importStylesheet($xsl);

    $xsltproc->registerPHPFunctions((fn () => array_map(fn ($name) => CSDBStatic::class . "::$name", get_class_methods(CSDBStatic::class)))());
    $xsltproc->registerPHPFunctions((fn () => array_map(fn ($name) => Helper::class . "::$name", get_class_methods(Helper::class)))());
    $xsltproc->registerPHPFunctions((fn () => array_map(fn ($name) => self::class . "::$name", get_class_methods(self::class)))());
    $xsltproc->registerPHPFunctions();    

    foreach ($params as $key => $param) {
      $xsltproc->setParameter('', $key, $param);
    }
    $transformed = $xsltproc->transformToDoc($this->document);
    // dd($transformed);
    if(!$transformed) return '';
    $bookmarkTree_el = $transformed->getElementsByTagNameNS('http://www.w3.org/1999/XSL/Format', 'bookmark-tree')[0];
    
    $new_bookmarks = CSDBStatic::transformBookmark_to_xml();
    if($new_bookmarks){
      $new_bookmarks = $new_bookmarks->documentElement->cloneNode(true);
      $imported = $bookmarkTree_el->ownerDocument->importNode($new_bookmarks, true);
      $bookmarkTree_el->replaceWith($imported);
    } else {
      $bookmarkTree_el->remove();
    }
    
    $transformed->preserveWhiteSpace = false;
    $transformed = $transformed->saveXML();
    $transformed = preg_replace("/\s+/m", ' ', $transformed);
    return $transformed;    
  }

  // public function transform_to_foxml(string $xslFile, array $params = [], string $output = 'html') :string
  // {
  //   $xsl = new \DOMDocument();
  //   if(!$xsl->load($xslFile)){
  //     return '';
  //   }

  //   $xsltproc = new \XSLTProcessor();
  //   $xsltproc->importStylesheet($xsl);

  //   $xsltproc->registerPHPFunctions((fn () => array_map(fn ($name) => CSDBStatic::class . "::$name", get_class_methods(CSDBStatic::class)))());
  //   $xsltproc->registerPHPFunctions((fn () => array_map(fn ($name) => Helper::class . "::$name", get_class_methods(Helper::class)))());
  //   $xsltproc->registerPHPFunctions((fn () => array_map(fn ($name) => self::class . "::$name", get_class_methods(self::class)))());
  //   $xsltproc->registerPHPFunctions();    

  //   foreach ($params as $key => $param) {
  //     $xsltproc->setParameter('', $key, $param);
  //   }

  //   $transformed = str_replace("#ln;", chr(10), $xsltproc->transformToXml($this->document));
  //   $transformed = $xsltproc->transformToXml($this->document);
  //   return $transformed;    
  // }
  

  // fungsi transform_HTML // sepertinya di taruh di Main\CSDB class saja 
  // fungsi transfrom_PDF // sepertinya di taruh di Main\CSDB class saja

  // fungsi autoGeneratedUniqueIdentifier for ICN
  
  /**
   * diperlukan untuk di XSL
   */
  public function set_pmEntryTitle(string $text)
  {
    $this->pmEntryTitle = $text;
  }

  /**
   * diperlukan untuk di XSL
   */
  public function get_pmEntryTitle()
  {
    return $this->pmEntryTitle;
  }
}
