<?php

namespace Ptdi\Mpub\Main;

class Applicability
{
  protected \DOMDocument $document;
  protected \DOMDocument $ACTdoc;
  protected \DOMDocument $CCTdoc;
  protected \DOMDocument $PCTdoc;
  public CSDBError $error;

  public function __construct(string $docUri)
  {
    $this->ACTdoc = new \DOMDocument();
    $this->CCTdoc = new \DOMDocument();
    $this->PCTdoc = new \DOMDocument();
    $this->document = new \DOMDocument();
    $this->document->load($docUri);
    $this->error = new CSDBError();
  }

  public function get(mixed $applic, bool $keppOneByOne = false, bool $useDisplayName = true, int $useDisplayText = 2): string
  {
    if (empty($applic)) return '';
    if (is_array($applic)) {
      $applic = $applic[0];
    }
    if ($useDisplayText) {
      if ($applic->firstElementChild->tagName === 'displayText') {
        $displayText = '';
        foreach ($applic->firstElementChild->childNodes as $simplePara) {
          $displayText .= ', ' . $simplePara->textContent;
        }
        return ltrim($displayText, ', ');
      }
      if ($useDisplayText === 1) {
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
   * saat ini hanya melakukan test assert terhadap ACTdoc saja, belum ke CCT dan PCT
   */
  private function arrayify_applic(\DOMElement $applic, $keepOneByOne = false, $useDisplayName = true): array
  {
    $doc = $this->document;
    $path = Helper::analyzeURI($this->document->baseURI)['path'];
    $domXpath = new \DOMXPath($doc);
    $dmRefIdent = $domXpath->evaluate("//identAndStatusSection/descendant::applicCrossRefTableRef/descendant::dmRefIdent")[0];
    $ACTFilename = CSDBStatic::resolve_dmIdent($dmRefIdent);
    // echo $ACTFilename . PHP_EOL;
    if ($this->ACTdoc->load($path . DIRECTORY_SEPARATOR . $ACTFilename)) {
      $domxpath = new \DOMXPath($this->ACTdoc);
      $dmRefIdent = $domxpath->evaluate("//content/descendant::condCrossRefTableRef/descendant::dmRefIdent")[0];
      $CCTFilename = CSDBStatic::resolve_dmIdent($dmRefIdent);
      $this->CCTdoc->load($path . DIRECTORY_SEPARATOR . $CCTFilename);
      // echo $CCTFilename . PHP_EOL;
      
      // $dmRefIdent = $domxpath->evaluate("//content/descendant::productCrossRefTableRef/descendant::dmRefIdent")[0];
      // $PCTFilename = CSDBStatic::resolve_dmIdent($dmRefIdent);
      // $this->PCTdoc->load($path . DIRECTORY_SEPARATOR . $PCTFilename);
      // echo $PCTFilename . PHP_EOL;      
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
  private function test_assert(\DOMElement $assert, bool $keepOneByOne = false, bool $useDisplayName = true): array
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
      $message = ($applicPropertyType === 'prodattr' ? "ACT " : "CCT") . "document is not available in CSDB,";
      $this->error->set('getApplicability', [$message]);
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
        } else {
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

      if ($useDisplayName and $status === 'success') {
        $testedValues['text'] = $displayName ? ($displayName . $testedValues['text']) : $testedValues['text'];
      }
      if ($status === 'fail') {
        $testedValues['text'] = !empty($assert->textContent) ? $assert->textContent : $testedValues['text'];
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
      $isFail = array_filter($resolved, (fn ($r) => isset($r['%STATUS']) and $r['%STATUS']  === 'fail' ? $r : false));
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
    return ['text' => '(' . $text . ')', 'andOr' => $andOr, 'children' => $evaluatedElement];
  }
}
