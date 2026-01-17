<?php

// run >>> ./vendor/bin/phpunit ./tests/BrexTest.php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Ptdi\Mpub\Main\CSDBObject;
use Ptdi\Mpub\Validation\CSDBValidatee;
use Ptdi\Mpub\Validation\CSDBValidator;
use Ptdi\Mpub\Validation\Validator\Brex;

require_once "./vendor/autoload.php";

final class BrexTest extends TestCase
{
  public static Brex $brex;

  public function testBrexInstance(): void
  {
    $validator = new CSDBObject();
    $validator->load("./assets/tests/DMC-S1000D-G-04-10-0301-00A-022A-D_001-00_EN-US.XML");
    $validator = new CSDBValidator($validator);

    $validatee = new CSDBObject();
    $validatee->load("./assets/tests/DMC-MALE-A-15-00-01-00A-018A-A_000-01_EN-EN.xml");
    $validatee = new CSDBValidatee($validatee);

    self::$brex = new Brex($validator, $validatee);
    self::$brex ? $this->assertTrue(true) : $this->assertFalse(true);
  }

  public function testBrexReadiness():void
  {
    self::$brex->isReady() ? $this->assertTrue(true) : $this->assertFalse(true);
  }

  public function testBrexValidation():void
  {
    self::$brex->validate();

    (!empty(self::$brex->result())) ? $this->assertTrue(true) : $this->assertFalse(true);
  }
}
