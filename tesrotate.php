<?php

require __DIR__ . '/vendor/autoload.php';

use Ptdi\Mpub\Object\DML;
use Ptdi\Mpub\Object\DModule;

use Ptdi\Mpub\Pdf2\PMC_PDF;
// use TCPDF;

// const CON = 'AAA';
define ('CON', 'AAAAA');
// const a = true;
// a = false;
// dd(a);
// dd(defined('a'));
// defined("AAA");
// dump(defined("AAA"));
// dd(defined("AAA"));
// dd(defined('CON'));

// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF
{
  public $xywalter = array();

  //Page header
  public function Test($ae)
  {
    if (!isset($this->xywalter)) {
      $this->xywalter = array();
    }
    $this->xywalter[] = array($this->GetX(), $this->GetY());
  }
}

// create new PDF document
$pdf = new MYPDF('L', PDF_UNIT, 'A1', true, 'UTF-8', false);

// set Rotate
$params = $pdf->serializeTCPDFtagParameters(array(90));
// other configs
$pdf->setOpenCell(0);
$pdf->SetCellPadding(0);
$pdf->setCellHeightRatio(1.25);

$pdf->AddPage();

// create some HTML content
$html = '<table width="100%" border="1" cellspacing="0" cellpadding="5">
<thead>
<tr bgcolor="#E6E6E6">
  <th rowspan="2" width="15%" align="center">ATIVIDADES E PROCESSOS</th>
  <th rowspan="2" width="10%" align="center" valign="bottom">ASPECTOS</th>
  <th rowspan="2" width="10%" align="center">IMPACTOS</th>
  <th colspan="3" width="6%" align="center">MEIO</th>
  <th rowspan="2" width="3%" align="center"><tcpdf method="Test" params="' . $params . '" /></th>
  <th colspan="9" width="18%" align="center">CLASSIFICA&Ccedil;&Otilde;ES</th>
  <th rowspan="2" width="3%" align="center"><tcpdf method="Test" params="' . $params . '" /></th>
  <th rowspan="2" width="10%" align="center">PROGRAMA</th>
  <th rowspan="2" width="10%" align="center">SUBPROGRAMA</th>
  <th rowspan="2" width="15%" align="center">A&Ccedil;&Otilde;ES DE CONTROLE, MEDIDAS MITIGADORAS, COMPENSAT&Oacute;RIAS E POTENCIALIZADORAS</th>
</tr>
<tr bgcolor="#E6E6E6">
  <th align="center" height="200"><tcpdf method="Test" params="' . $params . '" /></th>
  <th align="center"><tcpdf method="Test" params="' . $params . '" /></th>
  <th align="center"><tcpdf method="Test" params="' . $params . '" /></th>
  <th align="center"><tcpdf method="Test" params="' . $params . '" /></th>
  <th align="center"><tcpdf method="Test" params="' . $params . '" /></th>
  <th align="center"><tcpdf method="Test" params="' . $params . '" /></th>
  <th align="center"><tcpdf method="Test" params="' . $params . '" /></th>
  <th align="center"><tcpdf method="Test" params="' . $params . '" /></th>
  <th align="center"><tcpdf method="Test" params="' . $params . '" /></th>
  <th align="center"><tcpdf method="Test" params="' . $params . '" /></th>
  <th align="center"><tcpdf method="Test" params="' . $params . '" /></th>
  <th align="center"><tcpdf method="Test" params="' . $params . '" /></th>
</tr>
</thead>
<tr bgcolor="#E6E6E6">
<td colspan="20" align="center">Planejamento</td>
</tr>
<tr bgcolor="#FFFFFF">
<td rowspan="3" width="15%" align="left" bgcolor="#FFFFFF">Divulga&ccedil;&atilde;o do empreendimento</td>
<td rowspan="2" width="10%" align="left">Oferta de empregos diretos e indiretos</td>
<td rowspan="2" width="10%" align="left">Atra&ccedil;&atilde;o de popula&ccedil;&atilde;o para as comunidades do entorno</td>
<td rowspan="2" width="2%" align="center"></td>
<td rowspan="2" width="2%" align="center"></td>
<td rowspan="2" width="2%" align="center">X</td>
<td rowspan="2" width="3%" align="center">AII</td>
<td rowspan="2" width="2%" align="center">-</td>
<td rowspan="2" width="2%" align="center">Ind</td>
<td rowspan="2" width="2%" align="center">T</td>
<td rowspan="2" width="2%" align="center">Mp</td>
<td rowspan="2" width="2%" align="center">Po</td>
<td rowspan="2" width="2%" align="center">D</td>
<td rowspan="2" width="2%" align="center">R</td>
<td rowspan="2" width="2%" align="center">M</td>
<td rowspan="2" width="2%" align="center">M</td>
<td rowspan="2" width="3%" align="center">M</td>
<td width="10%">Programa de Apoio ao Desenvolvimento Socioeconomico da Regi&atilde;o</td>
<td width="10%">Subprograma de Apoio ao Desenvolvimento Habitacional</td>
<td width="15%">Coibir ocupa&ccedil;&atilde;o indevida de &aacute;reas inadequadas</td>
</tr>
<tr bgcolor="#FFFFFF">
<td>Programa de Comunica&ccedil;&atilde;o Social</td>
<td> -</td>
<td>A&ccedil;&otilde;es de comunica&ccedil;&atilde;o sobre o empreendimento e quest&otilde;es ambientais</td>
</tr>
<tr bgcolor="#FFFFFF">
<td align="left">Gera&ccedil;&atilde;o de expectativas na popula&ccedil;&atilde;o</td>
<td align="left">Gera&ccedil;&atilde;o de expectativas junto a popula&ccedil;&atilde;o</td>
<td align="center"></td>
<td align="center"></td>
<td align="center">X</td>
<td align="center">AII</td>
<td align="center">-</td>
<td align="center">Dir</td>
<td align="center">T</td>
<td align="center">Im</td>
<td align="center">Co</td>
<td align="center">L</td>
<td align="center">R</td>
<td align="center">P</td>
<td align="center">P</td>
<td align="center">B</td>
<td>Programa de Comunica&ccedil;&atilde;o Social</td>
<td> -</td>
<td>A&ccedil;&otilde;es de comunica&ccedil;&atilde;o sobre o empreendimento e questoes ambientais</td>
</tr>
</table>';

// $pdf->AddPage();

// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

// array with names of columns
$arr_nomes = array(
  array("ABRANGÃŠNCIA", 8, 59), // array(name, new X, new Y);
  array("SIGNIFICÃ‚NCIA", 8, 59),
  array("FÃSICO", 4, 52),
  array("BIÃ“TICO", 4, 52),
  array("SOCIOECONÃ”MICO", 4, 52),
  array("NATUREZA", 4, 52),
  array("ORIGEM", 4, 52),
  array("DURAÃ‡ÃƒO", 4, 52),
  array("OCORRÃŠNCIA / TEMPORALIDADE", 4, 52),
  array("FREQUÃŠNCIA", 4, 52),
  array("ESPACIALIZAÃ‡ÃƒO", 4, 52),
  array("REVERSIBILIDADE", 4, 52),
  array("MAGNITUDE", 4, 52),
  array("RELEVÃ‚NCIA", 4, 52)
);

// num of pages
$ttPages = $pdf->getNumPages();
for ($i = 1; $i <= $ttPages; $i++) {
  // set page
  $pdf->setPage($i);
  // all columns of current page
  foreach ($arr_nomes as $num => $arrCols) {
    if(!empty($pdf->xywalter)){
      $x = $pdf->xywalter[$num][0] + $arrCols[1]; // new X
      $y = $pdf->xywalter[$num][1] + $arrCols[2]; // new Y
      $n = $arrCols[0]; // column name
      // transforme Rotate
      $pdf->StartTransform();
      // Rotate 90 degrees counter-clockwise
      $pdf->Rotate(90, $x, $y);
      $pdf->Text($x, $y, $n);
      // Stop Transformation
      $pdf->StopTransform();
    }
  }
}

// reset pointer to the last page
$pdf->lastPage();

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output('example_006.pdf', 'I');
