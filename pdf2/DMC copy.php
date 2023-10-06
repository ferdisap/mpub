<?php

namespace Ptdi\Mpub\Pdf2;

use Ptdi\Mpub\CSDB;
use Ptdi\Mpub\Pdf2\Helper\TextElemGroup;
use TCPDF;

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

    $file_withLanguangeCode = $this->absolute_path_csdbInput.DIRECTORY_SEPARATOR.strtoupper($dmCode.$issueInfo.$languange).".xml";

    // dd($file_withLanguangeCode);
    $this->DOMDocument = CSDB::importDocument($file_withLanguangeCode,'','dmodule');
    
    $schemaXsd = self::getSchemaName($this->DOMDocument->firstElementChild);
    $this->schemaXsd = $schemaXsd;
  }

  public function render()
  {
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
  }

  public function render_descriptXsd()
  {
    // $headerMargin = 5;
    // $fontsize_para = isset(($ft = $this->pdf->pmType_config['fontsize']['levelledPara']['para'])) ? $ft : 9;
    // dd('foo', __CLASS__, __LINE__);
    $DOMXpath = new \DOMXPath($this->DOMDocument);
    $description_children = $DOMXpath->evaluate("//content/description/levelledPara");
    foreach($description_children as $key => $levelledPara){
      $this->resolve_levelledPara($levelledPara);
    }  
    
    // dd('foo');
  }

  public function resolve_levelledPara(\DOMElement $levelledPara)
  {
    $level = CSDB::checkLevel($levelledPara);
    // $index = CSDB::checkIndex($levelledPara);

    $paddingLeft = 0;
    $paddingLeft += (
      $level == 0 ? 0 : (
        $level == 1 ? 3 : (
          $level == 2 ? 7 : (
            $level == 3 ? 10 : (
              $level == 4 ? 15 : 0
            )
          )
        )
    ));
    
    $prefixnum = CSDB::getPrefixNum($levelledPara, 1);

    foreach (CSDB::children($levelledPara, 'element') as $element){
      switch ($element->tagName) {
        case 'title':
          $fontsize = $this->pdf->get_pmType_config()['fontsize']['levelledPara']['title'][$level];
          $this->resolve_title($element, $fontsize, $prefixnum);
          break;
        case 'para':
          $fontsize = $this->pdf->get_pmType_config()['fontsize']['levelledPara']['para'];
          $this->resolve_para($element, $fontsize);
          break;
      }     
    }
  }

  public function resolve_title(\DOMElement $title, int $fontsize = null, string $prefixnum = '')
  {
    $this->pdf->setFontSize($fontsize);
    $this->pdf->Ln(3);
    
    ($prefixnum OR ($prefixnum == '0')) ? $this->pdf->Write('',$prefixnum,'',false,'J',false,0,true,false): null;

    foreach($title->childNodes as $child){
      if($child instanceof \DOMText){
        $this->pdf->Write('',$child->nodeValue,'',false,'L',false,0,true,false);
      }
      elseif($child instanceof \DOMElement){
        TextElemGroup::resolve($this->pdf, $child);
      }
    }
    $this->pdf->Ln(5);
  }

  public function resolve_para(\DOMElement $para, int $fontsize = null)
  {
    $this->pdf->setFontSize($fontsize);
    $this->pdf->Ln(1.5);

    $str = <<<EOD
    <p style="border:1px solid red">Operators are <span style="border:1px solid green">foo</span> encouraged to develop their own 'quick reference' normal checklists 
    based on the procedures provided in this manual. The sequence of individual checklist 
    items should <sup> sub sub</sup> bar <b>emphasis</b> not be changed when operator-specific quick reference checklists are 
    developed. For example, turning off the autofeather system is listed as the last item
    in the After Takeoff checklist, and it should remain the last item in any operator-specific
    'quick reference' After Takeoff checklist.</p>
    EOD;
    // $this->pdf->MultiCell('','',$str,1,'J',false,1,'','',true,0,true,true,0,'T',true);
    // $this->pdf->Text('','',$str,0,false,true,1,1,"L",false,'',0,false,'T','M');
    // dump($this->pdf->GetY());
    // $this->pdf->writeHTML($str,true,false,true,true,'J');
    // dump($this->pdf->GetY());
    // $this->pdf->setXY(10,50);
    // $this->pdf->Cell(117,0,'foo',1);
    // $this->pdf->Ln();
    // dd($this->pdf);
    // $this->pdf->MultiCell('','',$lorem20,1,'J',false,0,'','', true,false,true);
    // $this->pdf->Ln();
    // dd($this->pdf->GetX());
    // dd($this->pdf->GetAbsX(), $this->pdf->GetX());
    // $this->pdf->Ln(1.5);
    // dd($this->pdf->GetX());
    // $this->pdf->Ln();
    // dd($this->pdf->GetX());
    // $this->pdf->MultiCell('','',$lorem20,1,'J',false,1,10,'', true,false,true);
    // dd($this->pdf->GetStringWidth('foobar'));
    // dd($this->pdf->getPageWidth());
    // dd($this->pdf->getMargins());
    // dd($this->pdf->GetStringWidth('foobar '));
    // for ($i=0; $i < 20; $i++) { 
      // if($i == 5){$this->pdf->Ln();}
      // $this->pdf->Cell(9,0,'foobar',1);
    // }
    // $this->pdf->Text($this->pdf->GetX(),$this->pdf->GetY(),'foobar',0,false,true,1,0,'J');
    // dd($this->pdf->GetX());
    // dd($this->pdf->getCellPaddings());
    // dd($this->pdf->getMargins(), $this->pdf->getCellMargins());
    $lorem20 = "Lorem ipsum dolor sit amet, consectessssssssssssstur adipisicing elit. Asperiores, mollitia laudantium? Fugit iusto illo voluptatum velit? Numquam obcaecati doloremque sequi.";
    $this->pdf->setCellPaddings(0);
    $availableTextAreaWidth =  $this->pdf->getPageWidth() - ($this->pdf->getMargins()['left'] + $this->pdf->getMargins()['right'] + $this->pdf->getCellPaddings()['L'] + $this->pdf->getCellPaddings()['R']);
    $strArr = explode(" ",$lorem20); // masih nyisa
    while(count($strArr) > 0){
      $line_str = "";
      while(($this->pdf->GetStringWidth($line_str) < $availableTextAreaWidth) && (isset($strArr[0]))){
        if(($w = $this->pdf->GetStringWidth($line_str)) + $this->pdf->GetStringWidth($strArr[0]) <= $availableTextAreaWidth){
          $line_str .= " ".$strArr[0];
          array_shift($strArr);
        } else {
          break;
        }
      }
      $x = $this->pdf->GetX();
      $y = $this->pdf->GetY();
      $this->pdf->setXY($x,$y);
      $this->pdf->Cell($availableTextAreaWidth,0,$line_str,1,0);
      $this->pdf->Ln();
    }
    // $this->pdf->Ln();
    // dd($strArr, $line_str);

    // dd($line_str, $strArr);
    // foreach ( as $word){
    //   $line_str[$i] += $word;
    //   $width = $this->pdf->GetStringWidth($line_str[$i]);
    //   if($width >)
    // }
    // dd($availableTextAreaWidth);
    // dd($this->pdf->get);
    foreach($para->childNodes as $child){
      if($child instanceof \DOMText){
        // $txt = preg_replace("/[\r\n] {2,}/",'',$child->nodeValue);
        $txt = preg_replace("/[\n\r]\s{2,}/",'',$child->nodeValue)." ";
        // $this->pdf->Text('','',$txt,0,false,true,0,0,"J",false,'',0,false,'T','M');
        // $this->pdf->Write('',$txt,'',false,'J',false,0,false,true);
        // $this->pdf->Write('','foo','',false,'L',false,0,false,true);
        // $this->pdf->WriteHTML($txt, false, false, true, false, 'L');
      }
      elseif($child instanceof \DOMElement){
        // TextElemGroup::resolve($this->pdf, $child);
      }
    }
    $this->pdf->Ln(5);
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
      return false;
    }
    $issueNumber = $issueInfo->getAttribute('issueNumber');
    $inWork = $issueInfo->getAttribute('inWork');
    return $issueNumber."-".$inWork;
  }

  public static function resolve_languange(\DOMElement $languange = null)
  {
    if(!$languange) {
      return false;
    }
    $languangeIsoCode = $languange->getAttribute('languageIsoCode');
    $countryIsoCode = $languange->getAttribute('countryIsoCode');
    return $languangeIsoCode."-".$countryIsoCode;
  }
}