<?php

namespace Ptdi\Mpub\Pdf2;

use Exception;
use Ptdi\Mpub\CSDB;
use Ptdi\Mpub\Helper;

/**
 * 
 */
trait Applicability
{
  public function aaa()
  {
    return 'aaa';
  }

  /**
   * incase assert tidak ada, pakai displayText
   * incase assert fail, akan menampilkan error
   * jadi module itu lebih baik tidak pakai assert jika ragu ragu
   * displayName itu seperti SN: ...
   */
  public function getApplicability($id = '', $options = '', $useDisplayName = true, $appl = null)
  {
    // $appl = [
    //   'applicability' => [
    //     'appl-001' => [ // jika tidak ada @id maka '0'
    //       'aircraft' => 'MALE, Amphibi',
    //       '%APPLICPROPERTYTYPE' => 'prodattr'
    //     ]
    //   ],
    //   "CSDB" => 'Ptdi\Mpub\CSDB'
    // ];
    $appl = $appl ?? CSDB::getApplicability($this->DOMDocument, $this->absolute_path_csdbInput);
    
    if((isset($this->ignore_error) AND $this->ignore_error) AND !$appl){
      return '';
    }
    $this->applicability = $appl['applicability'];
    $CSDB = $appl['CSDB'];

    if($id) {
      if (!isset($this->applicability[$id])) {
        throw new Exception("No such $id inside $this->dmIdent", 1);
      };
      // $str = '';
      // foreach ($this->applicability[$id] as $applicPropertyIdent => $stringApplic) {
      //   if ($applicPropertyIdent[0] == '%') continue;
      //   $str .= $stringApplic;
      // }
      // return $str;
    }
    switch ($options) {
      case 'first':
        $applic_id = array_key_first($this->applicability); // 'appl-001', '0', // ini kuncinya jika case 'first'
        break;
      case 'id':
        $applic_id = $id;
        break;
      default:
        $applic_id = '';
        break;
        
    }
    $str = '';
    if($applic_id){
      $useDisplayText = false;
      
      $status = $this->applicability[$applic_id]['%STATUS'];
      if($status == 'fail') return $this->applicability[$applic_id]['%MESSAGE'];

      foreach ($this->applicability[$applic_id] as $applicPropertyIdent => $stringApplic) {
        if ($applicPropertyIdent[0] == '%') continue;
        $str .= $stringApplic;
      }
      if (!$useDisplayName) return $str;
      if($str == '' AND isset($this->applicability[$applic_id]['%DISPLAYTEXT'])) {
        $str = $this->applicability[$applic_id]['%DISPLAYTEXT'];
        $useDisplayText = true;
      }
      krsort($this->applicability[$applic_id]); // supaya array key tersusun dari ['%DISPLAYTEXT', 'aircraft', '%APPLICPROPERTYIDENT'] menjadi ['aircraft','%DISPLAYTEXT', '%APPLICPROPERTYTYPE'];
      $applicPropertyIdent = array_key_first($this->applicability[$applic_id]); // aircraft
      $applicPropertyType = $this->applicability[array_key_first($this->applicability)]['%APPLICPROPERTYTYPE'] ?? ''; // prodattr
      if(!$useDisplayText){
        if(!$applicPropertyType AND (isset($this->ignore_error) AND $this->ignore_error)) return '';      
        $ct = ($applicPropertyType == 'prodattr') ? $CSDB->ACTdoc : $CSDB->CCTdoc;
        $ctxpath = new \DOMXPath($ct);
        $dt = $ctxpath->evaluate("//productAttribute[@id = '{$applicPropertyIdent}']")[0];
        $displayName = $dt->getElementsByTagName('displayName')[0];
        if ($displayName) {
          $str = $displayName->nodeValue . $str;
        }
      }
    }
    return $str;
    // return $this->applicability; // array
  }
}
