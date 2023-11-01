<?php

namespace Ptdi\Mpub\Pdf2\male;

use DOMXPath;
use Exception;
use Ptdi\Mpub\CSDB;
use Ptdi\Mpub\Pdf2\DMC;
use TCPDF;
use XSLTProcessor;

class DMC_male extends DMC
{
  // public function render_descriptXsd()
  // {
  //   $xsl = CSDB::importDocument(__DIR__."./xsl/descript.xsl", '',"xsl:stylesheet");
  //   $xsltproc = new XSLTProcessor();
  //   $xsltproc->importStylesheet($xsl);
  //   $xsltproc->registerPHPFunctions();

  //   $padding_levelPara = $this->pdf->get_pmType_config()['content']['padding']['levelledPara'];
  //   $xsltproc->setParameter('',"padding_levelPara_1", $padding_levelPara[0]);
  //   $xsltproc->setParameter('',"padding_levelPara_2", $padding_levelPara[1]);
  //   $xsltproc->setParameter('',"padding_levelPara_3", $padding_levelPara[2]);
  //   $xsltproc->setParameter('',"padding_levelPara_4", $padding_levelPara[3]);
  //   $xsltproc->setParameter('',"padding_levelPara_5", $padding_levelPara[4]);

  //   $fontsize_levelPara_title = $this->pdf->get_pmType_config()['fontsize']['levelledPara']['title'];
  //   $xsltproc->setParameter('',"fontsize_levelledPara_title_1", $fontsize_levelPara_title[0]);
  //   $xsltproc->setParameter('',"fontsize_levelledPara_title_2", $fontsize_levelPara_title[1]);
  //   $xsltproc->setParameter('',"fontsize_levelledPara_title_3", $fontsize_levelPara_title[2]);
  //   $xsltproc->setParameter('',"fontsize_levelledPara_title_4", $fontsize_levelPara_title[3]);
  //   $xsltproc->setParameter('',"fontsize_levelledPara_title_5", $fontsize_levelPara_title[4]);

  //   $fontsize_levelPara_title = $this->pdf->get_pmType_config()['fontsize']['levelledPara']['figure']['title'];
  //   $xsltproc->setParameter('',"fontsize_figure_title", $fontsize_levelPara_title);

  //   $xsltproc->setParameter('','dmOwner',$this->dmIdent);
  //   $xsltproc->setParameter('','absolute_path_csdbInput', $this->pdf->getAssetPath().DIRECTORY_SEPARATOR);
  //   $xsltproc->setParameter('','absolute_asset_path', __DIR__.DIRECTORY_SEPARATOR.$modelIdentCode.DIRECTORY_SEPARATOR."assets".DIRECTORY_SEPARATOR);

  //   $xsltproc->setParameter('','prefix', 'bar');

  //   $html = $xsltproc->transformToXml($this->DOMDocument);
  //   $html = preg_replace("/(?<=>)[\s]{2,}/",'',$html); // usntuk menghilangkan space/enter/multispace diawal setelah tag >
  //   $html = preg_replace("/[\n\r\s]+(?=<.+isfootnote)/",'[?f]',$html); // untuk menghilangkan space ketika didepan ada footnote
  
  //   $this->pdf->setPageOrientation($this->pdf->get_pmType_config()['page']['orientation']);
  //   $this->pdf->setPageUnit($this->pdf->get_pmType_config()['page']['unit']);
  //   $this->pdf->writeHTML($html, true, false, true, true,'J',true, $DOMDocument = $this->DOMDocument, $usefootnote = true, $tes = true);
  //   $this->pdf->applyCgMark($this->DOMDocument); // harus di apply di sini karena jika didalam levelledPara, bisa recursive padahal array $this->cgmark harus dikoleksi dulu semuanya
  // }
}