<?php

$headerLogo = $this->getAssetPath().DIRECTORY_SEPARATOR.$this->headerLogo;
$headerText = $this->headerText;
$headerTitle = $this->headerTitle;

// Tidak boleh ada space atau enter antara <td> dan <div>

$header_even = <<<EOD
<table style="width:100%;font-size:10;">
  <tr>
    <td align="left" style="width:30%;"><div>{$headerText}</div>
    </td>
    <td align="center" style="width:40%;"><div>{$headerTitle}</div>
    </td>
    <td align="left" style="width:30%;"><img src="{$headerLogo}"/>
    </td>
  </tr>
</table>
EOD;

$header_odd = <<<EOF
<table style="width:100%;font-size:10;">
  <tr>
    <td align="left" style="width:30%;"><img src="{$headerLogo}"/>
    </td>
    <td align="center" style="width:40%;"><div>{$headerTitle}</div>
    </td>
    <td align="right" style="width:30%;"><div>{$headerText}</div>
    </td>
  </tr>
</table>
EOF;

return [
  "odd" => $header_odd,
  "even" => $header_even,
];