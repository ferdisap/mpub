<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Ptdi\Mpub\Main\CSDBObject;
use Ptdi\Mpub\Transformer\Pdf;

require_once "./vendor/autoload.php";

final class PdfTest extends TestCase
{

  public static string $fo_file;

  public function x_testDevCreatePdf(): void
  {
    $pdf = new Pdf(
      input: str_replace("\\",'/',__DIR__)."/../assets/tests/pdf/examples/embedding/xml/fo/helloworld.fo",
      output: str_replace("\\",'/',__DIR__).'/../assets/tests/pdf/generated/helloworld.pdf');
    $create = $pdf->create();
    $create ? $this->assertTrue(true) : $this->assertFalse(true);
  }

  public function x_testDevCreatePdfWithExternalConfig(): void
  {
    $pdf = new Pdf(
      input: str_replace("\\",'/',__DIR__)."/../assets/tests/pdf/examples/embedding/xml/fo/helloworld.fo",
      output: str_replace("\\",'/',__DIR__)."/../assets/tests/pdf/generated/helloworld.pdf");
    $pdf->config =  str_replace("\\",'/',__DIR__)."/../assets/tests/pdf/examples/config/foo.xconf";
    $create = $pdf->create();
    $create ? $this->assertTrue(true) : $this->assertFalse(true);
  }

  public function x_testProdCreateFo()
  {
    $csdbFile = str_replace("\\",'/',__DIR__)."/../assets/tests/DMC-MALE-A-16-00-01-00A-018A-A_000-01_EN-EN.xml";
    $inputFile = str_replace("\\",'/',__DIR__)."/../src/Transformer/xsl/pdf/CN235/Main.xsl";
    // $outputFile = str_replace("\\",'/',__DIR__)."/../assets/tests/fo/DMC-MALE-A-16-00-01-00A-018A-A_000-01_EN-EN.fo";
    $outputFile = str_replace("\\",'/',__DIR__)."/../assets/tests/fo/tes.fo";
    $pdf = new Pdf($inputFile, $outputFile);
    $pdf->config = str_replace("\\",'/',__DIR__)."/../src/Config/config.xml";
    $pdf->configurableValues = str_replace("\\",'/',__DIR__)."/../src/Config/configurableValues.xml";
    $create = $pdf->createFo($csdbFile);

    $create ? $this->assertTrue(true) : $this->assertFalse(true);
    // $this->assertTrue($create);
  }

  public function testProdTransformToFo()
  {
    $csdbFile = str_replace("\\",'/',__DIR__)."/../assets/tests/DMC-MALE-A-16-00-01-00A-018A-A_000-01_EN-EN.xml";
    $xslFile = str_replace("\\",'/',__DIR__)."/../src/Transformer/xsl/pdf/CN235/Main.xsl";
    $outputFile = str_replace("\\",'/',__DIR__)."/../assets/tests/fo/DMC-MALE-A-16-00-01-00A-018A-A_000-01_EN-EN.xml";

    // coba melalui CSDBObject
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

    // coba langsung pakai Pdf
    $pdf = new Pdf(
      input: $xslFile,
      output: $outputFile
    ); 
    $pdf->config = str_replace("\\",'/',__DIR__)."/../src/Config/config.xml";
    $pdf->configurableValues = str_replace("\\",'/',__DIR__)."/../src/Config/configurableValues.xml";
    $create = $pdf->createFo($csdbFile);
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
