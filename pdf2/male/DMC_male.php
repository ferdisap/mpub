<?php

namespace Ptdi\Mpub\Pdf2\male;

use Ptdi\Mpub\CSDB;
use Ptdi\Mpub\Pdf2\DMC;
use Ptdi\Mpub\Pdf2\PMC_PDF;
use XSLTProcessor;

class DMC_male extends DMC
{
  
  public function getTableStyle(string $tablestyle, string $attribute)
  {
    return $this->pdf->get_pmType_config()['content']['tablestyle'][$tablestyle][$attribute];
  }

  public function getFontFamily()
  {
    return($this->pdf->getFontFamily());
  }

  public function dump()
  {
    // dd(count(explode(".",'10')));
    dump(func_get_args());
    // dd(func_get_args()[0] == func_get_args()[1]);
    // dd($any);
    // dd(preg_replace("/[^0-9]+/",'',$any));
  }
  
  public function render_descriptXsd()
  { 
    $modelIdentCode = strtolower(CSDB::get_modelIdentCode($this->DOMDocument));
    $xsl = CSDB::importDocument(__DIR__."./xsl/descript.xsl", '',"xsl:stylesheet");
    $xsltproc = new XSLTProcessor();
    $xsltproc->importStylesheet($xsl);

    $DMC_male_class_methods = array_map(function($name){
      return self::class."::$name";
    },get_class_methods(self::class));
    $xsltproc->registerPHPFunctions($DMC_male_class_methods);
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

    $fontsize_levelPara_figuretitle = $this->pdf->get_pmType_config()['fontsize']['levelledPara']['figure']['title'];
    $xsltproc->setParameter('',"fontsize_figure_title", $fontsize_levelPara_figuretitle);

    $xsltproc->setParameter('','dmOwner',$this->dmIdent);
    $xsltproc->setParameter('','absolute_path_csdbInput', $this->pdf->getAssetPath().DIRECTORY_SEPARATOR);
    $xsltproc->setParameter('','absolute_asset_path', __DIR__.DIRECTORY_SEPARATOR.$modelIdentCode.DIRECTORY_SEPARATOR."assets".DIRECTORY_SEPARATOR);

    $html = $xsltproc->transformToXml($this->DOMDocument);
    $html = preg_replace("/(?<=>)[\s]{2,}/",'',$html); // usntuk menghilangkan space/enter/multispace diawal setelah tag >
    $html = preg_replace("/[\n\r\s]+(?=<.+isfootnote)/",'[?f]',$html); // untuk menghilangkan space ketika didepan ada footnote
  
    $this->pdf->setPageOrientation($this->pdf->get_pmType_config()['page']['orientation']);
    $this->pdf->setPageUnit($this->pdf->get_pmType_config()['page']['unit']);
    
    
    // untuk nulis dm title
    $subSystemCode = number_format($this->DOMDocument->getElementsByTagName('dmCode')[0]->getAttribute('subSystemCode'));
    $assyCode = number_format($this->DOMDocument->getElementsByTagName('dmCode')[0]->getAttribute('assyCode'));
    $dmTitle = CSDB::resolve_dmTitle($this->DOMDocument->getElementsByTagName('dmTitle')[0]);
    // $tt = <<<EOD
    // <h1 style="font-size:{$fontsize_levelPara_title[0]}; text-align:center">{$assyCode}.   {$dmTitle}</h1><br style="line-height:0.7"/>
    // EOD;
    // <xsl:text>SECTION </xsl:text>
    //             <xsl:value-of select="number(//identAndStatusSection/descendant::dmCode[1]/@subSystemCode)"/>
    //             <xsl:text> - </xsl:text>
    // $this->pdf->Bookmark("SECTION {$subSystemCode} - " .$assyCode.".   ".$dmTitle, $this->pdf->pmEntry_level+1);
    $tt = <<<EOD
    <br/><br/>
    <h1 style="font-size:{$fontsize_levelPara_title[0]}; text-align:center">{$dmTitle}</h1><br style="line-height:0.7"/>
    EOD;
    $this->pdf->Bookmark("$subSystemCode - ".$dmTitle, $this->pdf->pmEntry_level+1);
    $this->pdf->pmEntry_level += 1;
    $this->pdf->writeHTML($tt, true, false, true, true,'J',true, $DOMDocument = $this->DOMDocument, $usefootnote = true, $tes = true);

    $this->pdf->writeHTML($html, true, false, true, true,'J',true, $DOMDocument = $this->DOMDocument, $usefootnote = true, $tes = true);
    $this->pdf->applyCgMark($this->DOMDocument); // harus di apply di sini karena jika didalam levelledPara, bisa recursive padahal array $this->cgmark harus dikoleksi dulu semuanya
  }

  public function render_crewXsd()
  {
    $this->pdf->page_ident = $this->pdf->get_pmEntryType_config()['printpageident'] ? $this->dmCode : '';
    $modelIdentCode = strtolower(CSDB::get_modelIdentCode($this->DOMDocument));
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
    $xsltproc->setParameter('','absolute_asset_path', __DIR__.DIRECTORY_SEPARATOR.$modelIdentCode.DIRECTORY_SEPARATOR."assets".DIRECTORY_SEPARATOR);

    $html = $xsltproc->transformToXml($this->DOMDocument);
    // dd($html);
    $html = preg_replace("/(?<=>)[\s]{2,}/",'',$html); // usntuk menghilangkan space/enter/multispace diawal setelah tag >
    $html = preg_replace("/[\n\r\s]+(?=<.+isfootnote)/",'[?f]',$html); // untuk menghilangkan space ketika didepan ada footnote

    $this->pdf->setPageOrientation($this->pdf->get_pmType_config()['page']['orientation']);
    $this->pdf->setPageUnit($this->pdf->get_pmType_config()['page']['unit']);

    $subSystemCode = number_format($this->DOMDocument->getElementsByTagName('dmCode')[0]->getAttribute('subSystemCode'));
    $dmTitle = CSDB::resolve_dmTitle($this->DOMDocument->getElementsByTagName('dmTitle')[0]);
    $tt = <<<EOD
    <br/><br/>
    <h1 style="font-size:{$fontsize_levelPara_title[0]}; text-align:center">{$dmTitle}</h1><br style="line-height:0.7"/>
    EOD;
    $this->pdf->Bookmark("$subSystemCode - ".$dmTitle, $this->pdf->pmEntry_level+1);
    $this->pdf->pmEntry_level += 1;
    $this->pdf->writeHTML($tt, true, false, true, true,'J',true, $DOMDocument = $this->DOMDocument, $usefootnote = true, $tes = true);


    $this->pdf->writeHTML($html, true, false, true, true,'J',true, $DOMDocument = $this->DOMDocument, $usefootnote = true, $tes = true);
    $this->pdf->applyCgMark($this->DOMDocument); // harus di apply di sini karena jika didalam levelledPara, bisa recursive padahal array $this->cgmark harus dikoleksi dulu semuanya
  }

}