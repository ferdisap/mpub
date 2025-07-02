<?php

/**
 * relative uri eg:./assets/... boleh
 * 
 * require param for interpret project configurable value eg. >>> php ./tools/BrexInterpret.php -i  --brexdoc=./assets/tests/DMC-S1000D-G-04-10-0301-00A-022A-D_001-00_EN-US.XML --path=./src/Config/Interpret
 *  -i
 *  --brexdoc=... (uri)
 *  --path=... (path folder output)
 * 
 * require param for merge: eg. >>> php ./tools/BrexInterpret.php -m --doc1=./src/Config/Interpret/projectConfigurableValue.xml --doc2=./src/Config/Interpret/color.xml
 *  -m
 *  --doc1=...(uri)
 *  --doc2=...(uri)
 * 
 * require param for combine: eg. >>> php ./tools/BrexInterpret.php -c --uri=./src/Config/config-configurableValues.xml --source=./src/Config/Interpret
 *  -c
 *  --uri = uri for saving file
 *  --source = path of source file
 */

function arguments($argv)
{
  $_ARG = array();

  foreach ($argv as $arg) {
    // echo $arg;
    if (preg_match('/--([^=]+)=(.*)/', $arg, $reg)) {
      $_ARG[$reg[1]] = $reg[2];
    } elseif (preg_match('/^-([a-zA-Z0-9_-])/', $arg, $reg)) {
      $_ARG[$reg[1]] = 'true';
    } else {
      $_ARG['input'][] = $arg;
    }
  }
  return $_ARG;
}

/**
 * sama dengan yang di FOP
 */
function getRelativePath($from, $to): string
{
  // some compatibility fixes for Windows paths
  $from = is_dir($from) ? rtrim($from, '\/') . '/' : $from;
  $to   = is_dir($to)   ? rtrim($to, '\/') . '/'   : $to;
  $from = str_replace('\\', '/', $from);
  $to   = str_replace('\\', '/', $to);

  $from     = explode('/', $from);
  $to       = explode('/', $to);
  $relPath  = $to;

  foreach ($from as $depth => $dir) {
    // find first non-matching dir
    if ($dir === $to[$depth]) {
      // ignore this directory
      array_shift($relPath);
    } else {
      // get number of remaining dirs to $from
      $remaining = count($from) - $depth;
      if ($remaining > 1) {
        // add traversals up to first matching dir
        $padLength = (count($relPath) + $remaining - 1) * -1;
        $relPath = array_pad($relPath, $padLength, '..');
        break;
      } else {
        $relPath[0] = './' . $relPath[0];
      }
    }
  }
  return implode('/', $relPath);
}

/**
 * output = relative path
 */
function schema_path(string $path = ''):string
{
  $schemaURI = str_replace("\\", "/", str_replace(getcwd(),'.',realpath(__DIR__ . "/../assets/schemas/interpretation.xsd"))); // berdasarkan working directory
  return $path ? getRelativePath($path, $schemaURI) : realpath($schemaURI);  
}

function createRoot(\DOMDocument &$doc, string $path = '') :void
{
  // $schemaURI = schema_path();
  $root = $doc->createElement('root');
  $schemaURI = schema_path($path);
  $root->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'xsi:noNamespaceSchemaLocation', $schemaURI);
  $doc->appendChild($root);
}

/**
 * akan mengambil semua '//@*' dan diubah sesuai schema interpretation.xsd
 */
function projectConfigurableValues(\DOMDocument $brexDoc, string $output): \DOMDocument
{
  $domXpath = new \DOMXPath($brexDoc);
  $attributes = [...$domXpath->evaluate("//objectPath[starts-with(./text(),'//@')]")];

  $projectConfigurableValueDoc = new \DOMDocument();
  createRoot($projectConfigurableValueDoc, $output);
  $root = $projectConfigurableValueDoc->documentElement;
  // $root = $projectConfigurableValueDoc->createElement('root');
  // $schemaURI = getRelativePath($output, "./assets/schemas/interpretation.xsd");
  // $root->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'xsi:noNamespaceSchemaLocation', $schemaURI);
  

  $l = count($attributes);
  for ($i = 0; $i < $l; $i++) {

    $values = $attributes[$i]->parentElement->getElementsByTagName("objectValue");
    $lv = count($values);
    for ($iv = 0; $iv < $lv; $iv++) {
      $attr = $projectConfigurableValueDoc->createElement('attr');
      $name = substr($attributes[$i]->nodeValue, 3);
      $attr->setAttribute('name', $name);
      $value = $values[$iv];
      if (($value->getAttribute('valueForm') === 'single') && ($value->hasAttribute('valueAllowed'))) {
        $attr->setAttribute('value', $value->getAttribute('valueAllowed'));
        $interpretation = $projectConfigurableValueDoc->createElement('interpretation');
        $interpretation->setAttribute('use', 'default');
        $interpretation->nodeValue = preg_replace("/\n|\r|\n\r/", '', $value->nodeValue);
        if (
          $value->parentElement->nodeName === 'structureObjectRule'
          && $value->parentElement->parentElement->nodeName === 'structureObjectRuleGroup'
          && $value->parentElement->parentElement->parentElement->nodeName === 'contextRules'
          && $value->parentElement->parentElement->parentElement->hasAttribute('rulesContext')
        ) {
          $interpretation->setAttribute('context', $value->parentElement->parentElement->parentElement->getAttribute('rulesContext'));
        }
        $attr->appendChild($interpretation);
        $root->appendChild($attr);
      }
    }
  }
  // $projectConfigurableValueDoc->appendChild($root);
  return $projectConfigurableValueDoc;
}

/**
 * merge doc2 to doc1
 */
function mergeInterpret(string $doc1Uri, string $doc2Uri): string
{
  $doc1 = new \DOMDocument();
  $doc2 = new \DOMDocument();
  try {
    @$doc1->load($doc1Uri);
  } catch (\Throwable $e) {
    @$doc1->loadXML($doc1Uri);
  }
  if(!isset($doc1->documentElement) || ($doc1->documentElement->tagName !== 'root')) createRoot($doc1, './src/Config');
  try {
    @$doc2->load($doc2Uri);
  } catch (\Throwable $e) {
    @$doc2->loadXML($doc2Uri);
  }
  if(!isset($doc2->documentElement) || ($doc2->documentElement->tagName !== 'root')) createRoot($doc2, './src/Config');

  $attr = $doc2->documentElement->firstElementChild;
  if(!$attr) return '';
  $merge = function ($attr) use ($doc1) {
    $doc1Xpath = new \DOMXPath($doc1);
    $name = $attr->getAttribute('name');
    $value = $attr->getAttribute('value');
    $doc1Attr = $doc1Xpath->evaluate("//attr[@name='$name' and @value='$value']")[0];
    if ($doc1Attr) {
      $interpretationsDoc1 = $doc1Attr->getElementsByTagName('interpretation');
      $interpretationsDoc2 = $attr->getElementsByTagName('interpretation');
      $l = count($interpretationsDoc2);
      $appended = [];
      
      for ($i = 0; $i < $l; $i++) {
        $filtered = array_filter(
          [...$interpretationsDoc1],
          fn ($interpretation1) => $interpretation1->getAttribute('use') ===  $interpretationsDoc2[$i]->getAttribute('use')
        );
        if (!count($filtered)) {
          $appended[] = $interpretationsDoc2[$i];
        }
      }
      $l = count($appended);
      for ($i = 0; $i < $l; $i++) {
        $appended[$i] = $appended[$i]->cloneNode(true);
        $newInterpretation = $doc1->importNode($appended[$i], true);
        $doc1Attr->appendChild($newInterpretation);
      }
    } else {
      $attr = $attr->cloneNode(true);
      $newAttr = $doc1->importNode($attr, true);
      $doc1->documentElement->appendChild($newAttr);
    }
  };
  $merge($attr);
  while ($attr->nextElementSibling) {
    $attr = $attr->nextElementSibling;
    $merge($attr);
  }
  return $doc1->saveXML();
}

function combine(string $uri, string $path): void
{
  $files = scandir($path);
  $files = array_filter($files,  fn($v) => ($v !== '.'));
  $files = array_filter($files,  fn($v) => ($v !== '..'));
  $files = array_values($files);
  $l = count($files);

  if(file_exists($uri)) unlink(realpath($uri));
  $p = preg_replace("/\/[a-zA-Z0-9_-]+.[a-zA-Z0-9_-]+$/",'',$uri);
  @mkdir($p, 0777, true);
  
  for ($i=0; $i < $l; $i++) { 
    $merged = mergeInterpret($uri, $path . "/". $files[$i]);
    file_put_contents($uri, $merged);
  }

  // change path schema location to relative
  $doc = new \DOMDocument();
  $doc->load($uri);
  $doc->documentElement->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'xsi:noNamespaceSchemaLocation', schema_path($uri));
}

$_ARG = arguments($argv);

// merging
if (isset($_ARG['m'])) {
  $mergedXMLString = mergeInterpret($_ARG['doc1'], $_ARG['doc2']);
  file_put_contents(preg_replace("/file:\/|file:\\/",'', $_ARG['doc1']), $mergedXMLString);
}

// combine
if (isset($_ARG['c'])) {
  combine($_ARG['uri'], $_ARG['source']);
}

// interpret
if (isset($_ARG['i'])) {
  $brexDoc = new \DOMDocument();
  $brexDoc->load($_ARG['brexdoc']);

  // create projectConfigurableValue
  $doc = projectConfigurableValues($brexDoc, $_ARG['path']);

  // validation against schema
  $validate = $doc->schemaValidate("./assets/schemas/interpretation.xsd");

  // save to disk
  if ($validate) {
    if (!file_exists($_ARG['path'])) {
      mkdir($_ARG['path'], 0777, true);
    }
    file_put_contents($_ARG['path'] . "/01.configurableValueFromBrex.xml", $doc->saveXML());
  }
}
