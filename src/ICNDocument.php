<?php

namespace Ptdi\Mpub;

require './vendor/james-heinrich/getid3/getid3/getid3.php';

class ICNDocument extends CSDB
{
  protected array $getID3;
  
  public function load($path, $filename)
  {
    $getID3 = new \getID3();
    $fileInfo = $getID3->analyze($path . DIRECTORY_SEPARATOR . $filename);
    $this->getID3 = $fileInfo;
  }
}
