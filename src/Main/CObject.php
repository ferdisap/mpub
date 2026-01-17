<?php

namespace Ptdi\Mpub\Main;

use Ptdi\Mpub\Main\CSDBStatic;
use Ptdi\Mpub\Main\Helper;
use Ptdi\Mpub\Main\ICNDocument;

class CObject
{
  public bool $preserveWhiteSpace = false;
  public bool $formatOutput = false;
  protected CError|null $errors = null;
  public static $availableSchema = ["appliccrossreftable.xsd", "brdoc.xsd", "brex.xsd", "checklist.xsd", "comment.xsd", "comrep.xsd", "condcrossreftable.xsd", "container.xsd", "crew.xsd", "dc.xsd", "ddn.xsd", "descript.xsd", "dml.xsd", "fault.xsd", "frontmatter.xsd", "icnmetadata.xsd", "ipd.xsd", "learning.xsd", "pm.xsd", "prdcrossreftable.xsd", "proced.xsd", "process.xsd", "rdf.xsd", "sb.xsd", "schedul.xsd", "scocontent.xsd", "scormcontentpackage.xsd", "update.xsd", "wrngdata.xsd", "wrngflds.xsd", "xcf.xsd"];
  protected string $initial = ''; //biasanya kan di setiap document ada dmRef, pmRef. Nah {dm}Ref dm nya adalah initial
  protected string $filename = ''; // tanpa path

  /**
   *  bisa berupa getID3 array or \DOMDocument
   */
  protected \DOMDocument|ICNDocument|null $document = null;

  public function __construct()
  {
    $this->errors = new CError();
  }

  /**
   * Belum mencakup seluruh S1000D doctype. Tinggal tambahkan di array nya
   */
  public function isS1000DDoctype()
  {
    if (($this->document instanceof \DOMDocument) && ($this->document->doctype) && in_array($this->document->doctype->nodeName, self::$availableSchema)) {
      return true;
    } else {
      $this->errors->set('s1000d_doctype', ['document must be be S1000D standard type.']);
      return false;
    }
  }

  /**
   * Set the object document, wheter it result is DOMDcoument or Array (if ICN file)
   * @param string $path dengan absolute path 
   * @return bool true or false
   */
  public function load($path): bool
  {
    libxml_use_internal_errors(true);
    $mime = file_exists($path) ? \GuzzleHttp\Psr7\MimeType::fromFilename($path) : 'undefined';
    if (str_contains($mime, 'text') || str_contains($mime, 'xml')) {
      $dom = new \DOMDocument('1.0');
      $dom->preserveWhiteSpace = $this->preserveWhiteSpace;
      $dom->formatOutput = $this->formatOutput;
      @$dom->load($path, LIBXML_PARSEHUGE);
      $errors = libxml_get_errors();

      if (count($errors)) {
        $this->errors->set('file_exist', []);
        foreach ($errors as $e) {
          $this->errors->append('file_exist', CError::display_xml_error($e));
        }
        libxml_clear_errors();
      }
      if (!$dom->documentElement) return false;
      $this->document = $dom;
      return true;
    } elseif ($mime === 'undefined') {
      $this->errors->set('load', ["Undefined mime content type or file doesn't exist."]);
      return false;
    } else {
      $this->document = new ICNDocument();
      if ($this->document->load($path)) return true;
      return false;
    }
    return false;
  }

  public function loadXML(string $text): bool
  {
    libxml_use_internal_errors(true);
    $dom = new \DOMDocument('1.0');
    $dom->preserveWhiteSpace = $this->preserveWhiteSpace;
    $dom->formatOutput = $this->formatOutput;
    @$dom->loadXML($text, LIBXML_PARSEHUGE);
    $errors = libxml_get_errors();
    if (count($errors)) {
      $this->errors->set('file_exist', []);
      foreach ($errors as $e) {
        $this->errors->append('file_exist', CError::display_xml_error($e));
      }
      libxml_clear_errors();
    }
    if (!$dom->documentElement) return false;
    $this->document = $dom;
    return true;
  }

  /**
   * @param \DOMElement $element
   * @param string $doctype berupa 'dmodule', 'dml', 'pm', 'infoEntity'
   */
  public function loadByElement(\DOMElement $element, string $doctype): bool
  {
    $path = Helper::analyzeURI($element->ownerDocument->baseURI)['path'];
    $filename = '';
    switch ($doctype) {
      case 'dmodule':
        $filename = CSDBStatic::resolve_dmIdent($element);
        break;
      case 'dml':
        $filename = CSDBStatic::resolve_dmlIdent($element);
        break;
      case 'infoEntity':
        $filename = CSDBStatic::resolve_infoEntityIdent($element);
        break;
      case 'pm':
        $filename = CSDBStatic::resolve_pmIdent($element);
        break;
    }
    if ($filename) {
      return $this->load($path . DIRECTORY_SEPARATOR . $filename);
    }
    return false;
  }

  public function getSchema(string|null $option = null)
  {
    if ($this->document->doctype and $this->document instanceof \DOMDocument) {
      if (!$option) {
        return $this->document->documentElement->getAttribute("xsi:noNamespaceSchemaLocation"); // kalau document di loadXML, tidak bisa pakai fungsi getAttributeNS().
      } elseif ($option === 'filename') {
        $schema = $this->document->documentElement->getAttribute("xsi:noNamespaceSchemaLocation"); // kalau document di loadXML, tidak bisa pakai fungsi getAttributeNS().
        preg_match("/\w+..xsd/", $schema, $schema);
        if (!empty($schema)) $schema = $schema[0];
        return $schema;
      }
    }
  }

  /**
   * get and set Filename
   * @return string
   */
  public function getFilename(): string
  {
    if ($this->document instanceof \DOMDocument) {
      $initial = $this->getInitial();
      $domXpath = new \DOMXPath($this->document);
      $ident = $domXpath->evaluate("//{$initial}Address/{$initial}Ident");
      if ($ident[0]) {
        // go to function resolve_dmlIdent, resolve_pmIdent, resolve_dmIdent, resolve_imfIdent
        $docIdent = call_user_func(CSDBStatic::class . "::resolve_" . $initial . "Ident", [$ident[0]]); //  argument#0 domElement / array, argument#1 prefix, argument#2 format
      }
      return $this->filename = $docIdent;
    } elseif ($this->document instanceof ICNDocument) {
      return $this->filename = $this->document['filename'];
    } else {
      return $this->filename = '';
    }
  }

  /**
   * biasanya kan di setiap document ada dmRef, pmRef. Nah {dm}Ref dm nya adalah initial
   * get and set Initial.
   * @return string
   */
  public function getInitial(): string
  {
    if ($this->document instanceof \DOMDocument) {
      if ($this->document->doctype) $initial = $this->document->doctype->nodeName;
      else $initial = $this->document->documentElement->nodeName;
      $initial = $initial === 'dmodule' ? 'dm' : (($initial === 'icnMetadataFile' || $initial === 'icnmetadata') ? 'imf' : $initial);
      return $this->initial = $initial;
    }
    return '';
  }

  /**
   * awalnya dibuat untuk fungsi CSDBObject@getBrexDm, class ini
   * @return bool
   */
  public function isBrex() :bool
  {
    $decode = CSDBStatic::decode_dmIdent($this->filename);
    if($decode['dmCode']['infoCode'] === '022') return true;
    return false;
  }

    public function query($xpath = '')
  {
    if(!($this->document instanceof \DOMDocument)) return '';
    if(!($xpath)) return '';
    $domXpath = new \DOMXpath($this->document);
    return [...$domXpath->query($xpath)];
  }

  public function evaluate($xpath = '')
  {
    if(!($this->document instanceof \DOMDocument)) return '';
    if(!($xpath)) return '';
    $domXpath = new \DOMXpath($this->document);
    return $domXpath->evaluate($xpath);
  }

}
