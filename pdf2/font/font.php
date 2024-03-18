<?php

namespace Ptdi\Mpub\Pdf2;

use TCPDF_FONTS;

function font_path()
{
  return __DIR__;
}

function add_font(array $fontFamily = [])
{
  foreach ($fontFamily as $font) {
    TCPDF_FONTS::addTTFfont(__DIR__ . "../../font/{$font}.ttf", '', '', 32); // akan me write file di tecnickcom/tcpdf/fonts/...
    TCPDF_FONTS::addTTFfont(__DIR__ . "../../font/{$font}b.ttf", '', '', 32); // akan me write file di tecnickcom/tcpdf/fonts/...
    TCPDF_FONTS::addTTFfont(__DIR__ . "../../font/{$font}i.ttf", '', '', 32); // akan me write file di tecnickcom/tcpdf/fonts/...
    TCPDF_FONTS::addTTFfont(__DIR__ . "../../font/{$font}bi.ttf", '', '', 32); // akan me write file di tecnickcom/tcpdf/fonts/..
  }
}
