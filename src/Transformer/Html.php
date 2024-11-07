<?php

namespace Ptdi\Mpub\Transformer;

class Html extends Transformator
{
  public string $config;
  public string $configurableValues;

  protected bool $isReady;

  /**
   * @param string $input berupa xsl stylesheet uri
   * @param string $output berupa uri dengan extension html
   */
  public function __construct(public string $input, public string $output = '')
  {
    if (!file_exists($input)) {
      if (!($i = realpath($input))) {
        if (!($i = realpath(__DIR__ . '/' . $input))) {
          $this->isReady = false;
        } else {
          $this->input = $i;
          $this->isReady = true;
        }
      } else {
        $this->input = $i;
        $this->isReady = true;
      }
    };
    $this->isReady = true;

    $this->config = parent::config_uri();
    $this->configurableValues = parent::configurableValues_uri();
  }

  public function create(string $source, array $params = []): bool
  {
    if (!$this->isReady) return false;

    $sourceDoc = new \DOMDocument();
    $sourceDoc->load($source);
    // $xsltproc = parent::makeProcessor(array_merge($params,['base_uri' => realpath($sourceDoc->baseURI)]));
    $xsltproc = parent::makeProcessor($params);
    
    if(file_exists($this->output)) unlink($this->output);
    return $xsltproc->transformToUri($sourceDoc, $this->output) && file_exists($this->output);
  }

  public function createHtml(string $source, array $params = []): bool|String
  {
    if (!$this->isReady) return false;

    $sourceDoc = new \DOMDocument();
    $sourceDoc->load($source);
    $xsltproc = parent::makeProcessor($params);
    
    return $xsltproc->transformToXml($sourceDoc);
  }
}
