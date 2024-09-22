<?php

// run >>> ./vendor/bin/phpunit ./tests/PdfTest.php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Ptdi\Mpub\Main\CSDBObject;
use Ptdi\Mpub\Transformer\Pdf;
use Ptdi\Mpub\Transformer\Transformator;

require_once "./vendor/autoload.php";

final class PdfTest extends TestCase
{

  public static string $fo_file;

  public function testDevCreatePdf(): void
  {
    $pdf = new Pdf(
      input: str_replace("\\",'/',__DIR__)."/../assets/tests/pdf/examples/embedding/xml/fo/helloworld.fo",
      output: str_replace("\\",'/',__DIR__).'/../assets/tests/pdf/generated/helloworld.pdf');
    $pdf->config = Transformator::config_uri();
    $pdf->configurableValues = Transformator::configurableValues_uri();
    $create = $pdf->create();
    $create ? $this->assertTrue(true) : $this->assertFalse(true);
  }

  public function testDevCreatePdfWithExternalConfig(): void
  {
    $pdf = new Pdf(
      input: str_replace("\\",'/',__DIR__)."/../assets/tests/pdf/examples/embedding/xml/fo/helloworld.fo",
      output: str_replace("\\",'/',__DIR__)."/../assets/tests/pdf/generated/helloworld.pdf");
    $pdf->config =  str_replace("\\",'/',__DIR__)."/../assets/tests/pdf/examples/config/foo.xconf";
    $pdf->config = Transformator::config_uri();
    $pdf->configurableValues = Transformator::configurableValues_uri();
    $create = $pdf->create();
    $create ? $this->assertTrue(true) : $this->assertFalse(true);
  }

  public function testProdTransformToFo_fromPdfClass()
  {
    $modelIdentCode = 'CN235';
    $config = new \DOMDocument();
    $config->load(Transformator::config_uri());
    $xpath = new \DOMXPath($config);
    $xslFo = $xpath->evaluate("string(//config/output/method[@type='pdf']/path[@product-name='$modelIdentCode'])");
    if(!$xslFo) $xslFo = $xpath->evaluate("string(//config/output/method[@type='pdf']/path[@product-name='*'])");

    $csdbFile = str_replace("\\",'/',__DIR__)."/../assets/tests/DMC-MALE-A-16-00-01-00A-018A-A_000-01_EN-EN.xml";
    $xslFile = str_replace("\\",'/',__DIR__)."/../src/Transformer/xsl/pdf/CN235/Main.xsl";
    $outputFile = str_replace("\\",'/',__DIR__)."/../assets/tests/fo/DMC-MALE-A-16-00-01-00A-018A-A_000-01_EN-EN.xml";

    $CSDBObject= new CSDBObject();
    $CSDBObject->load($csdbFile);

    $pdf = new Pdf($xslFile, $outputFile);
    $pdf->CSDBObject = $CSDBObject;
    $pdf->config = Transformator::config_uri();
    $pdf->configurableValues = Transformator::configurableValues_uri();
    $pdf->csdb_path = './';
    $create = $pdf->createFo(
      source: $csdbFile,
    );

    if($create) {
      self::$fo_file = $outputFile;
      $this->assertTrue(true);
    }
    else {
      $this->assertFalse(true);
    }
  }

  public function testProdTransformToFo_fromCSDBObjectClass()
  {
    $csdbFile = str_replace("\\",'/',__DIR__)."/../assets/tests/DMC-MALE-A-16-00-01-00A-018A-A_000-01_EN-EN.xml";
    $xslFile = str_replace("\\",'/',__DIR__)."/../src/Transformer/xsl/pdf/CN235/Main.xsl";
    $outputFile = str_replace("\\",'/',__DIR__)."/../assets/tests/fo/DMC-MALE-A-16-00-01-00A-018A-A_000-01_EN-EN.xml";

    $CSDBObject = new CSDBObject();
    $CSDBObject->load($csdbFile);
    $create = $CSDBObject->transform_to_fo($xslFile, $outputFile);

    if($create) {
      self::$fo_file = $outputFile;
      $this->assertTrue(true);
    }
    else {
      $this->assertFalse(true);
    }
  }

  public function testProdCreatePdf()
  {
    $pdf = new Pdf(
      input: self::$fo_file,
      output: str_replace("\\",'/',__DIR__).'/../assets/tests/pdf/generated/DMC-MALE-A-16-00-01-00A-018A-A_000-01_EN-EN.pdf');
    $create = $pdf->create();
    $create ? $this->assertTrue(true) : $this->assertFalse(true);
  }

}
