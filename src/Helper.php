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
      'path' => $path[0] ?? '',
      'filename' => $filename[0] ?? '',
    ];
    $ret = array_map(fn($v) => $v = str_replace('%20',' ', $v), $ret);
    return $ret;

  }
}
