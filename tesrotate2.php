<?php

require __DIR__ . '/vendor/autoload.php';

use Ptdi\Mpub\Object\DML;
use Ptdi\Mpub\Object\DModule;

use Ptdi\Mpub\Pdf2\PMC_PDF;

$pdf = new TCPDF('P','mm','A5');
$pdf->AddPage();

$n = 0;

$html_start = 
'<div>
  <style>
    table, th, td {
      border:1px solid red;
    }
  </style>
  <table rotate="90">
    <thead>';
    
    function get_tr(){
      global $n;
      $n += 1;
      return " <tr>
          <th>$n foo</th>
        </tr>";
    }
$html_end = "</thead>
  </table>
</div>";

$html = $html_start;

for ($i=0; $i < 50; $i++) { 
  $html .= get_tr();
}

$html .= $html_end;

// $html = <<<EOD
// <div>
//   <style>
//     table, th, td {
//       border:1px solid red;
//     }
//   </style>
//   <table rotate="90">
//     <thead>
//       <tr>
//         <th>foo</th>
//       </tr>
//     </thead>
//   </table>
// </div>
// EOD;

// dd($html);

// $pdf->writeHTML($html, true, false, true, false, '');
// dump($pdf->GetX(), $pdf->GetY());
// dump($pdf->getPageHeight() - $pdf->getMargins()['top'] - $pdf->getMargins()['bottom']);

// $ah = $pdf->getPageHeight() - $pdf->getMargins()['top'] - $pdf->getMargins()['bottom'];
// $aw = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
// // dd($aw, $ah);
// $pdf->StartTransform();
// // Rotate 90 degrees counter-clockwise
// $pdf->Rotate(90,$ah/2, $aw/2);
// $pdf->writeHTML($html, true,false,true);
// // Stop Transformation
// $pdf->StopTransform();

$pdf->writeHTML($html, true,false,true, false, '', true);

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output('example_006.pdf', 'I');
