<?php

$documentNumber = $this->documentNumber;
// $aa_approved = (isset($this->aa_approved['name']) ? $this->aa_approved['name'] . ":" : '') . ($this->aa_approved['date'] ?? '');

$issueDate = $this->dm_issueDate_rendering ?? '';
$footer_even = <<<EOD
<table style="width:100%;font-size:9;">
  <tr>
    <td align="left">&#160;</td>
    <td align="right">&#160;</td>
  </tr>
  <tr>
    <td align="left">Page {$this->prefix_pagenum}{$this->getPageNumGroupAlias()}</td>
    <td align="right">{$issueDate}</td>
  </tr>
</table>
EOD;

$footer_odd = <<<EOF
<table style="width:108%;font-size:9;">
  <tr>
    <td align="left">&#160;</td>
    <td align="right">&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;</td>
  </tr>
  <tr>
    <td align="left">Issue date: {$issueDate}</td>
    <td align="right" style="">Page {$this->prefix_pagenum}{$this->getPageNumGroupAlias()}</td>
  </tr>
</table>
EOF;

return [
  "odd" => $footer_odd,
  "even" => $footer_even,
];