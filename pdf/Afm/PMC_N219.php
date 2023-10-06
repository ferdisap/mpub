<?php 

namespace Ptdi\Mpub\Pdf\Afm;

use DOMDocument;
use DOMXPath;
use Exception;
use Ptdi\Mpub\CSDB;
use Ptdi\Mpub\Object\DModule;
use Ptdi\Mpub\Schema\Schema;
use TCPDF;
use TCPDF_FONTS;
use TCPDF_STATIC;

class PMC_N219 extends TCPDF
{
  protected $dmPath;
  public $DOMDocument;    
  public $pdf;
  public string $prefix_pagenum = "4-";
  public array $aa_approved = array();

  //Page header
  public function Header() {
    if(($this->getPage() % 2) == 0){
      $header = <<<EOD
      <table style="width:100%;font-size:10">
        <tr>
          <td align="left">
            <br/>
            <div style="line-height:1.5">SECTION 4 <br/> NORMAL PROCEDURES </div>
          </td>
          <td align="right">
            <img src="./ietp_n219/assets/header_logo_afm.jpeg" width="65mm"/>
          </td>
        </tr>
      </table>
      EOD;
      $this->writeHTML($header, true);
    } else {
      $header = <<<EOD
      <table style="width:100%;font-size:10">
        <tr>
          <td align="left">
            <img src="./ietp_n219/assets/header_logo_afm.jpeg" width="65mm"/>
          </td>
          <td align="right">
            <br/>
            <div style="line-height:1.5">SECTION 4 <br/> NORMAL PROCEDURES </div>
          </td>
        </tr>
      </table>
      EOD;
      $this->writeHTML($header, true);
    } 
  }
  // Page footer
  public function Footer() {
    $aa_approved = (isset($this->aa_approved['name']) ? $this->aa_approved['name'].":" : '').($this->aa_approved['date'] ?? '');
    if(($this->getPage() % 2) == 0){
      $this->SetY(-15);
      $footer = <<<EOD
      <table style="width:100%;font-size:10;">
        <tr>
          <td align="left">D661ND1001</td>
          <td align="right">Original</td>
        </tr>
        <tr>
          <td align="left">Page {$this->prefix_pagenum}{$this->getAliasNumPage()}</td>
          <td align="right">{$aa_approved}</td>
        </tr>
      </table>
      EOD;
      $this->writeHTML($footer,true,false,true,false,'C');
    } else {
      // Position at 15 mm from bottom
      $this->SetY(-15);
      $footer = <<<EOD
      <table style="width:108%;font-size:10;">
        <tr>
          <td align="left">Original</td>
          <td align="right">D661ND1001&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
        </tr>
        <tr>
          <td align="left">{$aa_approved}</td>
          <td align="right" style="">Page {$this->prefix_pagenum}{$this->getAliasNumPage()}</td>
        </tr>
      </table>
      EOD;
      $this->writeHTML($footer,true,false,true,false,'C');
    }
  }

  public function AddPage($orientation='', $format='', $keepmargins=false, $tocpage=false) {
    if ($this->inxobj) {
      // we are inside an XObject template
      return;
    }
    
    // terminate previous page
    $this->endPage();

    if (!isset($this->original_lMargin) OR $keepmargins) {
      $this->original_lMargin = $this->lMargin;
    }
    if (!isset($this->original_rMargin) OR $keepmargins) {
      $this->original_rMargin = $this->rMargin;
    }

    // start new page
    $this->startPage($orientation, $format, $tocpage);

    // dd($this->getPageHeight()*3/4);
    
    // dd($this->getCellMargins());
    // dd($this->getMargins());
    // dd($this->getPageWidth());
    $y_pos = $this->getPageHeight()*1/2;
    
    $dmCode = "N219-A-15-30-00-00A-001A-A";    
    $template_dmc_identification = $this->startTemplate(80,80,true);
    $this->StartTransform();
    // $this->Rotate(90,25,25);
    // $this->Rotate(60,50,0);
    $this->setFontSize(6);
    $this->Rotate(90,25,25);
    $this->Cell(50,0,$dmCode,0,0,'C',false,'',0,false,'T','M');
    $this->StopTransform();
    $this->endTemplate();

    $this->printTemplate($template_dmc_identification,140,$y_pos,'','','','',false);

  }

	public function addTOC($page=null, $numbersfont='', $filler='.', $toc_name='TOC', $style='', $color=array(0,0,0)) {
		$fontsize = $this->FontSizePt;
		$fontfamily = $this->FontFamily;
		$fontstyle = $this->FontStyle;
		$w = $this->w - $this->lMargin - $this->rMargin;
		$spacer = $this->GetStringWidth(chr(32)) * 4;
		$lmargin = $this->lMargin;
		$rmargin = $this->rMargin;
		$x_start = $this->GetX();
		$page_first = $this->page;
		$current_page = $this->page;
		$page_fill_start = false;
		$page_fill_end = false;
		$current_column = $this->current_column;
		if (TCPDF_STATIC::empty_string($numbersfont)) {
			$numbersfont = $this->default_monospaced_font;
		}
		if (TCPDF_STATIC::empty_string($filler)) {
			$filler = ' ';
		}
		if (TCPDF_STATIC::empty_string($page)) {
			$gap = ' ';
		} else {
			$gap = ' ';
			if ($page < 1) {
				$page = 1;
			}
		}
		$this->setFont($numbersfont, $fontstyle, $fontsize);
		$numwidth = $this->GetStringWidth('00000');
		$maxpage = 0; //used for pages on attached documents
		foreach ($this->outlines as $key => $outline) {
			// check for extra pages (used for attachments)
			if (($this->page > $page_first) AND ($outline['p'] >= $this->numpages)) {
				$outline['p'] += ($this->page - $page_first);
			}
			if ($this->rtl) {
				$aligntext = 'R';
				$alignnum = 'L';
			} else {
				$aligntext = 'L';
				$alignnum = 'R';
			}
			if ($outline['l'] == 0) {
				$this->setFont($fontfamily, $outline['s'].'B', $fontsize);
			} else {
				$this->setFont($fontfamily, $outline['s'], $fontsize - $outline['l']);
			}
			$this->setTextColorArray($outline['c']);
			// check for page break
			$this->checkPageBreak(2 * $this->getCellHeight($this->FontSize));
			// set margins and X position
			if (($this->page == $current_page) AND ($this->current_column == $current_column)) {
				$this->lMargin = $lmargin;
				$this->rMargin = $rmargin;
			} else {
				if ($this->current_column != $current_column) {
					if ($this->rtl) {
						$x_start = $this->w - $this->columns[$this->current_column]['x'];
					} else {
						$x_start = $this->columns[$this->current_column]['x'];
					}
				}
				$lmargin = $this->lMargin;
				$rmargin = $this->rMargin;
				$current_page = $this->page;
				$current_column = $this->current_column;
			}
			$this->setX($x_start);
			$indent = ($spacer * $outline['l']);
			if ($this->rtl) {
				$this->x -= $indent;
				$this->rMargin = $this->w - $this->x;
			} else {
				$this->x += $indent;
				$this->lMargin = $this->x;
			}
			$link = $this->AddLink();
			$this->setLink($link, $outline['y'], $outline['p']);
			// write the text
			if ($this->rtl) {
				$txt = ' '.$outline['t'];
			} else {
				$txt = $outline['t'].' ';
			}
			$this->Write(0, $txt, $link, false, $aligntext, false, 0, false, false, 0, $numwidth, '');
			if ($this->rtl) {
				$tw = $this->x - $this->lMargin;
			} else {
				$tw = $this->w - $this->rMargin - $this->x;
			}
			$this->setFont($numbersfont, $fontstyle, $fontsize);
			if (TCPDF_STATIC::empty_string($page)) {
				$pagenum = $outline['p'];
			} else {
				// placemark to be replaced with the correct number
				$pagenum = '{#'.($outline['p']).'}';
				if ($this->isUnicodeFont()) {
					$pagenum = '{'.$pagenum.'}';
				}
				$maxpage = max($maxpage, $outline['p']);
			}
      $gap_titleAndFiller = 3;
			$fw = ($tw - $this->GetStringWidth($pagenum.$filler)) - $gap_titleAndFiller;
			$wfiller = $this->GetStringWidth($filler);
      // dd($fw , $wfiller);
			if ($wfiller > 0) {
				$numfills = floor($fw / $wfiller); // jumlah titik2 atau filler toc nya
			} else {
				$numfills = 0;
			}
			if ($numfills > 0) {
				$rowfill = str_repeat($filler, $numfills);
			} else {
				$rowfill = '';
			}
			if ($this->rtl) {
        $pagenum = $pagenum.$gap.$rowfill;
			} else {
        // $pagenum = $rowfill.$gap.$pagenum;
        ### bypass page number by adding prefix
        $pagenum = $rowfill.$gap.$this->prefix_pagenum.$pagenum;
        ### end bypass
			}
			// write the number
			$this->Cell($tw, 0, $pagenum, 0, 1, $alignnum, 0, $link, 0);      
		}
		$page_last = $this->getPage();
		$numpages = ($page_last - $page_first + 1);
		// account for booklet mode
		if ($this->booklet) {
			// check if a blank page is required before TOC
			$page_fill_start = ((($page_first % 2) == 0) XOR (($page % 2) == 0));
			$page_fill_end = (!((($numpages % 2) == 0) XOR ($page_fill_start)));
			if ($page_fill_start) {
				// add a page at the end (to be moved before TOC)
				$this->addPage();
				++$page_last;
				++$numpages;
			}
			if ($page_fill_end) {
				// add a page at the end
				$this->addPage();
				++$page_last;
				++$numpages;
			}
		}
    // code untuk update page number aliasnya
		$maxpage = max($maxpage, $page_last);
		if (!TCPDF_STATIC::empty_string($page)) {
			for ($p = $page_first; $p <= $page_last; ++$p) {
				// get page data
				$temppage = $this->getPageBuffer($p);
				for ($n = 1; $n <= $maxpage; ++$n) {
					// update page numbers
					$a = '{#'.$n.'}';
					// get page number aliases
					$pnalias = $this->getInternalPageNumberAliases($a);
					// calculate replacement number
					if (($n >= $page) AND ($n <= $this->numpages)) {
						$np = $n + $numpages;
					} else {
						$np = $n;
					}
					$na = TCPDF_STATIC::formatTOCPageNumber(($this->starting_page_number + $np - 1));
					$nu = TCPDF_FONTS::UTF8ToUTF16BE($na, false, $this->isunicode, $this->CurrentFont);
					// replace aliases with numbers
					foreach ($pnalias['u'] as $u) {
						$sfill = str_repeat($filler, max(0, (strlen($u) - strlen($nu.' '))));
						if ($this->rtl) {
							$nr = $nu.TCPDF_FONTS::UTF8ToUTF16BE(' '.$sfill, false, $this->isunicode, $this->CurrentFont);
						} else {
							$nr = TCPDF_FONTS::UTF8ToUTF16BE($sfill.' ', false, $this->isunicode, $this->CurrentFont).$nu;
						}
						$temppage = str_replace($u, $nr, $temppage);
					}
					foreach ($pnalias['a'] as $a) {
						$sfill = str_repeat($filler, max(0, (strlen($a) - strlen($na.' '))));
						if ($this->rtl) {
							$nr = $na.' '.$sfill;
						} else {
              $nr = $sfill.' '.$na;
              ### remove character ... from nr (page number)
              // $nr = preg_replace("/\W/m",'',$nr);
              ### end remove
						}
						$temppage = str_replace($a, $nr, $temppage);
					}
				}
				$this->setPageBuffer($p, $temppage, false ,$tes = true);
			}
			// move pages
			$this->Bookmark($toc_name, 0, 0, $page_first, $style, $color);
			if ($page_fill_start) {
				$this->movePage($page_last, $page_first);
			}
			for ($i = 0; $i < $numpages; ++$i) {
				$this->movePage($page_last, $page);
			}
		}
    // modified by me: adding intetionally left page
    $page_last_TOC = $numpages;
    if($this->getNumPages() % 2 != 0){
      $this->addIntentionallyLeftBlankPage();
      $this->movePage($this->getPage(), $page_last_TOC+1);
    }
	}

  private function addIntentionallyLeftBlankPage(){
    $pdf = $this->pdf;
    if(($pdf->getNumPages() % 2) != 0){
      $pdf->AddPage();
      $html = <<<EOD
      <div>
      <br/><br/><br/><br/><br/><br/><br/>
      <br/><br/><br/><br/><br/><br/><br/>
      <br/><br/><br/><br/><br/>
      INTENTIONALLY LEFT BLANK
      </div>
      EOD;
      $pdf->writeHTML($html,true,false,true,false,'C');
    }
  }

  public function __construct($tcpdf = null, string $aa_approved = '')
  {
    if($tcpdf){
      $this->pdf = $tcpdf;
    } else {
      // $this->pdf = new TCPDF('P','mm','A5',true,'UTF-8',false);
      parent::__construct('P','mm','A5',true,'UTF-8',false);
      $this->pdf = $this;
    }
    if($aa_approved != ''){
      $aa_name = preg_replace("/:.+/",'',$aa_approved);
      $approved_date = preg_replace("/.+:/",'',$aa_approved);
      $this->aa_approved['name'] = $aa_name;
      $this->aa_approved['date'] = $approved_date;
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

  public function import(string $xmlString){
    $dom = new DOMDocument();
    $dom->loadXML($xmlString);
    $this->DOMDocument = $dom;
  }

  // render pdf document
  public function render(){
    $DOMDocument = $this->DOMDocument;
    $pdf = $this->pdf;
    $pdf->setHeaderMargin(5);
    $pdf->setMargins(0,30,0);
    $pdf->setBooklet(true,10,20);
    $pdf->setFontSize(9);
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    $pdf->setFont('helvetica');
    $pdf->startPageGroup();

    $DOMXpath = new DOMXPath($DOMDocument);

    $pmEntry = $DOMXpath->evaluate("//content/pmEntry");
    $pmEntry = isset($pmEntry[0]) ? $pmEntry[0] : throw new Exception("The Publication Module shall have at least one pmEntry inside content.", 1);

    $pmEntry_children = (function () use ($pmEntry){
      $arr = [];
      foreach($pmEntry->childNodes as $childNodes){
        $childNodes instanceof \DOMElement ? array_push($arr, $childNodes) : null;
      }
      return $arr;      
    })();

    foreach($pmEntry_children as $childElement){
      if(($dmRef = $childElement)->tagName == 'dmRef'){
        $dmCode = $dmRef->getElementsByTagName('dmCode')[0];
        $issueInfo = $dmRef->getElementsByTagName('issueInfo')[0];

        $dmcDocument = CSDB::load($this->dmPath.DIRECTORY_SEPARATOR.DModule::resolveDMCode($dmCode, $issueInfo).".xml");

        $pdf->AddPage();
        // $pdf->Bookmark('foo',0,0);
        $dmc = new DMC_N219($this->pdf);
        $dmc->DOMDocument = $dmcDocument;
        $dmc->render();
      }
      elseif($childElement->tagName == 'pmRef'){
        // code
      }
    }

    // $pdf->AddPage();
    // // $pdf->setXY(50,50);
    // // $pdf->setX(50);
    // $pdf->setY(50);
    // $pdf->Write('','foo bar','',false,'J',false,0,true,true);
    // $pdf->Write('','bar','',false,'J',false,0,true,true);
    // dd($pdf->GetY()); // untuk mendapatkan posisi saat ini sehingga bisa untuk change mark
    // $$pdf->endPage();
    // $pdf->AddPage();

    // // add TOC
    $pdf->addTOCPage();
    $pdf->setLeftMargin(15);
    $pdf->setCellMargins(5);
    $pdf->WriteHTML('<b style="font-size:12">Table of Contents</b><br/>',true,false,true,true,'C');
    $pdf->addTOC(0,$pdf->getFontFamily(),'.','SECTION 04', 'B',array(128,0,0));
    $pdf->endTOCPage();

    
    // $pdf->setLeftMargin(0);
    // $pdf->AddPage();
    // $dmCode = "N219-A-15-30-00-00A-001A-A";    
    // $template_dmc_identification = $pdf->startTemplate(80,80,true);
    // $pdf->StartTransform();
    // $pdf->Rotate(90,25,25);
    // $pdf->Cell(50,0,$dmCode,0,0,'C',false,'',0,false,'T','M');
    // $pdf->StopTransform();
    // $pdf->endTemplate();
    // $pdf->printTemplate($template_dmc_identification,5,100,'','','','',false);
    


  }

  public function getPdfFile(){
    $this->pdf->Output('tes_pmc.pdf', 'I');
  }
  
  
}
