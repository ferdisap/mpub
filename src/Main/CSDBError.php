<?php 

namespace Ptdi\Mpub\Main;
use Countable;

class CSDBError implements Countable{
  
  ########### NEW CODE below ###########
  // new code dibuat agar tidak static
  protected array $collection = [];
  public function set(string $id, array $errors)
  {
    $this->collection[$id] = $errors;
  }
  public function append($id, mixed $value)
  {
    if(is_array($this->collection[$id])){
      $this->collection[$id][] = $value;
    }
  }
  public function get(string $id = '', bool $delete = true)
  {
    if ($id && $this->collection[$id]) {
      $e = $this->collection[$id];
      if($delete) unset($this->collection[$id]);
      return $e;
    }
    elseif($id && !($this->collection[$id])) return null;
    elseif(!($id)){
      $e = $this->collection;
      array_walk($e, function (&$v) {
        if (is_array($v)) return $v = array_unique($v);
        else return $v;
      });
      return $e;
    }
  }

  #[\ReturnTypeWillChange]
  public function count() 
  {
    return count($this->collection);
  }


  ########### OLD CODE below ###########
  // semua old code akan di deprecated, kecuali display_xml_error;

  /**
   * @deprecated
   */
  public static string $processId = '';
  /**
   * @deprecated
   */
  protected static array $errors = array();  
  /**
   * @deprecated
   */
  public static function setError($processId = '', string $message)
  {
    if($processId){
      self::$errors[$processId][] = $message;
    }
    elseif (self::$processId) {
      self::$errors[self::$processId][] = $message;
    } 
    else {
      self::$errors[] = $message;
    }
  }
  /**
   * @deprecated
   * @return array
   */
  public static function getErrors(bool $deleteErrors = true, string $processId = '')
  {
    if ($processId and isset(self::$errors[$processId])) {
      $e = self::$errors[$processId];
      if ($deleteErrors) {
        unset(self::$errors[$processId]);
      }
      return $e;
    } elseif ($processId and !isset(self::$errors[$processId])) {
      return null;
    } elseif (!$processId) {
      $e = self::$errors;
      if ($deleteErrors) {
        self::$errors = array();
      }
      array_walk($e, function (&$v) {
        if (is_array($v)) {
          return $v = array_unique($v);
        } else {
          return $v;
        }
      });
      return $e;
    }
  }

  /**
   * @return string
   */
  public static function display_xml_error($error)
  {
    $return = '--- ';

    switch ($error->level) {
      case LIBXML_ERR_WARNING:
        $return .= "Warning $error->code: ";
        break;
      case LIBXML_ERR_ERROR:
        $return .= "Error $error->code: ";
        break;
      case LIBXML_ERR_FATAL:
        $return .= "Fatal Error $error->code: ";
        break;
    }

    $message = preg_replace("/file:\S+(?=\/|\\\\)/m", '', $error->message);
    $return .= trim($message) .
      // $return .= trim($error->message) .

      ". Line: $error->line" .
      ". Column: $error->column";

    if ($error->file) {
      $return .= ". File: $error->file";
    }

    return "$return ---";
  }
}