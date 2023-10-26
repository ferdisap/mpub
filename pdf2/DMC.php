<?php

namespace Ptdi\Mpub\Pdf2;

use DOMXPath;
use Exception;
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
  protected $applicability = '';

  // public function importDocument_byIdent(\DOMElement $identExtension = null, \DOMElement $dmCode, \DOMElement $issueInfo = null, \DOMElement $languange = null)
  // {
  //   $dmCode = CSDB::resolve_dmCode($dmCode);
  //   $issueInfo = ($if = CSDB::resolve_issueInfo($issueInfo)) ? "_". $if : '';
  //   $languange = ($lg = CSDB::resolve_languange($languange)) ? "_". $lg : '';

  //   $this->dmCode = $dmCode;
  //   $this->issueInfo = $issueInfo;
  //   $this->languange = $languange;
  //   $this->pdf->curEntry = $this->dmCode.$this->issueInfo.$this->languange;

  //   $file_withLanguangeCode = $this->absolute_path_csdbInput.DIRECTORY_SEPARATOR.strtoupper($dmCode.$issueInfo.$languange).".xml";

  //   $this->DOMDocument = CSDB::importDocument($file_withLanguangeCode,'','dmodule');
    
  //   $schemaXsd = self::getSchemaName($this->DOMDocument->firstElementChild);
  //   $this->schemaXsd = $schemaXsd;
  //   $this->pdf->page_ident = $this->pdf->get_pmEntryType_config()['printpageident'] ? $this->dmCode : '';

  //   $dmTitle = $this->DOMDocument->getElementsByTagName("dmTitle")[0];
  //   $techname = $dmTitle->firstElementChild->nodeValue;
  //   $infoname = $dmTitle->firstElementChild->nextElementSibling ? $dmTitle->firstElementChild->nextElementSibling->nodeValue : null;
    
  //   $this->pdf->Bookmark($techname.($infoname ? '-'. $infoname : ''),$this->pdf->pmEntry_level += 1);
  // }

  public function setDocument(\DOMElement $dmRef){
    $dmIdent =  $dmRef->getElementsByTagName('dmRefIdent')[0];
    if(!$dmIdent) return false;
    $filename = CSDB::resolve_dmIdent($dmIdent);
    
    $this->pdf->curEntry = preg_replace("/.xml/",'',$filename);
    $this->dmIdent = $this->pdf->curEntry;

    preg_match('/(\S+)_(\S+)_(\S+)/', $this->pdf->curEntry, $matches, PREG_OFFSET_CAPTURE, 0);
    $this->dmCode = $matches[1][0];
    $this->issueInfo = $matches[2][0];
    $this->language = $matches[3][0];

    $this->DOMDocument = CSDB::importDocument($this->absolute_path_csdbInput.DIRECTORY_SEPARATOR.$filename,'','dmodule');

    $schemaXsd = self::getSchemaName($this->DOMDocument->firstElementChild);
    $this->schemaXsd = $schemaXsd;
    $this->pdf->page_ident = $this->pdf->get_pmEntryType_config()['printpageident'] ? $this->dmCode : '';

    // jika ingin di bookmark setiap dmRef
    // $dmTitle = $this->DOMDocument->getElementsByTagName("dmTitle")[0];
    // $techname = $dmTitle->firstElementChild->nodeValue;
    // $infoname = $dmTitle->firstElementChild->nextElementSibling ? $dmTitle->firstElementChild->nextElementSibling->nodeValue : null;
    // $this->pdf->Bookmark($techname.($infoname ? '-'. $infoname : ''),$this->pdf->pmEntry_level += 1);
  }

  public function getApplicabilty($id = '', $options = '', $useDisplayName = true){
    $appl = CSDB::getApplicability($this->DOMDocument, $this->absolute_path_csdbInput);
    // dd($appl);
    $this->applicability = $appl['applicability'];
    $CSDB = $appl['CSDB'];
    
    if($id){
      if(!isset($this->applicability[$id])) {
        throw new Exception("No such $id inside $this->dmIdent", 1);
      };      
      $str = '';
      foreach($this->applicability[$id] as $applicPropertyIdent => $stringApplic){
        if($applicPropertyIdent[0] == '%') continue;
        $str .= $stringApplic;
      }
      return $str;
    }
    switch ($options) {
      case 'first':
        $str = '';
        foreach($this->applicability[array_key_first($this->applicability)] as $applicPropertyIdent => $stringApplic){
          if($applicPropertyIdent[0] == '%') continue;
          $str .= $stringApplic;
        }
        if(!$useDisplayName) return $str;
        $applicPropertyIdent = array_key_first($this->applicability[array_key_first($this->applicability)]);
        $applicPropertyType = $this->applicability[array_key_first($this->applicability)]['%APPLICPROPERTYTYPE'];
        // dd($CSDB);
        $ct = ($applicPropertyType == 'prodattr') ? $CSDB->ACTdoc : $CSDB->CCTdoc;
        $ctxpath = new DOMXPath($ct);
        $dt = $ctxpath->evaluate("//productAttribute[@id = '{$applicPropertyIdent}']")[0];
        $displayName = $dt->getElementsByTagName('displayName')[0];
        if($displayName){
          $str = $displayName->nodeValue . $str;
        }
        return $str;
    }
    return $this->applicability; // array
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
      case 'crew.xsd':
        $this->render_crewXsd();
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
      if(($i % 2) == 0 AND ($this->pdf->isBooklet())){
        $x = $this->pdf->getPageWidth() - $this->pdf->get_pmType_config()['page']['margins']['L'];
      } else{
        $x = $this->pdf->getPageWidth() - $this->pdf->get_pmType_config()['page']['margins']['R'];
      }
      $this->pdf->printTemplate($tmpl, $x, $y_pos, $w, $h, '', '', false);
    }
  }

  public function render_frontmatterXsd()
  {    
    $this->pdf->page_ident = $this->pdf->get_pmEntryType_config()['printpageident'] ? $this->dmCode : '';
    $CSDB_class_methods = array_map(function($name){
      return CSDB::class."::$name";
    },get_class_methods(CSDB::class));

    $xsl = CSDB::importDocument(__DIR__."./xsl/frontmatter.xsl", '',"xsl:stylesheet");
    $xsltproc = new XSLTProcessor();
    $xsltproc->importStylesheet($xsl);
    $xsltproc->registerPHPFunctions($CSDB_class_methods);
    $xsltproc->registerPHPFunctions(__CLASS__."::"."getApplicabilty");
    $xsltproc->registerPHPFunctions();
    
    $xsltproc->setParameter('','dmOwner',$this->dmIdent);
    $xsltproc->setParameter('','absolute_path_csdbInput', $this->pdf->getAssetPath().DIRECTORY_SEPARATOR);
    // harusnya logo_ptdi pakai absolute_asset_path
    $xsltproc->setParameter('','logo_ptdi', __DIR__.DIRECTORY_SEPARATOR."assets".DIRECTORY_SEPARATOR."Logo-PTDI.jpg");
    $xsltproc->setParameter('','absolute_asset_path', __DIR__.DIRECTORY_SEPARATOR."assets".DIRECTORY_SEPARATOR);
    
    $html = $xsltproc->transformToXml($this->DOMDocument);
    $html = preg_replace("/(?<=>)[\s]{2,}/",'',$html); // usntuk menghilangkan space/enter/multispace diawal setelah tag >

    $this->pdf->setPageOrientation($this->pdf->get_pmType_config()['page']['orientation']);
    $this->pdf->setPageUnit($this->pdf->get_pmType_config()['page']['unit']);

    // dd($html);
    $this->pdf->writeHTML($html, true, false, true, true,'J',true, $DOMDocument = $this->DOMDocument, $usefootnote = false ,$tes = true);
  }

  /**
   * $type bisa berupa DOMElement(crewMemberGroup), atau DOMattr (crewMemberType)
   */
  public function getCrewMember($type){
    if(is_array($type)) $type = $type[0];

    if(($type instanceof \DOMElement) AND $type->nodeName == 'crewMemberGroup'){
      $typearr = [];
      foreach(CSDB::get_childrenElement($type) as $crewMember){
        $typearr[] = $crewMember->getAttribute('crewMemberType');
      }
      foreach($typearr as $i => $cm){
        if(isset($this->pdf->get_pmType_config()['attributes']['crewMemberType'][$cm])){
          $typearr[$i] = $this->pdf->get_pmType_config()['attributes']['crewMemberType'][$cm];
        }
      }
      return join(', ',$typearr);
    }
    
    if($type instanceof \DOMAttr) $type = $type->nodeValue;
    if(isset($this->pdf->get_pmType_config()['attributes']['crewMemberType'][$type])){
      return $this->pdf->get_pmType_config()['attributes']['crewMemberType'][$type];
    }
  }

  public function render_crewXsd()
  {
    $this->pdf->page_ident = $this->pdf->get_pmEntryType_config()['printpageident'] ? $this->dmCode : '';
    $xsl = CSDB::importDocument(__DIR__."./xsl/crew.xsl", '',"xsl:stylesheet");
    $xsltproc = new XSLTProcessor();
    $xsltproc->importStylesheet($xsl);
    // dd(__CLASS__."::"."getApplicabilty", PMC_PDF::class."::".'getCrewMember');
    $xsltproc->registerPHPFunctions(__CLASS__."::".'getCrewMember');
    // $xsltproc->registerPHPFunctions(__CLASS__."::".'getCrewMember');
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

    $xsltproc->setParameter('','dmOwner',$this->dmIdent);
    $xsltproc->setParameter('','absolute_path_csdbInput', $this->pdf->getAssetPath().DIRECTORY_SEPARATOR);
    $xsltproc->setParameter('','absolute_asset_path', __DIR__.DIRECTORY_SEPARATOR."assets".DIRECTORY_SEPARATOR);

    $html = $xsltproc->transformToXml($this->DOMDocument);
    // dd($html);
    $html = preg_replace("/(?<=>)[\s]{2,}/",'',$html); // usntuk menghilangkan space/enter/multispace diawal setelah tag >
    $html = preg_replace("/[\n\r\s]+(?=<.+isfootnote)/",'[?f]',$html); // untuk menghilangkan space ketika didepan ada footnote

    $this->pdf->setPageOrientation($this->pdf->get_pmType_config()['page']['orientation']);
    $this->pdf->setPageUnit($this->pdf->get_pmType_config()['page']['unit']);
    $this->pdf->writeHTML($html, true, false, true, true,'J',true, $DOMDocument = $this->DOMDocument, $usefootnote = true, $tes = true);
    $this->pdf->applyCgMark($this->DOMDocument); // harus di apply di sini karena jika didalam levelledPara, bisa recursive padahal array $this->cgmark harus dikoleksi dulu semuanya
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

    $xsltproc->setParameter('','dmOwner',$this->dmIdent);
    $xsltproc->setParameter('','absolute_path_csdbInput', $this->pdf->getAssetPath().DIRECTORY_SEPARATOR);
    $xsltproc->setParameter('','absolute_asset_path', __DIR__.DIRECTORY_SEPARATOR."assets".DIRECTORY_SEPARATOR);

    $html = $xsltproc->transformToXml($this->DOMDocument);
    $html = preg_replace("/(?<=>)[\s]{2,}/",'',$html); // usntuk menghilangkan space/enter/multispace diawal setelah tag >
    $html = preg_replace("/[\n\r\s]+(?=<.+isfootnote)/",'[?f]',$html); // untuk menghilangkan space ketika didepan ada footnote
  
    $this->pdf->setPageOrientation($this->pdf->get_pmType_config()['page']['orientation']);
    $this->pdf->setPageUnit($this->pdf->get_pmType_config()['page']['unit']);
    $this->pdf->writeHTML($html, true, false, true, true,'J',true, $DOMDocument = $this->DOMDocument, $usefootnote = true, $tes = true);
    $this->pdf->applyCgMark($this->DOMDocument); // harus di apply di sini karena jika didalam levelledPara, bisa recursive padahal array $this->cgmark harus dikoleksi dulu semuanya
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