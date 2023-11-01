<?php

$documentNumber = $this->documentNumber;
// $aa_approved = (isset($this->aa_approved['name']) ? $this->aa_approved['name'] . ":" : '') . ($this->aa_approved['date'] ?? '');

$issueDate = $this->dm_issueDate_rendering ?? '';

$controlAuthorityhtml = $this->getControlAuthority('cat51'); // cat51 adalah manual approved

$footer_even = <<<EOD
<table style="width:100%;font-size:9;border-top:2px solid grey">
  <tr>
    <td style="border-top:2px solid grey;width:100%;line-height:0.3"></td>
  </tr>
  <tr>
    <td align="left" style="width:30%">Page {$this->prefix_pagenum}{$this->getPageNumGroupAlias()}</td>
    <td align="left" style="width:40%">&#160;{$controlAuthorityhtml}</td>
    <td align="right" style="width:30%">Issue date: {$issueDate}</td>
  </tr>
</table>
EOD;

$footer_odd = <<<EOF
<table style="width:100%;font-size:9;">
  <tr>
    <td style="border-top:2px solid grey;width:100%;line-height:0.3"></td>
  </tr>
  <tr>
    <td align="left" style="width:30%">Issue date: {$issueDate}</td>
    <td align="right" style="width:40%">&#160;{$controlAuthorityhtml}</td>
    <td align="right" style="width:36%">Page {$this->prefix_pagenum}{$this->getPageNumGroupAlias()}</td>
  </tr>
</table>
EOF;

return [
  "odd" => $footer_odd,
  "even" => $footer_even,
];