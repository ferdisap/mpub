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
    $this->pdf->page_ident = $this->dmCode;

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
        // PMC_PDF::addIntentionallyLeftBlankPage($this->pdf);
        break;
      case 'frontmatter.xsd':
        $this->render_frontmatterXsd();
      default:
        # code...
        break;
    }
    
    // note the end page of DMC
    $end_page = $this->pdf->getPage();
    // add page ident in every page
    for($i = $first_page; $i <= $end_page; $i++){
      $this->pdf->setPage($i);
      $y_pos = $this->pdf->getPageHeight() * 1 / 2;

      $template_dmc_identification = $this->pdf->startTemplate(80, 80, true);
      $this->pdf->StartTransform();
      $this->pdf->setFontSize(6);
      $this->pdf->Rotate(90, 25, 25);
      $this->pdf->Cell(50, 0, $this->pdf->page_ident, 0, 0, 'C', false, '', 0, false, 'T', 'M');
      $this->pdf->StopTransform();
      $this->pdf->endTemplate();

      if(($i % 2) == 0){
        $x = $this->pdf->getPageWidth() - $this->pdf->get_pmType_config()['page']['margins']['L'];
        $this->pdf->printTemplate($template_dmc_identification, $x, $y_pos, '', '', '', '', false);
      } else{
        $x = $this->pdf->getPageWidth() - $this->pdf->get_pmType_config()['page']['margins']['R'];
        $this->pdf->printTemplate($template_dmc_identification, $x, $y_pos, '', '', '', '', false);
      }
    }
  }

  public function render_frontmatterXsd(){
    $this->pdf->page_ident = '';
    $CSDB_class_methods = array_map(function($name){
      return CSDB::class."::$name";
    },get_class_methods(CSDB::class));

    $xsl = CSDB::importDocument(__DIR__."./xsl/frontmatter.xsl", '',"xsl:stylesheet");
    $xsltproc = new XSLTProcessor();
    $xsltproc->importStylesheet($xsl);
    $xsltproc->registerPHPFunctions($CSDB_class_methods);
    
    $xsltproc->setParameter('','dmOwner',$this->dmCode.$this->issueInfo.$this->languange);
    $xsltproc->setParameter('','absolute_path_csdbInput', $this->pdf->getAssetPath().DIRECTORY_SEPARATOR);
    $xsltproc->setParameter('','logo_ptdi', __DIR__.DIRECTORY_SEPARATOR."assets".DIRECTORY_SEPARATOR."Logo-PTDI.jpg");
    
    $html = $xsltproc->transformToXml($this->DOMDocument);
    $html = preg_replace("/(?<=>)[\s]{2,}/",'',$html); // usntuk menghilangkan space/enter/multispace diawal setelah tag >
    $this->pdf->writeHTML($html, true, false, true, true,'J',true, $DOMDocument = $this->DOMDocument , $tes = true);
    
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

    // $this->pdf->setPrintHeader($this->pdf->get_pmEntryType_config()['useheader']);
    // $this->pdf->setPrintFooter($this->pdf->get_pmEntryType_config()['usefooter']);

    $html = $xsltproc->transformToXml($this->DOMDocument);
    $html = preg_replace("/(?<=>)[\s]{2,}/",'',$html); // usntuk menghilangkan space/enter/multispace diawal setelah tag >
    $html = preg_replace("/[\n\r\s]+(?=<.+isfootnote)/",'[?f]',$html); // untuk menghilangkan space ketika didepan ada footnote
    // dd($html);
    // dd('a',$html);
    $this->pdf->writeHTML($html, true, false, true, true,'J',true, $DOMDocument = $this->DOMDocument, $usefootnote = true, $tes = true);
    // $this->pdf->writeHTML($html, true, false, true, true,'J',true, null , $tes = true);
    $this->pdf->applyCgMark($this->DOMDocument); // harus di apply di sini karena jika didalam levelledPara, bisa recursive padahal array $this->cgmark harus dikoleksi dulu semuanya
    // $this->pdf->setPage(2);
    // $this->pdf->Line(0,$this->pdf->getPageDimensions(2)['PageBreakTrigger'], 50, $this->pdf->getPageDimensions(2)['PageBreakTrigger']);
    // dd($this->pdf);
    // dump($this->pdf->footnotes);
  }

  // public function resolve_levelledPara(\DOMElement $levelledPara)
  // {
  //   $level = CSDB::checkLevel($levelledPara);

  //   $indentation = $this->pdf->get_pmType_config()['content']['indentation']['levelledPara'][$level];
    
  //   $prefixnum = CSDB::getPrefixNum($levelledPara, 1);

  //   $attributes = [];
  //   foreach($levelledPara->attributes as $attribute){
  //     $attributes[$attribute->nodeName] = $attribute->nodeValue;
  //   }
  //   // set id for fragment if any
  //   if(isset($attributes['id'])){
  //     $this->pdf->addInternalReference($this->pdf->curEntry, $attributes['id'], $this->pdf->getPage(), $this->pdf->GetY());
  //   }

  //   // set changemark if any
  //   if((isset($attributes['changeMark']) && $attributes['changeMark'] == "1") && isset($attributes['reasonForUpdateRefIds'])){
  //     $st_pos_y = $this->pdf->GetY();
  //     $reasonForUpdateRefIds = $attributes['reasonForUpdateRefIds'];
  //     $index_cgmark = count($this->pdf->cgmark);
  //     $this->pdf->addCgMark($index_cgmark, $st_pos_y, null, $this->pdf->getPage(), $reasonForUpdateRefIds, 'ini levelledPara');
  //   }

  //   foreach (CSDB::children($levelledPara, 'element') as $element){
  //     switch ($element->tagName) {
  //       case 'title':
  //         $this->pdf->setCellPaddings($indentation);
  //         $this->resolve_title($element, $level, $prefixnum);
  //         break;
  //       case 'warning':
  //         break;
  //       case 'caution':
  //         break;
  //       case 'note':
  //         break;
  //       case 'circuitBreakerDescrGroup':
  //         break;
  //       case 'para':
  //         $this->resolve_para($element);
  //         break;
  //       case 'figure':
  //         $this->resolve_figure($element);
  //         break;
  //       case 'figureAlts':
  //         break;
  //       case 'multimedia':
  //         break;
  //       case 'multimediaAlts':
  //         break;
  //       case 'foldout':
  //         break;
  //       case 'table':
  //         break;
  //       case 'levelledPara':
  //         $this->resolve_levelledPara($element);
  //         break;
  //       case 'levelledParaAlts':
  //         break;
          
  //     }
  //     if((isset($attributes['changeMark']) && $attributes['changeMark'] == "1") && isset($attributes['reasonForUpdateRefIds'])){
  //       $ed_pos_y = $this->pdf->GetY();
  //       $this->pdf->addCgMark($index_cgmark, null, $ed_pos_y, $this->pdf->getPage(), $reasonForUpdateRefIds, '');
  //     }
  //   }
  // }

  // public function resolve_figure(\DOMElement $figure){
  //   $index = CSDB::checkIndex($figure, 1);

  //   $xsl_figure = CSDB::importDocument(__DIR__."./xsl/figure.xsl", '',"xsl:stylesheet");
  //   $xsltproc = new XSLTProcessor();
  //   $xsltproc->importStylesheet($xsl_figure);
  //   $xsltproc->setParameter('','absolute_path_csdbInput', $this->pdf->getAssetPath().DIRECTORY_SEPARATOR);
  //   $xsltproc->setParameter('','prefixnum', $index);
  //   $xsltproc->setParameter('','dmOwner',$this->dmCode.$this->issueInfo.$this->languange);
  //   $html = $xsltproc->transformToXml(CSDB::importDocument('',$figure->C14N(),'figure'));
  //   $this->pdf->Ln(3);
  //   $this->pdf->writeHTML($html, true, false, true, true,'J',true, $tes = true, $who = "figure");
  //   $this->pdf->Ln(1.5);
  // }

  // public function resolve_title(\DOMElement $title, int $level, string $prefixnum = '')
  // { 
  //   $fontsize = $this->pdf->get_pmType_config()['fontsize']['levelledPara']['title'][$level];
  //   $this->pdf->setFontSize($fontsize);

  //   $xsl_title = CSDB::importDocument(__DIR__."./xsl/title.xsl", '',"xsl:stylesheet");
  //   $xsltproc = new XSLTProcessor();
  //   $xsltproc->importStylesheet($xsl_title);
  //   // $xsltproc->setParameter('','level',$level+1);  
  //   $xsltproc->setParameter('','prefixnum',$prefixnum);
  //   $xsltproc->setParameter('','indentation',2);
  //   $xsltproc->setParameter('','dmOwner',$this->dmCode.$this->issueInfo.$this->languange);
  //   $html = $xsltproc->transformToXml(CSDB::importDocument('',$title->C14N(),'title'));
  //   $html = preg_replace("/[\r\n]|\s{2,}/",'',$html);

  //   $this->pdf->Ln(3);
  //   $this->pdf->writeHTML($html, true, false, true, true,'J',true);
  //   $this->pdf->Ln(1.5);

  //   $tb = '';
  //   foreach($title->childNodes as $node){
  //     $tb .= $node->nodeValue;
  //   }
  //   $this->pdf->Bookmark($prefixnum." ".$tb, $level+1, 0,'',''); // level +1 karena di PM Entry sudah di bookmark level top nya
  // }

  // public function resolve_para(\DOMElement $para)
  // {
  //   $fontsize = $this->pdf->get_pmType_config()['fontsize']['levelledPara']['para'];
  //   $this->pdf->setFontSize($fontsize);
    
  //   $xsl_para = CSDB::importDocument(__DIR__."./xsl/para.xsl", '',"xsl:stylesheet");
  //   $xsltproc = new XSLTProcessor();
  //   $xsltproc->importStylesheet($xsl_para);
  //   $xsltproc->setParameter('','dmOwner',$this->dmCode.$this->issueInfo.$this->languange);
  //   $html = $xsltproc->transformToXml(CSDB::importDocument('',$para->C14N(),'para'));
  //   $html = preg_replace("/[\r\n]|\s{2,}/",'',$html);

  //   $this->pdf->writeHTML($html, true, false, true, true,'J',true, $tes = false);
  //   $this->pdf->Ln(1.5);
  // }

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