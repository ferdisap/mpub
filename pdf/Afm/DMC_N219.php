<?php 

namespace Ptdi\Mpub\Pdf\Afm;

use DOMDocument;
use DOMXPath;
use Exception;
use Ptdi\Mpub\CSDB;
use Ptdi\Mpub\Object\DModule;
use Ptdi\Mpub\Pdf\TextElemGroup;
use TCPDF;

class DMC_N219 extends TCPDF
{
  protected $dmPath;
  public $DOMDocument;   
  public $pdf;

  public function __construct($tcpdf = null)
  {
    if($tcpdf){
      $this->pdf = $tcpdf;
    } else {
      $this->pdf = new TCPDF('P','mm','A5',true,'UTF-8',false);
    }
  }

  public function set_dmPath(string $absolutePath){
    $this->dmPath = $absolutePath;
  }
  
  // import content
  public function import_file(string $absolute_path){
    $dmodule = CSDB::load($absolute_path);
    $this->DOMDocument = $dmodule;
  }

  // render pdf document
  public function render(){
    $schema = DModule::getSchemaName($this->DOMDocument->firstElementChild);    
    switch ($schema) {
      case 'descript.xsd':
        $this->render_descriptXsd();
        $this->addIntentionallyLeftBlankPage();
        break;
      case 'frontmatter.xsd':
        $this->render_frontmatterXsd();
        $this->addIntentionallyLeftBlankPage();
        break;
      default:
        // $this->pdf->AddPage();
        break;
    }
    
    
    // dd($this->DOMDocument);
    // dd(DModule::getDMName($this->DOMDocument));
    // $DOMXpath = new DOMXPath($this->DOMDocument);
    // $dmCode = $DOMXpath->evaluate("//identAndStatusSection/dmAddress/dmIdent/dmCode")[0];
    // $dmCode = DModule::resolveDMCode($dmCode,null,null);
    
    // $template_dmc_ident = $pdf->startTemplate(0,0,false);
    // $pdf->
  }

  private function addIntentionallyLeftBlankPage(){
    $pdf = $this->pdf;
    // dd($pdf);
    if(($pdf->getNumPages() % 2) != 0){
      // jika halaman ganjil
      $pdf->AddPage();
      $html = <<<EOD
      <div>
      <br/><br/><br/><br/><br/><br/><br/>
      <br/><br/><br/><br/><br/><br/><br/>
      <br/><br/><br/><br/><br/><br/><br/>
      INTENTIONALLY LEFT BLANK
      </div>
      EOD;
      $pdf->writeHTML($html,true,false,true,false,'C');
    }
  }

  private function render_frontmatterXsd(){
    $DOMDocument = $this->DOMDocument;
    $pdf = $this->pdf;
    $pdf->setHeaderMargin(5);
    $pdf->setFontSize(9);
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    $DOMXpath = new DOMXPath($DOMDocument);

    // dd($DOMDocument);
    $frontMatter = $DOMXpath->evaluate("//content/frontMatter");
    $frontMatter = $frontMatter[0] ?? throw new Exception("The Data Module shall have at least one frontMatter inside content.", 1);

    switch ($frontMatter->firstElementChild->tagName) {
      case 'frontMatterTitlePage':
        $this->resolve_frontMatterTitlePage($frontMatter->firstElementChild);
        break;
      
      default:
        break;
    }
  }

  public function resolve_frontMatterTitlePage(\DOMElement $frontMatterTitlePage){
    $children = (function () use ($frontMatterTitlePage){
      $arr = [];
      foreach($frontMatterTitlePage->childNodes as $childNodes){
        $childNodes instanceof \DOMElement ? array_push($arr, $childNodes) : null;
      }
      return $arr;      
    })();

    // to make the title is center
    $this->pdf->Cell(0,55,'',0,1);

    foreach($children as $child){
      $text = "";
      $isrev = $this->isrev($child);
      if(($element = $child) instanceof \DOMElement){
        switch ($element->tagName) {
          case 'pmTitle':
            $text = $text. $element->nodeValue;
            $this->writeCell($isrev, $text, 5, 16, 'C');
            break;            
          case 'pmCode':
            $text = $text. DModule::resolvePMCode($element);
            $this->writeCell($isrev, $text, 5, 12, 'C');
            break;
          case 'responsiblePartnerCompany':
            $enterpriseName = $element->firstElementChild;
            $text = $text. 'responsiblity by: '. $enterpriseName->nodeValue;
            $this->writeCell($isrev,$text, 5, 9, 'C');
            break;
          default:
            # code...
            break;
        }
      }
    }
  }

  private function render_descriptXsd(){
    $DOMDocument = $this->DOMDocument;
    $pdf = $this->pdf;
    $pdf->setHeaderMargin(5);
    $pdf->setFontSize(9);
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    $DOMXpath = new DOMXPath($DOMDocument);
    $result = $DOMXpath->evaluate("//content/description/levelledPara");
    foreach($result as $key => $levelledPara){
      $this->resolve_levelledPara($levelledPara,'',$key+1);
    }
  }

  public function resolve_levelledPara(\DOMElement $levelledPara, string $prefix_no = '', $index = 0){
    $child_levelledPara = CSDB::children($levelledPara);

    $level = $this->levelCheck($levelledPara);
    $paddingLeft = 5; // level 1
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
    $fontsize = 14; // level 1
    $fontsize += (
      $level == 0 ? 0: (
        $level == 1 ? -2 : (
          $level == 2 ? -4 : (
            $level == 3 ? -5 : (
              $level == 4 ? -6 : 0
            )
          )
        )
    ));
    $prefix_no = ($prefix_no ? $prefix_no.'.'.$index : $index);

    foreach($child_levelledPara as $child){
      $text = "";
      $isrev = $this->isrev($child);
      // ngechek apakah didalam Title ada subscript, empahsis dll
      foreach ($child->childNodes as $node){
        if($node instanceof \DOMText){
          $text = $text. $node->nodeValue;
        }
        elseif ($node instanceof \DOMElement){
          $text = $text. TextElemGroup::resolve($node);
        }
      }
      if($child->tagName == 'title'){
        // $text = join(".",$this->levelParaNumbered).'<span>   </span>'. $text;
        $this->pdf->Ln(3);
        $text = $prefix_no.'<span>   </span>'. $text;
        $this->pdf->Bookmark(preg_replace("/<[\w\s=\":;\/]+>\s?/",'',$text), $level, 0, '');
        $this->writeCell($isrev, $text, $paddingLeft, $fontsize);
        $this->pdf->Ln(1.5);
      }
      elseif (($para = $child)->tagName == 'para'){
        $this->writeCell($isrev, $text, $paddingLeft, 9);
        $this->pdf->Ln(1.5);
      }
      elseif (($figure = $child)->tagName == 'figure'){
        // code..
      }
      elseif (($table = $child)->tagName == 'table'){
        // code..
      }
    }

    $levelledPara_childs = $levelledPara->getElementsByTagName('levelledPara');
    foreach($levelledPara_childs as $key => $child){
      $this->resolve_levelledPara($child,$prefix_no,$key+1);
    }
  }

  public function writeCell(string $isrev, string $text, int $paddingLeft, int $fontsize, string $align = 'J'){
    $paddingLeft = $paddingLeft ?? 5;
    $pdf = $this->pdf;
    $pdf->setFontSize($fontsize);
    $pdf->setCellPaddings($paddingLeft);
    // $pdf->MultiCell(0,0,$text,$isrev ? 'L' : 0,$align,false,1,'','',true,0,true,false,0,'T',true);    
    $pdf->MultiCell(0,0,$text,$isrev ? 'L' : 1,$align,false,1,'','',true,0,true,false,0,'T',true);        
  }

  public function isrev(\DOMElement $element){
    $domXpath = new DOMXPath($element->ownerDocument);
    $result = $domXpath->evaluate("//@changeMark",$element);
    $changeMark = $result[0] ? true : false;
    return $changeMark;
  }

  /**
   * @return int
   */
  private function levelCheck(\DOMElement $levelledPara){
    $level = 0;
    return $this->checkParent($levelledPara,$level);
  }

  private function checkParent($node, $level = 1, $nodeName = 'levelledPara'){
    if(($parent = $node->parentNode)->nodeName == $nodeName){
      $node = $parent;
      $this->checkParent($node, $level);
      $level += 1;
    } 
    return (int)$level;
  }

  public function savePdf(){}
  
  
}
