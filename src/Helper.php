<?php

namespace Ptdi\Mpub;

class Helper
{
  public static function analyzeURI(string $uri)
  {
    preg_match_all('/(^[a-z]+:[\/\\\\\\\\]{1,3})|(.+(?=[\/\\\\]))|([^\/^\\\\]+$)/', $uri, $matches, PREG_UNMATCHED_AS_NULL, 0); // 3 elements

    $protocol = array_values(array_filter($matches[1], fn($v) => $v));
    $path = array_values(array_filter($matches[2], fn($v) => $v));
    $filename = array_values(array_filter($matches[3], fn($v) => $v));

    $ret = [
      'uri' => $uri,
      'protocol' => $protocol[0] ?? '',
      // 'path' => $path[0] ?? '',
      'path' => isset($path[0]) ? trim($path[0],"\/\\") : '',
      'filename' => $filename[0] ?? '',
    ];
    $ret = array_map(fn($v) => $v = str_replace('%20',' ', $v), $ret);
    return $ret;
  }

  /**
   * #return 
   */
  public static function decode_ident(string $filename, bool $ref = true)
  {
    $prefix = substr($filename,0,4);
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
      default:
        return  '';
        break;
    }
  }

  /**
   * @return Array
   */
  public static function decode_pmIdent(string $filename, $ref = true)
  {
    $prefix = 'PMC-';
    $f = str_replace($prefix, '', $filename); // MALE-K0378-A0001-00_000-01_EN-EN.xml
    $f = preg_replace('/.xml/','',$f);

    $f_array = explode('_', $f);
    $code = $f_array[0];
    $issueInfo = $f_array[1];
    $language = $f_array[2];

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
      'issueNumber' => $issueInfo_array[0],
      'inWork' => $issueInfo_array[1],
    ];

    $data['language'] = [
      'languageIsoCode' => strtolower($language_array[0]),
      'countryIsoCode' => $language_array[1],
    ];

    $xml_string = function($data = []) use($ref) {
      $d = [];
      array_walk($data['pmCode'], function($v,$name) use(&$d){
        $d[$name] = ($v != '') ? ("{$name}=". '"' . "$v" . '"') : '';
      });
      array_walk($data['issueInfo'], function($v,$name) use(&$d){
        $d[$name] = ($v != '') ? ("{$name}=". '"' . "$v" . '"') : '';
      });
      array_walk($data['language'], function($v,$name) use(&$d){
        $d[$name] = ($v != '') ? ("{$name}=". '"' . "$v" . '"') : '';
      });
      
      $ident = <<<EOD
        <pm{$ref}Ident>
          <pmCode {$d['modelIdentCode']} {$d['pmIssuer']} {$d['pmNumber']} {$d['pmVolume']}/>
          <language {$d['languageIsoCode']} {$d['countryIsoCode']}/>
          <issueInfo {$d['issueNumber']} {$d['inWork']}/>
        </pm{$ref}Ident>
      EOD;

      if($ref){
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
    $f = preg_replace('/.xml/','',$f);

    $f_array = explode('_', $f);
    $code = $f_array[0];
    $issueInfo = $f_array[1];

    $code_array = explode('-', $code);
    $issueInfo_array = explode('-', $issueInfo);

    $ref = $ref ? 'Ref' : '';

    $data = [];
    $data['dmlCode'] =  [
      "modelIdentCode" => $code_array[0],
      "senderIdent" => $code_array[1],
      "dmlType" => $code_array[2],
      "yearOfDataIssue" => $code_array[3],
      "seqNumber" => $code_array[4],
    ];

    $data['prefix'] = $prefix;
    $data['issueInfo'] = [
      'issueNumber' => $issueInfo_array[0],
      'inWork' => $issueInfo_array[1],
    ];

    $xml_string = function($data = []) use($ref) {
      $d = [];
      array_walk($data['dmlCode'], function($v,$name) use(&$d){
        $d[$name] = ($v != '') ? ("{$name}=". '"' . "$v" . '"') : '';
      });
      array_walk($data['issueInfo'], function($v,$name) use(&$d){
        $d[$name] = ($v != '') ? ("{$name}=". '"' . "$v" . '"') : '';
      });
      
      $ident = <<<EOD
        <dml{$ref}Ident>
          <dmlCode {$d['modelIdentCode']} {$d['senderIdent']} {$d['dmlType']} {$d['yearOfDataIssue']} {$d['seqNumber']} />
          <issueInfo {$d['issueNumber']} {$d['inWork']}/>
        </dml{$ref}Ident>
      EOD;

      if($ref){
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
  public static function decode_dmIdent(string $filename, $ref = true)
  {
    $prefix = 'DMC-'; // DMC-,
    $f = str_replace($prefix, '', $filename); // MALE-SNS-Disscode-infoCode,
    // $f = substr($filename,4); 
    $f = preg_replace('/.xml/','',$f);

    $f_array = explode('_', $f);
    $code = $f_array[0];
    $issueInfo = $f_array[1];
    $language = $f_array[2];

    $code_array = explode('-', $code);
    $issueInfo_array = explode('-', $issueInfo);
    $language_array = explode('-', $language);

    $ref = $ref ? 'Ref' : '';

    $data = [];
    $data['dmCode'] =  [
      "modelIdentCode" => $code_array[0],
      "systemDiffCode" => $code_array[1],
      "systemCode" => $code_array[2],
      "subSystemCode" => $code_array[3][0],
      "subSubSystemCode" => $code_array[3][1],
      "assyCode" => $code_array[4],
      "disassyCode" => substr($code_array[5],0,2),
      "disassyCodeVariant" => substr($code_array[5],2),
      "infoCode" => substr($code_array[6],0,3),
      "infoCodeVariant" => substr($code_array[6],3),
      "itemLocationCode" => $code_array[7],
    ];
    if(isset($dmCode_array[8])){
      $data['dmCode']['learnCode'] = strtoupper(substr($dmCode_array[8],0,3));
      $data['dmCode']['learnEventCode'] = strtoupper(substr($dmCode_array[8],4));
    } else {
      $data['dmCode']['learnCode'] = '';
      $data['dmCode']['learnEventCode'] = '';
    }
    
    $data['prefix'] = $prefix;
    $data['issueInfo'] = [
      'issueNumber' => $issueInfo_array[0],
      'inWork' => $issueInfo_array[1],
    ];

    $data['language'] = [
      'languageIsoCode' => strtolower($language_array[0]),
      'countryIsoCode' => $language_array[1],
    ];

    
    $xml_string = function($data = []) use($ref) {
      $d = [];
      array_walk($data['dmCode'], function($v,$name) use(&$d){
        $d[$name] = ($v != '') ? ("{$name}=". '"' . "$v" . '"') : '';
      });
      array_walk($data['issueInfo'], function($v,$name) use(&$d){
        $d[$name] = ($v != '') ? ("{$name}=". '"' . "$v" . '"') : '';
      });
      array_walk($data['language'], function($v,$name) use(&$d){
        $d[$name] = ($v != '') ? ("{$name}=". '"' . "$v" . '"') : '';
      });

      $ident = <<<EOD
        <dm{$ref}Ident>
          <dmCode {$d['modelIdentCode']} {$d['systemDiffCode']} {$d['systemCode']} {$d['subSystemCode']} {$d['subSubSystemCode']} {$d['assyCode']} {$d['disassyCode']} {$d['disassyCodeVariant']} {$d['infoCode']} {$d['infoCodeVariant']} {$d['itemLocationCode']} {$d['learnCode']} {$d['learnEventCode']}/>
          <issueInfo {$d['issueNumber']} {$d['inWork']}/>
          <language {$d['languageIsoCode']} {$d['countryIsoCode']}/>
        </dm{$ref}Ident>
      EOD;

      if($ref){
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
}

