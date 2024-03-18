<?php

namespace Ptdi\Mpub\Main;

class ICNDocument
{
  protected array $fileinfo;
  protected string $filename;
  protected string $path;
  
  public function load(string $filename)
  {
    $getID3 = new \getID3();
    $fileinfo = $getID3->analyze($filename);
    $this->fileinfo = $fileinfo;
    $this->filename = $filename;
    $this->path = $fileinfo['filepath'];
  }

  public function getFile($option = '')
  {
    switch ($option) {
      case '':
        return (file_get_contents($this->fileinfo['filepath']. DIRECTORY_SEPARATOR. $this->filename));
        break;
      case 'base64':
        return base64_encode((file_get_contents($this->fileinfo['filepath']. DIRECTORY_SEPARATOR. $this->filename)));
        break;
      case 'SplFileInfo':
        // return = new \SplFileInfo($this->getFileinfo()['filepath'] . "/". $doc->getFilename());
        return new \SplFileInfo($this->path . "/". $this->filename);      
      default:
        # code...
        break;
    }
  }

  public function getFileinfo(){
    return $this->fileinfo;
  }

  public function changeFilename($filename){
    $this->filename = $filename;
  }

  public function getFilename(){
    return $this->filename;
  }
}
