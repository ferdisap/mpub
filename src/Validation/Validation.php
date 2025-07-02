<?php 

namespace Ptdi\Mpub\Validation;

use Ptdi\Mpub\Main\CSDBObject;
use Ptdi\Mpub\ICNDocument;

abstract class Validation
{  
  /**
   * @param mixed CSDBObject bisa berupa \DOMDocument, Ptdi\Mpub\ICNDocument, String, Array
   * @return \DOMElement
   * @return bool
   * @return Array
   */
  function check(mixed $CSDBObject){    
    if(is_countable($CSDBObject)){
      $l = count($CSDBObject);
      for ($i=0; $i < $l; $i++) { 
        $CSDBObject[$i] = $this->check($CSDBObject[$i]);
      }
      return $CSDBObject;
    }
    else if($CSDBObject->document instanceof \DOMDocument){
      return $CSDBObject->document->documentElement ?? false;
    }
    elseif($CSDBObject->document instanceof ICNDocument){
      return $CSDBObject->document->isExist() ?? false;
    }
    elseif(is_string($CSDBObject)){
      if(file_exists($CSDBObject)) {
        $filename = $CSDBObject;
        $CSDBObject = new CSDBObject();
        $CSDBObject->load($filename);
        return $this->check($CSDBObject);
      }
      else {
        return false;
      }
    }
    return false;
  }
}