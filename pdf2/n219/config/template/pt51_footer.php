<?php

$documentNumber = $this->documentNumber;
$aa_approved = (isset($this->aa_approved['name']) ? $this->aa_approved['name'] . ":" : '') . ($this->aa_approved['date'] ?? '');

$footer_even = <<<EOD
<table style="width:100%;font-size:9;">
  <tr>
    <td align="left">{$documentNumber}</td>
    <td align="right"></td>
  </tr>
  <tr>
    <td align="left">Page {$this->prefix_pagenum}{$this->getPageNumGroupAlias()}</td>
    <td align="right">{$aa_approved}</td>
  </tr>
</table>
EOD;

$footer_odd = <<<EOF
<table style="width:108%;font-size:9;">
  <tr>
    <td align="left"></td>
    <td align="right">{$documentNumber}&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;</td>
  </tr>
  <tr>
    <td align="left">{$aa_approved}</td>
    <td align="right" style="">Page {$this->prefix_pagenum}{$this->getPageNumGroupAlias()}</td>
  </tr>
</table>
EOF;

return [
  "odd" => $footer_odd,
  "even" => $footer_even,
];