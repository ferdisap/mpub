<?php 

namespace Ptdi\Mpub\Publisher;

class Message {
  public static string $text;
  public static int $code;
  public static string $class_name;
  public static string $line;

  public static function generate(int $code, string $text = '', string $class_name = '', string $line = ''){
    // $self = (new self());
    // $self::$code = $code;
    // $self::$text = $text;
    // $self::$class_name = $class_name;
    // $self::$line = $line;
    // return $self;

    self::$code = $code;
    self::$text = $text;
    self::$class_name = $class_name;
    self::$line = $line;
    
  }
}