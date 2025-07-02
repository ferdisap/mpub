<?php 

// run >>> ./vendor/bin/phpunit ./tests/CSDBErrorTest.php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Ptdi\Mpub\Main\CSDBError;

require_once "./vendor/autoload.php";

/**
 * untuk ngetes kalau CSDBError not static sama dengan yang static karena CSDBError yang non static itu dibuat untuk menggantikan yang CSDBError static
 */
final class CSDBErrorTest extends TestCase
{
  public static CSDBError $e;

  public function testCreateError() :void
  {
    self::$e = new CSDBError();
    self::$e->set('foo', []);
    self::$e->append('foo', 'bar');
    $this->assertCount(1, self::$e);
  }

  public function testGetError() :void
  {
    self::$e->append('foo', 'baz');
    $error1 = self::$e->get('',false);
    $error11 = self::$e->get('foo',false);

    CSDBError::$processId = 'foo';
    CSDBError::setError('foo','bar');
    CSDBError::setError('foo','baz');
    $error2 = CSDBError::getErrors(false);
    $error22 = CSDBError::getErrors(false,'foo');

    $this->assertEquals($error2, $error1);
    $this->assertEquals($error22, $error11);
  }

  public function testErrorConcatToTechpubResponse() :void
  {
    self::$e->set('abc', ['def','ghi']);
    $error = ['error' => self::$e->get('',false)];
    $expected = <<<EOL
    {
      "error": {
        "foo": ["bar", "baz"],
        "abc": ["def","ghi"]
      }
    }
    EOL;
    $expectedArray = json_decode($expected,true);
    // $expectedJson = json_encode($expectedArray);
    // var_dump($expectedArray);
    // var_dump($expectedArray['error']['foo']);
    // var_dump($error); 
    $this->assertEquals($error, $expectedArray); // OK
  }
}