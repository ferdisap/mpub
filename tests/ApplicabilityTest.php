<?php 

// run >>> ./vendor/bin/phpunit ./tests/ApplicabilityTest.php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Ptdi\Mpub\Main\Applicability;
use Ptdi\Mpub\Main\CSDBObject;

final class ApplicabilityTest extends TestCase
{
  public function testGet()
  {
    $csdbFile = str_replace("\\",'/',__DIR__)."/../assets/tests/DMC-MALE-A-16-00-01-00A-018A-A_000-01_EN-EN.xml";

    $Applicability = new Applicability($csdbFile);

    $doc = new \DOMDocument();
    $doc->load($csdbFile);

    $domxpath = new \DOMXPath($doc);
    $applic = $domxpath->evaluate("//dmStatus/applic")[0];

    $a = $Applicability->get($applic,true); // "MALE" atau // "SN: N001, N002, N003, N004, N005", tergantung applic nya

    $this->assertEquals("SN: N001, N002, N003, N004, N005",$a);
  }

  public function testGetApplicability()
  {
    $csdbFile = str_replace("\\",'/',__DIR__)."/../assets/tests/DMC-MALE-A-16-00-01-00A-018A-A_000-01_EN-EN.xml";

    $CSDBObject = new CSDBObject();
    $CSDBObject->load($csdbFile);

    $applic = $CSDBObject->evaluate("//dmStatus/applic")[0];
    $a = $CSDBObject->getApplicability($applic, true);

    $this->assertEquals("SN: N001, N002, N003, N004, N005",$a);
  }
}
