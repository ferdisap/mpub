<?php 

// run >>> ./vendor/bin/phpunit ./tests/XsiTest.php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Ptdi\Mpub\Validation\Validator\Xsi;

require_once "./vendor/autoload.php";

final class XsiTest extends TestCase
{
  public function testValidate() :void
  {
    $doc = new \DOMDocument();
    // $doc->load('./assets/tests/DMC-S1000D-G-04-10-0301-00A-022A-D_001-00_EN-US.XML'); // true
    // $doc->load('./assets/tests/DMC-MALE-A-16-00-01-00A-018A-A_000-01_EN-EN.xml'); // false
    $doc->load('./assets/tests/DMC-S1000D-G-04-10-0301-00A-022A-D_001-00_EN-EN.XML'); // false

    $xsi = new Xsi($doc);
    $xsi->validate();
    // echo PHP_EOL;
    // var_dump($xsi->errors->get(''));
    // var_dump($xsi->errors->get('xsi_validation'));
    // var_dump($xsi->result());
    // echo PHP_EOL;
    $this->assertTrue($xsi->result());
  }
}