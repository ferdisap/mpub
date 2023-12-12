<?php

namespace Ptdi\Mpub\Pdf2\male;

use DOMDocument;
use DOMXPath;
use Ptdi\Mpub\CSDB;
use Ptdi\Mpub\Pdf2\DMC;
use Ptdi\Mpub\Pdf2\PMC_PDF;
use TCPDF;
use TCPDF_COLORS;
use TCPDF_FONT_DATA;
use TCPDF_FONTS;
use TCPDF_STATIC;
use XSLTProcessor;

class PMC_male extends PMC_PDF
{
  protected $modelIdentCode = 'male';
  protected $shortPmTitle = '';
  protected $pmTitle = '';
  protected $pmEntryTitle = '';
  protected $responsiblePartnerCompany = '';

  protected function pmEntry(\DOMElement $pmEntry, bool $allowLocalFiles = true)
  {

    $this->setAllowLocalFiles($allowLocalFiles);

    $this->pmEntryTitle = $pmEntry->getElementsByTagName('pmEntryTitle')[0];
    if ($this->pmEntryTitle) $this->pmEntryTitle = $this->pmEntryTitle->nodeValue;

    $this->pmTitle = $this->DOMDocument->getElementsByTagName('pmTitle')[0];
    $this->shortPmTitle = $this->pmTitle->nextElementSibling;
    $this->pmTitle = $this->pmTitle->nodeValue;
    if ($this->shortPmTitle) $this->shortPmTitle = $this->shortPmTitle->nodeValue;

    $this->responsiblePartnerCompany = $this->DOMDocument->getElementsByTagName('responsiblePartnerCompany')[0];
    $this->responsiblePartnerCompany = $this->responsiblePartnerCompany ? ($this->responsiblePartnerCompany->firstElementChild ? $this->responsiblePartnerCompany->firstElementChild->nodeValue :
      $this->responsiblePartnerCompany->getAttribute('enterpriseCode')
    ) : '';

    $pmEntryType_config = $this->attributes['pmEntryType'][$pmEntry->getAttribute('pmEntryType')];
    $this->pmEntryType_config = $pmEntryType_config;

    $orientation = $this->pmType_config['page']['orientation'];
    $headerMargin = $this->pmType_config['page']['headerMargin'];
    $footerMargin = $this->pmType_config['page']['footerMargin'];
    $topMargin = isset($pmEntryType_config['page']['margins']['T']) ? $pmEntryType_config['page']['margins']['T'] :  $this->pmType_config['page']['margins']['T'];
    $bottomMargin = isset($pmEntryType_config['page']['margins']['B']) ? $pmEntryType_config['page']['margins']['B'] : $this->pmType_config['page']['margins']['B'];
    $leftMargin = isset($pmEntryType_config['page']['margins']['B']) ? $pmEntryType_config['page']['margins']['L'] : $this->pmType_config['page']['margins']['L'];
    $rightMargin = isset($pmEntryType_config['page']['margins']['B']) ? $pmEntryType_config['page']['margins']['R'] : $this->pmType_config['page']['margins']['R'];
    $fontsize = $this->pmType_config['fontsize']['para'];
    $this->SetFont($this->pmType_config['fontfamily']);

    $this->setHeaderMargin($headerMargin);
    $this->setFooterMargin($footerMargin);
    $this->setMargins($leftMargin, $topMargin, $rightMargin);
    $this->setAutoPageBreak(true, $bottomMargin);
    $orientation == 'L' ? $this->setVgutter(10) : $this->setBooklet(true, $rightMargin, $leftMargin);
    $this->setFontSize($fontsize);
    $this->setImageScale(PDF_IMAGE_SCALE_RATIO);

    $this->setPrintHeader($pmEntryType_config['useheader'] ?? $this->pmType_config['useheader']);
    $this->startPageGroup();
    ($this->page < 1) ? $this->AddPage() : null;
    $orientation == 'L' ? $this->setVgutter(10) : $this->setBooklet(true, $leftMargin, $rightMargin);
    $this->setPrintFooter($pmEntryType_config['usefooter'] ?? $this->pmType_config['usefooter']);

    $TOC = $pmEntryType_config['usetoc'] ?? false;
    $BOOKMARK = $pmEntryType_config['usebookmark'] ?? false;

    $level = $this->checkLevel($pmEntry);
    if ($BOOKMARK) {
      $title = $this->pmTitle;
      $txt = ($pmEntryTitle = $pmEntry->firstElementChild)->tagName == 'pmEntryTitle' ? $pmEntryTitle->nodeValue : ($title);
      $this->Bookmark(strtoupper($txt), $level);
    }
    $children = CSDB::get_childrenElement($pmEntry);
    foreach ($children as $key => $child) {
      // dd($child->nodeName, $this->pmTitle);
      switch ($child->nodeName) {
        case 'dmRef':
          $this->setFontSize($fontsize);
          $this->pmEntry_level = $level;
          $this->resetFootnotes();
          if (($this->page > 1) and ($this->page % 2 == 0)) {
            $this->AddPage();
            $orientation == 'L' ? $this->setVgutter(10) : $this->setBooklet(true, $leftMargin, $rightMargin);
          }
          $this->dmRef($child);
          $this->addIntentionallyLeftBlankPage($this);
          break;
        case 'pmRef':
          $filename = CSDB::resolve_pmIdent($child->getElementsByTagName('pmRefIdent')[0]);
          $this->importDocument($this->absolute_path_csdbInput . DIRECTORY_SEPARATOR, $filename, '');
          $this->render();
          break;
        case 'externalPubRef';
          $this->setLeftMargin($leftMargin);
          $xsl = CSDB::importDocument(PMC_PDF::pmcpdf_path() . DIRECTORY_SEPARATOR . "/general/xsl/", 'externalPubRef.xsl');

          $externalPubRef = new DOMDocument();
          $child = $externalPubRef->importNode($child->cloneNode(true), true);
          $externalPubRef->appendChild($child);

          $xsltprocc = new XSLTProcessor();
          $xsltprocc->importStylesheet($xsl);
          $html = $xsltprocc->transformToXml($externalPubRef);

          $this->setPrintHeader(false);
          $this->setPrintFooter(false);
          $this->AddPage();
          $this->writeHTML($html, true, false, true, true,'J',true, $DOMDocument = null, $usefootnote = false);
          $this->addIntentionallyLeftBlankPage($this);
          break;
      }
    }

    // add TOC
    if ($TOC) {
      $this->addTOCPage();
      $this->SetFont($this->getFontFamily(), 'B', 14);
      $this->MultiCell(0, 0, chr(10) . strtoupper($this->pmTitle) . chr(10) . strtoupper($this->shortPmTitle) . chr(10) . chr(10), 0, 'C', 0, 1, '', '', true, 0);
      $this->MultiCell(0, 0, 'Table Of Content', 0, 'C', 0, 1, '', '', true, 0);
      $this->Ln();
      $this->SetFont($this->getFontFamily(), '', 10);
      $this->addTOC(!empty($this->endPageGroup) ? ($this->endPageGroup + 1) : 1, $this->getFontFamily(), '.', $txt, 'B', array(128, 0, 0));
      $this->endTOCPage();
    }
    $this->endPageGroup = $this->getPage();
    $this->updateLink();
  }

  private function dmRef(\DOMElement $dmRef)
  {
    // if(($this->page > 1) AND ($this->page % 2 == 0)){
    // $this->AddPage();
    // $this->setBooklet($this->lMargin,$this->rMargin);
    // }

    // $dmc = new DMC();
    $dmc = DMC::instance('male');
    $dmc->absolute_path_csdbInput = $this->absolute_path_csdbInput;
    $dmc->pdf = $this;
    $dmc->setDocument($dmRef);
    $this->dm_issueDate_rendering = $dmc->issueDate;
    $dmc->render();
    // // dd($dmc->pdf->getMargins()['left']);
    // dump($dmc->pdf->getPage()."|".$dmc->pdf->getMargins()['left']);
  }

  public function getControlAuthority($id = '', $controlAuthorityType = '', $controlAuthoritySource = '', $ishtml = true)
  {
    $controlAuthoritys = array();
    $domxpath = new DOMXPath($this->DOMDocument);
    if ($id) {
      $controlAuthoritys = $domxpath->evaluate("//@id/parent::controlAuthority");
    } elseif ($controlAuthorityType) {
      $controlAuthoritys = $domxpath->evaluate("//@controlAuthorityType/parent::controlAuthority");
    } elseif ($controlAuthoritySource) {
      $controlAuthoritys = $domxpath->evaluate("//@controlAuthoritySource/parent::controlAuthority");
    } else {
      $controlAuthoritys = $domxpath->evaluate("//controlAuthority");
    }

    $txt = '';
    if (count($controlAuthoritys) > 0) {
      foreach ($controlAuthoritys as $controlAuthority) {
        // dump($controlAuthority);
        if ($ishtml) {
          $xsl = CSDB::importDocument(__DIR__ . DIRECTORY_SEPARATOR . "xsl/element/", 'controlAuthority.xsl', '', "xsl:stylesheet");
          $xsltproc = new XSLTProcessor($xsl);
          $xsltproc->importStylesheet($xsl);

          $newdoc = new DOMDocument();
          $newdoc->loadXML($controlAuthority->C14N());
          $html = $xsltproc->transformToXml($newdoc);
          $html = preg_replace("/(?<=>)[\s]{2,}/", '', $html); // usntuk menghilangkan space/enter/multispace diawal setelah tag >

          $txt .= $html;
        }
      }
      return $txt;
    }
  }
}
