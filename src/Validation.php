<?php 

namespace Ptdi\Mpub;

use DOMDocument;
use DOMElement;
use DOMNodeList;
use DOMXPath;
use Ptdi\Mpub\Schema\Schema;

/**
 * 
 */
trait Validation
{
  public static function validate(string $type = null, $doc, $validator = '', $absolute_path = '')
  {
    if($type == 'BREX'){
      return self::validateByBrex($doc, $validator, $absolute_path);
    }
    elseif($type == 'BREX-NONCONTEXT'){
      return self::validateByBrexForNonContext($doc, $absolute_path);
    }
    elseif($type == 'XSI'){
      return self::validateBySchema($doc, $validator);
    }
  }

  /**
   * jika $object type tidak termasuk bagian yang perlu di validasi dmrl, maka dianggap true
   * if true, return [true, ''];
   * else, return [false, [$text], 'info/dmrl/entry'];
   * 'info' ini semacam kode error tapi tidak fatal
   */
  public static function validateByDMRL( $dmrlpath = '', $dmrlfilename = '', string $entryFilename, string $type = '')
  {
    // ini adalah firstElementChild pof \DOMDocument
    if (!in_array($type, ['dmodule', 'pm', 'infoEntity', 'comment', 'dml'])) {
      return [true, ''];
    }
    $dmrl_dom = CSDB::importDocument($dmrlpath, $dmrlfilename);
    if(!$dmrl_dom) return [false, 'No such DMRL', 'dmrl'];
    if(!CSDB::validate('XSI', $dmrl_dom, 'dml.xsd')){
      $err = CSDB::get_errors(true, 'validateBySchema');
      $err[] = "DMRL must be comply to dml.xsd";
      return [false, join(", ", $err), 'dmrl'];
    }

    $xpath = new \DOMXPath($dmrl_dom);
    $dmlEntries = $xpath->evaluate("//dmlEntry");
    $nominal_idents = array();
    foreach ($dmlEntries as $key => $dmlEntry) {
      $ident = str_replace("Ref", '', $dmlEntry->firstElementChild->tagName);
      if ($dmlEntry->firstElementChild->tagName == 'infoEntityRef') {
        $nominal_idents[] = $dmlEntry->firstElementChild->getAttribute('infoEntityRefIdent');
      } else {
        $nominal_idents[] = call_user_func_array(CSDB::class . "::resolve_{$ident}Ident", [$dmlEntry->getElementsByTagName("{$ident}RefIdent")[0]]);
      }
    }
    $actual_ident = preg_replace("/_\d{3,5}-\d{2}|_[A-Za-z]{2,3}-[A-Z]{2}/", '', $entryFilename); // untuk membersihkan inwork dan issue number pada filename
    if (!in_array($actual_ident, $nominal_idents)) {
      $actual_ident = preg_replace('/\.\w+$/', '', $actual_ident);
      return [false, "{$actual_ident} is not required by the DMRL.", 'entry'];
    }
    return [true, ''];
  }

  private static function validateByBrexForNonContext(string $doc, $absolute_path){
    return true;
  }

  private static function validateBySchema(\DOMDocument $doc, string $validator = '')
  {
    libxml_use_internal_errors(true);

    if($validator == ''){
      $validator = CSDB::getSchemaUsed($doc,'filename');
    }
    // $schema = new DOMDocument();
    // $schema->strictErrorChecking = false;
    // $schema->load(__DIR__.DIRECTORY_SEPARATOR."Schema".DIRECTORY_SEPARATOR.$validator);
    // $schema = CSDB::importDocument(__DIR__.DIRECTORY_SEPARATOR."Schema".DIRECTORY_SEPARATOR, $validator,null,'');
    $schema = CSDB::importDocument(__DIR__.DIRECTORY_SEPARATOR."Schema", $validator,null,'');
    if(!$schema) {
      CSDB::setError('validateBySchema', "schema cannot be identified");
      return false;
    }
    @$doc->schemaValidateSource($schema->C14N(), LIBXML_PARSEHUGE);
    $errors = libxml_get_errors();
    $errors = array_filter($errors, (fn($LibXMLError) => $LibXMLError->level > 2 ? true : false)); // supaya error hanya LIBXML_ERR_FATAL doang
    if(!empty($errors)){
      CSDB::setError('validateBySchema', "error during validate by xsi in file ".CSDB::resolve_DocIdent($doc).".");
      foreach ($errors as $err) {
        CSDB::setError('validateBySchema', "line: {$err->line}; message: {$err->message}");
      }
      return false;
    } else {
      return true;
    }    
  }

  /**
   * @return false with errors that you can get by function get_errors
   * @return true
   */
  private static function validateByBrex(\DOMDocument $doc, $validator = null, $absolute_path = null)
  {
    $domXpath = new \DOMXPath($doc);
    $brexDoc = $domXpath->evaluate("//identAndStatusSection/descendant::brexDmRef");
    if($brexDoc->length == 0){
      $docIdent = CSDB::resolve_DocIdent($doc);
      CSDB::setError('validateByBrex', "element brexDmRef cannot found in identAndStatusSection of {$docIdent}");
      return false;
    } else {
      $brexDoc = $brexDoc[0];
      $brexDoc = CSDB::resolve_dmIdent($brexDoc);
      $path = $absolute_path ?? Helper::analyzeURI($doc->baseURI)['path'];
      $brexDoc = CSDB::importDocument($path."/",$brexDoc,null,'','brexDoc');
      if($errors = CSDB::get_errors(true,'file_exists')){
        CSDB::setError('validateByBrex', "error during validate by brex in file ".CSDB::resolve_DocIdent($doc).".");
        foreach($errors as $err){
          CSDB::setError('validateByBrex', $err);
        }
        return false;
      }
    }
    
    $schema = CSDB::getSchemaUsed($brexDoc,'filename');
    $domXpath = new DOMXPath($brexDoc);
    $contexRules = $domXpath->evaluate("//contextRules[not(@rulesContext)] | //contextRules[@rulesContext = '{$schema}']");

    foreach($contexRules as $contextRule){
      $structureObjectRuleGroup = $contextRule->firstElementChild;
      $structureObjectRules = CSDB::get_childrenElement($structureObjectRuleGroup,'');
      foreach ($structureObjectRules as $structureObjectRule){
        self::validateByStructureObjectRule($doc, $structureObjectRule, $path);
      }
    }
    $errors = CSDB::get_errors(false,'validateByBrex');
    return empty($errors) ? true: false;
  }

  /**
   * php tidak bisa pakai fungsi xpath //applic/child::*\/name(), melainkan local-name(//applic/child::*)
   * hindari objectPath yang bernilai boolean karena jika dari setiap result ada yang true, padahal yang lain false, maka hasilnya true
   */
  private static function validateByStructureObjectRule(\DOMDocument $doc, \DOMElement $structureObjectRule, $absolute_path = '')
  {
    $docIdent = CSDB::resolve_DocIdent($doc);

    $id = $structureObjectRule->getAttribute(('id'));

    $brDecisionRefs = $structureObjectRule->getElementsByTagName('brDecisionRef'); // DOM Element
    $brDecisionIdentNumber = array();
    foreach ($brDecisionRefs as $brDecisionRef) {
      $brDecisionIdentNumber[] = $brDecisionRef->getAttribute('brDecisionIdentNumber');
    }
    $brDecisionIdentNumber = join(", ", $brDecisionIdentNumber);
    
    // validasi apakah schema nya termasuk di contextRule atau tidak
    $allowedSchema = [];
    foreach ($brDecisionRefs as $brDecisionRef) {
      $brDecisionIdentNumber = $brDecisionRef->getAttribute('brDecisionIdentNumber');
      if($brDecisionRef->firstElementChild){
        $refs = $brDecisionRef->firstElementChild;
        if($refs->firstElementChild->tagName == 'dmRef'){
          $dom = CSDB::importDocument($absolute_path."/", CSDB::resolve_dmIdent($refs->firstElementChild));
          $domXpath = new \DOMXPath($dom);
          $res = $domXpath->evaluate("//brDecision[@brDecisionIdentNumber = '{$brDecisionIdentNumber}']/ancestor::brPara/descendant::s1000dSchemas/@*[. = 1]");
          foreach($res as $schema){
            $allowedSchema[] = str_replace('Xsd', '.xsd', $schema->nodeName);
          }
        }
      }
    }
    if(!empty($allowedSchema)){
      $schmeUsed = CSDB::getSchemaUsed($doc, 'filename');
      // jika tidak ada di allowable schema, maka tidak perlu di validasi (aman)
      if(!in_array($schmeUsed, $allowedSchema)){
        return;
      }
    }


    $objectPath = $structureObjectRule->getElementsByTagName('objectPath')[0]; // DOM Element
    $allowedObjectFlag = $objectPath->getAttribute('allowedObjectFlag');

    $objectUse = $structureObjectRule->getElementsByTagName('objectUse')[0] ?? new DOMElement('foo'); // DOM element

    $objectValues = $structureObjectRule->getElementsByTagName('objectValue'); // nodeList
    $values = array();
    foreach ($objectValues as $v) {
      $valueForm = $v->getAttribute('valueForm');
      $valueAllowed = $v->getAttribute('valueAllowed');
      $valueText = $v->nodeValue;

      $values[] = [
        'valueForm' => $valueForm,
        'valueAllowed' => $valueAllowed,
        'valueText' => $valueText,
      ];
    }
    $objectValues = $values;
    unset($values);

    // jika objectPath result boolean
    $domXpath = new DOMXPath($doc);
    $results = $domXpath->evaluate($objectPath->nodeValue);

    // jika result boolean
    // jika result bernilai benar dan diperbolehkan, maka aman
    if(is_bool($results) AND $results == true AND $allowedObjectFlag == 1){
      return; // kalau true dan $allowedObject=1, misal //originator/@enterpriseCode='0001Z' berarti ga ada masalah (udah sesuai dengan rule)
    }
    // jika result bernilai benar, tapi tidak diperbolehkan, maka fail
    elseif(is_bool($results) AND $results == true AND $allowedObjectFlag == 0) {
      CSDB::setError('validateByBrex', "Document: {$docIdent}, the value does not match. id:{$id}, BR number:{$brDecisionIdentNumber}. Object Use: {$objectUse->nodeValue}");
      return;
    }
    // jika result bernilai true, tapi sunnah, maka aman
    elseif(is_bool($results) AND $results == true AND $allowedObjectFlag == 2){
      return;
    } 
    // jika result bernilai false, maka fail karena harusnya diperbolehkan (harus true)
    elseif(is_bool($results) AND $results == false AND $allowedObjectFlag == 1) {
      CSDB::setError('validateByBrex', "Document: {$docIdent}, the value does not match. id:{$id}, BR number:{$brDecisionIdentNumber}. Object Use: {$objectUse->nodeValue}");
      return;
    } 
    // jika result bernilai false dan juga tidak diperbolehkan, maka aman
    elseif(is_bool($results) AND $results == false AND $allowedObjectFlag == 0) {
      return;
    }
    // jika result bernilai false, tapi sunnah, fail
    elseif(is_bool($results) AND $results == false AND $allowedObjectFlag == 2){
      // harusnya return warning/caution/info saja. Nanti buat CSDB::setInfo()
      return;
    } 
    // jika tidak ada yang ditemukan, berarti aman (tidak perlu di validasi)
    // elseif (count($results) == 0){
    // elseif (is_array($results) AND count($results) == 0){
    elseif (is_iterable($results) AND count($results) == 0){
      return;
    }

    $res = array();
    if(is_iterable($results) AND $results instanceof \DOMNodeList){
      foreach($results as $r){
        $res[]['value'] = $r->nodeValue;
      }
    } else {
      $res[]['value'] = $results;
    }
    $results = $res;
    unset($res);

    // configurasi
    $type = '';
    if($allowedObjectFlag == 0 AND !empty($objectValues)){
      // jika ada objectValue yang match maka fail
      $type = 'value_is_not_allowed';
    }
    elseif($allowedObjectFlag == 0 AND empty($objectValues)){
      // berarti jika ada result yang match, maka fail
      $type = 'objectPath_is_not_allowed';
    }
    elseif($allowedObjectFlag == 1 AND !empty($objectValues)){
      // INI SALAH: berarti jika Xpath pada result match tapi tidak satupun value match, maka fail
      // jika ada result yang tidak match, maka fail      
      $type = 'value_is_allowed';
    }
    elseif($allowedObjectFlag == 1 AND empty($objectValues)){
      // jika results itu null, maka fail. Karena @allowedObjectFlag=1 berarti HARUS match
      $type = 'objectPath_is_allowed';
    }

    if($type == 'objectPath_is_not_allowed'){
      if(count($results) > 0){
        CSDB::setError('validateByBrex', "Document: {$docIdent}, the value does not match. id:{$id}, BR number:{$brDecisionIdentNumber}. Object Use: {$objectUse->nodeValue}");
      }
    }
    elseif($type == 'objectPath_is_allowed'){
      // jika results itu null, maka fail. Karena @allowedObjectFlag=1 berarti HARUS match
      if(count($results) == 0){
        CSDB::setError('validateByBrex', "Document: {$docIdent}, the value does not match. id:{$id}, BR number:{$brDecisionIdentNumber}. Object Use: {$objectUse->nodeValue}");
      }
    }
    
    if($type == 'value_is_not_allowed'){
      foreach($results as $result){
        foreach($objectValues as $value){
          if(isset($value['valueForm']) AND isset($value['valueAllowed'])){
            if($value['valueForm'] == 'single'){
              if($result['value'] == $value['valueAllowed']){
                CSDB::setError('validateByBrex', "Document: {$docIdent}, the value does not match. id:{$id}, BR number:{$brDecisionIdentNumber}. Object Use: {$objectUse->nodeValue} Value Text: {$value['valueText']}");
              }
            }
            elseif($value['valueForm'] == 'pattern'){
              preg_match_all($value['valueAllowed'], $result['value'], $matches);
              // untuk check jika ada value yang tidak kosong (matched) maka fail
              $m = function($matches, $m) use ($docIdent, $id, $brDecisionIdentNumber, $objectUse, $value){
                $k = 0;
                $l = count($matches);
                while(is_array($matches) AND $k < $l){
                  if(is_array($matches[$k]) AND !empty($matches[$k])){
                    $m($matches[$k], $m);
                  }
                  elseif(!empty($matches[$k])){
                    CSDB::setError('validateByBrex', "Document: {$docIdent}, the value does not match. id:{$id}, BR number:{$brDecisionIdentNumber}. Object Use: {$objectUse->nodeValue} Value Text: {$value['valueText']}");
                  }
                  $k++;
                }
              };
              $m($matches, $m);
            }
            elseif($value['valueForm'] == 'range'){
              $range = explode('~', $value['valueAllowed']);
              $range = range($range[0], $range[1]);
              // jika nodeValue ada di range, maka fail (karena @allowedObjectFlag = 0)
              if(in_array($result['value'], $range)){
                CSDB::setError('validateByBrex', "Document: {$docIdent}, the value does not match. id:{$id}, BR number:{$brDecisionIdentNumber}. Object Use: {$objectUse->nodeValue} Value Text: {$value['valueText']}");
              }
            }
          }
        }
      }
    }
    elseif($type == "value_is_allowed"){
      $lengthV = count($objectValues);
      foreach($results as $result){
        $k = 0;
        while(isset($objectValues[$k]) AND $k < $lengthV){
          $value = $objectValues[$k];
          if(isset($value['valueForm']) AND isset($value['valueAllowed'])){
            if($value['valueForm'] == 'single' AND $value['valueAllowed'] == $result['value']){
              break; // berarti udah ada yang match.
            }
            elseif($value['valueForm'] == 'pattern'){
              preg_match_all($value['valueAllowed'], $result['value'], $matches);
              // untuk check jika ada value match maka break. artinya udah ada yang benar
              $m = function($matches) use ($docIdent, $id, $brDecisionIdentNumber, $objectUse, $value){
                $k = 0;
                $l = count($matches);
                while(is_array($matches) AND $k < $l){
                  if(!empty($matches[$k])){
                    return true; // berarti udah ada yang match.
                  }
                  $k++;
                }
              };
              if($m($matches)){
                break; // akan break while
              };
            }
            elseif($value['valueForm'] == 'range'){
              $range = explode('~', $value['valueAllowed']);
              $range = range($range[0], $range[1]);
              if(in_array($result['value'], $range)){
                break; // berarti udah ada yang match.
              }
            }
          }
          $k++;
          if($k == $lengthV){
            CSDB::setError('validateByBrex', "Document: {$docIdent}, the value does not match. id:{$id}, BR number:{$brDecisionIdentNumber}. Object Use: {$objectUse->nodeValue}");
          }
        }
      }
    }
  }

  public static function validateRootname($dom){
    if(!isset($dom->firstElementChild->tagName)){
      CSDB::$errors[__FUNCTION__][] = 'Text must be valid of XML written.';
      return false;
    }
    $rootname = $dom->firstElementChild->tagName;
    if($rootname == 'dmodule'){
      $csdbIdent = $dom->getElementsByTagName('dmIdent')[0];
      $csdb_filename = CSDB::resolve_dmIdent($csdbIdent);
      $ident = 'dmodule';
      $initial = 'dm';
    } 
    elseif ($rootname == 'pm'){
      $csdbIdent = $dom->getElementsByTagName('pmIdent')[0];
      $csdb_filename = CSDB::resolve_pmIdent($csdbIdent);
      $ident = 'pm';
      $initial = 'pm';
    } 
    elseif ($rootname == 'dml'){
      $csdbIdent = $dom->getElementsByTagName('dmlIdent')[0];
      $csdb_filename = CSDB::resolve_dmlIdent($csdbIdent);
      $ident = 'dml';
      $initial = 'dml';
    }
    elseif($rootname == 'icnMetadataFile'){
      $csdbIdent = $dom->getElementsByTagName('imfIdent')[0];
      $csdb_filename = CSDB::resolve_imfIdent($csdbIdent);
      $ident = 'icnMetadataFile';
      $initial = 'imf';
      // $csdbIdent = $dom->getElementsByTagName('imfCode')[0];
      // $csdb_filename = "IMF-".$csdbIdent->getAttribute('imfIdentIcn')."_". $csdbIdent->nextElementSibling->getAttribute('issueNumber')."-". $csdbIdent->nextElementSibling->getAttribute('inWork'). '.xml';
    }
    else {
      CSDB::$errors[__FUNCTION__][] = 'CSDB cannot identified as PM, DM, ICN Meta Data File.';
      return false;
    }
    return [$csdbIdent, $csdb_filename, $ident, $initial];
  }
}
