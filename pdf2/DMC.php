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
    $dmCode = self::resolve_dmCode($dmCode);
    $issueInfo = ($if = self::resolve_issueInfo($issueInfo)) ? "_". $if : '';
    $languange = ($lg = self::resolve_languange($languange)) ? "_". $lg : '';

    $this->dmCode = $dmCode;
    $this->issueInfo = $issueInfo;
    $this->languange = $languange;
    $this->pdf->curEntry = $this->dmCode.$this->issueInfo.$this->languange;

    $file_withLanguangeCode = $this->absolute_path_csdbInput.DIRECTORY_SEPARATOR.strtoupper($dmCode.$issueInfo.$languange).".xml";

    $this->DOMDocument = CSDB::importDocument($file_withLanguangeCode,'','dmodule');
    
    $schemaXsd = self::getSchemaName($this->DOMDocument->firstElementChild);
    $this->schemaXsd = $schemaXsd;
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
        PMC_PDF::addIntentionallyLeftBlankPage($this->pdf);
        break;
      default:
        # code...
        break;
    }
    
    // note the end page of DMC
    $end_page = $this->pdf->getPage();
    // add page ident in every page
    $this->pdf->page_ident = $this->dmCode ?? '';
    for($i = $first_page; $i <= $end_page; $i++){
      $this->pdf->setPage($i);

      $y_pos = $this->pdf->getPageHeight() * 1 / 2;

      if($this->pdf->page_ident){
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

  }
  public function render_descriptXsd()
  {    
    // dump($this->pdf->getMargins()['left']);
    // dump($this->pdf->getPage());
    // render levelledPara only
    $DOMXpath = new \DOMXPath($this->DOMDocument);
    $description_children = $DOMXpath->evaluate("//content/description/levelledPara");
    foreach($description_children as $levelledPara){
      $this->resolve_levelledPara($levelledPara);
    }
    $this->pdf->applyCgMark($this->DOMDocument); // harus di apply di sini karena jika didalam levelledPara, bisa recursive padahal array $this->cgmark harus dikoleksi dulu semuanya
    // return;
    // dump($this->pdf->getPage());
  }

  public function resolve_levelledPara(\DOMElement $levelledPara)
  {
    $level = CSDB::checkLevel($levelledPara);

    $indentation = $this->pdf->get_pmType_config()['content']['indentation']['levelledPara'][$level];
    
    $prefixnum = CSDB::getPrefixNum($levelledPara, 1);

    $attributes = [];
    foreach($levelledPara->attributes as $attribute){
      $attributes[$attribute->nodeName] = $attribute->nodeValue;
    }
    // set id for fragment if any
    if(isset($attributes['id'])){
      $this->pdf->addInternalReference($this->pdf->curEntry, $attributes['id'], $this->pdf->getPage(), $this->pdf->GetY());
    }

    // set changemark if any
    if((isset($attributes['changeMark']) && $attributes['changeMark'] == "1") && isset($attributes['reasonForUpdateRefIds'])){
      $st_pos_y = $this->pdf->GetY();
      $reasonForUpdateRefIds = $attributes['reasonForUpdateRefIds'];
      $index_cgmark = count($this->pdf->cgmark);
      $this->pdf->addCgMark($index_cgmark, $st_pos_y, null, $this->pdf->getPage(), $reasonForUpdateRefIds, 'ini levelledPara');
    }

    foreach (CSDB::children($levelledPara, 'element') as $element){
      switch ($element->tagName) {
        case 'title':
          $this->pdf->setCellPaddings($indentation);
          $this->resolve_title($element, $level, $prefixnum);
          break;
        case 'warning':
          break;
        case 'caution':
          break;
        case 'note':
          break;
        case 'circuitBreakerDescrGroup':
          break;
        case 'para':
          $this->resolve_para($element);
          break;
        case 'figure':
          $this->resolve_figure($element);
          break;
        case 'figureAlts':
          break;
        case 'multimedia':
          break;
        case 'multimediaAlts':
          break;
        case 'foldout':
          break;
        case 'table':
          break;
        case 'levelledPara':
          $this->resolve_levelledPara($element);
          break;
        case 'levelledParaAlts':
          break;
          
      }
      if((isset($attributes['changeMark']) && $attributes['changeMark'] == "1") && isset($attributes['reasonForUpdateRefIds'])){
        $ed_pos_y = $this->pdf->GetY();
        $this->pdf->addCgMark($index_cgmark, null, $ed_pos_y, $this->pdf->getPage(), $reasonForUpdateRefIds, '');
      }
    }
  }

  public function resolve_figure(\DOMElement $figure){
    $index = CSDB::checkIndex($figure, 1);

    $xsl_figure = CSDB::importDocument(__DIR__."./xsl/figure.xsl", '',"xsl:stylesheet");
    $xsltproc = new XSLTProcessor();
    $xsltproc->importStylesheet($xsl_figure);
    $xsltproc->setParameter('','absolute_path_csdbInput', $this->pdf->getAssetPath().DIRECTORY_SEPARATOR);
    $xsltproc->setParameter('','prefixnum', $index);
    $xsltproc->setParameter('','dmOwner',$this->dmCode.$this->issueInfo.$this->languange);
    $html = $xsltproc->transformToXml(CSDB::importDocument('',$figure->C14N(),'figure'));
    $this->pdf->Ln(3);
    $this->pdf->writeHTML($html, true, false, true, true,'J',true, $tes = true, $who = "figure");
    $this->pdf->Ln(1.5);
  }

  public function resolve_title(\DOMElement $title, int $level, string $prefixnum = '')
  { 
    $fontsize = $this->pdf->get_pmType_config()['fontsize']['levelledPara']['title'][$level];
    $this->pdf->setFontSize($fontsize);

    $xsl_title = CSDB::importDocument(__DIR__."./xsl/title.xsl", '',"xsl:stylesheet");
    $xsltproc = new XSLTProcessor();
    $xsltproc->importStylesheet($xsl_title);
    // $xsltproc->setParameter('','level',$level+1);  
    $xsltproc->setParameter('','prefixnum',$prefixnum);
    $xsltproc->setParameter('','indentation',2);
    $xsltproc->setParameter('','dmOwner',$this->dmCode.$this->issueInfo.$this->languange);
    $html = $xsltproc->transformToXml(CSDB::importDocument('',$title->C14N(),'title'));
    $html = preg_replace("/[\r\n]|\s{2,}/",'',$html);

    $this->pdf->Ln(3);
    $this->pdf->writeHTML($html, true, false, true, true,'J',true);
    $this->pdf->Ln(1.5);

    $tb = '';
    foreach($title->childNodes as $node){
      $tb .= $node->nodeValue;
    }
    $this->pdf->Bookmark($prefixnum." ".$tb, $level+1, 0,'',''); // level +1 karena di PM Entry sudah di bookmark level top nya
  }

  public function resolve_para(\DOMElement $para)
  {
    $fontsize = $this->pdf->get_pmType_config()['fontsize']['levelledPara']['para'];
    $this->pdf->setFontSize($fontsize);
    
    $xsl_para = CSDB::importDocument(__DIR__."./xsl/para.xsl", '',"xsl:stylesheet");
    $xsltproc = new XSLTProcessor();
    $xsltproc->importStylesheet($xsl_para);
    $xsltproc->setParameter('','dmOwner',$this->dmCode.$this->issueInfo.$this->languange);
    $html = $xsltproc->transformToXml(CSDB::importDocument('',$para->C14N(),'para'));
    $html = preg_replace("/[\r\n]|\s{2,}/",'',$html);

    $this->pdf->writeHTML($html, true, false, true, true,'J',true, $tes = false);
    $this->pdf->Ln(1.5);
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

  public static function resolve_dmCode(\DOMElement $dmCode, string $prefix = 'DMC-')
  {
    $modelIdentCode = $dmCode->getAttribute('modelIdentCode');
    $systemDiffCode = $dmCode->getAttribute('systemDiffCode');
    $systemCode = $dmCode->getAttribute('systemCode');
    $subSystemCode = $dmCode->getAttribute('subSystemCode');
    $subSubSystemCode = $dmCode->getAttribute('subSubSystemCode');
    $assyCode = $dmCode->getAttribute('assyCode');
    $disassyCode = $dmCode->getAttribute('disassyCode');
    $disassyCodeVariant = $dmCode->getAttribute('disassyCodeVariant');
    $infoCode = $dmCode->getAttribute('infoCode');
    $infoCodeVariant = $dmCode->getAttribute('infoCodeVariant');
    $itemLocationCode = $dmCode->getAttribute('itemLocationCode');

    $name = $prefix.
    $modelIdentCode."-".$systemDiffCode."-".
    $systemCode."-".$subSystemCode.$subSubSystemCode."-".
    $assyCode."-".$disassyCode.$disassyCodeVariant."-".
    $infoCode.$infoCodeVariant."-".$itemLocationCode;

    return $name;
  }

  public static function resolve_issueInfo(\DOMElement $issueInfo = null)
  {
    if(!$issueInfo){
      return '';
    }
    $issueNumber = $issueInfo->getAttribute('issueNumber');
    $inWork = $issueInfo->getAttribute('inWork');
    return $issueNumber."-".$inWork;
  }

  public static function resolve_languange(\DOMElement $languange = null)
  {
    if(!$languange) {
      return '';
    }
    $languangeIsoCode = $languange->getAttribute('languageIsoCode');
    $countryIsoCode = $languange->getAttribute('countryIsoCode');
    return $languangeIsoCode."-".$countryIsoCode;
  }

}