<?php

namespace Ptdi\Mpub\Pdf2;

use DOMXPath;
use Ptdi\Mpub\CSDB;
// use Ptdi\Mpub\Pdf2\Helper\TextElemGroup;
use TCPDF;
use XSLTProcessor;

class DMC 
{
  public string $absolute_path_csdbInput = '';
  public PMC_PDF $pdf;
  protected \DOMDocument $DOMDocument;
  protected bool $validateSchema = true;
  protected bool $validateBrex = true;
  protected string $schemaXsd;

  public function importDocument_byIdent(\DOMElement $identExtension = null, \DOMElement $dmCode, \DOMElement $issueInfo = null, \DOMElement $languange = null)
  {
    $dmCode = CSDB::resolve_dmCode($dmCode);
    $issueInfo = ($if = CSDB::resolve_issueInfo($issueInfo)) ? "_". $if : '';
    $languange = ($lg = CSDB::resolve_languange($languange)) ? "_". $lg : '';

    $this->dmCode = $dmCode;
    $this->issueInfo = $issueInfo;
    $this->languange = $languange;
    $this->pdf->curEntry = $this->dmCode.$this->issueInfo.$this->languange;

    $file_withLanguangeCode = $this->absolute_path_csdbInput.DIRECTORY_SEPARATOR.strtoupper($dmCode.$issueInfo.$languange).".xml";

    $this->DOMDocument = CSDB::importDocument($file_withLanguangeCode,'','dmodule');
    
    $schemaXsd = self::getSchemaName($this->DOMDocument->firstElementChild);
    $this->schemaXsd = $schemaXsd;
    $this->pdf->page_ident = $this->pdf->get_pmEntryType_config()['printpageident'] ? $this->dmCode : '';

    $dmTitle = $this->DOMDocument->getElementsByTagName("dmTitle")[0];
    $techname = $dmTitle->firstElementChild->nodeValue;
    $infoname = $dmTitle->firstElementChild->nextElementSibling ? $dmTitle->firstElementChild->nextElementSibling->nodeValue : null;
    
    $this->pdf->Bookmark($techname.($infoname ? '-'. $infoname : ''),$this->pdf->pmEntry_level += 1);
  }

  public function render()
  {
    // note the first page of DMC
    $first_page = $this->pdf->getPage();

    if(!($this->validateSchema && $this->validateBrex)){
      return false;
    }
    switch ($this->schemaXsd) {
      case 'descript.xsd':
        $this->render_descriptXsd();
        break;
      case 'frontmatter.xsd':
        $this->render_frontmatterXsd();
        break;
      default:
        # code...
        break;
    }
    $end_page = $this->pdf->getPage();
    // dd($end_page);
    for($i = $first_page; $i <= $end_page; $i++){
      $this->pdf->setPage($i);
      $w = 5;
      $h = 60;
      $y_pos = $this->pdf->getPageHeight() * 1 / 3;
      $tmpl = $this->pdf->startTemplate($w,$h);
      $this->pdf->StartTransform();
      $this->pdf->setFontSize(6);
      $this->pdf->Rotate(90,18,18);
      $this->pdf->Text('','',$this->pdf->page_ident);
      $this->pdf->StopTransform();
      $this->pdf->endTemplate();
      if(($i % 2) == 0){
        $x = $this->pdf->getPageWidth() - $this->pdf->get_pmType_config()['page']['margins']['L'];
      } else{
        $x = $this->pdf->getPageWidth() - $this->pdf->get_pmType_config()['page']['margins']['R'];
      }
      $this->pdf->printTemplate($tmpl, $x, $y_pos, $w, $h, '', '', false);
    }
  }

  public function render_frontmatterXsd(){
    
    $this->pdf->page_ident = $this->pdf->get_pmEntryType_config()['printpageident'] ? $this->dmCode : '';
    $CSDB_class_methods = array_map(function($name){
      return CSDB::class."::$name";
    },get_class_methods(CSDB::class));

    $xsl = CSDB::importDocument(__DIR__."./xsl/frontmatter.xsl", '',"xsl:stylesheet");
    $xsltproc = new XSLTProcessor();
    $xsltproc->importStylesheet($xsl);
    $xsltproc->registerPHPFunctions($CSDB_class_methods);
    $xsltproc->registerPHPFunctions();
    
    $xsltproc->setParameter('','dmOwner',$this->dmCode.$this->issueInfo.$this->languange);
    $xsltproc->setParameter('','absolute_path_csdbInput', $this->pdf->getAssetPath().DIRECTORY_SEPARATOR);
    $xsltproc->setParameter('','logo_ptdi', __DIR__.DIRECTORY_SEPARATOR."assets".DIRECTORY_SEPARATOR."Logo-PTDI.jpg");
    
    $html = $xsltproc->transformToXml($this->DOMDocument);
    $html = preg_replace("/(?<=>)[\s]{2,}/",'',$html); // usntuk menghilangkan space/enter/multispace diawal setelah tag >

    $this->pdf->setPageOrientation($this->pdf->get_pmType_config()['page']['orientation']);
    $this->pdf->setPageUnit($this->pdf->get_pmType_config()['page']['unit']);

    $this->pdf->writeHTML($html, true, false, true, true,'J',true, $DOMDocument = $this->DOMDocument, $usefootnote = false ,$tes = true);
    
    // $this->pdf->addIntentionallyLeftBlankPage($this->pdf);
  }

  public function render_descriptXsd()
  { 
    $xsl = CSDB::importDocument(__DIR__."./xsl/descript.xsl", '',"xsl:stylesheet");
    $xsltproc = new XSLTProcessor();
    $xsltproc->importStylesheet($xsl);
    $xsltproc->registerPHPFunctions();

    $padding_levelPara = $this->pdf->get_pmType_config()['content']['padding']['levelledPara'];
    $xsltproc->setParameter('',"padding_levelPara_1", $padding_levelPara[0]);
    $xsltproc->setParameter('',"padding_levelPara_2", $padding_levelPara[1]);
    $xsltproc->setParameter('',"padding_levelPara_3", $padding_levelPara[2]);
    $xsltproc->setParameter('',"padding_levelPara_4", $padding_levelPara[3]);
    $xsltproc->setParameter('',"padding_levelPara_5", $padding_levelPara[4]);

    $fontsize_levelPara_title = $this->pdf->get_pmType_config()['fontsize']['levelledPara']['title'];
    $xsltproc->setParameter('',"fontsize_levelledPara_title_1", $fontsize_levelPara_title[0]);
    $xsltproc->setParameter('',"fontsize_levelledPara_title_2", $fontsize_levelPara_title[1]);
    $xsltproc->setParameter('',"fontsize_levelledPara_title_3", $fontsize_levelPara_title[2]);
    $xsltproc->setParameter('',"fontsize_levelledPara_title_4", $fontsize_levelPara_title[3]);
    $xsltproc->setParameter('',"fontsize_levelledPara_title_5", $fontsize_levelPara_title[4]);

    $xsltproc->setParameter('','dmOwner',$this->dmCode.$this->issueInfo.$this->languange);
    $xsltproc->setParameter('','absolute_path_csdbInput', $this->pdf->getAssetPath().DIRECTORY_SEPARATOR);

    $html = $xsltproc->transformToXml($this->DOMDocument);
    $html = preg_replace("/(?<=>)[\s]{2,}/",'',$html); // usntuk menghilangkan space/enter/multispace diawal setelah tag >
    $html = preg_replace("/[\n\r\s]+(?=<.+isfootnote)/",'[?f]',$html); // untuk menghilangkan space ketika didepan ada footnote
  
    $this->pdf->setPageOrientation($this->pdf->get_pmType_config()['page']['orientation']);
    $this->pdf->setPageUnit($this->pdf->get_pmType_config()['page']['unit']);
    $this->pdf->writeHTML($html, true, false, true, true,'J',true, $DOMDocument = $this->DOMDocument, $usefootnote = true, $tes = true);
    $this->pdf->applyCgMark($this->DOMDocument); // harus di apply di sini karena jika didalam levelledPara, bisa recursive padahal array $this->cgmark harus dikoleksi dulu semuanya

    // $this->pdf->addIntentionallyLeftBlankPage($this->pdf);
  }
  public static function getSchemaName(\DOMElement $dmodule)
  {
    $xsiNoNamespaceSchemaLocation = $dmodule->getAttribute("xsi:noNamespaceSchemaLocation");
    preg_match('/[a-z]+(?=.xsd)/', $xsiNoNamespaceSchemaLocation, $matches ,PREG_OFFSET_CAPTURE, 0);
    if ($matches){
      return $matches[0][0].".xsd";
    } else {
      return false;
    }
  }


}