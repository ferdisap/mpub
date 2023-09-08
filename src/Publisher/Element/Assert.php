<?php

namespace Ptdi\Mpub\Publisher\Element;

use DOMElement;
use DOMXPath;
use Ptdi\Mpub\Object\ACT;
use Ptdi\Mpub\Object\CCT;
use Ptdi\Mpub\Publisher\Element;
use Ptdi\Mpub\Publisher\Message;
use Ptdi\Mpub\Resolver\Applicability;

class Assert extends Element
{
  // $crossRefTable is ACT or CCT
  public $crossRefTable; 
  public array $applicability = [];

  public function __construct(array $attributes = [], array $inner = [])
  {
    parent::__construct($attributes, $inner);
    return $this;
  }

  public function setCrossRefTable(mixed $crossRefTable = null){
    if ($crossRefTable instanceof ACT || $crossRefTable instanceof CCT) {
      $this->crossRefTable = $crossRefTable;
      return true;
    } else {
      Message::generate(300, "Cross Reference Table is not stated in data module");
      return false;
    }
  }


  public function test(mixed $crossRefTable = null){
    if(!$this->checkAttributes()){
      foreach($this->inner as $inner){
        return $inner instanceof \DOMText ? ['text' => [$inner->nodeValue]] : '';
      }
    }

    if(!$this->setCrossRefTable($crossRefTable)){
      return false;
    };

    $producedValues = $this->generateValue($this->applicPropertyValues);  
    $nominalValues = $this->generateValue($this->crossRefTable->getApplicPropertyValuesFromCrossRefTable($this->applicPropertyIdent));
    $testedValues = [];

    dump($producedValues, $nominalValues, __CLASS__,__LINE__);
    if($nominalValues){
      foreach($producedValues as $value){
        if(!in_array($value, $nominalValues)){
          Message::generate(300, "The attribute @applicPropertyValue is not comply with {($this->applicPropertyType == 'prodattr' ? 'product attribute' : 'conditionTypeList attribute')} value.");
          return false;
        } else {
          $testedValues = $producedValues;
        }
      }

      $ejaan_serialnumber = ['SERIALNUMBER', 'Serialnumber', 'serialnumber', 'serialNumber', 'SerialNumber', 'SERIAL_NUMBER', 'Serial_umber', 'serial_number', 'serial_Number', 'Serial_Number'];
        if(in_array($this->applicPropertyIdent, $ejaan_serialnumber)){
          foreach($testedValues as $n => $v){                
            if($v < 10) {
            $testedValues[$n] = "00".$testedValues[$n];
          } elseif ($v < 100){
            $testedValues[$n] = "0".$testedValues[$n];
          }
        }          
      }

      // @valuePattern kan cuma dipakai saat @valueDataType == 'string', sehingga ini tidak akan dijalankan kalau tidak ada pattern nya. Jadi aman jika assert berisi integer/booelan
      // @valuePattern harus memiliki 1 capturing group yang akan diganti dengan nilai baru.
      $pattern = $crossRefTable->isexistValuePattern($this->applicPropertyIdent);
      if($pattern){
        $regex = "/.*(\(.*\)).*/"; // akan match dengan yang didalam kurungnya /N(219)/ akan match dengan 219
        preg_match_all($regex, $pattern, $structure, PREG_SET_ORDER, 0);
        if($structure){
          for ($i=0; $i < count($testedValues); $i++) { 
            $newValue = str_replace($structure[0][1], $testedValues[$i], $structure[0][0]);
            $newValue = trim($newValue);
            $newValue = substr_replace($newValue, "", 0,1);
            $newValue = substr_replace($newValue, "", strlen($newValue)-1,1);
            $testedValues[$i] = $newValue;
          }
        }
      }
      // dump($testedValues, $structure);
      return [$this->applicPropertyIdent => $testedValues];
    }
  }

  private function generateValue(string $applicPropertyValues){
    $this->valueDataType = $this->crossRefTable->getValueDataType($applicPropertyValues);

    $values_generated = [];
    $matches = $this->breakApplicPropertyValues($applicPropertyValues);

    foreach($matches as $values){
      $start = $this->crossRefTable->validateTowardsPattern($this->applicPropertyIdent, $values[1], $this->valueDataType);
      $end = $this->crossRefTable->validateTowardsPattern($this->applicPropertyIdent, $values[2], $this->valueDataType);
      // dump($values,$values[3],__CLASS__,__LINE__);
      // dump('foo');

      $range = range($start, $end);
      if($start && $end){
        foreach($range as $v){
          array_push($values_generated, $v);
        }
      }

      // dd($values, $values[3], 'values3');
      if($values[3]){
        $singleValue = $this->crossRefTable->validateTowardsPattern($this->applicPropertyIdent ,$values[3], $this->valueDataType);
        // dump($singleValue,__CLASS__,__LINE__);
        if($singleValue){
          array_push($values_generated, $singleValue);
        }
      }
      // dump($values_generated,__CLASS__,__LINE__);
    }
    // dd($values_generated,__CLASS__,__LINE__);
    return $values_generated;
  }

  /**
   * untuk mengubah applicPropertyValues menjadi array yang dapat digunakan untuk di validasi terhadap @valuePattern if @valueDataType=="string"
   * dan dapat digunakan untuk ranging number
   * 
   * @param array contain matched values breaked by pipe char.
   * Each those values is an [] which contains value breaked by tilde char.
   * Those value is n index. [0] is matches. [1] and [2] are $start and $end, [3] is single value   * 
   */
  private function breakApplicPropertyValues(string $applicPropertyValues)
  {
    // $applicPropertyValues = "N071|N001N005`N010|N015throughN020|N020|N030~N035|N001~N005~N010";
    // $regex[0] untuk match ->N030~N035<- ->N001~N005~N010<-
    // $regex[1] untuk match ->N071<- ->N015throughN020<- ->N020<-
    // semua value yang akan di cek terhadap @valuePattern (jika @valueDataType is string) ada dalam match-group ke 1(index ke 1) atau 2 atau 3
    // jika range (tilde) maka $start = group 1; $end = group 2
    // jika singe value maka group 3
    $regex = ["([A-Za-z0-9\-\/]+)~([A-Za-z0-9\-\/]+)(?:[~`!@#$%^&*()\-_+={}\[\]\\;:'" . '",<.>\/? A-Za-z0-9]+)*', "|", "(?<![`~!@#$%^&*()-_=+{}\[\]\\;;'" . '",<.>\/? ])([A-Za-z0-9\-\/]+)(?![`~!@#$%^&*()-_=+{}\[\]\\;;' . "',<.>\/? ])"]; // https://regex101.com/r/vKhlJB/3 account ferdisaptoko@gmail.com
    $regex = "/" . implode($regex) . "/";
    preg_match_all($regex, $applicPropertyValues, $matches, PREG_SET_ORDER, 0); // matches1 = "N003~N005", matches2 = "N010~N015"

    // $matches contains array [[],[],[],[]]. Each index is an array [0,1,2,3] that 0 is string matches, 1,2 is ranges ($start, $end), 3 is single value
    return $matches;
  }

  /**
   * @return true
   * @return false
   */
  private function checkAttributes(){
    if($this->applicPropertyIdent){
      if($this->applicPropertyType){
        if($this->applicPropertyValues){
          return true;
        } else {
          return false;
        }
      } else {
        return false;
      }
    } else {
      return false;
    }
  }
}
