<?php 

namespace Ptdi\Mpub\Object;

use Exception;
use Ptdi\Mpub\Object\DModule;

class ACT extends DModule {

  public $CCT;
  public $PCT;
  public array $productAttributes;

  public function __construct(string $filename)
  {
    parent::__construct($filename);
    if(DModule::getSchemaName($this->getDOMDocument()->firstElementChild) != 'appliccrossreftable')
    {
      throw new Exception("The DModule is not type of ACT data module", 1);
    }

    if ($CCT = new CCT($this->getCCTnPCT_name(2).".xml")){
      $this->CCT = $CCT;
    }
    if ($PCT = new PCT($this->getCCTnPCT_name(3).".xml")){
      $this->PCT = $PCT;
    }
  }

  private function getCCTnPCT_name(int $dmType)
  {
    return parent::getDMName($this->getDOMDocument(), $dmType);
    // dd($this->getDOMDocument());
    // dd(parent::getDMName($this->getDOMDocument(), 2));
    // return $cctOrPct == 'pct' ? new PCT(parent::getDMName($this).'.xml') : 
    // dd($this->getDOMDocument(), 'getCCTName');
  }
}