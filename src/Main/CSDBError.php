<?php 

namespace Ptdi\Mpub\Main;

class CSDBError {
  
  public static string $processId = '';
  protected static array $errors = array();

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