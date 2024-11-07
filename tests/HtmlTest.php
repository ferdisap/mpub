<?php

// run >>> ./vendor/bin/phpunit ./tests/HtmlTest.php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Ptdi\Mpub\Main\CSDBObject;
use Ptdi\Mpub\Transformer\Html;
use Ptdi\Mpub\Transformer\Pdf;
use Ptdi\Mpub\Transformer\Transformator;

require_once "./vendor/autoload.php";

final class HtmlTest extends TestCase
{
  public string $file1 = 'IMF-0001Z-00012-001-01_000-01.xml';
  public string $outputfile1 = 'IMF-0001Z-00012-001-01_000-01.html';

  public function testCreateHtml(): void
  {
    $html = new Html(
      input: str_replace("\\",'/',__DIR__)."/../src/Transformer/xsl/html/Main.xsl",
      output: str_replace("\\",'/',__DIR__)."/../assets/tests/html/generated/{$this->outputfile1}"
    );
    $create = $html->create(str_replace("\\",'/',__DIR__)."/../assets/tests/{$this->file1}");
    $this->assertTrue($create);
  }
}