<?php

declare(strict_types=1);

use Ptdi\Mpub\Fop\Pdf;
use PHPUnit\Framework\TestCase;

require_once "./vendor/autoload.php";

final class PdfTest extends TestCase
{

  public function testCreatePdf(): void
  {
    $pdf = new Pdf(
      input:"D:/application/php-app/mpub/assets/tests/pdf/examples/embedding/xml/fo/helloworld.fo",
      output:"D:/application/php-app/mpub/assets/tests/pdf/generated/helloworld.pdf");
    $create = $pdf->create();
    $create ? $this->assertTrue(true) : $this->assertFalse(true);
  }

  public function testCreatePdfWithExternalConfig(): void
  {
    $pdf = new Pdf(
      input:"D:/application/php-app/mpub/assets/tests/pdf/examples/embedding/xml/fo/helloworld.fo",
      output:"D:/application/php-app/mpub/assets/tests/pdf/generated/helloworld.pdf");
    $pdf->config = "D:/application/php-app/mpub/assets/tests/pdf/examples/config/foo.xconf";
    $create = $pdf->create();
    $create ? $this->assertTrue(true) : $this->assertFalse(true);
  }

}
