<?php

// $modelIdentCode = strtolower(Ptdi\Mpub\CSDB::get_modelIdentCode($this->DOMDocument));
// $headerLogo = $this->getAssetPath()."/../pdf2/{$modelIdentCode}/assets/Logo-PTDI.jpg";
// $headerText = $this->headerText;
// $headerTitle = $this->headerTitle;


// Tidak boleh ada space atau enter antara <td> dan <div>

// $pmTitle = explode('-',$this->pmTitle);
// $pmTitle = explode('-','FRONTMATTER');
// $title1 = trim($pmTitle[0]);
// if(!in_array(explode(" ", $title1), ['SECTION', 'section', 'Section'])){
//   $title2 = $title1;
//   $title1 = '';
// } else {
//   $title2 = trim($pmTitle[1]);
// }

$title1 = strtoupper($this->pmTitle);
$title2 = $this->shortPmTitle ? strtoupper($this->shortPmTitle) : '';
$responsiblePartnerCompany = strtoupper($this->responsiblePartnerCompany);
$aircraft = "MALE";
$header_even = <<<EOD
<table style="width:100%;font-size:9pt;border-bottom:2px solid grey">
<tr>
  <td style="width:45%;text-align:left">{$responsiblePartnerCompany}</td>
  <td style="width:55%;text-align:right;font-weight:bold">{$title1}</td>
</tr>
<tr>
  <td style="width:45%;text-align:left">{$aircraft}</td>
  <td style="width:55%;font-weight:bold;text-align:right">{$title2}</td>
</tr>
<tr>
  <td style="line-height:0.3"></td>
</tr>
</table>
EOD;


$header_odd = <<<EOF
<table style="width:100%;font-size:9pt;border-bottom:2px solid grey">
<tr>
  <td style="width:55%;text-align:left;font-weight:bold">{$title1}</td>
  <td style="width:45%;text-align:right">{$responsiblePartnerCompany}</td>
</tr>
<tr>
  <td style="width:55%;font-weight:bold">{$title2}</td>
  <td style="width:45%;text-align:right">{$aircraft}</td>
</tr>
<tr>
  <td style="line-height:0.3"></td>
</tr>
</table>
EOF;

return [
  "odd" => $header_odd,
  "even" => $header_even,
];