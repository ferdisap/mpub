<?php

namespace Ptdi\Mpub\Main;

class CError
{
  protected static array $errorsBag = [];

  protected static string|null $path = null;

  public static function set_path(string $path)
  {
    self::$path = $path;
  }

  private static function reconstruct_error(mixed &$errors): void
  {
    if (is_array($errors)) {
      $errors = array_filter($errors, fn($v) => $v);
      $errors = array_map((fn($v) => is_array($v) ? $v : $v = [$v]), $errors);
    } else {
      $errors = [$errors];
    }
  }

  public static function set(string $key, array|string|int $errors)
  {
    if (self::$path) {
      self::reconstruct_error($errors);
      self::$errorsBag[self::$path][$key] = $errors;
    }
  }

  public static function append(string $key, array|string|int $values)
  {
    if (self::$path) {
      self::reconstruct_error($values);
      $collection = self::$errorsBag[self::$path];
      if ($collection[$key]) {
        $collection[$key] = array_merge($collection[$key], $values);
      } else {
        $collection[$key] = $values;
      }
      $collection[$key] = array_unique($collection[$key]);
      self::$errorsBag[self::$path] = $collection;
    }
  }

  public static function get(string|null $id = null, bool $delete = true): null | array
  {
    $collection = self::$errorsBag[self::$path];

    if ($id && $collection[$id]) {
      $e = $collection[$id];
      if ($delete) unset($collection[$id]);
      return is_array($e) ? $e : [$e];
    } elseif ($id && !($collection[$id])) return null;
    elseif (!($id)) {
      $e = $collection;
      array_walk($e, function (&$v) {
        if (is_array($v)) return $v = array_unique($v);
        else return $v;
      });
      return $e;
    }
    return null;
  }

  /**
   * @return string
   */
  public static function display_xml_error(mixed $error)
  {
    if (count($error) > 0) {
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
}
