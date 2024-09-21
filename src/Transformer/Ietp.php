<?php 

namespace Ptdi\Mpub\Transformer;

use Ptdi\Mpub\Main\CSDBStatic;
use Ptdi\Mpub\Main\Helper;

class Ietp
{
  /**
   * check is this class ready to create or not
   */
  protected bool $isReady;

  /**
   * untuk default main xsl file
   */
  public string $xsl;

  /**
   * untuk transform()
   */
  public array $params;

  /**
   * @param string $input adalah xml csdb object
   */
  public function __construct(public string $input, public string $output)
  {
    $this->isReady = file_exists($input);
  }

  public function create()
  {
    // $xsl = $this
  }

  public function transform()
  {
    $xsl = new \DOMDocument();
    $xsl->load($this->xsl ?? "./xsl/main.xsl");
    
    $xsltproc = new \XSLTProcessor();
    $xsltproc->importStylesheet($xsl);

    $xsltproc->registerPHPFunctions((fn () => array_map(fn ($name) => CSDBStatic::class . "::$name", get_class_methods(CSDBStatic::class)))());
    $xsltproc->registerPHPFunctions((fn () => array_map(fn ($name) => Helper::class . "::$name", get_class_methods(Helper::class)))());
    $xsltproc->registerPHPFunctions((fn () => array_map(fn ($name) => self::class . "::$name", get_class_methods(self::class)))());
    $xsltproc->registerPHPFunctions();

    foreach ($this->params as $key => $param) {
      $xsltproc->setParameter('', $key, $param);
    }
  }
}