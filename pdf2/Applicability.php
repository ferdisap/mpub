<?php

namespace Ptdi\Mpub\Pdf2;

use Exception;
use Ptdi\Mpub\CSDB;

/**
 * 
 */
trait Applicability
{
  public function getApplicability($id = '', $options = '', $useDisplayText = true)
  {
    $appl = CSDB::getApplicability($this->DOMDocument, $this->absolute_path_csdbInput);
    // dd($appl);
    $this->applicability = $appl['applicability'];
    $CSDB = $appl['CSDB'];

    if ($id) {
      if (!isset($this->applicability[$id])) {
        throw new Exception("No such $id inside $this->dmIdent", 1);
      };
      $str = '';
      foreach ($this->applicability[$id] as $applicPropertyIdent => $stringApplic) {
        if ($applicPropertyIdent[0] == '%') continue;
        $str .= $stringApplic;
      }
      return $str;
    }
    switch ($options) {
      case 'first':
        $str = '';
        foreach ($this->applicability[array_key_first($this->applicability)] as $applicPropertyIdent => $stringApplic) {
          if ($applicPropertyIdent[0] == '%') continue;
          $str .= $stringApplic;
        }
        if (!$useDisplayText) return $str;
        $applicPropertyIdent = array_key_first($this->applicability[array_key_first($this->applicability)]);
        $applicPropertyType = $this->applicability[array_key_first($this->applicability)]['%APPLICPROPERTYTYPE'];
        // dd($CSDB);
        $ct = ($applicPropertyType == 'prodattr') ? $CSDB->ACTdoc : $CSDB->CCTdoc;
        $ctxpath = new \DOMXPath($ct);
        $dt = $ctxpath->evaluate("//productAttribute[@id = '{$applicPropertyIdent}']")[0];
        $displayName = $dt->getElementsByTagName('displayName')[0];
        if ($displayName) {
          $str = $displayName->nodeValue . $str;
        }
        return $str;
    }
    return $this->applicability; // array
  }
}
