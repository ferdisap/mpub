<?php

// $modelIdentCode = strtolower(Ptdi\Mpub\CSDB::get_modelIdentCode($this->DOMDocument));
// $headerLogo = $this->getAssetPath()."/../pdf2/{$modelIdentCode}/assets/Logo-PTDI.jpg";
// $headerText = $this->headerText;
// $headerTitle = $this->headerTitle;


// Tidak boleh ada space atau enter antara <td> dan <div>

// $pmTitle = explode('-',$this->pmTitle);
$pmTitle = explode('-','FRONTMATTER');
$pmTitle1 = trim($pmTitle[0]);
if(!in_array(explode(" ", $pmTitle1), ['SECTION', 'section', 'Section'])){
  $pmTitle2 = $pmTitle1;
  $pmTitle1 = '';
} else {
  $pmTitle2 = trim($pmTitle[1]);
}

$header_even = <<<EOD
<table style="width:100%;font-size:9pt;border-bottom:2px solid grey">
<tr>
  <td style="width:70%;text-align:left">PT. DIRGANTARA INDONESIA</td>
  <td style="width:30%;text-align:right">{$pmTitle1}</td>
</tr>
<tr>
  <td style="width:30%;text-align:left">PUNA MALE</td>
  <td style="width:70%;font-weight:bold;text-align:right">{$pmTitle2}</td>
</tr>
</table>
EOD;


$header_odd = <<<EOF
<table style="width:100%;font-size:9pt;border-bottom:2px solid grey">
<tr>
  <td style="width:30%;text-align:left">{$pmTitle1}</td>
  <td style="width:70%;text-align:right">PT. DIRGANTARA INDONESIA</td>
</tr>
<tr>
  <td style="width:70%;font-weight:bold">{$pmTitle2}</td>
  <td style="width:30%;text-align:right">PUNA MALE</td>
</tr>
</table>
EOF;

return [
  "odd" => $header_odd,
  "even" => $header_even,
];