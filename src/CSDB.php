<?php 

namespace Ptdi\Mpub;

use DOMElement;
use DOMXPath;
use Exception;

// abstract class CSDB {
//  untuk keperluan program baru, ini dibuat tidak abstact
class CSDB {
  
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

  public static function resolve_issueType($issueType, string $option = ''){
    if(!$issueType) return '';
    // untuk mengakomodir penggunaan fungsi di XSLT
    if(is_array($issueType)){
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

  public static function resolve_dmIdent(\DOMElement $dmIdent){
    $dmCode = self::resolve_dmCode($dmIdent->getElementsByTagName('dmCode')[0]);
    $issueInfo = ($if = self::resolve_issueInfo($dmIdent->getElementsByTagName('issueInfo')[0])) ? "_".$if : '';
    $languange = ($lg = self::resolve_languange($dmIdent->getElementsByTagName('language')[0])) ? "_".$lg : '';

    return strtoupper($dmCode.$issueInfo.$languange).".xml";

  }

  public static function getApplicability(\DOMDocument $doc, string $absolute_path_csdbInput = ''){
    $CSDB = new self();

    $domxpath = new DOMXPath($doc);
    $dmRefIdent = $domxpath->evaluate("//identAndStatusSection/dmStatus/applicCrossRefTableRef/descendant::dmRefIdent")[0];
    $ACTdoc = self::importDocument($absolute_path_csdbInput. DIRECTORY_SEPARATOR. self::resolve_dmIdent($dmRefIdent),'', 'dmodule');
    $CSDB->ACTdoc = $ACTdoc;

    $actdomxpath = new DOMXPath($ACTdoc);
    $dmRefIdent = $actdomxpath->evaluate("//content/applicCrossRefTable/condCrossRefTableRef/descendant::dmRefIdent")[0];
    $CCTdoc =  self::importDocument($absolute_path_csdbInput. DIRECTORY_SEPARATOR. self::resolve_dmIdent($dmRefIdent),'', 'dmodule');
    $CSDB->CCTdoc = $CCTdoc;

    $dmRefIdent = $actdomxpath->evaluate("//content/applicCrossRefTable/productCrossRefTableRef/descendant::dmRefIdent")[0];
    $PCTdoc =  self::importDocument($absolute_path_csdbInput. DIRECTORY_SEPARATOR. self::resolve_dmIdent($dmRefIdent),'', 'dmodule');
    $CSDB->PCTdoc = $PCTdoc;

    $CSDB->applics = array();
    $applics = $domxpath->evaluate("//applic");
    foreach($applics as $applic){
      // $CSDB->applics[] = $applic;
      foreach (self::get_childrenElement($applic) as $child){
        switch ($child->tagName) {
          case 'assert':
            $CSDB->assertTest($child);
            break;
          case 'evaluate':
            # code...
            break;
          case 'displayText':
            break;
        }
      }
    }
    // dd(self::get_childrenElement($applics[0]));
    dd(__CLASS__,__LINE__);
    dd($ACTdoc);

    dd($ACTdoc);
    dd($dmCode, $issueInfo, $languange);
    // dd($ACT_doc);
    return '';
  }

  private function assertTest(\DOMElement $assert){
    foreach($assert->attributes as $att){
      if(!in_array($att->nodeName,['applicPropertyIdent', 'applicPropertyType', 'applicPropertyValues'])){
        return false;
      }
    }

    if($assert->firstChild instanceof \DOMText){
      return ['text' => $assert->firstChild->nodeValue];
    }

    $applicPropertyIdent = $assert->getAttribute('applicPropertyIdent');
    $applicPropertyType = $assert->getAttribute('applicPropertyType');
    $applicPropertyValues = $assert->getAttribute('applicPropertyValues');

    
    // #1 getApplicPropertyValuesFromCrossRefTable
    $crossRefTable = ($applicPropertyType == 'prodattr') ? $this->ACTdoc : $this->CCTdoc;
    $crossRefTableDomXpath = new DOMXPath($crossRefTable);
    if(str_contains(($schema = $crossRefTable->firstElementChild->getAttribute('xsi:noNamespaceSchemaLocation')), 'appliccrossreftable.xsd')){
      $query_enum = "//enumeration[parent::*/@id = '{$applicPropertyIdent}']/@applicPropertyValues";
      $valueDataType = $crossRefTableDomXpath->evaluate("//productAttribute[@id = '{$applicPropertyIdent}']");
      $valueDataType = (count($valueDataType) > 0) ? ($valueDataType[0]->getAttribute('valueDataType') ?? null ) : null;
    } 
    elseif (str_contains(($schema = $crossRefTable->firstElementChild->getAttribute('xsi:noNamespaceSchemaLocation')), 'condcrossreftable.xsd') ){
      $query_condTypeRefId = "//cond[@id = '{$applicPropertyIdent}']/@condTypeRefId";
      $condTypeRefId = $crossRefTableDomXpath->evaluate($query_condTypeRefId);
      $condTypeRefId = $condTypeRefId[0]->value;
      $query_enum = "//enumeration[parent::*/@id = '{$condTypeRefId}']/@applicPropertyValues";

      $valueDataType = $crossRefTableDomXpath->evaluate("//condType[@id = '{$condTypeRefId}']");
      $valueDataType = (count($valueDataType) > 0) ? ($valueDataType[0]->getAttribute('valueDataType') ?? null ) : null;
    }
    else {
      return false;
    }
    $enums = $crossRefTableDomXpath->evaluate($query_enum);
    $applicPropertyValuesFromCrossRefTable = '';
    $pattern = $crossRefTableDomXpath->evaluate("//@valuePattern[parent::*/@id = '$applicPropertyIdent']");
    $pattern = (count($pattern) > 0) ? $pattern[0]->nodeValue : null;
    if(count($enums) == 0){
      // isexistValuePattern()
      if($pattern){
        $propertyValue = trim($pattern);
        $propertyValue = substr_replace($propertyValue, "", 0,1);
        $propertyValue = substr_replace($propertyValue, "", strlen($propertyValue)-1,1); 
        $applicPropertyValuesFromCrossRefTable = $propertyValue;
      }
    } else {
      $applicPropertyValuesFromCrossRefTable = $enums[0]->value;
    }

    // #2 generateValue for Nominal and Prodcued/actual value
    $generateValue = function(string $applicPropertyValues) use($valueDataType, $pattern) {
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
      foreach($matches as $values){
        // get start value for iterating
        $start = null;
        $end = null;
        $singleValue = null;
        if($valueDataType != 'string'){
          $start = $values[1];
          $end = $values[2];
          $singleValue = (isset($values[3]) AND $values[3]) ? $values[3] : null;
        } 
        else {
          if(!empty($pattern)){
            preg_match_all($pattern, $values[1], $matches, PREG_SET_ORDER);
            $start = isset($matches[0][0]) ? $matches[0][1] : null;
            preg_match_all($pattern, $values[2], $matches, PREG_SET_ORDER);
            $end = isset($matches[0][0]) ? $matches[0][1] : null;
            if((isset($values[3]) AND $values[3])){
              preg_match_all($pattern, $values[2], $matches, PREG_SET_ORDER);
              $singleValue = isset($matches[0][0]) ? $matches[0][1] : null;
            }
          }
        }
        if($start AND $end){
          $range = range($start, $end);
          foreach($range as $v) ($values_generated[] = $v);
        }
        if($singleValue){
          $values_generated[] = $singleValue;
        }
      }
      return $values_generated;
    };

    $nominalValues = $generateValue($applicPropertyValuesFromCrossRefTable);
    $producedValues = $generateValue($applicPropertyValues);

    $testedValues = array();
    if(!empty($nominalValues) AND !empty($producedValues)){
      foreach($producedValues as $value){
        if(in_array($value, $nominalValues)){
          $testedValues[] = $value; // berbeda dengan script awal. Yang ini, walaupun aday ang ga match antara produced dan nominal values, tidak membuat semuanya false
        }
      }
      
      if(in_array($applicPropertyIdent, ['SERIALNUMBER', 'Serialnumber', 'serialnumber', 'serialNumber', 'SerialNumber', 'SERIAL_NUMBER', 'Serial_umber', 'serial_number', 'serial_Number', 'Serial_Number'])){
        $keep = false; // ubah keep nya jika ingin oneByOne atau tidak
        $oneByOne = false;
        $s = [];
        $i = 0;
        while(isset($testedValues[$i])){;
          
          $s[] = $testedValues[$i];
          if(isset($testedValues[$i+1]) AND 
            (($testedValues[$i+1] - $testedValues[$i]) >= 1) 
            ){
              if( (count($s) > 1) AND !$oneByOne){
                array_pop($s);
                $oneByOne = false;
              } else {
                $keep ? null : ($s[] = 'through');
              }
              if(($testedValues[$i+1] - $testedValues[$i]) >= 2){
                $s[] = $testedValues[$i];
                $oneByOne =  true;
              } 
              else {
                $oneByOne = ($keep) ? true : false;
                // $oneByOne = false;
              }
            }
          $i++;
        }
        if($pattern){
          $regex = "/.*(\(.*\)).*/"; // akan match dengan yang didalam kurungnya /N(219)/ akan match dengan 219
          preg_match_all($regex, $pattern, $structure, PREG_SET_ORDER, 0);
        }
        foreach($s as $n => $v){
          if(!is_string($v)){
            $s[$n] = sprintf('%03d',$s[$n]);
            if($pattern){    
              if($structure){
                $newValue = str_replace($structure[0][1], $s[$n], $structure[0][0]); // $newValue = "/N001/"
                $newValue = trim($newValue);
                $newValue = substr_replace($newValue, "", 0,1); // delete "/" di depan
                $newValue = substr_replace($newValue, "", strlen($newValue)-1,1); // delete "/" dibelakang
                $s[$n] = $newValue;
              }
            }
          }
        }
        dd('baru selesai sampai Assert.php line 94', __CLASS__, __LINE__);
        
        dd($s,$testedValues);
      }
    }







    // $nominalValues = 
    // dd($crossRefTable);


    

    
  }
}