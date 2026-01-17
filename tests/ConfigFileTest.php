<?php

// run >>> ./vendor/bin/phpunit ./tests/ConfigFileTest.php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once "./vendor/autoload.php";

final class ConfigFileTest extends TestCase
{
  public function testCreateConfigurableValue(): void
  {
    unlink("./src/Config/Interpret/01.configurableValueFromBrex.xml");

    $command = "php ./tools/BrexInterpret.php -i --brexdoc=./assets/tests/DMC-S1000D-G-04-10-0301-00A-022A-D_001-00_EN-US.XML --path=./src/Config/Interpret";
    exec($command);

    (file_exists('./src/Config/Interpret/01.configurableValueFromBrex.xml')) ? $this->assertTrue(true) : $this->assertFalse(true);

    $doc = new \DOMDocument();
    $doc->load('./src/Config/Interpret/01.configurableValueFromBrex.xml');

    $schema = "./assets/schemas/interpretation.xsd";
    $doc->schemaValidate($schema) ? $this->assertTrue(true) : $this->assertFalse(true);
  }

  public function testCombineAllConfigFileForConfigurableValues(): void
  {
    unlink("./src/Config/configurableValues.xml");

    $command = "php ./tools/BrexInterpret.php -c --uri=./src/Config/configurableValues.xml --source=./src/Config/Interpret";
    exec($command);

    (file_exists('./src/Config/configurableValues.xml')) ? $this->assertTrue(true) : $this->assertFalse(true);

    $doc = new \DOMDocument();
    $doc->load('./src/Config/configurableValues.xml');

    $schema = "./assets/schemas/interpretation.xsd";
    $doc->schemaValidate($schema) ? $this->assertTrue(true) : $this->assertFalse(true);
  }
}

// $config = "./src/Ietp/xsl/config.dtd";
// $config = '<!DOCTYPE config SYSTEM "'.$config.'" >';
// $config .= file_get_contents("./src/Ietp/xsl/configpdf.xml");

// $configDoc = new \DOMDocument();
// $configDoc->loadXML($config);


// $validate = $configDoc->validate();

// $file = file_get_contents("./src/schema.xsd");
// $validate = $configDoc->schemaValidateSource($file);
// $validate = $configDoc->schemaValidate("./src/schemaTes.xsd");
// dd($validate);