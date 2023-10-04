<?php

/**
 * Campuran antara example_023 (page group) and example_045 (TOC) yang sudah di edit tcpdf nya
 */


//============================================================+
// File name   : example_023.php
// Begin       : 2008-03-04
// Last Update : 2013-05-14
//
// Description : Example 023 for TCPDF class
//               Page Groups
//
// Author: Nicola Asuni
//
// (c) Copyright:
//               Nicola Asuni
//               Tecnick.com LTD
//               www.tecnick.com
//               info@tecnick.com
//============================================================+

/**
 * Creates an example PDF TEST document using TCPDF
 * @package com.tecnick.tcpdf
 * @abstract TCPDF - Example: Page Groups.
 * @author Nicola Asuni
 * @since 2008-03-04
 */

// Include the main TCPDF library (search for installation path).
// require_once('tcpdf_include.php');
require __DIR__.'/vendor/autoload.php';

// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// $pdf->setBooklet(true);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Nicola Asuni');
$pdf->SetTitle('TCPDF Example 023');
$pdf->SetSubject('TCPDF Tutorial');
$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

// set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 023', PDF_HEADER_STRING);

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
    require_once(dirname(__FILE__).'/lang/eng.php');
    $pdf->setLanguageArray($l);
}

// ---------------------------------------------------------

// set font
$pdf->SetFont('times', 'BI', 14);

// set some text to print
$txt = <<<EOD
Example of page groups.
Check the page numbers on the page footer.

This is the first page of group 1.
EOD;


// Start First Page Group
$pdf->startPageGroup();
// add a page
$pdf->AddPage();
// $pdf->Bookmark('group 1.bar', 0);
// print a block of text using Write()
$pdf->Bookmark('group 1', 0); // level 0 tidak di tulis di TOC karena khusus 
$pdf->Bookmark('group 1.foo', 1);
// $pdf->Bookmark('group 1.fooxx', 0);
$pdf->Write(0, $txt, '', 0, 'L', true, 0, false, false, 0);
// add second page
$pdf->AddPage();
$pdf->Cell(0, 10, 'This is the second page of group 1', 0, 1, 'L');
$pdf->AddPage();
$pdf->Cell(0, 10, 'This is the second page of group 1', 0, 1, 'L');

$pdf->addTOCPage();
$pdf->SetFont('times', 'B', 16);
$pdf->MultiCell(0, 0, 'Table Of Content 1', 0, 'C', 0, 1, '', '', true, 0);
$pdf->Ln();
$pdf->SetFont('dejavusans', '', 12);
$pdf->addTOC(1, 'courier', '.', 'INDEX', 'B', array(128,0,0));
$pdf->endTOCPage();

// dd($pdf->getPage());

// Start Second Page Group
$endPageGroup = $pdf->getPage();

$pdf->startPageGroup();
$pdf->AddPage();
$pdf->Bookmark('group 2', 0);
$pdf->Bookmark('group 2.foo',1);
$pdf->Cell(0, 10, 'This is the first page of group 2', 0, 1, 'L');
$pdf->AddPage();
$pdf->Cell(0, 10, 'This is the second page of group 2', 0, 1, 'L');
$pdf->AddPage();
$pdf->Cell(0, 10, 'This is the third page of group 2', 0, 1, 'L');
$pdf->AddPage();
$pdf->Cell(0, 10, 'This is the fourth page of group 2', 0, 1, 'L');
// $pdf->endPage();

$pdf->addTOCPage();
$pdf->SetFont('times', 'B', 16);
$pdf->MultiCell(0, 0, 'Table Of Content 2', 0, 'C', 0, 1, '', '', true, 0);
$pdf->Ln();
$pdf->SetFont('dejavusans', '', 12);
$pdf->addTOC($endPageGroup+1, 'courier', '.', 'INDEX2', 'B', array(128,0,0));
$pdf->endTOCPage();

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output('example_023.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+