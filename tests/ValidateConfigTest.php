<?php 

declare(strict_types=1);

require_once "./vendor/autoload.php";

$config = "./src/Ietp/xsl/config.dtd";
$config = '<!DOCTYPE config SYSTEM "'.$config.'" >';
$config .= file_get_contents("./src/Ietp/xsl/configpdf.xml");

$configDoc = new \DOMDocument();
$configDoc->loadXML($config);


$validate = $configDoc->validate();

// $file = file_get_contents("./src/schema.xsd");
// $validate = $configDoc->schemaValidateSource($file);
// $validate = $configDoc->schemaValidate("./src/schemaTes.xsd");
// dd($validate);