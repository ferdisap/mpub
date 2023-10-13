<?php

namespace Ptdi\Mpub\Pdf2;

use DOMDocument;
use DOMXPath;
use Ptdi\Mpub\CSDB;
use TCPDF;
use TCPDF_COLORS;
use TCPDF_FONT_DATA;
use TCPDF_FONTS;
use TCPDF_STATIC;

class PMC_PDF extends TCPDF
{
  protected string $absolute_path_csdbOutput;
  protected string $absolute_path_csdbInput;
  protected \DOMDocument $DOMDocument;
  protected bool $validateSchema = true;
  protected bool $validateBrex = true;
  protected array $pmType_config;

  public string $prefix_pagenum = '';
  protected array $aa_approved;
  
  public string $page_ident = '';

  public array $cgmark = [];
  public array $references = [];
  public string $curEntry;
  public $lastpageIntentionallyLeftBlank = 0;

  public array $footnotes = [];
  public array $footnotesRef = [];
  public bool $inFootnote = false;

  public float $footnoteheight = 0;

  /**
   * filename. It must be set the $absolute_path_csdbInput at first
   */
  public string $headerLogo = '../pdf2/assets/logo.png';

  /**
   * a text allow HTML entities or Number entities such &nbsp; or &#160;
   */
  public string $headerText = 'Header &#10; Text';

  /**
   * a text allow HTML entities or Number entities such &nbsp; or &#160;
   * This is located in the midle of the header
   */
  public string $headerTitle = 'Publication Title';

  /**
   * a text allow HTML entities or Number entities such &nbsp; or &#160;
   * This is used for the footer of the page;
   */
  public string $documentNumber = '';

  private array $qty_pmEntry = array();

  //Page header
  public function Header()
  {
    if (($this->getPage() % 2) == 0) {
      $header = (require "config/template/{$this->pmType_config['value']}_header.php")['even'];
      // $this->writeHTML($header, true, false, false,true,'J',false);
    } else {
      $header = (require "config/template/{$this->pmType_config['value']}_header.php")['odd'];
      // $this->writeHTML($header, true, false, false,true,'J',false);
    }
  }
  // Page footer
  public function Footer()
  {    
    if (($this->getPage() % 2) == 0) {
      $this->SetY(-15);
      $footer = (require "config/template/{$this->pmType_config['value']}_footer.php")['even'];
      // $this->writeHTML($footer, true, false, true, false, 'C');
    } else {
      // Position at 15 mm from bottom
      $this->SetY(-15);
      $footer = (require "config/template/{$this->pmType_config['value']}_footer.php")['odd'];
      // $this->writeHTML($footer, true, false, true, false, 'C');
    }
  }
  
  /**
	 * Move a page to a previous position.
	 * @param int $frompage number of the source page
	 * @param int $topage number of the destination page (must be less than $frompage)
	 * @return bool true in case of success, false in case of error.
	 * @public
	 * @since 4.5.000 (2009-01-02)
	 */
	public function movePage($frompage, $topage) {
		if (($frompage > $this->numpages) OR ($frompage <= $topage)) {
			return false;
		}
		if ($frompage == $this->page) {
			// close the page before moving it
			$this->endPage();
		}
		// move all page-related states
		$tmppage = $this->getPageBuffer($frompage);
		$tmppagedim = $this->pagedim[$frompage];
		$tmppagelen = $this->pagelen[$frompage];
		$tmpintmrk = $this->intmrk[$frompage];
		$tmpbordermrk = $this->bordermrk[$frompage];
		$tmpcntmrk = $this->cntmrk[$frompage];
		$tmppageobjects = $this->pageobjects[$frompage];
		if (isset($this->footerpos[$frompage])) {
			$tmpfooterpos = $this->footerpos[$frompage];
		}
		if (isset($this->footerlen[$frompage])) {
			$tmpfooterlen = $this->footerlen[$frompage];
		}
		if (isset($this->transfmrk[$frompage])) {
			$tmptransfmrk = $this->transfmrk[$frompage];
		}
		if (isset($this->PageAnnots[$frompage])) {
			$tmpannots = $this->PageAnnots[$frompage];
		}
		if (isset($this->newpagegroup) AND !empty($this->newpagegroup)) {
			for ($i = $frompage; $i > $topage; --$i) {
				if (isset($this->newpagegroup[$i]) AND (($i + $this->pagegroups[$this->newpagegroup[$i]]) > $frompage)) {
					--$this->pagegroups[$this->newpagegroup[$i]];
					break;
				}
			}
      /** EDITTED
       *  Tidak diketahui manfaatnya code ini
       *  Ini hanya menambah total jumlah halaman setiap page group, padahal aktualnya tidak, sehingga pada total page di footer nya juga akan bertambah
       */
			// for ($i = $topage; $i > 0; --$i) {
			// 	if (isset($this->newpagegroup[$i]) AND (($i + $this->pagegroups[$this->newpagegroup[$i]]) > $topage)) {
			// 		++$this->pagegroups[$this->newpagegroup[$i]];
			// 		break;
			// 	}
			// }
		}
		for ($i = $frompage; $i > $topage; --$i) {
			$j = $i - 1;
			// shift pages down
			$this->setPageBuffer($i, $this->getPageBuffer($j));
			$this->pagedim[$i] = $this->pagedim[$j];
			$this->pagelen[$i] = $this->pagelen[$j];
			$this->intmrk[$i] = $this->intmrk[$j];
			$this->bordermrk[$i] = $this->bordermrk[$j];
			$this->cntmrk[$i] = $this->cntmrk[$j];
			$this->pageobjects[$i] = $this->pageobjects[$j];
			if (isset($this->footerpos[$j])) {
				$this->footerpos[$i] = $this->footerpos[$j];
			} elseif (isset($this->footerpos[$i])) {
				unset($this->footerpos[$i]);
			}
			if (isset($this->footerlen[$j])) {
				$this->footerlen[$i] = $this->footerlen[$j];
			} elseif (isset($this->footerlen[$i])) {
				unset($this->footerlen[$i]);
			}
			if (isset($this->transfmrk[$j])) {
				$this->transfmrk[$i] = $this->transfmrk[$j];
			} elseif (isset($this->transfmrk[$i])) {
				unset($this->transfmrk[$i]);
			}
			if (isset($this->PageAnnots[$j])) {
				$this->PageAnnots[$i] = $this->PageAnnots[$j];
			} elseif (isset($this->PageAnnots[$i])) {
				unset($this->PageAnnots[$i]);
			}
      /**
       * EDITTED - supaya TOC masuk ke page group juga,
       * kalau tidak di edit, page group dimulai setelah TOC. Bisa berefek ke page group numbernya di footer
       * keys pada array $newpagegroup adalah halaman pagegroup dimulai
       */
			if (isset($this->newpagegroup[$j])) {
				$this->newpagegroup[$i] = $this->newpagegroup[$j];
        // jika ini dilakukan, total page pada footer harus di kurangi, akan ada tambahan script lagi.
        // jika ini dilakuka, TOC akan berada di halaman terpisah, namun total page di footer akan bertambah meskipun aktualnya tidak
				// unset($this->newpagegroup[$j]); 
				unset($this->newpagegroup[$i]); // sepertinya ini salah unset. Seharusnya TOC itu masuk kedalam group
			}
      /** end EDITTED */
			if ($this->currpagegroup == $j) {
				$this->currpagegroup = $i;
			}
		}
		$this->setPageBuffer($topage, $tmppage);
		$this->pagedim[$topage] = $tmppagedim;
		$this->pagelen[$topage] = $tmppagelen;
		$this->intmrk[$topage] = $tmpintmrk;
		$this->bordermrk[$topage] = $tmpbordermrk;
		$this->cntmrk[$topage] = $tmpcntmrk;
		$this->pageobjects[$topage] = $tmppageobjects;
		if (isset($tmpfooterpos)) {
			$this->footerpos[$topage] = $tmpfooterpos;
		} elseif (isset($this->footerpos[$topage])) {
			unset($this->footerpos[$topage]);
		}
		if (isset($tmpfooterlen)) {
			$this->footerlen[$topage] = $tmpfooterlen;
		} elseif (isset($this->footerlen[$topage])) {
			unset($this->footerlen[$topage]);
		}
		if (isset($tmptransfmrk)) {
			$this->transfmrk[$topage] = $tmptransfmrk;
		} elseif (isset($this->transfmrk[$topage])) {
			unset($this->transfmrk[$topage]);
		}
		if (isset($tmpannots)) {
			$this->PageAnnots[$topage] = $tmpannots;
		} elseif (isset($this->PageAnnots[$topage])) {
			unset($this->PageAnnots[$topage]);
		}
		// adjust outlines
		$tmpoutlines = $this->outlines;
		foreach ($tmpoutlines as $key => $outline) {
			if (!$outline['f']) {
				if (($outline['p'] >= $topage) AND ($outline['p'] < $frompage)) {
					$this->outlines[$key]['p'] = ($outline['p'] + 1);
				} elseif ($outline['p'] == $frompage) {
					$this->outlines[$key]['p'] = $topage;
				}
			}
		}
		// adjust dests
		$tmpdests = $this->dests;
		foreach ($tmpdests as $key => $dest) {
			if (!$dest['f']) {
				if (($dest['p'] >= $topage) AND ($dest['p'] < $frompage)) {
					$this->dests[$key]['p'] = ($dest['p'] + 1);
				} elseif ($dest['p'] == $frompage) {
					$this->dests[$key]['p'] = $topage;
				}
			}
		}
		// adjust links
		$tmplinks = $this->links;
		foreach ($tmplinks as $key => $link) {
			if (!$link['f']) {
				if (($link['p'] >= $topage) AND ($link['p'] < $frompage)) {
					$this->links[$key]['p'] = ($link['p'] + 1);
				} elseif ($link['p'] == $frompage) {
					$this->links[$key]['p'] = $topage;
				}
			}
		}
		// adjust javascript
		$jfrompage = $frompage;
		$jtopage = $topage;
		if (preg_match_all('/this\.addField\(\'([^\']*)\',\'([^\']*)\',([0-9]+)/', $this->javascript, $pamatch) > 0) {
			foreach($pamatch[0] as $pk => $pmatch) {
				$pagenum = intval($pamatch[3][$pk]) + 1;
				if (($pagenum >= $jtopage) AND ($pagenum < $jfrompage)) {
					$newpage = ($pagenum + 1);
				} elseif ($pagenum == $jfrompage) {
					$newpage = $jtopage;
				} else {
					$newpage = $pagenum;
				}
				--$newpage;
				$newjs = "this.addField(\'".$pamatch[1][$pk]."\',\'".$pamatch[2][$pk]."\',".$newpage;
				$this->javascript = str_replace($pmatch, $newjs, $this->javascript);
			}
			unset($pamatch);
		}
    // adjust references, not links
    // $tmpreferences = $this->references;
    // foreach($tmpreferences as $key => $reference){
    //   if(!$reference['f']){
    //     if(($reference['p'] >= $topage) AND ($reference['p'] < $frompage)){
    //       $this->references[$key]['p'] = ($reference['p'] + 1);
    //     } elseif ($reference['p'] == $frompage){
    //       $this->reference[$key]['p'] = $topage;
    //     }
    //   }
    // }
    
		// return to last page
    /** EDITTED - comment lastPage() function agar tidak terjadi perbedaan margin setiap pmEntry.
     *  walaupun parameter ($resetmargin) di set false, malah berantakan
     */
		// $this->lastPage(true);
		return true;
	}

  /**
   * Output a Table of Content Index (TOC).
   * This method must be called after all Bookmarks were set.
   * Before calling this method you have to open the page using the addTOCPage() method.
   * After calling this method you have to call endTOCPage() to close the TOC page.
   * You can override this method to achieve different styles.
   * @param int|null $page page number where this TOC should be inserted (leave empty for current page).
   * @param string $numbersfont set the font for page numbers (please use monospaced font for better alignment).
   * @param string $filler string used to fill the space between text and page number.
   * @param string $toc_name name to use for TOC bookmark.
   * @param string $style Font style for title: B = Bold, I = Italic, BI = Bold + Italic.
   * @param array $color RGB color array for bookmark title (values from 0 to 255).
   * @public
   * @author Nicola Asuni
   * @since 4.5.000 (2009-01-02)
   * @see addTOCPage(), endTOCPage(), addHTMLTOC()
   */
  public function addTOC($page = null, $numbersfont = '', $filler = '.', $toc_name = 'TOC', $style = '', $color = array(0, 0, 0))
  {
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
    // *** EDITED
    $writtenPage = []; // page mana saja di $outline['p'] yang di cetak
    for ($i=0; $i < count($this->outlines); $i++){
      $outline = $this->outlines[$i];
      
      if($outline['l'] == 0){
        continue; // abandond level 0 of bookmarked (outline), sehingga tidak di write di TOC page nya
      }

      if($outline['p'] < $page){
        continue; // supaya jika ada bookmark yang menuju halaman yang ada di pagegroup sebelumnya, maka tidak akan di write di page TOC nya
      }
      array_push($writtenPage,$outline['p']);

      // check for extra pages (used for attachments)
      if (($this->page > $page_first) and ($outline['p'] >= $this->numpages)) {
        $outline['p'] += ($this->page - $page_first);
      }
      if ($this->rtl) {
        $aligntext = 'R';
        $alignnum = 'L';
      } else {
        $aligntext = 'L';
        $alignnum = 'R';
      }
      $this->setFont($fontfamily, $outline['s'], $fontsize); // fontsize is same with default
      
      $this->setTextColorArray($outline['c']);
      // check for page break
      $this->checkPageBreak(2 * $this->getCellHeight($this->FontSize));
      // set margins and X position
      if (($this->page == $current_page) and ($this->current_column == $current_column)) {
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
      $indent = ($spacer * ($outline['l']));
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
        $txt = ' ' . $outline['t'];
      } else {
        $txt = $outline['t'] . '  ';
      }
      // *** EDITED - make vertical space before Level 1, not level 0 because level is abandoned
      if ($outline['l'] == 1) {
        $this->Ln(1.5);
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
        $pagenum = '{#' . ($outline['p']) . '}';
        if ($this->isUnicodeFont()) {
          $pagenum = '{' . $pagenum . '}';
        }
        $maxpage = max($maxpage, $outline['p']);
      }
      $fw = ($tw - $this->GetStringWidth($pagenum . $filler));
      $wfiller = $this->GetStringWidth($filler);
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
        $pagenum = $pagenum . $gap . $rowfill;
      } else {
        // $pagenum = $rowfill.$gap.$pagenum;
        // *** EDITED ### bypass page number by adding prefix
        $pagenum = $rowfill.$this->prefix_pagenum . $pagenum; // gap di hilangkan dari sini karena akan ditambahkan saat replacing page number alias dibawah (masih di fungsi addTOC())
        ### end bypass
      }
      // write the number
      $this->Cell($tw, 0, $pagenum, 0, 1, $alignnum, 0, $link, 0, false, 'T', 'M');
    }
    $page_last = $this->getPage();
    $numpages = ($page_last - $page_first + 1);
    // account for booklet mode
    if ($this->booklet) {
      // check if a blank page is required before TOC
      $page_fill_start = ((($page_first % 2) == 0) xor (($page % 2) == 0));
      $page_fill_end = (!((($numpages % 2) == 0) xor ($page_fill_start)));
      if ($page_fill_start) {
        // add a page at the end (to be moved before TOC)
        // *** EDITED
        self::addIntentionallyLeftBlankPage($this);
        ++$page_last;
        ++$numpages;
      }
      if ($page_fill_end) {
        // add a page at the end
        // *** EDITED
        self::addIntentionallyLeftBlankPage($this);
        ++$page_last;
        ++$numpages;
      }
    };
    // code untuk update page number aliasnya
    $maxpage = max($maxpage, $page_last);
    if (!TCPDF_STATIC::empty_string($page)) {
      if(!empty($writtenPage)){
        for ($p = $page_first; $p <= $page_last; ++$p) {
          // get page data
          $temppage = $this->getPageBuffer($p);
          for ($n = 1; $n <= $maxpage; ++$n) {
            // update page numbers
            $a = '{#' . $n . '}';
            // get page number aliases
            $pnalias = $this->getInternalPageNumberAliases($a);
            // calculate replacement number
            if (($n >= $page) and ($n <= $this->numpages)) {
              $np = $n + $numpages;
            } else {
              $np = $n;
            }
            $na = TCPDF_STATIC::formatTOCPageNumber(($this->starting_page_number + $np - 1));
            $nu = TCPDF_FONTS::UTF8ToUTF16BE($na, false, $this->isunicode, $this->CurrentFont);
  
            /** EDITTED - tambahan
             * agar addressed page di TOC tertulis sesuai dengan pergroup. 
             * Jika ada dua group, maka addressed page tidak akan dihitung dari group sebelumnya
             */
            $qty = 0;
            foreach($this->pagegroups as $index => $qtyPage){
              if(($qtyPage + $qty) > $page){
                break;
              } else {
                $qty += $qtyPage;
              }
            }
            $na -= $qty;
            /** end EDITTED */
  
            // replace aliases with numbers
            foreach ($pnalias['u'] as $u) {
              $sfill = str_repeat($filler, max(0, (strlen($u) - strlen($nu . ' '))));
              if ($this->rtl) {
                $nr = $nu . TCPDF_FONTS::UTF8ToUTF16BE(' ' . $sfill, false, $this->isunicode, $this->CurrentFont);
              } else {
                $nr = TCPDF_FONTS::UTF8ToUTF16BE($sfill . ' ', false, $this->isunicode, $this->CurrentFont) . $nu;
              }
              $temppage = str_replace($u, $nr, $temppage);
            }
            foreach ($pnalias['a'] as $a) {
              $sfill = str_repeat($filler, max(0, (strlen($a) - strlen($na . ' '))));
              if ($this->rtl) {
                $nr = $na . ' ' . $sfill;
              } else {
                $nr = $sfill . ' ' . $na;
                ### remove character ... from nr (page number)
                $nr = preg_replace("/\W/m",'',$nr);                
                ### end remove
              }
  
              // karena Cell di render dengan page alias, jadi width cell tidak aktual (tidak sama align di kanan antar list toc nya) saat di replace aliasnya, jadi lakukan script dibawah ini
              // $nr adalah pengganti nya, $a aliasnya eg.:{#1}
              $nr = $gap.$nr;
              $wa = $this->GetStringWidth($a);
              $wnr = $this->GetStringWidth($nr);
              $ls = $wa - $wnr;
              $numtb = floor($ls / $wfiller);
              if ($wfiller > 0) {
                $numfills = floor($fw / $wfiller); // jumlah titik2 atau filler toc nya
              }
              if ($numtb > 0) {
                $tb = str_repeat($filler, $numtb);
              } else {
                $tb = '';
              }
              $nr = $tb.$nr;
  
              $temppage = str_replace($a, $nr, $temppage);
            }
          }
          $this->setPageBuffer($p, $temppage, false, $tes = true);
        }
      }
      // move pages
      if ($page_fill_start) {
        $this->movePage($page_last, $page_first); // ini digunakan untuk menukar 2 halaman terkahir (dua halaman itu adalah TOC (intentionally left blank page))
      }
      // dump($page_last, $page);
      for ($i = 0; $i < $numpages; ++$i) {
        $this->movePage($page_last, $page); // ini untuk memindahkan TOC (2 halaman atau lebih) ke halaman yang kita inginkan ($page) sesuai parameter fungsi
      }
    }
  }

  /**
   * editannya: menghilangkan default setelan border['cap'].
	 * Returns the border style array from CSS border properties
	 * @param string $cssborder border properties
	 * @return array containing border properties
	 * @protected
	 * @since 5.7.000 (2010-08-02)
	 */
	protected function getCSSBorderStyle($cssborder) {
		$bprop = preg_split('/[\s]+/', trim($cssborder));
		$count = count($bprop);
		if ($count > 0 && $bprop[$count - 1] === '!important') {
			unset($bprop[$count - 1]);
			--$count;
		}

		$border = array(); // value to be returned
		switch ($count) {
			case 2: {
				$width = 'medium';
				$style = $bprop[0];
				$color = $bprop[1];
				break;
			}
			case 1: {
				$width = 'medium';
				$style = $bprop[0];
				$color = 'black';
				break;
			}
			case 0: {
				$width = 'medium';
				$style = 'solid';
				$color = 'black';
				break;
			}
			default: {
				$width = $bprop[0];
				$style = $bprop[1];
				$color = $bprop[2];
				break;
			}
		}
		if ($style == 'none') {
			return array();
		}
		// $border['cap'] = 'square';
		$border['join'] = 'miter';
		$border['dash'] = $this->getCSSBorderDashStyle($style);
		if ($border['dash'] < 0) {
			return array();
		}
		$border['width'] = $this->getCSSBorderWidth($width);
		$border['color'] = TCPDF_COLORS::convertHTMLColorToDec($color, $this->spot_colors);
		return $border;
	}

  /**
   * tambahan nya adalah menambah attribute cgmarkid disetiap dom yang punya changemark==1
	 * Returns the HTML DOM array.
	 * @param string $html html code
	 * @return array
	 * @protected
	 * @since 3.2.000 (2008-06-20)
	 */
	protected function getHtmlDomArray($html) {
    // dump($html);
		// array of CSS styles ( selector => properties).
		$css = array();
		// get CSS array defined at previous call
		$matches = array();
		if (preg_match_all('/<cssarray>([^\<]*?)<\/cssarray>/is', $html, $matches) > 0) {
			if (isset($matches[1][0])) {
				$css = array_merge($css, json_decode($this->unhtmlentities($matches[1][0]), true));
			}
			$html = preg_replace('/<cssarray>(.*?)<\/cssarray>/is', '', $html);
		}
		// extract external CSS files
		$matches = array();
		if (preg_match_all('/<link([^\>]*?)>/is', $html, $matches) > 0) {
			foreach ($matches[1] as $key => $link) {
				$type = array();
				if (preg_match('/type[\s]*=[\s]*"text\/css"/', $link, $type)) {
					$type = array();
					preg_match('/media[\s]*=[\s]*"([^"]*)"/', $link, $type);
					// get 'all' and 'print' media, other media types are discarded
					// (all, braille, embossed, handheld, print, projection, screen, speech, tty, tv)
					if (empty($type) OR (isset($type[1]) AND (($type[1] == 'all') OR ($type[1] == 'print')))) {
						$type = array();
						if (preg_match('/href[\s]*=[\s]*"([^"]*)"/', $link, $type) > 0) {
							// read CSS data file
                            $cssdata = $this->getCachedFileContents(trim($type[1]));
							if (($cssdata !== FALSE) AND (strlen($cssdata) > 0)) {
								$css = array_merge($css, TCPDF_STATIC::extractCSSproperties($cssdata));
							}
						}
					}
				}
			}
		}
		// extract style tags
		$matches = array();
		if (preg_match_all('/<style([^\>]*?)>([^\<]*?)<\/style>/is', $html, $matches) > 0) {
			foreach ($matches[1] as $key => $media) {
				$type = array();
				preg_match('/media[\s]*=[\s]*"([^"]*)"/', $media, $type);
				// get 'all' and 'print' media, other media types are discarded
				// (all, braille, embossed, handheld, print, projection, screen, speech, tty, tv)
				if (empty($type) OR (isset($type[1]) AND (($type[1] == 'all') OR ($type[1] == 'print')))) {
					$cssdata = $matches[2][$key];
					$css = array_merge($css, TCPDF_STATIC::extractCSSproperties($cssdata));
				}
			}
		}
		// create a special tag to contain the CSS array (used for table content)
		$csstagarray = '<cssarray>'.htmlentities(json_encode($css)).'</cssarray>';
		// remove head and style blocks
		$html = preg_replace('/<head([^\>]*?)>(.*?)<\/head>/is', '', $html);
		$html = preg_replace('/<style([^\>]*?)>([^\<]*?)<\/style>/is', '', $html);
		// define block tags
		$blocktags = array('blockquote','br','dd','dl','div','dt','h1','h2','h3','h4','h5','h6','hr','li','ol','p','pre','ul','tcpdf','table','tr','td');
		// define self-closing tags
		$selfclosingtags = array('area','base','basefont','br','hr','input','img','link','meta');
		// remove all unsupported tags (the line below lists all supported tags)
		$html = strip_tags($html, '<marker/><a><b><blockquote><body><br><br/><dd><del><div><dl><dt><em><font><form><h1><h2><h3><h4><h5><h6><hr><hr/><i><img><input><label><li><ol><option><p><pre><s><select><small><span><strike><strong><sub><sup><table><tablehead><tcpdf><td><textarea><th><thead><tr><tt><u><ul>');
		//replace some blank characters
		$html = preg_replace('/<pre/', '<xre', $html); // preserve pre tag
		$html = preg_replace('/<(table|tr|td|th|tcpdf|blockquote|dd|div|dl|dt|form|h1|h2|h3|h4|h5|h6|br|hr|li|ol|ul|p)([^\>]*)>[\n\r\t]+/', '<\\1\\2>', $html);
		$html = preg_replace('@(\r\n|\r)@', "\n", $html);
		$repTable = array("\t" => ' ', "\0" => ' ', "\x0B" => ' ', "\\" => "\\\\");
		$html = strtr($html, $repTable);
		$offset = 0;
		while (($offset < strlen($html)) AND ($pos = strpos($html, '</pre>', $offset)) !== false) {
			$html_a = substr($html, 0, $offset);
			$html_b = substr($html, $offset, ($pos - $offset + 6));
			while (preg_match("'<xre([^\>]*)>(.*?)\n(.*?)</pre>'si", $html_b)) {
				// preserve newlines on <pre> tag
				$html_b = preg_replace("'<xre([^\>]*)>(.*?)\n(.*?)</pre>'si", "<xre\\1>\\2<br />\\3</pre>", $html_b);
			}
			while (preg_match("'<xre([^\>]*)>(.*?)".$this->re_space['p']."(.*?)</pre>'".$this->re_space['m'], $html_b)) {
				// preserve spaces on <pre> tag
				$html_b = preg_replace("'<xre([^\>]*)>(.*?)".$this->re_space['p']."(.*?)</pre>'".$this->re_space['m'], "<xre\\1>\\2&nbsp;\\3</pre>", $html_b);
			}
			$html = $html_a.$html_b.substr($html, $pos + 6);
			$offset = strlen($html_a.$html_b);
		}
		$offset = 0;
		while (($offset < strlen($html)) AND ($pos = strpos($html, '</textarea>', $offset)) !== false) {
			$html_a = substr($html, 0, $offset);
			$html_b = substr($html, $offset, ($pos - $offset + 11));
			while (preg_match("'<textarea([^\>]*)>(.*?)\n(.*?)</textarea>'si", $html_b)) {
				// preserve newlines on <textarea> tag
				$html_b = preg_replace("'<textarea([^\>]*)>(.*?)\n(.*?)</textarea>'si", "<textarea\\1>\\2<TBR>\\3</textarea>", $html_b);
				$html_b = preg_replace("'<textarea([^\>]*)>(.*?)[\"](.*?)</textarea>'si", "<textarea\\1>\\2''\\3</textarea>", $html_b);
			}
			$html = $html_a.$html_b.substr($html, $pos + 11);
			$offset = strlen($html_a.$html_b);
		}
		$html = preg_replace('/([\s]*)<option/si', '<option', $html);
		$html = preg_replace('/<\/option>([\s]*)/si', '</option>', $html);
		$offset = 0;
		while (($offset < strlen($html)) AND ($pos = strpos($html, '</option>', $offset)) !== false) {
			$html_a = substr($html, 0, $offset);
			$html_b = substr($html, $offset, ($pos - $offset + 9));
			while (preg_match("'<option([^\>]*)>(.*?)</option>'si", $html_b)) {
				$html_b = preg_replace("'<option([\s]+)value=\"([^\"]*)\"([^\>]*)>(.*?)</option>'si", "\\2#!TaB!#\\4#!NwL!#", $html_b);
				$html_b = preg_replace("'<option([^\>]*)>(.*?)</option>'si", "\\2#!NwL!#", $html_b);
			}
			$html = $html_a.$html_b.substr($html, $pos + 9);
			$offset = strlen($html_a.$html_b);
		}
		if (preg_match("'</select'si", $html)) {
			$html = preg_replace("'<select([^\>]*)>'si", "<select\\1 opt=\"", $html);
			$html = preg_replace("'#!NwL!#</select>'si", "\" />", $html);
		}
		$html = str_replace("\n", ' ', $html);
		// restore textarea newlines
		$html = str_replace('<TBR>', "\n", $html);
		// remove extra spaces from code
		$html = preg_replace('/[\s]+<\/(table|tr|ul|ol|dl)>/', '</\\1>', $html);
		$html = preg_replace('/'.$this->re_space['p'].'+<\/(td|th|li|dt|dd)>/'.$this->re_space['m'], '</\\1>', $html);
		$html = preg_replace('/[\s]+<(tr|td|th|li|dt|dd)/', '<\\1', $html);
		$html = preg_replace('/'.$this->re_space['p'].'+<(ul|ol|dl|br)/'.$this->re_space['m'], '<\\1', $html);
		$html = preg_replace('/<\/(table|tr|td|th|blockquote|dd|dt|dl|div|dt|h1|h2|h3|h4|h5|h6|hr|li|ol|ul|p)>[\s]+</', '</\\1><', $html);
		$html = preg_replace('/<\/(td|th)>/', '<marker style="font-size:0"/></\\1>', $html);
		$html = preg_replace('/<\/table>([\s]*)<marker style="font-size:0"\/>/', '</table>', $html);
		$html = preg_replace('/'.$this->re_space['p'].'+<img/'.$this->re_space['m'], chr(32).'<img', $html);
		$html = preg_replace('/<img([^\>]*)>[\s]+([^\<])/xi', '<img\\1>&nbsp;\\2', $html);
		$html = preg_replace('/<img([^\>]*)>/xi', '<img\\1><span><marker style="font-size:0"/></span>', $html);
		$html = preg_replace('/<xre/', '<pre', $html); // restore pre tag
		$html = preg_replace('/<textarea([^\>]*)>([^\<]*)<\/textarea>/xi', '<textarea\\1 value="\\2" />', $html);
		$html = preg_replace('/<li([^\>]*)><\/li>/', '<li\\1>&nbsp;</li>', $html);
		$html = preg_replace('/<li([^\>]*)>'.$this->re_space['p'].'*<img/'.$this->re_space['m'], '<li\\1><font size="1">&nbsp;</font><img', $html);
		$html = preg_replace('/<([^\>\/]*)>[\s]/', '<\\1>&nbsp;', $html); // preserve some spaces
		$html = preg_replace('/[\s]<\/([^\>]*)>/', '&nbsp;</\\1>', $html); // preserve some spaces
		$html = preg_replace('/<su([bp])/', '<zws/><su\\1', $html); // fix sub/sup alignment
		$html = preg_replace('/<\/su([bp])>/', '</su\\1><zws/>', $html); // fix sub/sup alignment
		$html = preg_replace('/'.$this->re_space['p'].'+/'.$this->re_space['m'], chr(32), $html); // replace multiple spaces with a single space
		// trim string
		$html = $this->stringTrim($html);
		// fix br tag after li
		$html = preg_replace('/<li><br([^\>]*)>/', '<li> <br\\1>', $html);
		// fix first image tag alignment
		$html = preg_replace('/^<img/', '<span style="font-size:0"><br /></span> <img', $html, 1);
		// pattern for generic tag
		$tagpattern = '/(<[^>]+>)/';
		// explodes the string
		$a = preg_split($tagpattern, $html, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
		// count elements
		$maxel = count($a);
		$elkey = 0;
		$key = 0;
		// create an array of elements
		$dom = array();
		$dom[$key] = array();
		// set inheritable properties fot the first void element
		// possible inheritable properties are: azimuth, border-collapse, border-spacing, caption-side, color, cursor, direction, empty-cells, font, font-family, font-stretch, font-size, font-size-adjust, font-style, font-variant, font-weight, letter-spacing, line-height, list-style, list-style-image, list-style-position, list-style-type, orphans, page, page-break-inside, quotes, speak, speak-header, text-align, text-indent, text-transform, volume, white-space, widows, word-spacing
		$dom[$key]['tag'] = false;
		$dom[$key]['block'] = false;
		$dom[$key]['value'] = '';
		$dom[$key]['parent'] = 0;
		$dom[$key]['hide'] = false;
		$dom[$key]['fontname'] = $this->FontFamily;
		$dom[$key]['fontstyle'] = $this->FontStyle;
		$dom[$key]['fontsize'] = $this->FontSizePt;
		$dom[$key]['font-stretch'] = $this->font_stretching;
		$dom[$key]['letter-spacing'] = $this->font_spacing;
		$dom[$key]['stroke'] = $this->textstrokewidth;
		$dom[$key]['fill'] = (($this->textrendermode % 2) == 0);
		$dom[$key]['clip'] = ($this->textrendermode > 3);
		$dom[$key]['line-height'] = $this->cell_height_ratio;
		$dom[$key]['bgcolor'] = false;
		$dom[$key]['fgcolor'] = $this->fgcolor; // color
		$dom[$key]['strokecolor'] = $this->strokecolor;
		$dom[$key]['align'] = '';
		$dom[$key]['listtype'] = '';
		$dom[$key]['text-indent'] = 0;
		$dom[$key]['text-transform'] = '';
		$dom[$key]['border'] = array();
		$dom[$key]['dir'] = $this->rtl?'rtl':'ltr';
		$thead = false; // true when we are inside the THEAD tag
		++$key;
		$level = array();
		array_push($level, 0); // root
		while ($elkey < $maxel) {
			$dom[$key] = array();
			$element = $a[$elkey];
			$dom[$key]['elkey'] = $elkey;

			if (preg_match($tagpattern, $element)) {
				// html tag
				$element = substr($element, 1, -1);
				// get tag name
				preg_match('/[\/]?([a-zA-Z0-9]*)/', $element, $tag);
				$tagname = strtolower($tag[1]);
				// check if we are inside a table header
				if ($tagname == 'thead') {
					if ($element[0] == '/') {
						$thead = false;
					} else {
						$thead = true;
					}
					++$elkey;
					continue;
				}
				$dom[$key]['tag'] = true;
				$dom[$key]['value'] = $tagname;
				if (in_array($dom[$key]['value'], $blocktags)) {
					$dom[$key]['block'] = true;
				} else {
					$dom[$key]['block'] = false;
				}
				if ($element[0] == '/') {
					// *** closing html tag
					$dom[$key]['opening'] = false;
					$dom[$key]['parent'] = end($level);
					array_pop($level);
					$dom[$key]['hide'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['hide'];
					$dom[$key]['fontname'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['fontname'];
					$dom[$key]['fontstyle'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['fontstyle'];
					$dom[$key]['fontsize'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['fontsize'];
					$dom[$key]['font-stretch'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['font-stretch'];
					$dom[$key]['letter-spacing'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['letter-spacing'];
					$dom[$key]['stroke'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['stroke'];
					$dom[$key]['fill'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['fill'];
					$dom[$key]['clip'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['clip'];
					$dom[$key]['line-height'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['line-height'];
					$dom[$key]['bgcolor'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['bgcolor'];
					$dom[$key]['fgcolor'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['fgcolor'];
					$dom[$key]['strokecolor'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['strokecolor'];
					$dom[$key]['align'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['align'];
					$dom[$key]['text-transform'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['text-transform'];
					$dom[$key]['dir'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['dir'];
					if (isset($dom[($dom[($dom[$key]['parent'])]['parent'])]['listtype'])) {
						$dom[$key]['listtype'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['listtype'];
					}
					// set the number of columns in table tag
					if (($dom[$key]['value'] == 'tr') AND (!isset($dom[($dom[($dom[$key]['parent'])]['parent'])]['cols']))) {
						$dom[($dom[($dom[$key]['parent'])]['parent'])]['cols'] = $dom[($dom[$key]['parent'])]['cols'];
					}
					if (($dom[$key]['value'] == 'td') OR ($dom[$key]['value'] == 'th')) {
						$dom[($dom[$key]['parent'])]['content'] = $csstagarray;
						for ($i = ($dom[$key]['parent'] + 1); $i < $key; ++$i) {
							$dom[($dom[$key]['parent'])]['content'] .= stripslashes($a[$dom[$i]['elkey']]);
						}
						$key = $i;
						// mark nested tables
						$dom[($dom[$key]['parent'])]['content'] = str_replace('<table', '<table nested="true"', $dom[($dom[$key]['parent'])]['content']);
						// remove thead sections from nested tables
						$dom[($dom[$key]['parent'])]['content'] = str_replace('<thead>', '', $dom[($dom[$key]['parent'])]['content']);
						$dom[($dom[$key]['parent'])]['content'] = str_replace('</thead>', '', $dom[($dom[$key]['parent'])]['content']);
					}
					// store header rows on a new table
					if (
						($dom[$key]['value'] === 'tr')
						&& !empty($dom[($dom[$key]['parent'])]['thead'])
						&& ($dom[($dom[$key]['parent'])]['thead'] === true)
					) {
						if (TCPDF_STATIC::empty_string($dom[($dom[($dom[$key]['parent'])]['parent'])]['thead'])) {
							$dom[($dom[($dom[$key]['parent'])]['parent'])]['thead'] = $csstagarray.$a[$dom[($dom[($dom[$key]['parent'])]['parent'])]['elkey']];
						}
						for ($i = $dom[$key]['parent']; $i <= $key; ++$i) {
							$dom[($dom[($dom[$key]['parent'])]['parent'])]['thead'] .= $a[$dom[$i]['elkey']];
						}
						if (!isset($dom[($dom[$key]['parent'])]['attribute'])) {
							$dom[($dom[$key]['parent'])]['attribute'] = array();
						}
						// header elements must be always contained in a single page
						$dom[($dom[$key]['parent'])]['attribute']['nobr'] = 'true';
					}
					if (($dom[$key]['value'] == 'table') AND (!TCPDF_STATIC::empty_string($dom[($dom[$key]['parent'])]['thead']))) {
						// remove the nobr attributes from the table header
						$dom[($dom[$key]['parent'])]['thead'] = str_replace(' nobr="true"', '', $dom[($dom[$key]['parent'])]['thead']);
						$dom[($dom[$key]['parent'])]['thead'] .= '</tablehead>';
					}
				} else {
					// *** opening or self-closing html tag
					$dom[$key]['opening'] = true;
					$dom[$key]['parent'] = end($level);
					if ((substr($element, -1, 1) == '/') OR (in_array($dom[$key]['value'], $selfclosingtags))) {
						// self-closing tag
						$dom[$key]['self'] = true;
					} else {
						// opening tag
						array_push($level, $key);
						$dom[$key]['self'] = false;
					}
					// copy some values from parent
					$parentkey = 0;
					if ($key > 0) {
						$parentkey = $dom[$key]['parent'];
						$dom[$key]['hide'] = $dom[$parentkey]['hide'];
						$dom[$key]['fontname'] = $dom[$parentkey]['fontname'];
						$dom[$key]['fontstyle'] = $dom[$parentkey]['fontstyle'];
						$dom[$key]['fontsize'] = $dom[$parentkey]['fontsize'];
						$dom[$key]['font-stretch'] = $dom[$parentkey]['font-stretch'];
						$dom[$key]['letter-spacing'] = $dom[$parentkey]['letter-spacing'];
						$dom[$key]['stroke'] = $dom[$parentkey]['stroke'];
						$dom[$key]['fill'] = $dom[$parentkey]['fill'];
						$dom[$key]['clip'] = $dom[$parentkey]['clip'];
						$dom[$key]['line-height'] = $dom[$parentkey]['line-height'];
						$dom[$key]['bgcolor'] = $dom[$parentkey]['bgcolor'];
						$dom[$key]['fgcolor'] = $dom[$parentkey]['fgcolor'];
						$dom[$key]['strokecolor'] = $dom[$parentkey]['strokecolor'];
						$dom[$key]['align'] = $dom[$parentkey]['align'];
						$dom[$key]['listtype'] = $dom[$parentkey]['listtype'];
						$dom[$key]['text-indent'] = $dom[$parentkey]['text-indent'];
						$dom[$key]['text-transform'] = $dom[$parentkey]['text-transform'];
						$dom[$key]['border'] = array();
						$dom[$key]['dir'] = $dom[$parentkey]['dir'];
					}
					// get attributes
					preg_match_all('/([^=\s]*)[\s]*=[\s]*"([^"]*)"/', $element, $attr_array, PREG_PATTERN_ORDER);
					$dom[$key]['attribute'] = array(); // reset attribute array
                    foreach($attr_array[1] as $id => $name) {
                        $dom[$key]['attribute'][strtolower($name)] = $attr_array[2][$id];
                    }
					if (!empty($css)) {
						// merge CSS style to current style
						list($dom[$key]['csssel'], $dom[$key]['cssdata']) = TCPDF_STATIC::getCSSdataArray($dom, $key, $css);
						$dom[$key]['attribute']['style'] = TCPDF_STATIC::getTagStyleFromCSSarray($dom[$key]['cssdata']);
					}
					// split style attributes
					if (isset($dom[$key]['attribute']['style']) AND !empty($dom[$key]['attribute']['style'])) {
						// get style attributes
						preg_match_all('/([^;:\s]*):([^;]*)/', $dom[$key]['attribute']['style'], $style_array, PREG_PATTERN_ORDER);
						$dom[$key]['style'] = array(); // reset style attribute array
                        foreach($style_array[1] as $id => $name) {
                            // in case of duplicate attribute the last replace the previous
                            $dom[$key]['style'][strtolower($name)] = trim($style_array[2][$id]);
                        }
						// --- get some style attributes ---
						// text direction
						if (isset($dom[$key]['style']['direction'])) {
							$dom[$key]['dir'] = $dom[$key]['style']['direction'];
						}
						// display
						if (isset($dom[$key]['style']['display'])) {
							$dom[$key]['hide'] = (trim(strtolower($dom[$key]['style']['display'])) == 'none');
						}
						// font family
						if (isset($dom[$key]['style']['font-family'])) {
							$dom[$key]['fontname'] = $this->getFontFamilyName($dom[$key]['style']['font-family']);
						}
						// list-style-type
						if (isset($dom[$key]['style']['list-style-type'])) {
							$dom[$key]['listtype'] = trim(strtolower($dom[$key]['style']['list-style-type']));
							if ($dom[$key]['listtype'] == 'inherit') {
								$dom[$key]['listtype'] = $dom[$parentkey]['listtype'];
							}
						}
						// text-indent
						if (isset($dom[$key]['style']['text-indent'])) {
							$dom[$key]['text-indent'] = $this->getHTMLUnitToUnits($dom[$key]['style']['text-indent']);
							if ($dom[$key]['text-indent'] == 'inherit') {
								$dom[$key]['text-indent'] = $dom[$parentkey]['text-indent'];
							}
						}
						// text-transform
						if (isset($dom[$key]['style']['text-transform'])) {
							$dom[$key]['text-transform'] = $dom[$key]['style']['text-transform'];
						}
						// font size
						if (isset($dom[$key]['style']['font-size'])) {
							$fsize = trim($dom[$key]['style']['font-size']);
							$dom[$key]['fontsize'] = $this->getHTMLFontUnits($fsize, $dom[0]['fontsize'], $dom[$parentkey]['fontsize'], 'pt');
						}
						// font-stretch
						if (isset($dom[$key]['style']['font-stretch'])) {
							$dom[$key]['font-stretch'] = $this->getCSSFontStretching($dom[$key]['style']['font-stretch'], $dom[$parentkey]['font-stretch']);
						}
						// letter-spacing
						if (isset($dom[$key]['style']['letter-spacing'])) {
							$dom[$key]['letter-spacing'] = $this->getCSSFontSpacing($dom[$key]['style']['letter-spacing'], $dom[$parentkey]['letter-spacing']);
						}
						// line-height (internally is the cell height ratio)
						if (isset($dom[$key]['style']['line-height'])) {
							$lineheight = trim($dom[$key]['style']['line-height']);
							switch ($lineheight) {
								// A normal line height. This is default
								case 'normal': {
									$dom[$key]['line-height'] = $dom[0]['line-height'];
									break;
								}
								case 'inherit': {
									$dom[$key]['line-height'] = $dom[$parentkey]['line-height'];
								}
								default: {
									if (is_numeric($lineheight)) {
										// convert to percentage of font height
										$lineheight = ($lineheight * 100).'%';
									}
									$dom[$key]['line-height'] = $this->getHTMLUnitToUnits($lineheight, 1, '%', true);
									if (substr($lineheight, -1) !== '%') {
										if ($dom[$key]['fontsize'] <= 0) {
											$dom[$key]['line-height'] = 1;
										} else {
											$dom[$key]['line-height'] = (($dom[$key]['line-height'] - $this->cell_padding['T'] - $this->cell_padding['B']) / $dom[$key]['fontsize']);
										}
									}
								}
							}
						}
						// font style
						if (isset($dom[$key]['style']['font-weight'])) {
							if (strtolower($dom[$key]['style']['font-weight'][0]) == 'n') {
								if (strpos($dom[$key]['fontstyle'], 'B') !== false) {
									$dom[$key]['fontstyle'] = str_replace('B', '', $dom[$key]['fontstyle']);
								}
							} elseif (strtolower($dom[$key]['style']['font-weight'][0]) == 'b') {
								$dom[$key]['fontstyle'] .= 'B';
							}
						}
						if (isset($dom[$key]['style']['font-style']) AND (strtolower($dom[$key]['style']['font-style'][0]) == 'i')) {
							$dom[$key]['fontstyle'] .= 'I';
						}
						// font color
						if (isset($dom[$key]['style']['color']) AND (!TCPDF_STATIC::empty_string($dom[$key]['style']['color']))) {
							$dom[$key]['fgcolor'] = TCPDF_COLORS::convertHTMLColorToDec($dom[$key]['style']['color'], $this->spot_colors);
						} elseif ($dom[$key]['value'] == 'a') {
							$dom[$key]['fgcolor'] = $this->htmlLinkColorArray;
						}
						// background color
						if (isset($dom[$key]['style']['background-color']) AND (!TCPDF_STATIC::empty_string($dom[$key]['style']['background-color']))) {
							$dom[$key]['bgcolor'] = TCPDF_COLORS::convertHTMLColorToDec($dom[$key]['style']['background-color'], $this->spot_colors);
						}
						// text-decoration
						if (isset($dom[$key]['style']['text-decoration'])) {
							$decors = explode(' ', strtolower($dom[$key]['style']['text-decoration']));
							foreach ($decors as $dec) {
								$dec = trim($dec);
								if (!TCPDF_STATIC::empty_string($dec)) {
									if ($dec[0] == 'u') {
										// underline
										$dom[$key]['fontstyle'] .= 'U';
									} elseif ($dec[0] == 'l') {
										// line-through
										$dom[$key]['fontstyle'] .= 'D';
									} elseif ($dec[0] == 'o') {
										// overline
										$dom[$key]['fontstyle'] .= 'O';
									}
								}
							}
						} elseif ($dom[$key]['value'] == 'a') {
							$dom[$key]['fontstyle'] = $this->htmlLinkFontStyle;
						}
						// check for width attribute
						if (isset($dom[$key]['style']['width'])) {
							$dom[$key]['width'] = $dom[$key]['style']['width'];
						}
						// check for height attribute
						if (isset($dom[$key]['style']['height'])) {
							$dom[$key]['height'] = $dom[$key]['style']['height'];
						}
						// check for text alignment
						if (isset($dom[$key]['style']['text-align'])) {
							$dom[$key]['align'] = strtoupper($dom[$key]['style']['text-align'][0]);
						}
						// check for CSS border properties
						if (isset($dom[$key]['style']['border'])) {
							$borderstyle = $this->getCSSBorderStyle($dom[$key]['style']['border']);
							if (!empty($borderstyle)) {
								$dom[$key]['border']['LTRB'] = $borderstyle;
							}
						}
						if (isset($dom[$key]['style']['border-color'])) {
							$brd_colors = preg_split('/[\s]+/', trim($dom[$key]['style']['border-color']));
							if (isset($brd_colors[3])) {
								$dom[$key]['border']['L']['color'] = TCPDF_COLORS::convertHTMLColorToDec($brd_colors[3], $this->spot_colors);
							}
							if (isset($brd_colors[1])) {
								$dom[$key]['border']['R']['color'] = TCPDF_COLORS::convertHTMLColorToDec($brd_colors[1], $this->spot_colors);
							}
							if (isset($brd_colors[0])) {
								$dom[$key]['border']['T']['color'] = TCPDF_COLORS::convertHTMLColorToDec($brd_colors[0], $this->spot_colors);
							}
							if (isset($brd_colors[2])) {
								$dom[$key]['border']['B']['color'] = TCPDF_COLORS::convertHTMLColorToDec($brd_colors[2], $this->spot_colors);
							}
						}
						if (isset($dom[$key]['style']['border-width'])) {
							$brd_widths = preg_split('/[\s]+/', trim($dom[$key]['style']['border-width']));
							if (isset($brd_widths[3])) {
								$dom[$key]['border']['L']['width'] = $this->getCSSBorderWidth($brd_widths[3]);
							}
							if (isset($brd_widths[1])) {
								$dom[$key]['border']['R']['width'] = $this->getCSSBorderWidth($brd_widths[1]);
							}
							if (isset($brd_widths[0])) {
								$dom[$key]['border']['T']['width'] = $this->getCSSBorderWidth($brd_widths[0]);
							}
							if (isset($brd_widths[2])) {
								$dom[$key]['border']['B']['width'] = $this->getCSSBorderWidth($brd_widths[2]);
							}
						}
						if (isset($dom[$key]['style']['border-style'])) {
							$brd_styles = preg_split('/[\s]+/', trim($dom[$key]['style']['border-style']));
							if (isset($brd_styles[3]) AND ($brd_styles[3]!='none')) {
								$dom[$key]['border']['L']['cap'] = 'square';
								$dom[$key]['border']['L']['join'] = 'bevel';
								$dom[$key]['border']['L']['dash'] = $this->getCSSBorderDashStyle($brd_styles[3]);
								if ($dom[$key]['border']['L']['dash'] < 0) {
									$dom[$key]['border']['L'] = array();
								}
							}
							if (isset($brd_styles[1])) {
								$dom[$key]['border']['R']['cap'] = 'square';
								$dom[$key]['border']['R']['join'] = 'bevel';
								$dom[$key]['border']['R']['dash'] = $this->getCSSBorderDashStyle($brd_styles[1]);
								if ($dom[$key]['border']['R']['dash'] < 0) {
									$dom[$key]['border']['R'] = array();
								}
							}
							if (isset($brd_styles[0])) {
								$dom[$key]['border']['T']['cap'] = 'square';
								$dom[$key]['border']['T']['join'] = 'bevel';
								$dom[$key]['border']['T']['dash'] = $this->getCSSBorderDashStyle($brd_styles[0]);
								if ($dom[$key]['border']['T']['dash'] < 0) {
									$dom[$key]['border']['T'] = array();
								}
							}
							if (isset($brd_styles[2])) {
								$dom[$key]['border']['B']['cap'] = 'square';
								$dom[$key]['border']['B']['join'] = 'bevel';
								$dom[$key]['border']['B']['dash'] = $this->getCSSBorderDashStyle($brd_styles[2]);
								if ($dom[$key]['border']['B']['dash'] < 0) {
									$dom[$key]['border']['B'] = array();
								}
							}
						}
						$cellside = array('L' => 'left', 'R' => 'right', 'T' => 'top', 'B' => 'bottom');
						foreach ($cellside as $bsk => $bsv) {
							if (isset($dom[$key]['style']['border-'.$bsv])) {
								$borderstyle = $this->getCSSBorderStyle($dom[$key]['style']['border-'.$bsv]);
								if (!empty($borderstyle)) {
									$dom[$key]['border'][$bsk] = $borderstyle;
								}
							}
							if (isset($dom[$key]['style']['border-'.$bsv.'-color'])) {
								$dom[$key]['border'][$bsk]['color'] = TCPDF_COLORS::convertHTMLColorToDec($dom[$key]['style']['border-'.$bsv.'-color'], $this->spot_colors);
							}
							if (isset($dom[$key]['style']['border-'.$bsv.'-width'])) {
								$dom[$key]['border'][$bsk]['width'] = $this->getCSSBorderWidth($dom[$key]['style']['border-'.$bsv.'-width']);
							}
							if (isset($dom[$key]['style']['border-'.$bsv.'-style'])) {
								$dom[$key]['border'][$bsk]['dash'] = $this->getCSSBorderDashStyle($dom[$key]['style']['border-'.$bsv.'-style']);
								if ($dom[$key]['border'][$bsk]['dash'] < 0) {
									$dom[$key]['border'][$bsk] = array();
								}
							}
						}
						// check for CSS padding properties
						if (isset($dom[$key]['style']['padding'])) {
							$dom[$key]['padding'] = $this->getCSSPadding($dom[$key]['style']['padding']);
						} else {
							$dom[$key]['padding'] = $this->cell_padding;
						}
						foreach ($cellside as $psk => $psv) {
							if (isset($dom[$key]['style']['padding-'.$psv])) {
								$dom[$key]['padding'][$psk] = $this->getHTMLUnitToUnits($dom[$key]['style']['padding-'.$psv], 0, 'px', false);
							}
						}
						// check for CSS margin properties
						if (isset($dom[$key]['style']['margin'])) {
							$dom[$key]['margin'] = $this->getCSSMargin($dom[$key]['style']['margin']);
						} else {
							$dom[$key]['margin'] = $this->cell_margin;
						}
						foreach ($cellside as $psk => $psv) {
							if (isset($dom[$key]['style']['margin-'.$psv])) {
								$dom[$key]['margin'][$psk] = $this->getHTMLUnitToUnits(str_replace('auto', '0', $dom[$key]['style']['margin-'.$psv]), 0, 'px', false);
							}
						}
						// check for CSS border-spacing properties
						if (isset($dom[$key]['style']['border-spacing'])) {
							$dom[$key]['border-spacing'] = $this->getCSSBorderMargin($dom[$key]['style']['border-spacing']);
						}
						// page-break-inside
						if (isset($dom[$key]['style']['page-break-inside']) AND ($dom[$key]['style']['page-break-inside'] == 'avoid')) {
							$dom[$key]['attribute']['nobr'] = 'true';
						}
						// page-break-before
						if (isset($dom[$key]['style']['page-break-before'])) {
							if ($dom[$key]['style']['page-break-before'] == 'always') {
								$dom[$key]['attribute']['pagebreak'] = 'true';
							} elseif ($dom[$key]['style']['page-break-before'] == 'left') {
								$dom[$key]['attribute']['pagebreak'] = 'left';
							} elseif ($dom[$key]['style']['page-break-before'] == 'right') {
								$dom[$key]['attribute']['pagebreak'] = 'right';
							}
						}
						// page-break-after
						if (isset($dom[$key]['style']['page-break-after'])) {
							if ($dom[$key]['style']['page-break-after'] == 'always') {
								$dom[$key]['attribute']['pagebreakafter'] = 'true';
							} elseif ($dom[$key]['style']['page-break-after'] == 'left') {
								$dom[$key]['attribute']['pagebreakafter'] = 'left';
							} elseif ($dom[$key]['style']['page-break-after'] == 'right') {
								$dom[$key]['attribute']['pagebreakafter'] = 'right';
							}
						}
					}
					if (isset($dom[$key]['attribute']['display'])) {
						$dom[$key]['hide'] = (trim(strtolower($dom[$key]['attribute']['display'])) == 'none');
					}
					if (isset($dom[$key]['attribute']['border']) AND ($dom[$key]['attribute']['border'] != 0)) {
						$borderstyle = $this->getCSSBorderStyle($dom[$key]['attribute']['border'].' solid black');
						if (!empty($borderstyle)) {
							$dom[$key]['border']['LTRB'] = $borderstyle;
						}
					}
					// check for font tag
					if ($dom[$key]['value'] == 'font') {
						// font family
						if (isset($dom[$key]['attribute']['face'])) {
							$dom[$key]['fontname'] = $this->getFontFamilyName($dom[$key]['attribute']['face']);
						}
						// font size
						if (isset($dom[$key]['attribute']['size'])) {
							if ($key > 0) {
								if ($dom[$key]['attribute']['size'][0] == '+') {
									$dom[$key]['fontsize'] = $dom[($dom[$key]['parent'])]['fontsize'] + intval(substr($dom[$key]['attribute']['size'], 1));
								} elseif ($dom[$key]['attribute']['size'][0] == '-') {
									$dom[$key]['fontsize'] = $dom[($dom[$key]['parent'])]['fontsize'] - intval(substr($dom[$key]['attribute']['size'], 1));
								} else {
									$dom[$key]['fontsize'] = intval($dom[$key]['attribute']['size']);
								}
							} else {
								$dom[$key]['fontsize'] = intval($dom[$key]['attribute']['size']);
							}
						}
					}
					// force natural alignment for lists
					if ((($dom[$key]['value'] == 'ul') OR ($dom[$key]['value'] == 'ol') OR ($dom[$key]['value'] == 'dl'))
						AND (!isset($dom[$key]['align']) OR TCPDF_STATIC::empty_string($dom[$key]['align']) OR ($dom[$key]['align'] != 'J'))) {
						if ($this->rtl) {
							$dom[$key]['align'] = 'R';
						} else {
							$dom[$key]['align'] = 'L';
						}
					}
					if (($dom[$key]['value'] == 'small') OR ($dom[$key]['value'] == 'sup') OR ($dom[$key]['value'] == 'sub')) {
						if (!isset($dom[$key]['attribute']['size']) AND !isset($dom[$key]['style']['font-size'])) {
							$dom[$key]['fontsize'] = $dom[$key]['fontsize'] * K_SMALL_RATIO;
						}
					}
					if (($dom[$key]['value'] == 'strong') OR ($dom[$key]['value'] == 'b')) {
						$dom[$key]['fontstyle'] .= 'B';
					}
					if (($dom[$key]['value'] == 'em') OR ($dom[$key]['value'] == 'i')) {
						$dom[$key]['fontstyle'] .= 'I';
					}
					if ($dom[$key]['value'] == 'u') {
						$dom[$key]['fontstyle'] .= 'U';
					}
					if (($dom[$key]['value'] == 'del') OR ($dom[$key]['value'] == 's') OR ($dom[$key]['value'] == 'strike')) {
						$dom[$key]['fontstyle'] .= 'D';
					}
					if (!isset($dom[$key]['style']['text-decoration']) AND ($dom[$key]['value'] == 'a')) {
						$dom[$key]['fontstyle'] = $this->htmlLinkFontStyle;
					}
					if (($dom[$key]['value'] == 'pre') OR ($dom[$key]['value'] == 'tt')) {
						$dom[$key]['fontname'] = $this->default_monospaced_font;
					}
					if (!empty($dom[$key]['value']) AND ($dom[$key]['value'][0] == 'h') AND (intval($dom[$key]['value'][1]) > 0) AND (intval($dom[$key]['value'][1]) < 7)) {
						// headings h1, h2, h3, h4, h5, h6
						if (!isset($dom[$key]['attribute']['size']) AND !isset($dom[$key]['style']['font-size'])) {
							$headsize = (4 - intval($dom[$key]['value'][1])) * 2;
							$dom[$key]['fontsize'] = $dom[0]['fontsize'] + $headsize;
						}
						if (!isset($dom[$key]['style']['font-weight'])) {
							$dom[$key]['fontstyle'] .= 'B';
						}
					}
					if (($dom[$key]['value'] == 'table')) {
						$dom[$key]['rows'] = 0; // number of rows
						$dom[$key]['trids'] = array(); // IDs of TR elements
						$dom[$key]['thead'] = ''; // table header rows
					}
					if (($dom[$key]['value'] == 'tr')) {
						$dom[$key]['cols'] = 0;
						if ($thead) {
							$dom[$key]['thead'] = true;
							// rows on thead block are printed as a separate table
						} else {
							$dom[$key]['thead'] = false;
							$parent = $dom[$key]['parent'];

							if (!isset($dom[$parent]['rows'])) {
								$dom[$parent]['rows'] = 0;
							}
							// store the number of rows on table element
							++$dom[$parent]['rows'];

							if (!isset($dom[$parent]['trids'])) {
								$dom[$parent]['trids'] = array();
							}

							// store the TR elements IDs on table element
							array_push($dom[$parent]['trids'], $key);
						}
					}
					if (($dom[$key]['value'] == 'th') OR ($dom[$key]['value'] == 'td')) {
						if (isset($dom[$key]['attribute']['colspan'])) {
							$colspan = intval($dom[$key]['attribute']['colspan']);
						} else {
							$colspan = 1;
						}
						$dom[$key]['attribute']['colspan'] = $colspan;
						$dom[($dom[$key]['parent'])]['cols'] += $colspan;
					}
					// text direction
					if (isset($dom[$key]['attribute']['dir'])) {
						$dom[$key]['dir'] = $dom[$key]['attribute']['dir'];
					}
					// set foreground color attribute
					if (isset($dom[$key]['attribute']['color']) AND (!TCPDF_STATIC::empty_string($dom[$key]['attribute']['color']))) {
						$dom[$key]['fgcolor'] = TCPDF_COLORS::convertHTMLColorToDec($dom[$key]['attribute']['color'], $this->spot_colors);
					} elseif (!isset($dom[$key]['style']['color']) AND ($dom[$key]['value'] == 'a')) {
						$dom[$key]['fgcolor'] = $this->htmlLinkColorArray;
					}
					// set background color attribute
					if (isset($dom[$key]['attribute']['bgcolor']) AND (!TCPDF_STATIC::empty_string($dom[$key]['attribute']['bgcolor']))) {
						$dom[$key]['bgcolor'] = TCPDF_COLORS::convertHTMLColorToDec($dom[$key]['attribute']['bgcolor'], $this->spot_colors);
					}
					// set stroke color attribute
					if (isset($dom[$key]['attribute']['strokecolor']) AND (!TCPDF_STATIC::empty_string($dom[$key]['attribute']['strokecolor']))) {
						$dom[$key]['strokecolor'] = TCPDF_COLORS::convertHTMLColorToDec($dom[$key]['attribute']['strokecolor'], $this->spot_colors);
					}
					// check for width attribute
					if (isset($dom[$key]['attribute']['width'])) {
						$dom[$key]['width'] = $dom[$key]['attribute']['width'];
					}
					// check for height attribute
					if (isset($dom[$key]['attribute']['height'])) {
						$dom[$key]['height'] = $dom[$key]['attribute']['height'];
					}
					// check for text alignment
					if (isset($dom[$key]['attribute']['align']) AND (!TCPDF_STATIC::empty_string($dom[$key]['attribute']['align'])) AND ($dom[$key]['value'] !== 'img')) {
						$dom[$key]['align'] = strtoupper($dom[$key]['attribute']['align'][0]);
					}
					// check for text rendering mode (the following attributes do not exist in HTML)
					if (isset($dom[$key]['attribute']['stroke'])) {
						// font stroke width
						$dom[$key]['stroke'] = $this->getHTMLUnitToUnits($dom[$key]['attribute']['stroke'], $dom[$key]['fontsize'], 'pt', true);
					}
					if (isset($dom[$key]['attribute']['fill'])) {
						// font fill
						if ($dom[$key]['attribute']['fill'] == 'true') {
							$dom[$key]['fill'] = true;
						} else {
							$dom[$key]['fill'] = false;
						}
					}
					if (isset($dom[$key]['attribute']['clip'])) {
						// clipping mode
						if ($dom[$key]['attribute']['clip'] == 'true') {
							$dom[$key]['clip'] = true;
						} else {
							$dom[$key]['clip'] = false;
						}
					}
				} // end opening tag
			} else {
				// text
				$dom[$key]['tag'] = false;
				$dom[$key]['block'] = false;
				$dom[$key]['parent'] = end($level);
				$dom[$key]['dir'] = $dom[$dom[$key]['parent']]['dir'];
				if (!empty($dom[$dom[$key]['parent']]['text-transform'])) {
					// text-transform for unicode requires mb_convert_case (Multibyte String Functions)
					if (function_exists('mb_convert_case')) {
						$ttm = array('capitalize' => MB_CASE_TITLE, 'uppercase' => MB_CASE_UPPER, 'lowercase' => MB_CASE_LOWER);
						if (isset($ttm[$dom[$dom[$key]['parent']]['text-transform']])) {
							$element = mb_convert_case($element, $ttm[$dom[$dom[$key]['parent']]['text-transform']], $this->encoding);
						}
					} elseif (!$this->isunicode) {
						switch ($dom[$dom[$key]['parent']]['text-transform']) {
							case 'capitalize': {
								$element = ucwords(strtolower($element));
								break;
							}
							case 'uppercase': {
								$element = strtoupper($element);
								break;
							}
							case 'lowercase': {
								$element = strtolower($element);
								break;
							}
						}
					}
					$element = preg_replace("/&NBSP;/i", "&nbsp;", $element);
				}
				$dom[$key]['value'] = stripslashes($this->unhtmlentities($element));
			}
			++$elkey;
			++$key;
		}
    // tambahan
    foreach($dom as $key => $d){
      if( isset($dom[$key]['attribute']['changemark']) AND isset($dom[$key]['attribute']['reasonforupdaterefids']) ){
        if(($dom[$key]['attribute']['changemark'] == "1")){
          $dom[$key]['cgmarkid'] = rand(1,999999999999999999);
        }
      }
    }
    // end tambahan
		return $dom;
	}
  
  public function addInternalReference($ident, $id, $page, $y = 0){
    $link = $this->AddLink();
    $this->setLink($link,$y,$page);
    
    array_push($this->references, [
      'ident' => $ident,
      'id' => $id,
      'link' => $link,
      // 'p' => $page,
      // 'y' => $y,
      // 'f' => $fixed,
    ]);
  }

  public function __construct(string $absolute_path_csdbInput)
  {
    $this->absolute_path_csdbInput = $absolute_path_csdbInput;
  }  
  /**
   * @param string $absolute_path for publication module, if empty string, it call the $xml_string
   * @param string $xml_string of publication module
   */
  public function importDocument(string $absolute_path = '', string $xml_string = '')
  {
    $this->DOMDocument = CSDB::importDocument($absolute_path, $xml_string, 'pm');
    # validate DOMDocument here

    $pmType_value = $this->DOMDocument->firstElementChild->getAttribute('pmType');
    $pmType_config = require "config/attributes.php";
    $pmType_config = $pmType_config['pmType'][$pmType_value];
    $this->pmType_config = $pmType_config;
    
    $orientation = $this->pmType_config['page']['orientation'];
    $unit = $this->pmType_config['page']['unit'];
    $format = $this->pmType_config['page']['format'];

    parent::__construct($orientation, $unit, $format);
  }
  /**
   * @param string $aa_name
   * @param string $approved_date
   */
  public function setAA_Approved(string $aa_name, string $approved_date){
    $this->aa_approved['name'] = $aa_name;
    $this->aa_approved['date'] = $approved_date;
  }
  public function render()
  {
    if (!$this->validateSchema) {
      return false;
    }

    $this->setAllowLocalFiles(true);

    $headerMargin = $this->pmType_config['page']['headerMargin'];
    $topMargin = $this->pmType_config['page']['margins']['T'];
    $bottomMargin = $this->pmType_config['page']['margins']['B'];
    $leftMargin = $this->pmType_config['page']['margins']['L'];
    $rightMargin = $this->pmType_config['page']['margins']['R'];
    $bookletMargin = [$this->pmType_config['page']['margins']['R'], $this->pmType_config['page']['margins']['L']];
    $fontsize = $this->pmType_config['fontsize']['levelledPara']['para'];

    
    $DOMXpath = new \DOMXPath($this->DOMDocument);
    $pmEntries = $DOMXpath->evaluate("//content/pmEntry");
    foreach ($pmEntries as $index => $pmEntry) {
      $this->setHeaderMargin($headerMargin);
      $this->setMargins($leftMargin,$topMargin,$rightMargin);
      $this->setBooklet(true,$bookletMargin[0],$bookletMargin[1]);
      $this->setFontSize($fontsize);
      $this->SetAutoPageBreak(TRUE, $bottomMargin);
      $this->setImageScale(PDF_IMAGE_SCALE_RATIO);
      
      $this->pmEntry($pmEntry);
    }

    
    // tes caption
    
    // <p>Lorem ipsum dolor, sit amet consectetur adipisicing elit. Iste quasi necessitatibus sit amet lorem consectetur adipisicing elit
    // $html = <<<EOD
    // <p>Lorem ipsum dolor, sit gamet consectetur adipisicing elit. Iste quasi necessitatibus
    // <span style="font-weight:bold" captionline="true" calign="T" height="10mm" width="25mm" fillcolor="255,0,0" textcolor="0,0,0">ENG FIRE</span>
    // fugiat eum consectetur quod pariatur rerum, quas aut amet, laborum repellendus provident suscipit impedit ducimus. Ratione vitae odio voluptate quo voluptatibus suscipit, possimus ab fugiat aperiam? Labore cupiditate, dolore ipsa recusandae commodi accusantium, ex quasi voluptas ipsum itaque eius.</p>
    // EOD;

    // $txt = 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Ipsam, sint quisquam quibusdam ut in quasi tempore quidem optio fugiat fugit fuga minima adipisci delectus harum voluptatibus nam quam odio provident dolor voluptatem suscipit! Reprehenderit quisquam quod ullam iste distinctio sapiente debitis officia, iusto esse harum provident dolorum corrupti repellat! Ipsum.';
    
    // $this->AddPage();
    // $this->y = 50;
    // $this->writeHTML($html,false,false,false,false,'J');
    // while(strlen($txt) > 0){
    //   $strrest = $this->Write($this->lasth, $txt,'',false,'',false,0,true,false);
    //   $txt = $strrest;
    // }
    // $this->y += 10;
    // $strrest = $this->Write($this->lasth, 'foobar','',false,'',false,0,true,false);


    // $this->Cell(25,30,'ENG FIRE',1,0,'C',false,'',0,false,'T');
    // $this->Write('',' foo bar');
    // $this->writeHTML(' bazz');
    // dd($this->y);

  }
  private function pmEntry(\DOMElement $pmEntry)
  {
    $pmEntryType_config = require "config/attributes.php";
    $pmEntryType_config = $pmEntryType_config['pmEntryType'];
    $pmEntryType = $pmEntry->getAttribute('pmEntryType');
    
    // add Bookmark level 0 for TOC
    $index = $this->checkLevel($pmEntry);
    $pmEntryType_interpretation = isset($pmEntryType_config[$pmEntryType]) ? $pmEntryType_config[$pmEntryType]['interpretation'] : null;
    $txt = ($pmEntryTitle = $pmEntry->firstElementChild)->tagName == 'pmEntryTitle' ? $pmEntryTitle->nodeValue : ($pmEntryType_interpretation ?? 'Entry ' . $index);
    
    $children = $this->childrenElement($pmEntry);
    
    $this->startPageGroup();
    $this->AddPage();
    $this->Bookmark($txt, $index);
    
    foreach ($children as $child) {
      switch ($child->nodeName) {
        case 'dmRef':
          $this->dmRef($child);
          break;
      }
    }   

    
    
    // add TOC
    // $this->addTOCPage();
    // $this->SetFont($this->getFontFamily(), 'B', 14);
    // $this->MultiCell(0, 0, 'Table Of Content', 0, 'C', 0, 1, '', '', true, 0);
    // $this->Ln();    
    // $this->SetFont($this->getFontFamily(), '', 10);
    // $this->addTOC(!empty($this->endPageGroup) ? ($this->endPageGroup+1) : 1, $this->getFontFamily(), '.', $txt, 'B', array(128,0,0));
    // $this->endTOCPage();
    // $this->endPageGroup = $this->getPage();

    $this->updateLink();
    
  }

  public function getPageAnnots(){
    return $this->PageAnnots;
  }

  private function dmRef(\DOMElement $dmRef)
  {
    // if you want to utilize referredFragment, behavior, dmTitle, issueDate
    $referredFragment = $dmRef->getAttribute('referredFragment');
    $dmRefIdent = $dmRef->firstElementChild;
    $dmRefAddressItems = $dmRefIdent->nextElementSibling;
    $behavior = $dmRefAddressItems ? $dmRefAddressItems->nextElementSibling : null;

    $identExtension_el = ($idnt = $dmRefIdent->firstElementChild)->tagName == 'identExtension' ? $idnt : null;
    $dmCode_el = $identExtension_el ? $identExtension_el->nextElementSibling : $dmRefIdent->firstElementChild;
    $issueInfo_el = $dmCode_el->nextElementSibling;
    $languange_el = $issueInfo_el ? $issueInfo_el->nextElementSibling : null;

    $dmc = new DMC();
    $dmc->absolute_path_csdbInput = $this->absolute_path_csdbInput;
    $dmc->pdf = $this;
    $dmc->importDocument_byIdent($identExtension_el, $dmCode_el, $issueInfo_el, $languange_el);
    $dmc->render();
  }

  public static function addIntentionallyLeftBlankPage(TCPDF $pdf, $tes = '')
  {
    if (($pdf->getNumPages() % 2) == 0) {
      return false;
    } else {
      if(!empty($pdf->tes) AND $pdf->tes){
        // dump($pdf->y, $pdf->page, $pdf->getLastH());
      }
      $topMargin = $pdf->pmType_config['page']['margins']['T'];
      $bottomMargin = $pdf->pmType_config['page']['margins']['B'];    
      $page_height = $pdf->getPageHeight() - ($topMargin + $bottomMargin);
      $pdf->setFontSize(7);
      $pdf->Cell(0, $page_height, 'INTENTIONALLY LEFT BLANK', 0, 1, 'C');
      // $pdf->Cell(0, $page_height, $tes ? $tes : 'INTENTIONALLY LEFT BLANK', 0, 1, 'C');
      $pdf->lastpageIntentionallyLeftBlank = $pdf->getPage();
      return $pdf->getPage();
    }
  }

  private function childrenElement(\DOMElement $element)
  {
    $arr = [];
    foreach ($element->childNodes as $childNodes) {
      $childNodes instanceof \DOMElement ? array_push($arr, $childNodes) : null;
    }
    return $arr;
  }  

  public function updateLink(){
    $page_last = $this->getPage();
    for ($page=0; $page <= $page_last; $page++) { 
      if(!isset($this->PageAnnots[$page])){
        continue;
      }
      foreach($this->PageAnnots[$page] as $i => $annots){
        foreach($this->references as $reference){
          if(
            ($annots['txt'] == $reference['ident'].",".$reference['id']) 
            OR ("DMC-".$annots['txt'] == $reference['ident'].",".$reference['id']) 
            OR ("PMC-".$annots['txt'] == $reference['ident'].",".$reference['id']))
          {
            $this->PageAnnots[$page][$i]['txt'] = $reference['link'];
          }
        }
      }
    }
  } 

  /**
   * belum mengakomodir changemark di levelled para. 
   * saat ini applicable to children levelled para (title, para)
   */
  public function applyCgMark(\DOMDocument $DOMDocument)
  {
    // dump($this->cgmark);
    $topMargin = $this->get_pmType_config()['page']['margins']['T'];
    $bottomMargin = $this->get_pmType_config()['page']['margins']['B'];    

    // 1. adding the $cgmark[$node_number]['set'][$pagenumber] to be ready to render
    foreach($this->cgmark as $node_number => $cgmark){
      // dump($this->cgmark, $cgmark, __CLASS__.__LINE__);
      $first_page = min($cgmark['pagenum']);
      $latest_page = max($cgmark['pagenum']);
      $range = range($first_page, $latest_page);
      $cgmark['pagenum'] = $range;

      $cgmark['set'] = []; 
      foreach($cgmark['pagenum'] as $index => $pagenum){
        // dd($cgmark, $pagenum, count($cgmark['pagenum']));
        // if($pagenum  == (count($cgmark['pagenum']))-1){
        if($index == (count($cgmark['pagenum']))-1){ // jika halaman terakhir
        // $cgmark['set'][$pagenum] = $cgmark['set']['pagenum'] ?? array();
        // if($pagenum  == (count($cgmark['pagenum']) >= 1 ? count($cgmark['pagenum']) : 1)){
          $cgmark['set'][$pagenum] = [
            // 'st_pos_y' => $cgmark['st_pos_y'],
            // 'st_pos_y' => $this->getMargins()['top'],
            'st_pos_y' => (count($cgmark['pagenum']) > 1) ? $topMargin : $cgmark['st_pos_y'],
            'ed_pos_y' => $cgmark['ed_pos_y']
          ];
        }
        elseif($index == 0){ // jika halaman awal
          $cgmark['set'][$pagenum] = [
            'st_pos_y' => $cgmark['st_pos_y'],
            'ed_pos_y' => $this->getPageHeight() - $bottomMargin,
          ];
        } 
        else {
          $cgmark['set'][$pagenum] = [
            // 'st_pos_y' => $cgmark['st_pos_y'],
            // 'ed_pos_y' => $cgmark['ed_pos_y'],
            'st_pos_y' => $topMargin,
            'ed_pos_y' => $this->getPageHeight() - $bottomMargin,
          ];
        }
      }
      $this->cgmark[$node_number] = $cgmark;
    }

    // 2. filtering the cgmark whenever parent with reasonForUpdateRefIds is old (fewer index)
    $indexRFUIDs = [];
    foreach($this->cgmark as $node_number => $cgmark){
      $rfuids = $cgmark['reasonforupdaterefids'];
      $DOMXpath = new \DOMXPath($DOMDocument);
      $rfu = $DOMXpath->evaluate("//reasonForUpdate[@id = '{$rfuids}']");
      if($rfu->item(0)){
        $index = CSDB::checkIndex($rfu->item(0));
        $indexRFUIDs[$rfuids] = $index;
      }
    }
    for ($ky=1; $ky < count($this->cgmark) ; $ky++) { // $ky=1 karena dimulai dari index ke 1, bukan ke 0. Jika dari 0 makan tidak conflict (previous cgmark tidak ada)
        // $previous_cgmark = $this->cgmark[$ky - 1];
        foreach($this->cgmark[$ky]['pagenum'] as $pagenum){ // foreach pagenumber in every cgmark
          // unset the previous reasonForUpdateRefIds in the same page if it is older index and end length line is over
          // if both changemark in one same page AND 
          // if (current RFUID index > older RFUID index) AND
          // jika previous_cgmark berada di halaman yang sama AND (prev.cgmark.ed_pos_y >= current.cgmark.ed_pos_y // pervious lebih panjang)
          $currentIndex = $indexRFUIDs[$this->cgmark[$ky]['reasonforupdaterefids']]; // $cgmark['reasonforupdaterefids']
          // $previousIndex = $indexRFUIDs[$previous_cgmark['reasonforupdaterefids']];
          // if( in_array($pagenum,$previous_cgmark['pagenum']) AND ($currentIndex > $previousIndex) AND (isset($previous_cgmark['set'][$pagenum]) AND $previous_cgmark['set'][$pagenum]['ed_pos_y'] >= $this->cgmark[$ky]['set'][$pagenum]['ed_pos_y'] )){
              // unset($this->cgmark[$ky - 1]['set'][$pagenum]); // unset previous_cgmark
          // }
          // jika dihalaman yang sama AND
          // currenIndex <= previous AND
          // cgmark terkakhir / $this->cgmark[lastIndex] AND
          // jika previous_cgmark berada di halaman yang sama AND (prev.cgmark.ed_pos_y >= current.cgmark.ed_pos_y // pervious lebih panjang) 
          // (!isset($this->cgmark[$ky+1])) artinya jika tidak ada cgmark selanjutnya. Artinya saat ini adalah index terakhir
          // elseif( in_array($pagenum,$previous_cgmark['pagenum']) AND ($currentIndex <= $previousIndex) AND (!isset($this->cgmark[$ky+1])) AND (isset($previous_cgmark['set'][$pagenum]) AND $previous_cgmark['set'][$pagenum]['ed_pos_y'] >= $this->cgmark[$ky]['set'][$pagenum]['ed_pos_y'] )){
            // unset current cgmark for the same page
            // unset($this->cgmark[$ky]['set'][$pagenum]); // unset current cgmark
          // }
          // ini tidak seharusnya user menaruh @reasonForUpdateRefIds yang sama dalam first nested element
          // elseif( in_array($pagenum,$previous_cgmark['pagenum']) AND ($currentIndex == $previousIndex) AND (isset($previous_cgmark['set'][$pagenum]) AND $previous_cgmark['set'][$pagenum]['ed_pos_y'] >= $this->cgmark[$ky]['set'][$pagenum]['ed_pos_y'] )){
            // unset($this->cgmark[$ky-1]['set'][$pagenum]); // unset previous cgmark
          // }

          // meng-unset ['set'] setiap cgmark dihalaman yang sama
          // jika previous ed_pos_y >= current
          for($pr=$ky-1; $pr >= 0; $pr--){
            // dump($pr);
            $previous_cgmark = $this->cgmark[$pr];
            $previousIndex = $indexRFUIDs[$this->cgmark[$pr]['reasonforupdaterefids']];
            // dump($this->cgmark);
            // dump($pr,$this->cgmark[$pr]['set'][2]);
            // dump( $pr."|".$ky. ": ", "pagenum: {$pagenum}",$this->cgmark[$pr]['set']);
            if( in_array($pagenum,$previous_cgmark['pagenum'])){
              if(
                isset($this->cgmark[$pr]['set'][$pagenum])
                && ($this->cgmark[$pr]['set'][$pagenum]['ed_pos_y'] >= $this->cgmark[$ky]['set'][$pagenum]['ed_pos_y'] ) 
                )
              {
                if($currentIndex > $previousIndex){
                  unset($this->cgmark[$pr]['set'][$pagenum]);
                }
                elseif($currentIndex <= $previousIndex){
                  unset($this->cgmark[$ky]['set'][$pagenum]);
                }
              }
            }
          }
        }
    }
    
    // 3. install line vertical to page
    foreach($this->cgmark as $key => $cgmark){
      foreach($cgmark['set'] as $page => $pos){
        $this->setPage($page);

        $y1 = $pos['st_pos_y'];
        $y2 = $pos['ed_pos_y'];

        $x = $this->getMargins()['left'] - 5;
        $this->setLineStyle([
          'width' => 0.5,
          'cap' => 'butt',
          'join' => 'mitter',
          'dash' => 0,
          'color' => [0,0,0]
        ]);
        $this->Line($x, $y1, $x, $y2);
      }
    }
    $this->cgmark = [];
    $this->lastPage();  
  }

  /**
   * @param int|float $index is the sequential of cgmark
   */
  public function addCgMark(int|float $index, int|float $st_pos_y = null, int|float $ed_pos_y = null, int $pagenum, string $reasonForUpdateRefIds, mixed $value = '')
  {
    // if st_pos_y exist, it assign new st_pos_y in cgmark (replacing)
    if($st_pos_y && ($pagenum > 0) && $reasonForUpdateRefIds){ // the important things is st_pos_y
      $this->cgmark[$index]['st_pos_y'] = $this->cgmark[$index]['st_pos_y'] ?? $st_pos_y;
      $this->cgmark[$index]['pagenum'] = $this->cgmark[$index]['pagenum'] ?? array();
      $this->cgmark[$index]['reasonforupdaterefids'] = $reasonForUpdateRefIds;
      $this->cgmark[$index]['value'] = $value;  
      array_push($this->cgmark[$index]['pagenum'], $pagenum);
    }
    // if ed_pos_y exist, it assign new ed_pos_y (replacing)
    // the $ed_pos_y should come from $this->y.
    elseif ($ed_pos_y && ($pagenum > 0)){
      $this->cgmark[$index]['ed_pos_y'] = $ed_pos_y; //
      array_push($this->cgmark[$index]['pagenum'], $pagenum);
    }    
  }

  /**
   * minimum value of level is 0 (zero)
   */
  private function checkLevel(\DOMElement $element)
  {
    $tagName = $element->tagName;
    $parent_tagName = $tagName;

    $index = -1;

    $parentNode = $element->parentNode;
    while ($parentNode->tagName == $parent_tagName) {
      $index += 1;
      $parentNode = $parentNode->parentNode;
    }
    return ($index < 0) ? 0 : $index;
  }

  public function get_pmType_config()
  {
    return $this->pmType_config;
  }

  public function getAssetPath()
  {
    if($this->allowLocalFiles){
      return "file://{$this->absolute_path_csdbInput}";
    }
  }

  public function getPDF()
  {
    $this->Output('tes2.pdf', 'I');
  }

  public function changeXObjectHeight($template, $h){
    // $new_template = $start
    // if($this->xobject[$template]){

    // }
  }






























  /**
   * editannya: banyak (lupa nulis)
   * 1. Tambahan cgmark
   * 2. tambahan caption
   * 3. ...
	 * Allows to preserve some HTML formatting (limited support).<br />
	 * IMPORTANT: The HTML must be well formatted - try to clean-up it using an application like HTML-Tidy before submitting.
	 * Supported tags are: a, b, blockquote, br, dd, del, div, dl, dt, em, font, h1, h2, h3, h4, h5, h6, hr, i, img, li, ol, p, pre, small, span, strong, sub, sup, table, tcpdf, td, th, thead, tr, tt, u, ul
	 * NOTE: all the HTML attributes must be enclosed in double-quote.
	 * @param string $html text to display
	 * @param boolean $ln if true add a new line after text (default = true)
	 * @param boolean $fill Indicates if the background must be painted (true) or transparent (false).
	 * @param boolean $reseth if true reset the last cell height (default false).
	 * @param boolean $cell if true add the current left (or right for RTL) padding to each Write (default false).
	 * @param string $align Allows to center or align the text. Possible values are:<ul><li>L : left align</li><li>C : center</li><li>R : right align</li><li>'' : empty string : left for LTR or right for RTL</li></ul>
	 * @public
	 */
  public function writeHTMl($html, $ln=true, $fill=false, $reseth=false, $cell=false, $align='', $revmark = false, $tes = false, $who = '') {
    $gvars = $this->getGraphicVars();
		// store current values
		$prev_cell_margin = $this->cell_margin;
		$prev_cell_padding = $this->cell_padding;
		$prevPage = $this->page;
		$prevlMargin = $this->lMargin;
		$prevrMargin = $this->rMargin;
		$curfontname = $this->FontFamily;
		$curfontstyle = $this->FontStyle;
		$curfontsize = $this->FontSizePt;
		$curfontascent = $this->getFontAscent($curfontname, $curfontstyle, $curfontsize);
		$curfontdescent = $this->getFontDescent($curfontname, $curfontstyle, $curfontsize);
		$curfontstretcing = $this->font_stretching;
		$curfonttracking = $this->font_spacing;
		$this->newline = true;
		$newline = true;
		$startlinepage = $this->page;
		$minstartliney = $this->y;
		$maxbottomliney = 0;
		$startlinex = $this->x;
		$startliney = $this->y;
		$yshift = 0;
		$loop = 0;
		$curpos = 0;
		$this_method_vars = array();
		$undo = false;
		$fontaligned = false;
		$reverse_dir = false; // true when the text direction is reversed
		$this->premode = false;
    
		if ($this->inxobj) {
			// we are inside an XObject template
			$pask = count($this->xobjects[$this->xobjid]['annotations']);
		} elseif (isset($this->PageAnnots[$this->page])) {
			$pask = count($this->PageAnnots[$this->page]);
		} else {
			$pask = 0;
		}
		if ($this->inxobj) {
			// we are inside an XObject template
			$startlinepos = strlen($this->xobjects[$this->xobjid]['outdata']);
		} elseif (!$this->InFooter) {
			if (isset($this->footerlen[$this->page])) {
				$this->footerpos[$this->page] = $this->pagelen[$this->page] - $this->footerlen[$this->page];
			} else {
				$this->footerpos[$this->page] = $this->pagelen[$this->page];
			}
			$startlinepos = $this->footerpos[$this->page];
		} else {
			// we are inside the footer
			$startlinepos = $this->pagelen[$this->page];
		}
		$lalign = $align;
		$plalign = $align;
		if ($this->rtl) {
			$w = $this->x - $this->lMargin;
		} else {
			$w = $this->w - $this->rMargin - $this->x;
		}
		$w -= ($this->cell_padding['L'] + $this->cell_padding['R']);    
		if ($cell) {
			if ($this->rtl) {
				$this->x -= $this->cell_padding['R'];
				$this->lMargin += $this->cell_padding['L'];
			} else {
				$this->x += $this->cell_padding['L'];
				$this->rMargin += $this->cell_padding['R'];
			}
		}
		if ($this->customlistindent >= 0) {
			$this->listindent = $this->customlistindent;
		} else {
			$this->listindent = $this->GetStringWidth('000000');
		}
		$this->listindentlevel = 0;
		// save previous states
		$prev_cell_height_ratio = $this->cell_height_ratio;
		$prev_listnum = $this->listnum;
		$prev_listordered = $this->listordered;
		$prev_listcount = $this->listcount;
		$prev_lispacer = $this->lispacer;
		$this->listnum = 0;
		$this->listordered = array();
		$this->listcount = array();
		$this->lispacer = '';
		if ((TCPDF_STATIC::empty_string($this->lasth)) OR ($reseth)) {
			// reset row height
			$this->resetLastH();
		}
    // is_array($html) ? $dom = $html : 
    $dom = $this->getHtmlDomArray($html);

		$maxel = count($dom);
		$key = 0;
    
    $basic_cell_padding_L = $this->cell_padding['L'];
    $basic_w = $w;

    $tmp_footnotesdom = [];
    $domdoc = new DOMDocument();
    $domdoc->loadXML($html);
    $domxpath = new DOMXPath($domdoc);
    $res = $domxpath->evaluate("//span[@isfootnote = 'true']");

    foreach($res as $i => $fnt){
      $tmp_footnotesdom[$fnt->getAttribute('id')]['html'] = $fnt->c14N();
    }

    // dd($tmp_footnotesdom);
    // dd($tmp_footnotesdom, $id,  array_keys($tmp_footnotesdom, $id));
		while ($key < $maxel) {

      /** EDITTED - tambahan supaya kalau ada title dibawah dekat footer, maka page break */
      if(in_array($dom[$key]['value'], ['h1','h2','h3','h4','h5','h6'])){
        $h = $this->getCellHeight($dom[$key]['fontsize']) * 3; // dikali tiga biar kalau ga break, ada text paragraph yang membersamainya 
        $this->checkPageBreak($h, $this->y);
      }

      // bookmark if such attribute exist
      if(!empty($dom[$key]['attribute']['bookmarklvl']) AND !empty($dom[$key]['attribute']['bookmarktxt'])){
        $txt = preg_replace("/&#xA0;/",' ', $dom[$key]['attribute']['bookmarktxt']);
        $this->Bookmark($txt, $dom[$key]['attribute']['bookmarklvl']);
      }

      // set padding if dom[$key] has attribute paddingleft
      if(!$this->InFooter AND (isset($dom[$dom[$key]['parent']]['attribute']['paddingleft']))){
        $this->cell_padding['L'] = $basic_cell_padding_L + ($dom[$dom[$key]['parent']]['attribute']['paddingleft']);
        $w = $basic_w - ($dom[$dom[$key]['parent']]['attribute']['paddingleft']);
      }
      
			if ($dom[$key]['tag'] AND $dom[$key]['opening'] AND $dom[$key]['hide']) {
				// store the node key
				$hidden_node_key = $key;
				if ($dom[$key]['self']) {
					// skip just this self-closing tag
					++$key;
				} else {
					// skip this and all children tags
					while (($key < $maxel) AND (!$dom[$key]['tag'] OR $dom[$key]['opening'] OR ($dom[$key]['parent'] != $hidden_node_key))) {
						// skip hidden objects
						++$key;
					}
					++$key;
				}
			}      
			if ($key == $maxel) break;
			if ($dom[$key]['tag'] AND isset($dom[$key]['attribute']['pagebreak'])) {
        // check for pagebreak
				if (($dom[$key]['attribute']['pagebreak'] == 'true') OR ($dom[$key]['attribute']['pagebreak'] == 'left') OR ($dom[$key]['attribute']['pagebreak'] == 'right')) {
          // dump($this->pagegroups);
            $this->checkPageBreak($this->PageBreakTrigger + 1);
            $this->htmlvspace = ($this->PageBreakTrigger + 1);
				}
				if ((($dom[$key]['attribute']['pagebreak'] == 'left') AND (((!$this->rtl) AND (($this->page % 2) == 0)) OR (($this->rtl) AND (($this->page % 2) != 0))))
					OR (($dom[$key]['attribute']['pagebreak'] == 'right') AND (((!$this->rtl) AND (($this->page % 2) != 0)) OR (($this->rtl) AND (($this->page % 2) == 0))))) {
					// add a page (or trig AcceptPageBreak() for multicolumn mode)
					$this->checkPageBreak($this->PageBreakTrigger + 1);
					$this->htmlvspace = ($this->PageBreakTrigger + 1);
				}
			}
			if ($dom[$key]['tag'] AND $dom[$key]['opening'] AND isset($dom[$key]['attribute']['nobr']) AND ($dom[$key]['attribute']['nobr'] == 'true')) {
				if (isset($dom[($dom[$key]['parent'])]['attribute']['nobr']) AND ($dom[($dom[$key]['parent'])]['attribute']['nobr'] == 'true')) {
					$dom[$key]['attribute']['nobr'] = false;
				} else {  
					// store current object
					$this->startTransaction();
					// save this method vars
					$this_method_vars['html'] = $html;
					$this_method_vars['ln'] = $ln;
					$this_method_vars['fill'] = $fill;
					$this_method_vars['reseth'] = $reseth;
					$this_method_vars['cell'] = $cell;
					$this_method_vars['align'] = $align;
					$this_method_vars['gvars'] = $gvars;
					$this_method_vars['prevPage'] = $prevPage;
					$this_method_vars['prev_cell_margin'] = $prev_cell_margin;
					$this_method_vars['prev_cell_padding'] = $prev_cell_padding;
					$this_method_vars['prevlMargin'] = $prevlMargin;
					$this_method_vars['prevrMargin'] = $prevrMargin;
					$this_method_vars['curfontname'] = $curfontname;
					$this_method_vars['curfontstyle'] = $curfontstyle;
					$this_method_vars['curfontsize'] = $curfontsize;
					$this_method_vars['curfontascent'] = $curfontascent;
					$this_method_vars['curfontdescent'] = $curfontdescent;
					$this_method_vars['curfontstretcing'] = $curfontstretcing;
					$this_method_vars['curfonttracking'] = $curfonttracking;
					$this_method_vars['minstartliney'] = $minstartliney;
					$this_method_vars['maxbottomliney'] = $maxbottomliney;
					$this_method_vars['yshift'] = $yshift;
					$this_method_vars['startlinepage'] = $startlinepage;
					$this_method_vars['startlinepos'] = $startlinepos;
					$this_method_vars['startlinex'] = $startlinex;
					$this_method_vars['startliney'] = $startliney;
					$this_method_vars['newline'] = $newline;
					$this_method_vars['loop'] = $loop;
					$this_method_vars['curpos'] = $curpos;
					$this_method_vars['pask'] = $pask;
					$this_method_vars['lalign'] = $lalign;
					$this_method_vars['plalign'] = $plalign;
					$this_method_vars['w'] = $w;
					$this_method_vars['prev_cell_height_ratio'] = $prev_cell_height_ratio;
					$this_method_vars['prev_listnum'] = $prev_listnum;
					$this_method_vars['prev_listordered'] = $prev_listordered;
					$this_method_vars['prev_listcount'] = $prev_listcount;
					$this_method_vars['prev_lispacer'] = $prev_lispacer;
					$this_method_vars['fontaligned'] = $fontaligned;
					$this_method_vars['key'] = $key;
					$this_method_vars['dom'] = $dom;
				}
			}
			// print THEAD block
			if (($dom[$key]['value'] == 'tr') AND isset($dom[$key]['thead']) AND $dom[$key]['thead']) {
				if (isset($dom[$key]['parent']) AND isset($dom[$dom[$key]['parent']]['thead']) AND !TCPDF_STATIC::empty_string($dom[$dom[$key]['parent']]['thead'])) {
					$this->inthead = true;
					// print table header (thead)
					$this->writeHTML($this->thead, false, false, false, false, '');
					// check if we are on a new page or on a new column
					if (($this->y < $this->start_transaction_y) OR ($this->checkPageBreak($this->lasth, '', false))) {
						// we are on a new page or on a new column and the total object height is less than the available vertical space.
						// restore previous object
						$this->rollbackTransaction(true);
						// restore previous values
						foreach ($this_method_vars as $vkey => $vval) {
							$$vkey = $vval;
						}
						// disable table header
						$tmp_thead = $this->thead;
						$this->thead = '';
						// add a page (or trig AcceptPageBreak() for multicolumn mode)
						$pre_y = $this->y;
						if ((!$this->checkPageBreak($this->PageBreakTrigger + 1)) AND ($this->y < $pre_y)) {
							// fix for multicolumn mode
							$startliney = $this->y;
						}
						$this->start_transaction_page = $this->page;
						$this->start_transaction_y = $this->y;
						// restore table header
						$this->thead = $tmp_thead;
						// fix table border properties
						if (isset($dom[$dom[$key]['parent']]['attribute']['cellspacing'])) {
							$tmp_cellspacing = $this->getHTMLUnitToUnits($dom[$dom[$key]['parent']]['attribute']['cellspacing'], 1, 'px');
						} elseif (isset($dom[$dom[$key]['parent']]['border-spacing'])) {
							$tmp_cellspacing = $dom[$dom[$key]['parent']]['border-spacing']['V'];
						} else {
							$tmp_cellspacing = 0;
						}
						$dom[$dom[$key]['parent']]['borderposition']['page'] = $this->page;
						$dom[$dom[$key]['parent']]['borderposition']['column'] = $this->current_column;
						$dom[$dom[$key]['parent']]['borderposition']['y'] = $this->y + $tmp_cellspacing;
						$xoffset = ($this->x - $dom[$dom[$key]['parent']]['borderposition']['x']);
						$dom[$dom[$key]['parent']]['borderposition']['x'] += $xoffset;
						$dom[$dom[$key]['parent']]['borderposition']['xmax'] += $xoffset;
						// print table header (thead)
						$this->writeHTML($this->thead, false, false, false, false, '');
					}
				}
				// move $key index forward to skip THEAD block
				while ( ($key < $maxel) AND (!(
					($dom[$key]['tag'] AND $dom[$key]['opening'] AND ($dom[$key]['value'] == 'tr') AND (!isset($dom[$key]['thead']) OR !$dom[$key]['thead']))
					OR ($dom[$key]['tag'] AND (!$dom[$key]['opening']) AND ($dom[$key]['value'] == 'table'))) )) {
					++$key;
				}
			}
			if ($dom[$key]['tag'] OR ($key == 0)) {

				if ((($dom[$key]['value'] == 'table') OR ($dom[$key]['value'] == 'tr')) AND (isset($dom[$key]['align']))) {
					$dom[$key]['align'] = ($this->rtl) ? 'R' : 'L';
				}
				// vertically align image in line
				if ((!$this->newline) AND ($dom[$key]['value'] == 'img') AND (isset($dom[$key]['height'])) AND ($dom[$key]['height'] > 0)) {
					// get image height
					$imgh = $this->getHTMLUnitToUnits($dom[$key]['height'], ($dom[$key]['fontsize'] / $this->k), 'px');
					$autolinebreak = false;
					if (!empty($dom[$key]['width'])) {
						$imgw = $this->getHTMLUnitToUnits($dom[$key]['width'], ($dom[$key]['fontsize'] / $this->k), 'px', false);
						if (($imgw <= ($this->w - $this->lMargin - $this->rMargin - $this->cell_padding['L'] - $this->cell_padding['R']))
							AND ((($this->rtl) AND (($this->x - $imgw) < ($this->lMargin + $this->cell_padding['L'])))
							OR ((!$this->rtl) AND (($this->x + $imgw) > ($this->w - $this->rMargin - $this->cell_padding['R']))))) {
							// add automatic line break
							$autolinebreak = true;
							$this->Ln('', $cell);
							if ((!$dom[($key-1)]['tag']) AND ($dom[($key-1)]['value'] == ' ')) {
								// go back to evaluate this line break
								--$key;
							}
						}
					}
					if (!$autolinebreak) {
						if ($this->inPageBody()) {
							$pre_y = $this->y;
							// check for page break
							if ((!$this->checkPageBreak($imgh)) AND ($this->y < $pre_y)) {
								// fix for multicolumn mode
								$startliney = $this->y;
							}
						}
						if ($this->page > $startlinepage) {
							// fix line splitted over two pages
							if (isset($this->footerlen[$startlinepage])) {
								$curpos = $this->pagelen[$startlinepage] - $this->footerlen[$startlinepage];
							}
							// line to be moved one page forward
							$pagebuff = $this->getPageBuffer($startlinepage);
							$linebeg = substr($pagebuff, $startlinepos, ($curpos - $startlinepos));
							$tstart = substr($pagebuff, 0, $startlinepos);
							$tend = substr($this->getPageBuffer($startlinepage), $curpos);
							// remove line from previous page
							$this->setPageBuffer($startlinepage, $tstart.''.$tend);
							$pagebuff = $this->getPageBuffer($this->page);
							$tstart = substr($pagebuff, 0, $this->cntmrk[$this->page]);
							$tend = substr($pagebuff, $this->cntmrk[$this->page]);
							// add line start to current page
							$yshift = ($minstartliney - $this->y);
							if ($fontaligned) {
								$yshift += ($curfontsize / $this->k);
							}
							$try = sprintf('1 0 0 1 0 %F cm', ($yshift * $this->k));
							$this->setPageBuffer($this->page, $tstart."\nq\n".$try."\n".$linebeg."\nQ\n".$tend);
							// shift the annotations and links
							if (isset($this->PageAnnots[$this->page])) {
								$next_pask = count($this->PageAnnots[$this->page]);
							} else {
								$next_pask = 0;
							}
							if (isset($this->PageAnnots[$startlinepage])) {
								foreach ($this->PageAnnots[$startlinepage] as $pak => $pac) {
									if ($pak >= $pask) {
										$this->PageAnnots[$this->page][] = $pac;
										unset($this->PageAnnots[$startlinepage][$pak]);
										$npak = count($this->PageAnnots[$this->page]) - 1;
										$this->PageAnnots[$this->page][$npak]['y'] -= $yshift;
									}
								}
							}
							$pask = $next_pask;
							$startlinepos = $this->cntmrk[$this->page];
							$startlinepage = $this->page;
							$startliney = $this->y;
							$this->newline = false;
						}
						$this->y += ($this->getCellHeight($curfontsize / $this->k) - ($curfontdescent * $this->cell_height_ratio) - $imgh);
						$minstartliney = min($this->y, $minstartliney);
						$maxbottomliney = ($startliney + $this->getCellHeight($curfontsize / $this->k)); // tes
					}
				} 
        elseif (isset($dom[$key]['fontname']) OR isset($dom[$key]['fontstyle']) OR isset($dom[$key]['fontsize']) OR isset($dom[$key]['line-height'])) {
					// account for different font size
					$pfontname = $curfontname;
					$pfontstyle = $curfontstyle;
					$pfontsize = $curfontsize;
          $pfontascent = $fontascent ?? 0; // tambahan untuk membantu footnote
          $pfontdescent = $fontdescent ?? 0; // tambahan untuk membantu footnote
					$fontname = (isset($dom[$key]['fontname']) ? $dom[$key]['fontname'] : $curfontname);
					$fontstyle = (isset($dom[$key]['fontstyle']) ? $dom[$key]['fontstyle'] : $curfontstyle);
					$fontsize = (isset($dom[$key]['fontsize']) ? $dom[$key]['fontsize'] : $curfontsize);
					$fontascent = $this->getFontAscent($fontname, $fontstyle, $fontsize);
					$fontdescent = $this->getFontDescent($fontname, $fontstyle, $fontsize);
					if (($fontname != $curfontname) OR ($fontstyle != $curfontstyle) OR ($fontsize != $curfontsize)
						OR ($this->cell_height_ratio != $dom[$key]['line-height'])
						OR ($dom[$key]['tag'] AND $dom[$key]['opening'] AND ($dom[$key]['value'] == 'li')) ) {
						if (($key < ($maxel - 1)) AND (
								($dom[$key]['tag'] AND $dom[$key]['opening'] AND ($dom[$key]['value'] == 'li'))
								OR ($this->cell_height_ratio != $dom[$key]['line-height'])
								OR (!$this->newline AND is_numeric($fontsize) AND is_numeric($curfontsize)
								AND ($fontsize >= 0) AND ($curfontsize >= 0)
								AND (($fontsize != $curfontsize) OR ($fontstyle != $curfontstyle) OR ($fontname != $curfontname)))
							)) {
							if ($this->page > $startlinepage) {
								// fix lines splitted over two pages
								if (isset($this->footerlen[$startlinepage])) {
									$curpos = $this->pagelen[$startlinepage] - $this->footerlen[$startlinepage];
								}
								// line to be moved one page forward
								$pagebuff = $this->getPageBuffer($startlinepage);
								$linebeg = substr($pagebuff, $startlinepos, ($curpos - $startlinepos));
								$tstart = substr($pagebuff, 0, $startlinepos);
								$tend = substr($this->getPageBuffer($startlinepage), $curpos);
								// remove line start from previous page
								$this->setPageBuffer($startlinepage, $tstart.''.$tend);
								$pagebuff = $this->getPageBuffer($this->page);
								$tstart = substr($pagebuff, 0, $this->cntmrk[$this->page]);
								$tend = substr($pagebuff, $this->cntmrk[$this->page]);
								// add line start to current page
								$yshift = ($minstartliney - $this->y);
								$try = sprintf('1 0 0 1 0 %F cm', ($yshift * $this->k));
								$this->setPageBuffer($this->page, $tstart."\nq\n".$try."\n".$linebeg."\nQ\n".$tend);
								// shift the annotations and links
								if (isset($this->PageAnnots[$this->page])) {
									$next_pask = count($this->PageAnnots[$this->page]);
								} else {
									$next_pask = 0;
								}
								if (isset($this->PageAnnots[$startlinepage])) {
									foreach ($this->PageAnnots[$startlinepage] as $pak => $pac) {
										if ($pak >= $pask) {
											$this->PageAnnots[$this->page][] = $pac;
											unset($this->PageAnnots[$startlinepage][$pak]);
											$npak = count($this->PageAnnots[$this->page]) - 1;
											$this->PageAnnots[$this->page][$npak]['y'] -= $yshift;
										}
									}
								}
								$pask = $next_pask;
								$startlinepos = $this->cntmrk[$this->page];
								$startlinepage = $this->page;
								$startliney = $this->y;
							}
							if (!isset($dom[$key]['line-height'])) {
								$dom[$key]['line-height'] = $this->cell_height_ratio;
							}
							if (!$dom[$key]['block']) {
                if (!(isset($dom[($key + 1)]) AND $dom[($key + 1)]['tag'] AND (!$dom[($key + 1)]['opening']) AND ($dom[($key + 1)]['value'] != 'li') AND $dom[$key]['tag'] AND (!$dom[$key]['opening']))) {
                  $this->y += (((($curfontsize * $this->cell_height_ratio) - ($fontsize * $dom[$key]['line-height'])) / $this->k) + $curfontascent - $fontascent - $curfontdescent + $fontdescent) / 2; // ini masalahnya
                }
								if (($dom[$key]['value'] != 'sup') AND ($dom[$key]['value'] != 'sub')) {
									$current_line_align_data = array($key, $minstartliney, $maxbottomliney);
									if (isset($line_align_data) AND (($line_align_data[0] == ($key - 1)) OR (($line_align_data[0] == ($key - 2)) AND (isset($dom[($key - 1)])) AND (preg_match('/^([\s]+)$/', $dom[($key - 1)]['value']) > 0)))) {
										$minstartliney = min($this->y, $line_align_data[1]);
										$maxbottomliney = max(($this->y + $this->getCellHeight($fontsize / $this->k)), $line_align_data[2]);
									} else {
										$minstartliney = min($this->y, $minstartliney);
										$maxbottomliney = max(($this->y + $this->getCellHeight($fontsize / $this->k)), $maxbottomliney);
									}
									$line_align_data = $current_line_align_data;
								}
							}
							$this->cell_height_ratio = $dom[$key]['line-height'];
							$fontaligned = true;
						}
						$this->setFont($fontname, $fontstyle, $fontsize);
						// reset row height
						$this->resetLastH();
						$curfontname = $fontname;
						$curfontstyle = $fontstyle;
						$curfontsize = $fontsize;
						$curfontascent = $fontascent;
						$curfontdescent = $fontdescent;
					}
				}
        // dd($curfontsize,$fontsize,$fontname,$fontstyle,$fontascent, $fontdescent,__LINE__);
				// set text rendering mode
				$textstroke = isset($dom[$key]['stroke']) ? $dom[$key]['stroke'] : $this->textstrokewidth;
				$textfill = isset($dom[$key]['fill']) ? $dom[$key]['fill'] : (($this->textrendermode % 2) == 0);
				$textclip = isset($dom[$key]['clip']) ? $dom[$key]['clip'] : ($this->textrendermode > 3);
				$this->setTextRenderingMode($textstroke, $textfill, $textclip);
				if (isset($dom[$key]['font-stretch']) AND ($dom[$key]['font-stretch'] !== false)) {
					$this->setFontStretching($dom[$key]['font-stretch']);
				}
				if (isset($dom[$key]['letter-spacing']) AND ($dom[$key]['letter-spacing'] !== false)) {
					$this->setFontSpacing($dom[$key]['letter-spacing']);
				}
				if (($plalign == 'J') AND $dom[$key]['block']) {
					$plalign = '';
				}
				// get current position on page buffer
				$curpos = $this->pagelen[$startlinepage];
				if (isset($dom[$key]['bgcolor']) AND ($dom[$key]['bgcolor'] !== false)) {
					$this->setFillColorArray($dom[$key]['bgcolor']);
					$wfill = true;
				} else {
					$wfill = $fill | false;
				}
				if (isset($dom[$key]['fgcolor']) AND ($dom[$key]['fgcolor'] !== false)) {
					$this->setTextColorArray($dom[$key]['fgcolor']);
				}
				if (isset($dom[$key]['strokecolor']) AND ($dom[$key]['strokecolor'] !== false)) {
					$this->setDrawColorArray($dom[$key]['strokecolor']);
				}
				if (isset($dom[$key]['align'])) {
					$lalign = $dom[$key]['align'];
				}
				if (TCPDF_STATIC::empty_string($lalign)) {
					$lalign = $align;
				}
			}
      
			// align lines
			if ($this->newline AND (strlen($dom[$key]['value']) > 0) AND ($dom[$key]['value'] != 'td') AND ($dom[$key]['value'] != 'th')) {
        // dump($key."|".$dom[$key]['value']);
				$newline = true;
        // dump($key."|".$this->newline, $dom[$key]['value']);
				$fontaligned = false;
				// we are at the beginning of a new line
				if (isset($startlinex)) {

          // if(!$this->inFootnote) dump($startlinex."|".$this->x."|".$key."|".$dom[$key]['value']);

					$yshift = ($minstartliney - $startliney);
					if (($yshift > 0) OR ($this->page > $startlinepage)) {
						$yshift = 0;
					}
					$t_x = 0;
					// the last line must be shifted to be aligned as requested
					$linew = abs($this->endlinex - $startlinex);
          if($this->inFootnote) $startlinepos = strlen($this->xobjects[$this->xobjid]['outdata']); // tambahan untuk footnote
					if ($this->inxobj) {
						// we are inside an XObject template
						$pstart = substr($this->xobjects[$this->xobjid]['outdata'], 0, $startlinepos);
						if (isset($opentagpos)) {
							$midpos = $opentagpos;
						} else {
							$midpos = 0;
						}
						if ($midpos > 0) {
							$pmid = substr($this->xobjects[$this->xobjid]['outdata'], $startlinepos, ($midpos - $startlinepos));
							$pend = substr($this->xobjects[$this->xobjid]['outdata'], $midpos);
						} else {
							$pmid = substr($this->xobjects[$this->xobjid]['outdata'], $startlinepos);
							$pend = '';
						}
					} else {
						$pstart = substr($this->getPageBuffer($startlinepage), 0, $startlinepos);
						if (isset($opentagpos) AND isset($this->footerlen[$startlinepage]) AND (!$this->InFooter)) {
							$this->footerpos[$startlinepage] = $this->pagelen[$startlinepage] - $this->footerlen[$startlinepage];
							$midpos = min($opentagpos, $this->footerpos[$startlinepage]);
						} elseif (isset($opentagpos)) {
							$midpos = $opentagpos;
						} elseif (isset($this->footerlen[$startlinepage]) AND (!$this->InFooter)) {
							$this->footerpos[$startlinepage] = $this->pagelen[$startlinepage] - $this->footerlen[$startlinepage];
							$midpos = $this->footerpos[$startlinepage];
						} else {
							$midpos = 0;
						}
						if ($midpos > 0) {
							$pmid = substr($this->getPageBuffer($startlinepage), $startlinepos, ($midpos - $startlinepos));
							$pend = substr($this->getPageBuffer($startlinepage), $midpos);
						} else {
							$pmid = substr($this->getPageBuffer($startlinepage), $startlinepos);
							$pend = '';
						}
					}
					if ((((($plalign == 'C') OR ($plalign == 'J') OR (($plalign == 'R') AND (!$this->rtl)) OR (($plalign == 'L') AND ($this->rtl)))))) {
						// calculate shifting amount
						$tw = $w;
						if (($plalign == 'J') AND $this->isRTLTextDir() AND ($this->num_columns > 1)) {
              $tw += $this->cell_padding['R'];
						}
						if ($this->lMargin != $prevlMargin) {
              $tw += ($prevlMargin - $this->lMargin);
						}
						if ($this->rMargin != $prevrMargin) {
							$tw += ($prevrMargin - $this->rMargin);
						}
						$one_space_width = $this->GetStringWidth(chr(32));
						$no = 0; // number of spaces on a line contained on a single block
						if ($this->isRTLTextDir()) { // RTL
							// remove left space if exist
							$pos1 = TCPDF_STATIC::revstrpos($pmid, '[(');
							if ($pos1 > 0) {
								$pos1 = intval($pos1);
								if ($this->isUnicodeFont()) {
									$pos2 = intval(TCPDF_STATIC::revstrpos($pmid, '[('.chr(0).chr(32)));
									$spacelen = 2;
								} else {
									$pos2 = intval(TCPDF_STATIC::revstrpos($pmid, '[('.chr(32)));
									$spacelen = 1;
								}
								if ($pos1 == $pos2) {
									$pmid = substr($pmid, 0, ($pos1 + 2)).substr($pmid, ($pos1 + 2 + $spacelen));
									if (substr($pmid, $pos1, 4) == '[()]') {
										$linew -= $one_space_width;
									} elseif ($pos1 == strpos($pmid, '[(')) {
										$no = 1;
									}
								}
							}
						} else { // LTR
							// remove right space if exist
							$pos1 = TCPDF_STATIC::revstrpos($pmid, ')]');
							if ($pos1 > 0) {
								$pos1 = intval($pos1);
								if ($this->isUnicodeFont()) {
									$pos2 = intval(TCPDF_STATIC::revstrpos($pmid, chr(0).chr(32).')]')) + 2;
									$spacelen = 2;
								} else {
									$pos2 = intval(TCPDF_STATIC::revstrpos($pmid, chr(32).')]')) + 1;
									$spacelen = 1;
								}
								if ($pos1 == $pos2) {
									$pmid = substr($pmid, 0, ($pos1 - $spacelen)).substr($pmid, $pos1);
									$linew -= $one_space_width;
								}
							}
						}
						$mdiff = ($tw - $linew);
						if ($plalign == 'C') {
							if ($this->rtl) {
								$t_x = -($mdiff / 2);
							} else {
								$t_x = ($mdiff / 2);
							}
						} elseif ($plalign == 'R') {
							// right alignment on LTR document
							$t_x = $mdiff;
						} elseif ($plalign == 'L') {
							// left alignment on RTL document
							$t_x = -$mdiff;
						} 
            elseif (($plalign == 'J') AND ($plalign == $lalign)) {
							// Justification
							if ($this->isRTLTextDir()) {
								// align text on the left
								$t_x = -$mdiff;
							}
							$ns = 0; // number of spaces
							$pmidtemp = $pmid;
							// escape special characters
							$pmidtemp = preg_replace('/[\\\][\(]/x', '\\#!#OP#!#', $pmidtemp);
							$pmidtemp = preg_replace('/[\\\][\)]/x', '\\#!#CP#!#', $pmidtemp);
							// search spaces
							if (preg_match_all('/\[\(([^\)]*)\)\]/x', $pmidtemp, $lnstring, PREG_PATTERN_ORDER)) {
								$spacestr = $this->getSpaceString();
								$maxkk = count($lnstring[1]) - 1;
								for ($kk=0; $kk <= $maxkk; ++$kk) {
									// restore special characters
									$lnstring[1][$kk] = str_replace('#!#OP#!#', '(', $lnstring[1][$kk]);
									$lnstring[1][$kk] = str_replace('#!#CP#!#', ')', $lnstring[1][$kk]);
									// store number of spaces on the strings
									$lnstring[2][$kk] = substr_count($lnstring[1][$kk], $spacestr);
									// count total spaces on line
									$ns += $lnstring[2][$kk];
									$lnstring[3][$kk] = $ns;
								}
								if ($ns == 0) {
									$ns = 1;
								}
								// calculate additional space to add to each existing space
								$spacewidth = ($mdiff / ($ns - $no)) * $this->k;
								if ($this->FontSize <= 0) {
									$this->FontSize = 1;
								}
								$spacewidthu = -1000 * ($mdiff + (($ns + $no) * $one_space_width)) / $ns / $this->FontSize;
								if ($this->font_spacing != 0) {
									// fixed spacing mode
									$osw = -1000 * $this->font_spacing / $this->FontSize;
									$spacewidthu += $osw;
								}
								$nsmax = $ns;
								$ns = 0;
								reset($lnstring);
								$offset = 0;
								$strcount = 0;
								$prev_epsposbeg = 0;
								$textpos = 0;
								if ($this->isRTLTextDir()) {
									$textpos = $this->wPt;
								}
								while (preg_match('/([0-9\.\+\-]*)[\s](Td|cm|m|l|c|re)[\s]/x', $pmid, $strpiece, PREG_OFFSET_CAPTURE, $offset) == 1) {
									// check if we are inside a string section '[( ... )]'
									$stroffset = strpos($pmid, '[(', $offset);
									if (($stroffset !== false) AND ($stroffset <= $strpiece[2][1])) {
										// set offset to the end of string section
										$offset = strpos($pmid, ')]', $stroffset);
										while (($offset !== false) AND ($pmid[($offset - 1)] == '\\')) {
											$offset = strpos($pmid, ')]', ($offset + 1));
										}
										if ($offset === false) {
											$this->Error('HTML Justification: malformed PDF code.');
										}
										continue;
									}
									if ($this->isRTLTextDir()) {
										$spacew = ($spacewidth * ($nsmax - $ns));
									} else {
										$spacew = ($spacewidth * $ns);
									}
									$offset = $strpiece[2][1] + strlen($strpiece[2][0]);
									$epsposend = strpos($pmid, $this->epsmarker.'Q', $offset);
									if ($epsposend !== null) {
										$epsposend += strlen($this->epsmarker.'Q');
										$epsposbeg = strpos($pmid, 'q'.$this->epsmarker, $offset);
										if ($epsposbeg === null) {
											$epsposbeg = strpos($pmid, 'q'.$this->epsmarker, ($prev_epsposbeg - 6));
											$prev_epsposbeg = $epsposbeg;
										}
										if (($epsposbeg > 0) AND ($epsposend > 0) AND ($offset > $epsposbeg) AND ($offset < $epsposend)) {
											// shift EPS images
											$trx = sprintf('1 0 0 1 %F 0 cm', $spacew);
											$pmid_b = substr($pmid, 0, $epsposbeg);
											$pmid_m = substr($pmid, $epsposbeg, ($epsposend - $epsposbeg));
											$pmid_e = substr($pmid, $epsposend);
											$pmid = $pmid_b."\nq\n".$trx."\n".$pmid_m."\nQ\n".$pmid_e;
											$offset = $epsposend;
											continue;
										}
									}
									$currentxpos = 0;
									// shift blocks of code
									switch ($strpiece[2][0]) {
										case 'Td':
										case 'cm':
										case 'm':
										case 'l': {
											// get current X position
											preg_match('/([0-9\.\+\-]*)[\s]('.$strpiece[1][0].')[\s]('.$strpiece[2][0].')([\s]*)/x', $pmid, $xmatches);
											if (!isset($xmatches[1])) {
												break;
											}
											$currentxpos = $xmatches[1];
                      // $currentxpos == '' ? ($currentxpos = 0) : $currentxpos; // tambahan coba-coba untuk footnote
                      // dump($currentxpos);
                      // dump($pmid);
											$textpos = $currentxpos;
											if (($strcount <= $maxkk) AND ($strpiece[2][0] == 'Td')) {
												$ns = $lnstring[3][$strcount];
												if ($this->isRTLTextDir()) {
													$spacew = ($spacewidth * ($nsmax - $ns));
												}
												++$strcount;
											}
											// justify block
											if (preg_match('/([0-9\.\+\-]*)[\s]('.$strpiece[1][0].')[\s]('.$strpiece[2][0].')([\s]*)/x', $pmid, $pmatch) == 1) {
												$newpmid = sprintf('%F',(floatval($pmatch[1]) + $spacew)).' '.$pmatch[2].' x*#!#*x'.$pmatch[3].$pmatch[4];
												$pmid = str_replace($pmatch[0], $newpmid, $pmid);
												unset($pmatch, $newpmid);
											}
											break;
										}
										case 're': {
											// justify block
											if (!TCPDF_STATIC::empty_string($this->lispacer)) {
												$this->lispacer = '';
												break;
											}
											preg_match('/([0-9\.\+\-]*)[\s]([0-9\.\+\-]*)[\s]([0-9\.\+\-]*)[\s]('.$strpiece[1][0].')[\s](re)([\s]*)/x', $pmid, $xmatches);
											if (!isset($xmatches[1])) {
												break;
											}
											$currentxpos = $xmatches[1];
											$x_diff = 0;
											$w_diff = 0;
											if ($this->isRTLTextDir()) { // RTL
												if ($currentxpos < $textpos) {
													$x_diff = ($spacewidth * ($nsmax - $lnstring[3][$strcount]));
													$w_diff = ($spacewidth * $lnstring[2][$strcount]);
												} else {
													if ($strcount > 0) {
														$x_diff = ($spacewidth * ($nsmax - $lnstring[3][($strcount - 1)]));
														$w_diff = ($spacewidth * $lnstring[2][($strcount - 1)]);
													}
												}
											} else { // LTR
												if ($currentxpos > $textpos) {
													if ($strcount > 0) {
														$x_diff = ($spacewidth * $lnstring[3][($strcount - 1)]);
													}
													$w_diff = ($spacewidth * $lnstring[2][$strcount]);
												} else {
													if ($strcount > 1) {
														$x_diff = ($spacewidth * $lnstring[3][($strcount - 2)]);
													}
													if ($strcount > 0) {
														$w_diff = ($spacewidth * $lnstring[2][($strcount - 1)]);
													}
												}
											}
											if (preg_match('/('.$xmatches[1].')[\s]('.$xmatches[2].')[\s]('.$xmatches[3].')[\s]('.$strpiece[1][0].')[\s](re)([\s]*)/x', $pmid, $pmatch) == 1) {
												$newx = sprintf('%F',(floatval($pmatch[1]) + $x_diff));
												$neww = sprintf('%F',(floatval($pmatch[3]) + $w_diff));
												$newpmid = $newx.' '.$pmatch[2].' '.$neww.' '.$pmatch[4].' x*#!#*x'.$pmatch[5].$pmatch[6];
												$pmid = str_replace($pmatch[0], $newpmid, $pmid);
												unset($pmatch, $newpmid, $newx, $neww);
											}
											break;
										}
										case 'c': {
											// get current X position
											preg_match('/([0-9\.\+\-]*)[\s]([0-9\.\+\-]*)[\s]([0-9\.\+\-]*)[\s]([0-9\.\+\-]*)[\s]([0-9\.\+\-]*)[\s]('.$strpiece[1][0].')[\s](c)([\s]*)/x', $pmid, $xmatches);
											if (!isset($xmatches[1])) {
												break;
											}
											$currentxpos = $xmatches[1];
											// justify block
											if (preg_match('/('.$xmatches[1].')[\s]('.$xmatches[2].')[\s]('.$xmatches[3].')[\s]('.$xmatches[4].')[\s]('.$xmatches[5].')[\s]('.$strpiece[1][0].')[\s](c)([\s]*)/x', $pmid, $pmatch) == 1) {
												$newx1 = sprintf('%F',(floatval($pmatch[1]) + $spacew));
												$newx2 = sprintf('%F',(floatval($pmatch[3]) + $spacew));
												$newx3 = sprintf('%F',(floatval($pmatch[5]) + $spacew));
												$newpmid = $newx1.' '.$pmatch[2].' '.$newx2.' '.$pmatch[4].' '.$newx3.' '.$pmatch[6].' x*#!#*x'.$pmatch[7].$pmatch[8];
												$pmid = str_replace($pmatch[0], $newpmid, $pmid);
												unset($pmatch, $newpmid, $newx1, $newx2, $newx3);
											}
											break;
										}
									}
									// shift the annotations and links
									$cxpos = ($currentxpos / $this->k);
									$lmpos = ($this->lMargin + $this->cell_padding['L'] + $this->feps);
									if ($this->inxobj) {
										// we are inside an XObject template
										foreach ($this->xobjects[$this->xobjid]['annotations'] as $pak => $pac) {
											if (($pac['y'] >= $minstartliney) AND (($pac['x'] * $this->k) >= ($currentxpos - $this->feps)) AND (($pac['x'] * $this->k) <= ($currentxpos + $this->feps))) {
												if ($cxpos > $lmpos) {
													$this->xobjects[$this->xobjid]['annotations'][$pak]['x'] += ($spacew / $this->k);
													$this->xobjects[$this->xobjid]['annotations'][$pak]['w'] += (($spacewidth * $pac['numspaces']) / $this->k);
												} else {
													$this->xobjects[$this->xobjid]['annotations'][$pak]['w'] += (($spacewidth * $pac['numspaces']) / $this->k);
												}
												break;
											}
										}
									} elseif (isset($this->PageAnnots[$this->page])) {
										foreach ($this->PageAnnots[$this->page] as $pak => $pac) {
											if (($pac['y'] >= $minstartliney) AND (($pac['x'] * $this->k) >= ($currentxpos - $this->feps)) AND (($pac['x'] * $this->k) <= ($currentxpos + $this->feps))) {
												if ($cxpos > $lmpos) {
													$this->PageAnnots[$this->page][$pak]['x'] += ($spacew / $this->k);
													$this->PageAnnots[$this->page][$pak]['w'] += (($spacewidth * $pac['numspaces']) / $this->k);
												} else {
													$this->PageAnnots[$this->page][$pak]['w'] += (($spacewidth * $pac['numspaces']) / $this->k);
												}
												break;
											}
										}
									}
								} // end of while
								// remove markers
								$pmid = str_replace('x*#!#*x', '', $pmid);
								if ($this->isUnicodeFont()) {
									// multibyte characters
									$spacew = $spacewidthu;
									if ($this->font_stretching != 100) {
										// word spacing is affected by stretching
										$spacew /= ($this->font_stretching / 100);
									}
									// escape special characters
									$pos = 0;
									$pmid = preg_replace('/[\\\][\(]/x', '\\#!#OP#!#', $pmid);
									$pmid = preg_replace('/[\\\][\)]/x', '\\#!#CP#!#', $pmid);
									if (preg_match_all('/\[\(([^\)]*)\)\]/x', $pmid, $pamatch) > 0) {
										foreach($pamatch[0] as $pk => $pmatch) {
											$replace = $pamatch[1][$pk];
											$replace = str_replace('#!#OP#!#', '(', $replace);
											$replace = str_replace('#!#CP#!#', ')', $replace);
											$newpmid = '[('.str_replace(chr(0).chr(32), ') '.sprintf('%F', $spacew).' (', $replace).')]';
											$pos = strpos($pmid, $pmatch, $pos);
											if ($pos !== FALSE) {
												$pmid = substr_replace($pmid, $newpmid, $pos, strlen($pmatch));
											}
											++$pos;
										}
										unset($pamatch);
									}
									if ($this->inxobj) {
										// we are inside an XObject template
										$this->xobjects[$this->xobjid]['outdata'] = $pstart."\n".$pmid."\n".$pend;
									} else {
										$this->setPageBuffer($startlinepage, $pstart."\n".$pmid."\n".$pend);
									}
									$endlinepos = strlen($pstart."\n".$pmid."\n");
								} else {
									// non-unicode (single-byte characters)
									if ($this->font_stretching != 100) {
										// word spacing (Tw) is affected by stretching
										$spacewidth /= ($this->font_stretching / 100);
									}
									$rs = sprintf('%F Tw', $spacewidth);
									$pmid = preg_replace("/\[\(/x", $rs.' [(', $pmid);
									if ($this->inxobj) {
										// we are inside an XObject template
										$this->xobjects[$this->xobjid]['outdata'] = $pstart."\n".$pmid."\nBT 0 Tw ET\n".$pend;
                    // dump($pstart, $pmid, $pend);
									} else {
										$this->setPageBuffer($startlinepage, $pstart."\n".$pmid."\nBT 0 Tw ET\n".$pend);
									}
									$endlinepos = strlen($pstart."\n".$pmid."\nBT 0 Tw ET\n");
								}
							}
						} // end of J
					} // end if $startlinex
					if (($t_x != 0) OR ($yshift < 0)) {
						// shift the line
						$trx = sprintf('1 0 0 1 %F %F cm', ($t_x * $this->k), ($yshift * $this->k));
						$pstart .= "\nq\n".$trx."\n".$pmid."\nQ\n";
						$endlinepos = strlen($pstart);
						if ($this->inxobj) {
							// we are inside an XObject template
							$this->xobjects[$this->xobjid]['outdata'] = $pstart.$pend;
							foreach ($this->xobjects[$this->xobjid]['annotations'] as $pak => $pac) {
								if ($pak >= $pask) {
									$this->xobjects[$this->xobjid]['annotations'][$pak]['x'] += $t_x;
									$this->xobjects[$this->xobjid]['annotations'][$pak]['y'] -= $yshift;
								}
							}
						} else {
							$this->setPageBuffer($startlinepage, $pstart.$pend);
							// shift the annotations and links
							if (isset($this->PageAnnots[$this->page])) {
								foreach ($this->PageAnnots[$this->page] as $pak => $pac) {
									if ($pak >= $pask) {
										$this->PageAnnots[$this->page][$pak]['x'] += $t_x;
										$this->PageAnnots[$this->page][$pak]['y'] -= $yshift;
									}
								}
							}
						}
						$this->y -= $yshift;
					}
				}
				$pbrk = $this->checkPageBreak($this->lasth);
				$this->newline = false;
				$startlinex = $this->x;
				$startliney = $this->y;
				if ($dom[$dom[$key]['parent']]['value'] == 'sup') {
					$startliney -= ((0.3 * $this->FontSizePt) / $this->k);
				} elseif ($dom[$dom[$key]['parent']]['value'] == 'sub') {
					$startliney -= (($this->FontSizePt / 0.7) / $this->k);
				} else {
					$minstartliney = $startliney;
					$maxbottomliney = ($this->y + $this->getCellHeight($fontsize / $this->k)); // tes
          $parentDom = $dom[$dom[$key]['parent']];

          /** EDITTED - tambahan agar ol dan ul tidak ada vspace after*/
          if($parentDom['value'] == 'ol' OR $parentDom['value'] == 'ul'){
            $maxbottomliney = 0;
          }
          /** end EDITTED */
				}

				$startlinepage = $this->page;
				if (isset($endlinepos) AND (!$pbrk)) {
					$startlinepos = $endlinepos;
				} else {
					if ($this->inxobj) {
						// we are inside an XObject template
						$startlinepos = strlen($this->xobjects[$this->xobjid]['outdata']);
					} elseif (!$this->InFooter) {
						if (isset($this->footerlen[$this->page])) {
							$this->footerpos[$this->page] = $this->pagelen[$this->page] - $this->footerlen[$this->page];
						} else {
							$this->footerpos[$this->page] = $this->pagelen[$this->page];
						}
						$startlinepos = $this->footerpos[$this->page];
					} else {
						$startlinepos = $this->pagelen[$this->page];
					}
				}
				unset($endlinepos);
				$plalign = $lalign;
				if (isset($this->PageAnnots[$this->page])) {
					$pask = count($this->PageAnnots[$this->page]);
				} else {
					$pask = 0;
				}
				if (!($dom[$key]['tag'] AND !$dom[$key]['opening'] AND ($dom[$key]['value'] == 'table')
					AND (isset($this->emptypagemrk[$this->page]))
					AND ($this->emptypagemrk[$this->page] == $this->pagelen[$this->page]))) {
					$this->setFont($fontname, $fontstyle, $fontsize);
					if ($wfill) {
						$this->setFillColorArray($this->bgcolor);
					}
				}
			} // end newline
			if (isset($opentagpos)) {
				unset($opentagpos);
			}
			if ($dom[$key]['tag']) {
				if ($dom[$key]['opening']) { // kayaknya pindah ke sini si footnote

          // footnote #1 new
          // if(isset($dom[$key]['attribute']['isfootnote']) AND $dom[$key]['attribute']['isfootnote'] == 'true'
          //   AND !$this->inFootnote
          //   ){
          //   $id = $dom[$key]['attribute']['id'] ?? random_int(1000,10000);
          //   $this->footnotes[]['id'] = $id;
            
          //   // untuk menulis citation nya
          //   $wfnt = $this->w - $this->lMargin - $this->rMargin - $this->cell_padding['L'] - $this->cell_padding['R'];
          //   unset($template);
          //   $template = $this->startTemplate($wfnt,'');
          //   $this->x = $this->lMargin + $this->cell_padding['L'];
          //   $this->inFootnote = true;
          //   $this->xobjects[$template]['domkeystart'] = $key;
          //   $txt = count($this->footnotes);
          //   $this->Write('',"[{$txt}]    ",'',false,'J',false,0,true, true, 0, 0);
          //   $this->lMargin += $this->GetStringWidth("[{$txt}]    ",$curfontname, $curfontstyle, $curfontsize);
          // }

					// get text indentation (if any)
					if (isset($dom[$key]['text-indent']) AND $dom[$key]['block']) {
						$this->textindent = $dom[$key]['text-indent'];
						$this->newline = true;
					}
					// table
					if (($dom[$key]['value'] == 'table') AND isset($dom[$key]['cols']) AND ($dom[$key]['cols'] > 0)) {
						// available page width
						if ($this->rtl) {
							$wtmp = $this->x - $this->lMargin;
						} else {
							$wtmp = $this->w - $this->rMargin - $this->x;
						}
						// get cell spacing
						if (isset($dom[$key]['attribute']['cellspacing'])) {
							$clsp = $this->getHTMLUnitToUnits($dom[$key]['attribute']['cellspacing'], 1, 'px');
							$cellspacing = array('H' => $clsp, 'V' => $clsp);
						} elseif (isset($dom[$key]['border-spacing'])) {
							$cellspacing = $dom[$key]['border-spacing'];
						} else {
							$cellspacing = array('H' => 0, 'V' => 0);
						}
						// table width
						if (isset($dom[$key]['width'])) {
							$table_width = $this->getHTMLUnitToUnits($dom[$key]['width'], $wtmp, 'px');
						} else {
							$table_width = $wtmp;
						}
						$table_width -= (2 * $cellspacing['H']);
						if (!$this->inthead) {
							$this->y += $cellspacing['V'];
						}
						if ($this->rtl) {
							$cellspacingx = -$cellspacing['H'];
						} else {
							$cellspacingx = $cellspacing['H'];
						}
						// total table width without cellspaces
						$table_columns_width = ($table_width - ($cellspacing['H'] * ($dom[$key]['cols'] - 1)));
						// minimum column width
						$table_min_column_width = ($table_columns_width / $dom[$key]['cols']);
						// array of custom column widths
						$table_colwidths = array_fill(0, $dom[$key]['cols'], $table_min_column_width);
					}
					// table row
					if ($dom[$key]['value'] == 'tr') {
						// reset column counter
						$colid = 0;
					}
					// table cell
					if (($dom[$key]['value'] == 'td') OR ($dom[$key]['value'] == 'th')) {
						$trid = $dom[$key]['parent'];
						$table_el = $dom[$trid]['parent'];
						if (!isset($dom[$table_el]['cols'])) {
							$dom[$table_el]['cols'] = $dom[$trid]['cols'];
						}
						// store border info
						$tdborder = 0;
						if (isset($dom[$key]['border']) AND !empty($dom[$key]['border'])) {
							$tdborder = $dom[$key]['border'];
						}
						$colspan = intval($dom[$key]['attribute']['colspan']);
						if ($colspan <= 0) {
							$colspan = 1;
						}
						$old_cell_padding = $this->cell_padding;
						if (isset($dom[($dom[$trid]['parent'])]['attribute']['cellpadding'])) {
							$crclpd = $this->getHTMLUnitToUnits($dom[($dom[$trid]['parent'])]['attribute']['cellpadding'], 1, 'px');
							$current_cell_padding = array('L' => $crclpd, 'T' => $crclpd, 'R' => $crclpd, 'B' => $crclpd);
						} elseif (isset($dom[($dom[$trid]['parent'])]['padding'])) {
              $current_cell_padding = $dom[($dom[$trid]['parent'])]['padding'];
						} else {
							$current_cell_padding = array('L' => 0, 'T' => 0, 'R' => 0, 'B' => 0);
						}
						$this->cell_padding = $current_cell_padding;
						if (isset($dom[$key]['height'])) {
							// minimum cell height
							$cellh = $this->getHTMLUnitToUnits($dom[$key]['height'], 0, 'px');
						} else {
							$cellh = 0;
						}
						if (isset($dom[$key]['content'])) {
							$cell_content = $dom[$key]['content'];
						} else {
							$cell_content = '&nbsp;';
						}
						$tagtype = $dom[$key]['value'];
						$parentid = $key;
						while (($key < $maxel) AND (!(($dom[$key]['tag']) AND (!$dom[$key]['opening']) AND ($dom[$key]['value'] == $tagtype) AND ($dom[$key]['parent'] == $parentid)))) {
							// move $key index forward
							++$key; // ini move ke closing tag td
						}
						if (!isset($dom[$trid]['startpage'])) {
							$dom[$trid]['startpage'] = $this->page;
						} else {
							$this->setPage($dom[$trid]['startpage']);
						}
						if (!isset($dom[$trid]['startcolumn'])) {
							$dom[$trid]['startcolumn'] = $this->current_column;
						} elseif ($this->current_column != $dom[$trid]['startcolumn']) {
							$tmpx = $this->x;
							$this->selectColumn($dom[$trid]['startcolumn']);
							$this->x = $tmpx;
						}
						if (!isset($dom[$trid]['starty'])) {
							$dom[$trid]['starty'] = $this->y;
						} else {
							$this->y = $dom[$trid]['starty'];
						}
						if (!isset($dom[$trid]['startx'])) {
							$dom[$trid]['startx'] = $this->x;
							$this->x += $cellspacingx;
						} else {
							$this->x += ($cellspacingx / 2);
						}
						if (isset($dom[$parentid]['attribute']['rowspan'])) {
							$rowspan = intval($dom[$parentid]['attribute']['rowspan']);
						} else {
							$rowspan = 1;
						}
						// skip row-spanned cells started on the previous rows
						if (isset($dom[$table_el]['rowspans'])) {
							$rsk = 0;
							$rskmax = count($dom[$table_el]['rowspans']);
							while ($rsk < $rskmax) {
								$trwsp = $dom[$table_el]['rowspans'][$rsk];
								$rsstartx = $trwsp['startx'];
								$rsendx = $trwsp['endx'];
								// account for margin changes
								if ($trwsp['startpage'] < $this->page) {
									if (($this->rtl) AND ($this->pagedim[$this->page]['orm'] != $this->pagedim[$trwsp['startpage']]['orm'])) {
										$dl = ($this->pagedim[$this->page]['orm'] - $this->pagedim[$trwsp['startpage']]['orm']);
										$rsstartx -= $dl;
										$rsendx -= $dl;
									} elseif ((!$this->rtl) AND ($this->pagedim[$this->page]['olm'] != $this->pagedim[$trwsp['startpage']]['olm'])) {
										$dl = ($this->pagedim[$this->page]['olm'] - $this->pagedim[$trwsp['startpage']]['olm']);
										$rsstartx += $dl;
										$rsendx += $dl;
									}
								}
								if (($trwsp['rowspan'] > 0)
									AND ($rsstartx > ($this->x - $cellspacing['H'] - $current_cell_padding['L'] - $this->feps))
									AND ($rsstartx < ($this->x + $cellspacing['H'] + $current_cell_padding['R'] + $this->feps))
									AND (($trwsp['starty'] < ($this->y - $this->feps)) OR ($trwsp['startpage'] < $this->page) OR ($trwsp['startcolumn'] < $this->current_column))) {
									// set the starting X position of the current cell
									$this->x = $rsendx + $cellspacingx;
									// increment column indicator
									$colid += $trwsp['colspan'];
									if (($trwsp['rowspan'] == 1)
										AND (isset($dom[$trid]['endy']))
										AND (isset($dom[$trid]['endpage']))
										AND (isset($dom[$trid]['endcolumn']))
										AND ($trwsp['endpage'] == $dom[$trid]['endpage'])
										AND ($trwsp['endcolumn'] == $dom[$trid]['endcolumn'])) {
										// set ending Y position for row
										$dom[$table_el]['rowspans'][$rsk]['endy'] = max($dom[$trid]['endy'], $trwsp['endy']);
										$dom[$trid]['endy'] = $dom[$table_el]['rowspans'][$rsk]['endy'];
									}
									$rsk = 0;
								} else {
									++$rsk;
								}
							}
						}
						if (isset($dom[$parentid]['width'])) {
							// user specified width
							$cellw = $this->getHTMLUnitToUnits($dom[$parentid]['width'], $table_columns_width, 'px');
							$tmpcw = ($cellw / $colspan);
							for ($i = 0; $i < $colspan; ++$i) {
								$table_colwidths[($colid + $i)] = $tmpcw;
							}
						} else {
							// inherit column width
							$cellw = 0;
							for ($i = 0; $i < $colspan; ++$i) {
								$cellw += (isset($table_colwidths[($colid + $i)]) ? $table_colwidths[($colid + $i)] : 0);
							}
						}
						$cellw += (($colspan - 1) * $cellspacing['H']);
						// increment column indicator
						$colid += $colspan;
						// add rowspan information to table element
						if ($rowspan > 1) {
							$trsid = array_push($dom[$table_el]['rowspans'], array('trid' => $trid, 'rowspan' => $rowspan, 'mrowspan' => $rowspan, 'colspan' => $colspan, 'startpage' => $this->page, 'startcolumn' => $this->current_column, 'startx' => $this->x, 'starty' => $this->y));
						}
						$cellid = array_push($dom[$trid]['cellpos'], array('startx' => $this->x));
						if ($rowspan > 1) {
							$dom[$trid]['cellpos'][($cellid - 1)]['rowspanid'] = ($trsid - 1);
						}
						// push background colors
						if (isset($dom[$parentid]['bgcolor']) AND ($dom[$parentid]['bgcolor'] !== false)) {
							$dom[$trid]['cellpos'][($cellid - 1)]['bgcolor'] = $dom[$parentid]['bgcolor'];
						}
						// store border info
						if (!empty($tdborder)) {
							$dom[$trid]['cellpos'][($cellid - 1)]['border'] = $tdborder;
						}
						$prevLastH = $this->lasth;
						// store some info for multicolumn mode
						if ($this->rtl) {
							$this->colxshift['x'] = $this->w - $this->x - $this->rMargin;
						} else {
							$this->colxshift['x'] = $this->x - $this->lMargin;
						}
						$this->colxshift['s'] = $cellspacing;
						$this->colxshift['p'] = $current_cell_padding;
						// ****** write the cell content ******
            // dump($cell_content);
            // Lokasi mencetak <td>
						$this->MultiCell($cellw, $cellh, $cell_content, false, $lalign, false, 2, '', '', true, 0, true, true, 0, 'T', false);
						// restore some values
						$this->colxshift = array('x' => 0, 's' => array('H' => 0, 'V' => 0), 'p' => array('L' => 0, 'T' => 0, 'R' => 0, 'B' => 0));
						$this->lasth = $prevLastH;
						$this->cell_padding = $old_cell_padding;
						$dom[$trid]['cellpos'][($cellid - 1)]['endx'] = $this->x;
						// update the end of row position
						if ($rowspan <= 1) {
							if (isset($dom[$trid]['endy'])) {
								if (($this->page == $dom[$trid]['endpage']) AND ($this->current_column == $dom[$trid]['endcolumn'])) {
									$dom[$trid]['endy'] = max($this->y, $dom[$trid]['endy']);
								} elseif (($this->page > $dom[$trid]['endpage']) OR ($this->current_column > $dom[$trid]['endcolumn'])) {
									$dom[$trid]['endy'] = $this->y;
								}
							} else {
								$dom[$trid]['endy'] = $this->y;
							}
							if (isset($dom[$trid]['endpage'])) {
								$dom[$trid]['endpage'] = max($this->page, $dom[$trid]['endpage']);
							} else {
								$dom[$trid]['endpage'] = $this->page;
							}
							if (isset($dom[$trid]['endcolumn'])) {
								$dom[$trid]['endcolumn'] = max($this->current_column, $dom[$trid]['endcolumn']);
							} else {
								$dom[$trid]['endcolumn'] = $this->current_column;
							}
						} else {
							// account for row-spanned cells
							$dom[$table_el]['rowspans'][($trsid - 1)]['endx'] = $this->x;
							$dom[$table_el]['rowspans'][($trsid - 1)]['endy'] = $this->y;
							$dom[$table_el]['rowspans'][($trsid - 1)]['endpage'] = $this->page;
							$dom[$table_el]['rowspans'][($trsid - 1)]['endcolumn'] = $this->current_column;
						}
						if (isset($dom[$table_el]['rowspans'])) {
							// update endy and endpage on rowspanned cells
							foreach ($dom[$table_el]['rowspans'] as $k => $trwsp) {
								if ($trwsp['rowspan'] > 0) {
									if (isset($dom[$trid]['endpage'])) {
										if (($trwsp['endpage'] == $dom[$trid]['endpage']) AND ($trwsp['endcolumn'] == $dom[$trid]['endcolumn'])) {
											$dom[$table_el]['rowspans'][$k]['endy'] = max($dom[$trid]['endy'], $trwsp['endy']);
										} elseif (($trwsp['endpage'] < $dom[$trid]['endpage']) OR ($trwsp['endcolumn'] < $dom[$trid]['endcolumn'])) {
											$dom[$table_el]['rowspans'][$k]['endy'] = $dom[$trid]['endy'];
											$dom[$table_el]['rowspans'][$k]['endpage'] = $dom[$trid]['endpage'];
											$dom[$table_el]['rowspans'][$k]['endcolumn'] = $dom[$trid]['endcolumn'];
										} else {
											$dom[$trid]['endy'] = $this->pagedim[$dom[$trid]['endpage']]['hk'] - $this->pagedim[$dom[$trid]['endpage']]['bm'];
										}
									}
								}
							}
						}
						$this->x += ($cellspacingx / 2);
					} else {
						// opening tag (or self-closing tag)
						if (!isset($opentagpos)) {
							if ($this->inxobj) {
								// we are inside an XObject template
								$opentagpos = strlen($this->xobjects[$this->xobjid]['outdata']);
							} elseif (!$this->InFooter) {
								if (isset($this->footerlen[$this->page])) {
									$this->footerpos[$this->page] = $this->pagelen[$this->page] - $this->footerlen[$this->page];
								} else {
									$this->footerpos[$this->page] = $this->pagelen[$this->page];
								}
								$opentagpos = $this->footerpos[$this->page];
							}
						}

            if($revmark AND isset($dom[$key]['cgmarkid'])){
              $this->start_cgmark = $this->start_cgmark ?? [];
              // $this->start_cgmark[$key] = count($this->cgmark);
              $this->start_cgmark[$dom[$key]['cgmarkid']] = count($this->cgmark);
              $this->addCgMark(
                $this->start_cgmark[$dom[$key]['cgmarkid']], 
                $this->y, 
                null, 
                $this->getPage(), 
                $dom[$key]['attribute']['reasonforupdaterefids'], 
                $dom[$key]['value']); // assign first position ('st_pos_y')
            }

            if(isset($dom[$key]['attribute']['id'])){
              $this->addInternalReference($this->curEntry, $dom[$key]['attribute']['id'], $this->page, $this->y);
            }
						$dom = $this->openHTMLTagHandler($dom, $key, $cell);
					}
				} 
        else { // closing tag

          /** EDITED - Tambahan agar jika didalam paragrap berakhir dengan ul/ol, tidak diberi v space 2x tinggi text. 
           * tapi setelah di coba dengan html ol/ul yang ada border, berhasil
           * jika tidak diberi border, v space tidak ada sama sekali padahal butuh 1x vspace
           * kesimpulannya aneh sehingga ini tidak dipakai
          */
          // if(isset($dom[$key-1]) AND $dom[$key]['value'] == 'p' AND ($dom[$key-1]['value'] == 'ol' OR $dom[$key-1]['value'] == 'ul')){
            // $maxbottomliney -= $this->getCellHeight($fontsize);
          // }
					$prev_numpages = $this->numpages;
					$old_bordermrk = $this->bordermrk[$this->page];

          $parent_cgmarkid = $dom[$dom[$key]['parent']]['cgmarkid'] ?? null;
          if($revmark AND isset($parent_cgmarkid)){
            if(isset($this->start_cgmark[$parent_cgmarkid])){
              $this->addCgMark($this->start_cgmark[$parent_cgmarkid], null, ($this->y + $this->getLastH()), $this->getPage(), $dom[$dom[$key]['parent']]['attribute']['reasonforupdaterefids'], ''); // asign end position ('ed_pos_y')
            }
          }
          $dom = $this->closeHTMLTagHandler($dom, $key, $cell, $maxbottomliney);

					if ($this->bordermrk[$this->page] > $old_bordermrk) {
						$startlinepos += ($this->bordermrk[$this->page] - $old_bordermrk);
					}
					if ($prev_numpages > $this->numpages) {
						$startlinepage = $this->page;
					}

          
          // footnote #2
          // if($this->inFootnote AND !isset($reinstatefootnote)){
          //   $footnoteh = $this->y;
          //   $footnoteh +=  $this->lasth;
          //   $this->endTemplate();
          //   foreach($this->xobjects[$template]['dom'] as $k => $d){
          //     $dom[$k] = $d;
          //   }
          //   $keystart = $this->xobjects[$template]['domkeystart'];
          //   $key = $keystart;
          //   $dom[$key]['attribute']['isfootnote'] = 'false';
          //   unset($this->xobjects[$template]);
          //   unset($template);

          //   $reinstatefootnote = true;
          //   $template = $this->startTemplate($wfnt, $footnoteh);
            
          //   $this->x = $this->lMargin + $this->cell_padding['L'];

          //   $this->setFontSize(6);
          //   $txt = count($this->footnotes);
          //   $this->Write('',"[{$txt}]    ",'',false,'L',false,0,true, true,0,0);
          //   $this->lMargin += $this->GetStringWidth("[{$txt}]    ",$curfontname, $curfontstyle, $curfontsize);
          //   --$key;
          // }
          // elseif($this->inFootnote AND isset($reinstatefootnote)){
          //   $this->endTemplate();
          //   $startlinex = $this->lMargin;
          //   $minstartliney = $startliney;

          //   $this->y -= (((($curfontsize * $this->cell_height_ratio) - ($pfontsize * $dom[$dom[$key]['parent']]['line-height'])) / $this->k) + $curfontascent - $pfontascent - $curfontdescent + $pfontdescent) / 2;  
          //   $lastIndexfnt = count($this->footnotes) - 1;
          //   $this->footnotes[$lastIndexfnt]['template'][] = $template;
          //   $this->footnotes[$lastIndexfnt]['height'][] = $footnoteh;
          //   $this->inFootnote = false;
          //   $this->setFontSize($curfontsize);
          //   unset($reinstatefootnote);
          // }

          /** EDITTED - untuk tambah intentionally left blank atau page break */
          // saat di closing tag ini, jika parent (open tag) ada atribute @addintentionallyleftblank, maka..
          if(isset($dom[$dom[$key]['parent']]['attribute']['addintentionallyleftblank']) AND $dom[$dom[$key]['parent']]['attribute']['addintentionallyleftblank'] == 'true'){

            // jika tidak bisa ditambah intentionallyleftblank (karena page genap)
            if(!self::addIntentionallyLeftBlankPage($this)){
              $is_endpage = true;
              $i = $key;
              while($i <= $maxel){
                // determine if the page is at the end or not. If not the end, it is page break. if end, add Intentionally leftblank or not (jika page genap)
                // jika di next dom parentnya ada atribute @addintentionallyleftblank, maka.. bukan page terakhi, sehingga ditambah pagebreak saja untuk next title levelledPara
                if(isset($dom[$i+1]) AND isset($dom[$dom[$i+1]['parent']]['attribute']['addintentionallyleftblank']) AND $dom[$dom[$i+1]['parent']]['attribute']['addintentionallyleftblank'] == 'true'){
                  // dump($key."|".$this->page."|still exist at next page");
                  $is_endpage = false;
                  $this->checkPageBreak($this->PageBreakTrigger + 1);
                  $i = $maxel + 1;
                }
                $i++;
              }
              // tapi jika ini adalah halaman terakhir, page break tidak ditambah, tapi leftblank page ditambah (kalau page ganjil)
              if($is_endpage){
                self::addIntentionallyLeftBlankPage($this);
              }
            }
          }
				}
			}
      elseif (strlen($dom[$key]['value']) > 0) {
        // dump($key);
        // dump($dom[38]);
        // #2 untuk mencetak caption. Code ini berada di saat $key value = text
        if(
          ($dom[$dom[$key]['parent']]['value'] == 'span')
          AND isset($dom[$dom[$key]['parent']]['attribute']['captionline'])
          AND $dom[$dom[$key]['parent']]['attribute']['captionline'] == 'true'
          AND !isset($dom[$key]['opening'])
          )
        {
          $lh = $this->lasth;
          $value = $this->captionLineText;
          $height = isset($dom[$dom[$key]['parent']]['height']) ? $this->getHTMLUnitToUnits($dom[$dom[$key]['parent']]['height']) : $this->getCellHeight($this->FontSize, true);
          $calign = $dom[$dom[$key]['parent']]['attribute']['calign'] ?? 'B';
          $fillcolor = isset($dom[$dom[$key]['parent']]['attribute']['fillcolor']) ? $dom[$dom[$key]['parent']]['attribute']['fillcolor'] : '255,255,255,10';
          $textcolor = isset($dom[$dom[$key]['parent']]['attribute']['textcolor']) ? $dom[$dom[$key]['parent']]['attribute']['textcolor'] : '0,0,0';

          $fillcolor = explode(",",$fillcolor);
          $textcolor = explode(",",$textcolor);
          $cwa = ($this->w - $this->rMargin - $this->x - $this->cell_padding['L'] - $this->cell_padding['R']);
          if($captionWidth > $cwa){ // jika caption ada di ujung tulisan
            $this->x = $startlinex;
            $calign == 'B' ? ($this->y += $height) : (
            $calign == 'T' ? ($this->y += $this->stringHeight) : null);
            $cwa = ($this->w - $this->rMargin - $this->x - $this->cell_padding['L'] - $this->cell_padding['R']); 
          }
          if($captionWidth < $cwa){
            $calign == 'B' ? ($this->y += $this->stringHeight) : null;
            $this->setColor('fill', $fillcolor[0],$fillcolor[1],$fillcolor[2], $fillcolor[3] ?? -1);
            $this->setColor('text', $textcolor[0],$textcolor[1],$textcolor[2], $fillcolor[3] ?? -1);
            $this->Cell($captionWidth,$height,$value,1,0,'C',true,'',0,false,$calign);
            $calign == 'B' ? ($this->y -= $this->stringHeight) : null;

            $this->lasth = $lh;
            $key++;
            $this->captionLineHeight = true;

            $calign == 'T' ? ($this->lastCaptionLineHeight = $height - $this->stringHeight) : null;

            continue;
          }
        }// end #2
				// print list-item
				if (!TCPDF_STATIC::empty_string($this->lispacer) AND ($this->lispacer != '^')) {
					$this->setFont($pfontname, $pfontstyle, $pfontsize);
					$this->resetLastH();
					$minstartliney = $this->y;
					$maxbottomliney = ($startliney + $this->getCellHeight($this->FontSize)); // tes
					if (is_numeric($pfontsize) AND ($pfontsize > 0)) {
						$this->putHtmlListBullet($this->listnum, $this->lispacer, $pfontsize);
					}
					$this->setFont($curfontname, $curfontstyle, $curfontsize);
					$this->resetLastH();
					if (is_numeric($pfontsize) AND ($pfontsize > 0) AND is_numeric($curfontsize) AND ($curfontsize > 0) AND ($pfontsize != $curfontsize)) {
						$pfontascent = $this->getFontAscent($pfontname, $pfontstyle, $pfontsize);
						$pfontdescent = $this->getFontDescent($pfontname, $pfontstyle, $pfontsize);
						$this->y += ($this->getCellHeight(($pfontsize - $curfontsize) / $this->k) + $pfontascent - $curfontascent - $pfontdescent + $curfontdescent) / 2;
						$minstartliney = min($this->y, $minstartliney);
						$maxbottomliney = max(($this->y + $this->getCellHeight($pfontsize / $this->k)), $maxbottomliney);
					}
				}
				// text
				$this->htmlvspace = 0;
				$isRTLString = preg_match(TCPDF_FONT_DATA::$uni_RE_PATTERN_RTL, $dom[$key]['value']) || preg_match(TCPDF_FONT_DATA::$uni_RE_PATTERN_ARABIC, $dom[$key]['value']);
				if ((!$this->premode) AND $this->isRTLTextDir() AND !$isRTLString) {
					// reverse spaces order
					$lsp = ''; // left spaces
					$rsp = ''; // right spaces
					if (preg_match('/^('.$this->re_space['p'].'+)/'.$this->re_space['m'], $dom[$key]['value'], $matches)) {
						$lsp = $matches[1];
					}
					if (preg_match('/('.$this->re_space['p'].'+)$/'.$this->re_space['m'], $dom[$key]['value'], $matches)) {
						$rsp = $matches[1];
					}
					$dom[$key]['value'] = $rsp.$this->stringTrim($dom[$key]['value']).$lsp;
				}
        
				if ($newline) {  
          // #3 untuk menyetel posisi Y setelah text sebaris dengan caption
          if(isset($this->captionLineHeight) AND $this->captionLineHeight){
            $this->y += ($this->lastCaptionLineHeight ?? 0);
            $this->captionLineHeight = false;
          }
          // end #3

					if (!$this->premode) {
						$prelen = strlen($dom[$key]['value']);
						if ($this->isRTLTextDir() AND !$isRTLString) {
							// right trim except non-breaking space
							$dom[$key]['value'] = $this->stringRightTrim($dom[$key]['value']);
						} else {
              // left trim except non-breaking space
							$dom[$key]['value'] = $this->stringLeftTrim($dom[$key]['value']);
						}
						$postlen = strlen($dom[$key]['value']);
						if (($postlen == 0) AND ($prelen > 0)) {
              $dom[$key]['trimmed_space'] = true;
						}
					}
					$newline = false;
					$firstblock = true;
				} else {
					$firstblock = false;
					// replace empty multiple spaces string with a single space
					$dom[$key]['value'] = preg_replace('/^'.$this->re_space['p'].'+$/'.$this->re_space['m'], chr(32), $dom[$key]['value']);
				}
				$strrest = '';
				if ($this->rtl) {
					$this->x -= $this->textindent;
				} else {
					$this->x += $this->textindent;
				}
        
				if (!isset($dom[$key]['trimmed_space']) OR !$dom[$key]['trimmed_space']) { 
					$strlinelen = $this->GetStringWidth($dom[$key]['value']);
					if (!empty($this->HREF) AND (isset($this->HREF['url']))) {
						// HTML <a> Link
						$hrefcolor = '';
						if (isset($dom[($dom[$key]['parent'])]['fgcolor']) AND ($dom[($dom[$key]['parent'])]['fgcolor'] !== false)) {
							$hrefcolor = $dom[($dom[$key]['parent'])]['fgcolor'];
						}
						$hrefstyle = -1;
						if (isset($dom[($dom[$key]['parent'])]['fontstyle']) AND ($dom[($dom[$key]['parent'])]['fontstyle'] !== false)) {
							$hrefstyle = $dom[($dom[$key]['parent'])]['fontstyle'];
						}
						$strrest = $this->addHtmlLink($this->HREF['url'], $dom[$key]['value'], $wfill, true, $hrefcolor, $hrefstyle, true);
					} else {
						$wadj = 0; // space to leave for block continuity
						if ($this->rtl) {
							$cwa = ($this->x - $this->lMargin);
						} else {
							$cwa = ($this->w - $this->rMargin - $this->x);
						}

            // artinya ini adalah ketika str terakhir ditulis dalam sebuah block
            if (($strlinelen < $cwa) AND (isset($dom[($key + 1)])) AND ($dom[($key + 1)]['tag']) AND (!$dom[($key + 1)]['block'])) {
              // dump($key);

              // // footnote #0 - create footnoteRefTxt for filling up the citation
              // if(!$this->inFootnote){
              //   $v = $dom[$key]['value'];
              //   $v_n = preg_replace("/\[\?f\]/",'', $v);
              //   if($v != $v_n) {
              //     $dom[$key]['value'] = $v_n;
              //     $c = count($this->footnotes) + 1;
              //     $footnoteRefTxt = "<sup>[{$c}]</sup>&#160;";
              //   }
              //   if(!$this->inFootnote AND (($this->y + $this->footnoteheight + (2 * $this->getCellHeight($this->FontSize)) ) >= $this->PageBreakTrigger)){
              //     $undo = true;
              //     $dom[$key]['attribute']['nobr'] = 'false';
              //     --$key;
              //     $this->checkPageBreak($this->PageBreakTrigger + 1);
              //   }
              // }

              // // footnote #3
              // if(!$this->inFootnote){
              //   // dump($this->footnotes, $dom[$key]['value']);
              //   // dump($key);
              //   $qtyfnt = count($this->footnotes);
              //   for ($i= $qtyfnt-1; $i >= 0; $i--) {
              //     if(isset($this->footnotes[$i]) ){                    
              //       foreach ( $this->footnotes[$i]['template'] as $separated_footnote => $id){
              //         if(isset($this->footnotes[$i]['height'][$separated_footnote])){
              //           $hg = $this->footnotes[$i]['height'][$separated_footnote];
              //           $this->setFontSize($this->xobjects[$id]['gvars']['FontSizePt']);
                        
              //           $prevfntid = $prevfntid ?? null;
              //           if($prevfntid){
              //             $pattern = "/(?<=\/){$prevfntid}(?=\sDo)/";
              //             // dump($pattern, $id);
              //             // dump($this):
              //             // $pgbufr = preg_replace($pattern, $id, $this->pages[$this->page]);
              //             // $this->pages[$this->page] = $pgbufr;
              //           }

              //           $ypos = $this->PageBreakTrigger - $hg;
              //           $this->printTemplate($id,$this->lMargin, $ypos);
              //           unset($this->footnotes[$i]['template'][$separated_footnote]);
              //           $this->PageBreakTrigger -= $hg;
              //           if($dom[$key]['tag']) $key++;
              //           $prevfntid = $id;
              //           if($id == 'XT8'){
              //             # cari string <'/'.$id.' Do'> di buffer page yang sama, kemudian di tukar
              //             // dd($this);
              //             // $this->xobjects[$id]['gvars']['y'] -= 10;
              //           }
              //           // dump($this->xobjects);
              //           // dump($this->pages[$this->page]);
              //         }
              //       }
              //     }
              //     $this->setFontSize($curfontsize);
              //   }
              // }

							// check the next text blocks for continuity
							$nkey = ($key + 1);
							$write_block = true;
							$same_textdir = true;
							$tmp_fontname = $this->FontFamily;
							$tmp_fontstyle = $this->FontStyle;
							$tmp_fontsize = $this->FontSizePt;
							while ($write_block AND isset($dom[$nkey])) {
                break;
								if ($dom[$nkey]['tag']) {
									if ($dom[$nkey]['block']) {
										// end of block
										$write_block = false;
									}
									$tmp_fontname = isset($dom[$nkey]['fontname']) ? $dom[$nkey]['fontname'] : $this->FontFamily;
									$tmp_fontstyle = isset($dom[$nkey]['fontstyle']) ? $dom[$nkey]['fontstyle'] : $this->FontStyle;
									$tmp_fontsize = isset($dom[$nkey]['fontsize']) ? $dom[$nkey]['fontsize'] : $this->FontSizePt;
									$same_textdir = ($dom[$nkey]['dir'] == $dom[$key]['dir']);
								} else {
									$nextstr = TCPDF_STATIC::pregSplit('/'.$this->re_space['p'].'+/', $this->re_space['m'], $dom[$nkey]['value']);
									if (isset($nextstr[0]) AND $same_textdir) {
										$wadj += $this->GetStringWidth($nextstr[0], $tmp_fontname, $tmp_fontstyle, $tmp_fontsize);
										if (isset($nextstr[1])) {
											$write_block = false;
										}
									}
								}
								++$nkey;
							}
						}
						if (($wadj > 0) AND (($strlinelen + $wadj) >= $cwa)) {
							$wadj = 0;
							$nextstr = TCPDF_STATIC::pregSplit('/'.$this->re_space['p'].'/', $this->re_space['m'], $dom[$key]['value']);
							$numblks = count($nextstr);
							if ($numblks > 1) {
								// try to split on blank spaces
								$wadj = ($cwa - $strlinelen + $this->GetStringWidth($nextstr[($numblks - 1)]));
							} else {
								// set the entire block on new line
								$wadj = $this->GetStringWidth($nextstr[0]);
							}
						}
            
						// check for reversed text direction
						if (($wadj > 0) AND (($this->rtl AND ($this->tmprtl === 'L')) OR (!$this->rtl AND ($this->tmprtl === 'R')))) {
							// LTR text on RTL direction or RTL text on LTR direction
							$reverse_dir = true;
							$this->rtl = !$this->rtl;
							$revshift = ($strlinelen + $wadj + 0.000001); // add little quantity for rounding problems
							if ($this->rtl) {
								$this->x += $revshift;
							} else {
								$this->x -= $revshift;
							}
							$xws = $this->x;
						}

            /** EDITTED - tambahan untuk set y jika didepannya ada span height */
            // #1 untuk text sebelum spanheight, untuk menyetel y position sesuai caption height
            $this->stringHeight = $this->getCellHeight($this->FontSize, true);
            if(
              isset($dom[$key+1]['tag'])
              AND isset($dom[$key+1]['attribute']['captionline'])
              AND $dom[$key+1]['value'] == 'span'
              AND $dom[$key+1]['attribute']['captionline'] == 'true'
              )
            {
              $fname = $dom[$key+1]['fontname'];
              $fstyle = $dom[$key+1]['fontstyle'];
              $fsize = $dom[$key+1]['fontsize'];
              $calign = $dom[$key+1]['attribute']['calign'] ?? 'B';

              $captionLineText = '';
              $captionLineKey = $key+1;
              $i = 1;
              // untuk mengambil isi span text. Belum bisa mengakomodir jika didalam spantext ada text
              while((!$dom[$captionLineKey+$i]['tag'])){
                $captionLineText .= $dom[$captionLineKey+$i]['value'];
                if((!$dom[$captionLineKey+$i+1]['opening']) 
                    AND $dom[$captionLineKey+$i+1]['parent'] == $captionLineKey)
                {
                  $this->spanendtag = $captionLineKey+$i+1; 
                  break;
                }
                if($i >= $maxel){
                  break;
                }
                $i++;
              }              
              $this->captionLineText = $captionLineText;
              $captionWidth = !empty($dom[$key+1]['width']) ? $this->getHTMLUnitToUnits($dom[$key+1]['width']) : $this->GetStringWidth($captionLineText,$fname, $fstyle, $fsize);
              // jika text terakhir + panjang span <= available space ?
              if($strlinelen + $captionWidth <= $cwa){
                if($calign == 'B'){
                  $this->y += isset($dom[$key+1]['height']) ? ($this->getHTMLUnitToUnits($dom[$key+1]['height']) - $this->stringHeight) : $this->stringHeight; // tidak boleh ada "mm" di height nya
                }
              }
            }
            // $this->stringHeight = $this->getCellHeight($this->FontSize, true); // dipindah ke atas
            // end #1


            if ($strlinelen < $cwa){

              // footnote #1 - create footnoteRef
              $v = $dom[$key]['value'];
              $v_n = preg_replace("/\[\?f\]/",'', $v);
              if($v != $v_n) {
                $dom[$key]['value'] = $v_n;
                $c = count($this->footnotes) + 1;
                $footnoteRefTxt = "<span><sup>[{$c}]</sup>&#160;</span>";
              }

              if(($this->y + $this->footnoteheight + (2 * $this->getCellHeight($this->FontSize)) ) >= $this->PageBreakTrigger){
                $undo = true;
                $dom[$key]['attribute']['nobr'] = 'false';
                --$key;
                $this->checkPageBreak($this->PageBreakTrigger + 1);
              }

              // footnote #2 - create footnote xobject
              if(isset($dom[$key+1]['attribute']['id']) AND in_array($dom[$key+1]['attribute']['id'], array_keys($tmp_footnotesdom))){
                $kstartfnt = $key+1;

                $lastIndexfnt = count($this->footnotes) + 1;

                $ml = $tmp_footnotesdom[$dom[$key+1]['attribute']['id']]['html'];
                $lMargin = $this->lMargin;
                $rMargin = $this->rMargin;
                $this->lPadding = $this->cell_padding['L'];
                $this->rPadding = $this->cell_padding['R'];

                $template = $this->startTemplate($this->w,'');
                $this->lMargin = $lMargin;
                $this->rMargin = $rMargin;
                $this->cell_padding['L'] = $this->lPadding ;
                $this->cell_padding['R'] = $this->rPadding ;
                $this->x = $this->lMargin;

                $this->setFontSize(6);
                $this->Write('',"[{$lastIndexfnt}]    ",'',false,'J',false,0,true, true, 0, 0);
                $this->lMargin += $this->GetStringWidth("[{$lastIndexfnt}]    ",'helvetica', '', 6);

                $this->writeHTML("{$ml}", false,false);
                $footnoteh = $this->y + $this->lasth;
                $this->endTemplate();
                $this->resetLastH();
                unset($this->xobjects[$template]);

                $template = $this->startTemplate($this->w,$footnoteh);
                $this->lMargin = $lMargin ;
                $this->rMargin = $rMargin ;
                $this->cell_padding['L'] = $this->lPadding ;
                $this->cell_padding['R'] = $this->rPadding ;
                $this->x = $this->lMargin;

                $this->setFontSize(6);
                $this->Write('',"[{$lastIndexfnt}]    ",'',false,'L',false,0,true, true,0,0);
                $this->lMargin += $this->GetStringWidth("[{$lastIndexfnt}]    ",'helvetica', '', 6);

                $this->writeHTML("{$ml}", false,false);
                $this->endTemplate();

                $lastIndexfnt = count($this->footnotes) + 1;
                $this->footnotes[$lastIndexfnt]['id'][] = $dom[$key+1]['attribute']['id'];
                $this->footnotes[$lastIndexfnt]['template'][] = $template;
                $this->footnotes[$lastIndexfnt]['height'][] = $footnoteh;                
                
                $kk = $kstartfnt;
                while (isset($dom[$kk])){
                  $kk++;
                  if(!$dom[$kk]['tag'] AND !$dom[$key]['block']){
                    $dom[$kk]['value'] = '';
                  }
    
                  if($dom[$kk]['parent'] == $kstartfnt AND isset($dom[$kk]['opening']) AND !$dom[$kk]['opening']){
                    break;
                  }
                }
              }
            }

            // footnote #3 - printing footnoe
            $qtyfnt = count($this->footnotes);
            $yy = $this->y;
            for ($i= $qtyfnt; $i >= 0; $i--) {
              if(isset($this->footnotes[$i]) ){                    
                foreach ( $this->footnotes[$i]['template'] as $separated_footnote => $id){
                  if(isset($this->footnotes[$i]['height'][$separated_footnote])){
                    $hg = $this->footnotes[$i]['height'][$separated_footnote];
                    if($this->y + $hg + (2 * $this->getCellHeight($this->FontSize)) <= $this->PageBreakTrigger){
                      // dump($this->y + $hg + (2 * $this->getCellHeight($this->FontSize)) ."|".$this->PageBreakTrigger);
                      $ypos = $this->PageBreakTrigger - $hg;
                      $this->printTemplate($id,0, $ypos);
                      $this->PageBreakTrigger -= $hg;
                      unset($this->footnotes[$i]['template'][$separated_footnote]);                          
                      $this->y = $yy;
                    }        
                  }
                }
              }
            }

            $strrest = $this->Write($this->lasth, $dom[$key]['value'], '', $wfill, '', false, 0, true, $firstblock, 0, $wadj);
            
            // footnote #1 writing footnoteRefTxt
            if(isset($footnoteRefTxt)){
              $this->writeHTML($footnoteRefTxt,false);
              unset($footnoteRefTxt);
            }
            
            // restore default direction
						if ($reverse_dir AND ($wadj == 0)) {
							$this->x = $xws; // @phpstan-ignore-line
							$this->rtl = !$this->rtl;
							$reverse_dir = false;
						}
					}
				}
        
				$this->textindent = 0;
				if (strlen($strrest) > 0) {
					// store the remaining string on the previous $key position
					$this->newline = true;
					if ($strrest == $dom[$key]['value']) {
						// used to avoid infinite loop
						++$loop;
					}
           else {
						$loop = 0;
					}
					$dom[$key]['value'] = $strrest;
					if ($cell) {
						if ($this->rtl) {
							$this->x -= $this->cell_padding['R'];
						} else {
							$this->x += $this->cell_padding['L'];
						}
					}
					if ($loop < 3) {
						--$key; // selama $strrest != "" atau > 0, maka akan menjalankan if(strlen($dom[$key]['value']) > 0) diatas
					}
				} else {
					$loop = 0;
					// add the positive font spacing of the last character (if any)
					 if ($this->font_spacing > 0) {
					 	if ($this->rtl) {
							$this->x -= $this->font_spacing;
						} else {
							$this->x += $this->font_spacing;
						}
					}
				}
			}
			++$key;
			if (isset($dom[$key]['tag']) AND $dom[$key]['tag'] AND (!isset($dom[$key]['opening']) OR !$dom[$key]['opening']) AND isset($dom[($dom[$key]['parent'])]['attribute']['nobr']) AND ($dom[($dom[$key]['parent'])]['attribute']['nobr'] == 'true')) {
				// check if we are on a new page or on a new column
				if ((!$undo) AND (($this->y < $this->start_transaction_y) OR (($dom[$key]['value'] == 'tr') AND ($dom[($dom[$key]['parent'])]['endy'] < $this->start_transaction_y)))) {
					// we are on a new page or on a new column and the total object height is less than the available vertical space.
					// restore previous object
					$this->rollbackTransaction(true);
					// restore previous values
					foreach ($this_method_vars as $vkey => $vval) {
						$$vkey = $vval;
					}
					if (!empty($dom[$key]['thead'])) {
						$this->inthead = true;
					}
					// add a page (or trig AcceptPageBreak() for multicolumn mode)
					$pre_y = $this->y;
					if ((!$this->checkPageBreak($this->PageBreakTrigger + 1)) AND ($this->y < $pre_y)) {
						$startliney = $this->y;
					}
					$undo = true; // avoid infinite loop
				} else {
					$undo = false;
				}
			}
		} // end for each $key

    // $qtyfnt = count($this->footnotes);
    // $yy = $this->y;
    // for ($i= $qtyfnt-1; $i >= 0; $i--) {
    //   if(isset($this->footnotes[$i]) ){                    
    //     foreach ( $this->footnotes[$i]['template'] as $separated_footnote => $id){
    //       if(isset($this->footnotes[$i]['height'][$separated_footnote])){
    //         $hg = $this->footnotes[$i]['height'][$separated_footnote];
    //         if($this->y + $hg + (2 * $this->getCellHeight($this->FontSize)) <= $this->PageBreakTrigger){
    //           $ypos = $this->PageBreakTrigger - $hg;
    //           $this->printTemplate($id,0, $ypos);
    //           dump('printed');
    //           $this->PageBreakTrigger -= $hg;
    //           unset($this->footnotes[$i]['template'][$separated_footnote]);                          
    //           $this->y = $yy;
    //           // $printed = true;
    //         }

    //       }
    //     }
    //   }
    // }     
    // if(isset($printed)){
    //   dump($this->footnotes);
    // }
		// align the last line
		if (isset($startlinex)) {
			$yshift = ($minstartliney - $startliney);
			if (($yshift > 0) OR ($this->page > $startlinepage)) {
				$yshift = 0;
			}
			$t_x = 0;
			// the last line must be shifted to be aligned as requested
			$linew = abs($this->endlinex - $startlinex);
			if ($this->inxobj) {
				// we are inside an XObject template
				$pstart = substr($this->xobjects[$this->xobjid]['outdata'], 0, $startlinepos);
				if (isset($opentagpos)) {
					$midpos = $opentagpos;
				} else {
					$midpos = 0;
				}
				if ($midpos > 0) {
					$pmid = substr($this->xobjects[$this->xobjid]['outdata'], $startlinepos, ($midpos - $startlinepos));
					$pend = substr($this->xobjects[$this->xobjid]['outdata'], $midpos);
				} else {
					$pmid = substr($this->xobjects[$this->xobjid]['outdata'], $startlinepos);
					$pend = '';
				}
			} else {
				$pstart = substr($this->getPageBuffer($startlinepage), 0, $startlinepos);
				if (isset($opentagpos) AND isset($this->footerlen[$startlinepage]) AND (!$this->InFooter)) {
					$this->footerpos[$startlinepage] = $this->pagelen[$startlinepage] - $this->footerlen[$startlinepage];
					$midpos = min($opentagpos, $this->footerpos[$startlinepage]);
				} elseif (isset($opentagpos)) {
					$midpos = $opentagpos;
				} elseif (isset($this->footerlen[$startlinepage]) AND (!$this->InFooter)) {
					$this->footerpos[$startlinepage] = $this->pagelen[$startlinepage] - $this->footerlen[$startlinepage];
					$midpos = $this->footerpos[$startlinepage];
				} else {
					$midpos = 0;
				}
				if ($midpos > 0) {
					$pmid = substr($this->getPageBuffer($startlinepage), $startlinepos, ($midpos - $startlinepos));
					$pend = substr($this->getPageBuffer($startlinepage), $midpos);
				} else {
					$pmid = substr($this->getPageBuffer($startlinepage), $startlinepos);
					$pend = '';
				}
			}
			if ((((($plalign == 'C') OR (($plalign == 'R') AND (!$this->rtl)) OR (($plalign == 'L') AND ($this->rtl)))))) {
				// calculate shifting amount
				$tw = $w;
				if ($this->lMargin != $prevlMargin) {
					$tw += ($prevlMargin - $this->lMargin);
				}
				if ($this->rMargin != $prevrMargin) {
					$tw += ($prevrMargin - $this->rMargin);
				}
				$one_space_width = $this->GetStringWidth(chr(32));
				$no = 0; // number of spaces on a line contained on a single block
				if ($this->isRTLTextDir()) { // RTL
					// remove left space if exist
					$pos1 = TCPDF_STATIC::revstrpos($pmid, '[(');
					if ($pos1 > 0) {
						$pos1 = intval($pos1);
						if ($this->isUnicodeFont()) {
							$pos2 = intval(TCPDF_STATIC::revstrpos($pmid, '[('.chr(0).chr(32)));
							$spacelen = 2;
						} else {
							$pos2 = intval(TCPDF_STATIC::revstrpos($pmid, '[('.chr(32)));
							$spacelen = 1;
						}
						if ($pos1 == $pos2) {
							$pmid = substr($pmid, 0, ($pos1 + 2)).substr($pmid, ($pos1 + 2 + $spacelen));
							if (substr($pmid, $pos1, 4) == '[()]') {
								$linew -= $one_space_width;
							} elseif ($pos1 == strpos($pmid, '[(')) {
								$no = 1;
							}
						}
					}
				} else { // LTR
					// remove right space if exist
					$pos1 = TCPDF_STATIC::revstrpos($pmid, ')]');
					if ($pos1 > 0) {
						$pos1 = intval($pos1);
						if ($this->isUnicodeFont()) {
							$pos2 = intval(TCPDF_STATIC::revstrpos($pmid, chr(0).chr(32).')]')) + 2;
							$spacelen = 2;
						} else {
							$pos2 = intval(TCPDF_STATIC::revstrpos($pmid, chr(32).')]')) + 1;
							$spacelen = 1;
						}
						if ($pos1 == $pos2) {
							$pmid = substr($pmid, 0, ($pos1 - $spacelen)).substr($pmid, $pos1);
							$linew -= $one_space_width;
						}
					}
				}
				$mdiff = ($tw - $linew);
				if ($plalign == 'C') {
					if ($this->rtl) {
						$t_x = -($mdiff / 2);
					} else {
						$t_x = ($mdiff / 2);
					}
				} elseif ($plalign == 'R') {
					// right alignment on LTR document
					$t_x = $mdiff;
				} elseif ($plalign == 'L') {
					// left alignment on RTL document
					$t_x = -$mdiff;
				}
			} // end if startlinex
			if (($t_x != 0) OR ($yshift < 0)) {
				// shift the line
				$trx = sprintf('1 0 0 1 %F %F cm', ($t_x * $this->k), ($yshift * $this->k));
				$pstart .= "\nq\n".$trx."\n".$pmid."\nQ\n";
				$endlinepos = strlen($pstart);
				if ($this->inxobj) {
					// we are inside an XObject template
					$this->xobjects[$this->xobjid]['outdata'] = $pstart.$pend;
					foreach ($this->xobjects[$this->xobjid]['annotations'] as $pak => $pac) {
						if ($pak >= $pask) {
							$this->xobjects[$this->xobjid]['annotations'][$pak]['x'] += $t_x;
							$this->xobjects[$this->xobjid]['annotations'][$pak]['y'] -= $yshift;
						}
					}
				} else {
					$this->setPageBuffer($startlinepage, $pstart.$pend);
					// shift the annotations and links
					if (isset($this->PageAnnots[$this->page])) {
						foreach ($this->PageAnnots[$this->page] as $pak => $pac) {
							if ($pak >= $pask) {
								$this->PageAnnots[$this->page][$pak]['x'] += $t_x;
								$this->PageAnnots[$this->page][$pak]['y'] -= $yshift;
							}
						}
					}
				}
				$this->y -= $yshift;
				$yshift = 0;
			}
		}
		// restore previous values
		$this->setGraphicVars($gvars);
		if ($this->num_columns > 1) {
			$this->selectColumn();
		} elseif ($this->page > $prevPage) {
			$this->lMargin = $this->pagedim[$this->page]['olm'];
			$this->rMargin = $this->pagedim[$this->page]['orm'];
		}
		// restore previous list state
		$this->cell_height_ratio = $prev_cell_height_ratio;
		$this->listnum = $prev_listnum;
		$this->listordered = $prev_listordered;
		$this->listcount = $prev_listcount;
		$this->lispacer = $prev_lispacer;
		if ($ln AND (!($cell AND ($dom[$key-1]['value'] == 'table')))) {
			$this->Ln($this->lasth);
			if (($this->y < $maxbottomliney) AND ($startlinepage == $this->page)) {
				$this->y = $maxbottomliney;
			}
		}
		unset($dom);
	}

  /**
   * tambahanya hanya menambah tulisan intentionally left blank saja, tapi tidak jadi
	 * Add page if needed.
	 * @param float $h Cell height. Default value: 0.
	 * @param float|null $y starting y position, leave empty for current position.
	 * @param bool  $addpage if true add a page, otherwise only return the true/false state
	 * @return bool true in case of page break, false otherwise.
	 * @since 3.2.000 (2008-07-01)
	 * @protected
	 */
	protected function checkPageBreak($h=0, $y=null, $addpage=true) {
		if (TCPDF_STATIC::empty_string($y)) {
			$y = $this->y;
		}
		$current_page = $this->page;

		if ((($y + $h) > $this->PageBreakTrigger) AND ($this->inPageBody()) AND ($this->AcceptPageBreak())) {
			if ($addpage) {
				//Automatic page break
				$x = $this->x;
				$this->AddPage($this->CurOrientation);        
				$this->y = $this->tMargin;
				$oldpage = $this->page - 1;
        if(!empty($this->tes) AND $this->tes){
          // dump($oldpage . "|" .$current_page. "|". $y, $this->PageBreakTrigger);
        }
				if ($this->rtl) {
					if ($this->pagedim[$this->page]['orm'] != $this->pagedim[$oldpage]['orm']) {
						$this->x = $x - ($this->pagedim[$this->page]['orm'] - $this->pagedim[$oldpage]['orm']);
					} else {
						$this->x = $x;
					}
				} else {
					if ($this->pagedim[$this->page]['olm'] != $this->pagedim[$oldpage]['olm']) {
						$this->x = $x + ($this->pagedim[$this->page]['olm'] - $this->pagedim[$oldpage]['olm']);
					} else {
						$this->x = $x;
					}
				}
			}
			return true;
		}
		if ($current_page != $this->page) {
			// account for columns mode
			return true;
		}
		return false;
	}
}