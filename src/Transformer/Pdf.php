<?php

namespace Ptdi\Mpub\Transformer;

use Ptdi\Mpub\Main\CSDBObject;
use Ptdi\Mpub\Main\CSDBStatic;
use Ptdi\Mpub\Main\Helper;

class Pdf extends Transformator
{
  protected bool $isReady;

  public string $config;

  /**
   * current masterName
   */
  protected string $PDF_MasterName = '';

  /**
   * digunakan agar tidak ada multiple masterName di xsl fo layout
   * dicall di default-pm.xsl
   */
  protected array $masterName = [];

  /**
   * what entryType (@pmEntryType) used currently of transformatting
   * digunakan maintPlanning (scheduleXsd) karena table-table nya beda style. Mungkin akan digunakan di schema lainnya nanti
   * value string sebaiknya bukan berupa S1000D standard attribute value, melainkan sudah di interpretasikan, misal pmt01 adalah 'TP' atau 'Title Page'
   */
  protected string $pmEntryType = '';

  /**
   * [
   *  'id-000' => [ 
   *    'text' => 'lorem ipsum',
   *    'parent' => '',
   *  ],
   *  'id-001' => [
   *    'text' => 'lorem ipsum 2',
   *    'parent' => 'id-001'
   *  ]
   * ]
   */
  protected array $bookmarks = [];

  /**
   * @param string $input berupa uri, eg: xsl fo, belum bisa csdb object langsung
   * @param string $output berupa uri dengan extension pdf
   */
  public function __construct(public string $input, public string $output)
  {
    $this->isReady = file_exists($input);
  }

  /**
   * create pdf based on xsl fo file as input
   * @return bool
   */
  public function create(): bool
  {
    if (!$this->isReady) return false;

    $input = Helper::getRelativePath(__DIR__, $this->input);
    $output = Helper::getRelativePath(__DIR__, $this->output);
    $config = isset($this->config) && file_exists($this->config) ? Helper::getRelativePath(__DIR__, $this->config) : 'conf/fop.xconf';

    $is_OS_Windows = PHP_OS === 'WINNT' || PHP_OS === 'Windows' || PHP_OS === 'WIN32';
    $command = ($is_OS_Windows ? "fop" : './fop') . " -c $config -fo $input -pdf $output";
    chdir(__DIR__ . "/../Fop");
    shell_exec($command);

    return file_exists($output);
  }

  public function get_PDF_MasterName()
  {
    return $this->PDF_MasterName;
  }

  public function set_PDF_MasterName(string $text)
  {
    $this->PDF_MasterName = $text;
  }

  /**
   * digunakan sekalian untuk check apakah masterName sudah di tambahkan ke layout atau belum
   */
  public function add_masterName(string $name)
  {
    if (!in_array($name, self::$masterName, true)) {
      self::$masterName[] = $name;
      return true;
    } else {
      return false;
    }
  }

  /**
   * diperlukan untuk di XSL
   */
  public function set_pmEntryTitle(string $text)
  {
    $this->pmEntryTitle = $text;
  }

  /**
   * diperlukan untuk di XSL
   */
  public function get_pmEntryTitle()
  {
    return $this->pmEntryTitle;
  }

  /**
   */
  public function get_pmEntryType()
  {
    return $this->pmEntryType;
  }

  /**
   */
  public function set_pmEntryType(string $text)
  {
    $this->pmEntryType = $text;
  }

  /**
   */
  public function fillBookmark(string $destination, string $text, string $parent = '')
  {
    $this->bookmarks[$destination] = [
      'text' => $text,
      'parent' => $parent,
    ];
  }

  /**
   * tes saja
   */
  public function createFo(string $source, array $params = []): bool
  {
    $sourceDoc = new \DOMDocument();
    $sourceDoc->load($source);
    $xsltproc = parent::makeProcessor($params);

    unlink($this->output);
    $create = $xsltproc->transformToUri($sourceDoc, $this->output) && file_exists($this->output);

    // fill bookmark
    if ($create) {
      $transformed = new \DOMDocument();
      $transformed->load($this->output);
      $bookmarkTree_el = $transformed->getElementsByTagNameNS('http://www.w3.org/1999/XSL/Format', 'bookmark-tree')[0];
      $new_bookmarks = $this->transformBookmark_to_xml();
      if ($new_bookmarks) {
        $new_bookmarks = $new_bookmarks->documentElement->cloneNode(true);
        $imported = $bookmarkTree_el->ownerDocument->importNode($new_bookmarks, true);
        $bookmarkTree_el->replaceWith($imported);
      } else {
        $bookmarkTree_el ? $bookmarkTree_el->remove() : null;
      }
      file_put_contents($this->output, $transformed->saveXML(null, LIBXML_NOXMLDECL));
    }

    return $create;
  }

  /**
   * @return \DOMDocument
   */
  private function transformBookmark_to_xml()
  {
    // dump($this->bookmarks);
    if (empty($this->bookmarks)) return '';
    $dom = new \DOMDocument;
    $bookmarkTree_el = $dom->createElementNS('http://www.w3.org/1999/XSL/Format', 'bookmark-tree');
    $dom->appendChild($bookmarkTree_el);

    while (!empty($this->bookmarks)) {
      $keyfirst = array_key_first($this->bookmarks);

      $parent = $this->bookmarks[$keyfirst]['parent'];

      $bookmark_el = $dom->createElementNS('http://www.w3.org/1999/XSL/Format', 'bookmark');
      $bookmarkTitle_el = $dom->createElementNS('http://www.w3.org/1999/XSL/Format', 'bookmark-title');
      $bookmark_el->setAttribute('internal-destination', $keyfirst);
      $bookmarkTitle_el->textContent = $this->bookmarks[$keyfirst]['text'];

      $bookmark_el->appendChild($bookmarkTitle_el);
      $bookmarkTree_el->appendChild($bookmark_el);

      if ($parent) {
        $domxpath = new \DOMXpath($dom);
        $domxpath->registerNamespace('fo', 'http://www.w3.org/1999/XSL/Format');
        $xpath_string = "//fo:bookmark[@id = '$parent']";
        $e = $domxpath->query($xpath_string)[0];
        if ($e) {
          $e->appendChild($bookmark_el);
        }
      } else {
        $dom->appendChild($bookmarkTree_el);
      }
      unset($this->bookmarks[$keyfirst]);
    }
    return $dom;
  }
}
