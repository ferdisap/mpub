<?php

namespace Ptdi\Mpub;

class Helper
{
  // private static function scan($base_doc)
  // {
  //   $an = self::analyzeURI($base_doc->baseURI);
  //   $docXpath = new \DOMXPath($base_doc);
  //   $xpath = '//dmlRef | //dmRef | //pmRef | //infoEntityRef';
  //   $res = $docXpath->evaluate($xpath); 
  //   $uris = [];
  //   $found = [];
  //   $unfound = [];

  //   foreach($res as $k => $r){
  //     $tagName = str_replace('Ref','Ident',$r->tagName);
  //     $name = call_user_func_array(CSDB::class . "::resolve_{$tagName}", [$r]);

  //     $doc = CSDB::importDocument($an['path'], $name);

  //     if($doc){
  //       $uris[] = $an['path']. DIRECTORY_SEPARATOR . $name;
  //       $found[] = $name;
  //       $sc = self::scan($doc);
  //       $uris = array_merge($uris, $sc[0]);
  //       $found = array_merge($unfound, $sc[1]);
  //       $unfound = array_merge($unfound, $sc[2]);
  //     } else {
  //       $unfound[] = $name;
  //     }
  // }
  // return [$uris, $found, $unfound];
  // }

  /**
   * Fungsi ini akan menscan semua (nested) CSDB Object referenced
   * @return Array
   */
  public static function scanObjectRef(\DOMDocument $doc)
  {
    $doc_name = CSDB::resolve_DocIdent($doc);
    $an = self::analyzeURI($doc->baseURI);

    $scan = function ($base_doc) use ($an) {
      $docXpath = new \DOMXPath($base_doc);
      $xpath = '//dmlRef | //dmRef | //pmRef | //infoEntityRef';
      $res = $docXpath->evaluate($xpath);
      // dd($res->length);
      $found = [];
      $unfound = [];
      foreach ($res as $k => $r) {
        $tagName = str_replace('Ref', 'Ident', $r->tagName);
        $name = call_user_func_array(CSDB::class . "::resolve_{$tagName}", [$r]);
        $uri = $an['path'] . DIRECTORY_SEPARATOR . $name;
        if (file_exists($uri)) {
          $found[] = $name;
        } else {
          $unfound[] = $name;
        }
      }
      return [$found, $unfound];
    };
    $scanResult = $scan($doc);
    $found_name = $scanResult[0];
    $unfound_name = $scanResult[1];

    // #1. scan taip hasil temuan document ($doc)
    $loop = 0;
    while (isset($found_name[$loop]) and ($found_doc = (CSDB::importDocument($an['path'], $found_name[$loop])))) {
      $scanResult = $scan($found_doc);
      $found_name = array_merge($scanResult[0], $found_name);
      $unfound_name = array_merge($scanResult[1], $unfound_name);

      $found_name = array_unique($found_name);
      $unfound_name = array_unique($unfound_name);
      $loop++;
    }

    // #2. tambahkan dokumen base di index ke 0;
    array_unshift($found_name, $doc_name);

    return [
      'found' => $found_name,
      'unfound' => $unfound_name,
    ];
  }

  public static function analyzeURI(string $uri)
  {
    preg_match_all('/(^[a-z]+:[\/\\\\\\\\]{1,3})|(.+(?=[\/\\\\]))|([^\/^\\\\]+$)/', $uri, $matches, PREG_UNMATCHED_AS_NULL, 0); // 3 elements

    $protocol = array_values(array_filter($matches[1], fn ($v) => $v));
    $path = array_values(array_filter($matches[2], fn ($v) => $v));
    $filename = array_values(array_filter($matches[3], fn ($v) => $v));

    $ret = [
      'uri' => $uri,
      'protocol' => $protocol[0] ?? '',
      // 'path' => $path[0] ?? '',
      'path' => isset($path[0]) ? trim($path[0], "\/\\") : '',
      'filename' => $filename[0] ?? '',
    ];
    $ret = array_map(fn ($v) => $v = str_replace('%20', ' ', $v), $ret);
    return $ret;
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
   * ICN based on CAGE Code
   * @return Array
   */
  public static function decode_infoEntityIdent(string $filename, $ref = true)
  {
    $prefix = 'ICN-';
    $f = str_replace($prefix, '', $filename); // 0001Z-00014-002-01.jpg
    $f = preg_replace("/\.\w+$/", '', $f);
    $extension = str_replace($prefix . $f, '', $filename);

    $f_array = explode('_', $f);
    $code = $f_array[0];

    $code_array = explode('-', $code);

    $data = [];
    $data['infoEntityIdent'] =  [
      "cageCode" => $code_array[0],
      "seqNumber" => $code_array[1],
      "issueNumber" => $code_array[2],
      "securityClassification" => $code_array[3],
    ];

    $data['prefix'] = $prefix;
    $data['extension'] = $extension;

    $data['xml_string'] = <<<EOL
      <infoEntityRef infoEntityRefIdent="{$filename}"/>
    EOL;
    $data['prefix'] = $prefix;

    return $data;
  }


  /**
   * @return Array
   */
  public static function decode_pmIdent(string $filename, $ref = true)
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
  public static function decode_dmlIdent(string $filename, $ref = true)
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
   * @return false
   */
  public static function decode_dmIdent(string $filename, $ref = true)
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

    if (count($code_array) < 8) return false;
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
   * separator adalah '::'.
   * @param mixed $key is string or null
   * @return Array
   */
  public static function explodeSearchKey(mixed $key)
  {
    $m = [];
    preg_match_all("/[\w]+::[\s\S]*?(?=\s\w+::|$)/m", $key, $matches, PREG_SET_ORDER, 0);
    $pull = function (&$arr, $fn) use (&$m) {
      foreach ($arr as $k => $v) {
        if (is_array($v)) {
          $fn($v, $fn);
        } else {
          $xplode = explode("::", $v);
          $m[strtolower($xplode[0])] = $xplode[1];
        }
        unset($arr[$k]);
      }
    };
    $pull($matches, $pull); // $matches akan empty, $m akan berisi
    return !empty($m) ? $m : [$key];
  }

  public static function arrayify_applic($applic, $keepOneByOne = false, $useDisplayName = true)
  {
    $doc = $applic->ownerDocument;
    $path = self::analyzeURI($doc->baseURI)['path'];
    $domxpath = new \DOMXPath($doc);
    $dmRefIdent = $domxpath->evaluate("//identAndStatusSection/descendant::applicCrossRefTableRef/descendant::dmRefIdent")[0];
    $ACTdoc = CSDB::importDocument($path, CSDB::resolve_dmIdent($dmRefIdent), null, 'dmodule');

    if ($ACTdoc) {
      $domxpath = new \DOMXPath($ACTdoc);
      $dmRefIdent = $domxpath->evaluate("//identAndStatusSection/descendant::applicCrossRefTableRef/descendant::dmRefIdent")[0];
      $CCTdoc = CSDB::importDocument($path, CSDB::resolve_dmIdent($dmRefIdent), null, 'dmodule');
    }

    $id = $applic->getAttribute('id');
    $childApplic = self::children($applic);
    $result = [];
    // $applicability = [];
    foreach ($childApplic as $child) {
      // $result[$child->tagName] = $resolver($child, $resolver);
      $result[$child->tagName] = self::resolver_childApplic($child, $ACTdoc, $CCTdoc, null, $keepOneByOne, $useDisplayName);
    }
    return ($id) ? ($applicability[$id] = $result) : $applicability[] = $result;
  }

  /**
   * @return array containing ['text' => String, ...]
   */
  private static function resolver_childApplic(\DOMElement $child, $ACTdoc, $CCTdoc, $PCTdoc, $keepOneByOne, $useDisplayName)
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
        return self::test_assert($child, $ACTdoc, $CCTdoc, $PCTdoc, $keepOneByOne, $useDisplayName);
        break;
      case 'evaluate':
        return self::test_evaluate($child, $ACTdoc, $CCTdoc, $PCTdoc, $keepOneByOne, $useDisplayName);
        break;
      default: 
        return '';        
    }
  }

  /**
   * kalau test fail, key 'text' akan di isi oleh <assert> text content dan status menjadi 'success'. Sehingga saat di <evaluate> akan true;
   * @param bool $keepOneByOne 
   * @return array ['text' => String, '%STATUS' => String ('success' or 'fail'), '%APPLICPROPERTYTYPE' => String, '%APPLICPROPERTYIDENT' => String, %APPLICPROPERTYVALUES' => String];
   */
  public static function test_assert(\DOMElement $assert, $ACTdoc = null, $CCTdoc = null, $PCTdoc = null, bool $keepOneByOne = false, bool $useDisplayName = true)
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
    $crossRefTable = ($applicPropertyType === 'prodattr') ? $ACTdoc : $CCTdoc;
    if (!$crossRefTable) {
      CSDB::setError('getApplicability', join(", ", CSDB::get_errors(true, 'file_exists')));
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
   * @return array ['text' => string, 'andOr' => String, 'children' => array contain evaluated child]
   */
  public static function test_evaluate(\DOMElement $evaluate, $ACTdoc = null, $CCTdoc = null, $PCTdoc = null, bool $keepOneByOne = false, $useDisplayName = true)
  {
    $children = self::children($evaluate);
    foreach ($children as $child) {
      $resolved[] = self::resolver_childApplic($child, $ACTdoc, $CCTdoc, null, $keepOneByOne, $useDisplayName);
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
   * untuk mendapatkan child element
   * @param \DOMElement $element
   * @param array $exclude
   * @return array
   */
  public static function children(\DOMElement $element, array $excludeElement = [])
  {
    $arr = [];
    $element = $element->firstElementChild;
    if ($element) {
      if (!in_array($element->tagName, $excludeElement)) {
        $arr[] = $element;
      }
      while ($element = $element->nextElementSibling) {
        if (!in_array($element->tagName, $excludeElement)) {
          $arr[] = $element;
        }
      }
    }
    return $arr;
  }
}
