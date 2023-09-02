<?php 

namespace Ptdi\Mpub\Validation;

use DOMXPath;
use Ptdi\Mpub\Object\DModule;

/**
 * contains method vor verify and resolving the applicability of data module
 */
trait Applicability
{
  function load_ACT_DM(DModule $dmodule)
  {
    $doc = $dmodule->getDOMDocument();
    
    // $xpath = new DOMXPath($doc);

    // $applicCrossRefTableRef = 
  }
}
