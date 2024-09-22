<?php

namespace Ptdi\Mpub\Transformer;

use Ptdi\Mpub\Main\CSDBObject;
use Ptdi\Mpub\Main\CSDBStatic;
use Ptdi\Mpub\Main\Helper;

class Transformator
{
  public string $input; // uri
  public string $output; // uri
  public string $config; // uri xml
  public string $configurableValues; // uri xml
  public string $csdb_path; // absolute path

  // public mixed $CSDBObject;

  public static function config_uri()
  {
    return str_replace(' ', '%20', 'file:/'.str_replace("\\","/",realpath(__DIR__.'/../Config/config.xml'))); // sama dengan baseURI DOMDocument
  }
  public static function configurableValues_uri()
  {
    return str_replace(' ', '%20', 'file:/'.str_replace("\\","/",realpath(__DIR__.'/../Config/configurableValues.xml'))); // sama dengan baseURI DOMDocument
  }

  /**
   * @param string $source
   * input file berupa uri main.xsl file
   * @return bool
   */
  // public function createFo(array $params, array $fn = []): string
  // public function createFo(string $source, array $params = []): bool
  public function makeProcessor(array $params = []) :\XSLTProcessor
  {
    $xsl = new \DOMDocument();
    $xsl->load($this->input);

    $xsltproc = new \XSLTProcessor();
    $xsltproc->importStylesheet($xsl);

    // hindari pemakaian fungsi CSDBObject pada xsl file karena sekarang di pusatkan di sini
    $xsltproc->registerPHPFunctions((fn () => array_map(fn ($name) => CSDBStatic::class . "::$name", get_class_methods(CSDBStatic::class)))());
    $xsltproc->registerPHPFunctions((fn () => array_map(fn ($name) => Helper::class . "::$name", get_class_methods(Helper::class)))());
    $xsltproc->registerPHPFunctions((fn () => array_map(fn ($name) => self::class . "::$name", get_class_methods(self::class)))());
    $xsltproc->registerPHPFunctions((fn () => array_map(fn ($name) => get_class($this) . "::$name", get_class_methods(get_class($this))))());
    $xsltproc->registerPHPFunctions((fn () => array_map(fn ($name) => CSDBObject::class . "::$name", get_class_methods(CSDBObject::class)))());
    // $xsltproc->registerPHPFunctions((fn () => array_map(fn ($name) => $this->CSDBObject::class . "::$name", get_class_methods($this->CSDBObject::class)))());
    $xsltproc->registerPHPFunctions();

    foreach ($params as $key => $param) {
      $xsltproc->setParameter('', $key, $param);
    }

    $xsltproc->setParameter('', 'config_uri', str_replace("\\","/",$this->config));
    $xsltproc->setParameter('', 'configurableValues_uri', str_replace("\\", '/',$this->configurableValues));
    $xsltproc->setParameter('', 'csdb_path', $this->csdb_path ?? './');

    return $xsltproc;
    // $sourceDoc = new \DOMDocument();
    // $sourceDoc->load($source);
    // return $xsltproc->transformToUri($sourceDoc, $this->output) && file_exists($this->output);
  }

  /**
   * 
   * @param string $name adalah nama attribute eg: 'pmType'
   * @param string $value adalah value attribute eg:'pt01'
   */
  public function interpret(string $name, string $value) :string
  {
    $config = new \DOMDocument();
    $config->load($this->configurableValues);
    $domXpath = new \DOMXPath($config);

    $interpretValue = '';
    
    $arg_list = func_get_args();
    $use_i = 2;
    $use = $arg_list[$use_i] ?? 'default';
    while(!($interpretValue = $domXpath->evaluate("string(//attr[@name='$name' and @value='$value']/interpretation[@use='$use'])"))){
      $use_i += 1;
      $use = $arg_list[$use_i];
      if(!$use) break;
    }
    return $interpretValue ? $interpretValue : '';
  }

  /**
   * check level based on numbering eg: 1.2.1 is level 2
   */
  public static function checkLevelByPrefix(string $prefix = '')
  {
    return count(explode('.', $prefix));
  }

  /**
   * checking index by sibling
   * @return int
   */
  public static function checkIndex(\DOMElement $element, int $minimum = 0): int
  {
    $tagName = $element->tagName;
    $parent = $element->parentNode;
    $index = $minimum;
    if ($parent) {
      while ($prev_el = $element->previousElementSibling) {
        if ($prev_el->tagName == $tagName) {
          $index += 1;
        }
        $element = $prev_el;
      }
      return (int) $index;
    }
  }

  /**
   * @return int
   */
  public static function getPrefixNum(\DOMElement $element, $minimum = 0): string
  {
    $tagName = $element->tagName;
    $index = self::checkIndex($element) + $minimum;
    $prefixnum = array($index);

    while (($parent = $element->parentNode)->nodeName == $tagName) {
      $index = self::checkIndex($parent) + $minimum;
      array_push($prefixnum, $index);
      $element = $parent;
    }
    $prefixnum = array_reverse($prefixnum);
    return (string) join(".", $prefixnum);
  }

  public static function interpretDimension(string $unit): string
  {
    // <xsl:variable name="units" select="php:function('preg_replace', '/[0-9\.]+/' ,'', string(ancestor::tgroup/colspec[1]/@colwidth))"/>
    if (!$unit) return '';
    preg_match('/([0-9\.]+)(.)/', $unit, $matches);
    $n = $matches[1];
    $u = $matches[2];
    if (($n > 0) and ($n <= 1)) {
      $n = $n * 100;
    }
    if ($u === '*') {
      $u = '%';
    }
    return $n . $u;
  }
  
}
