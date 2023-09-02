<?php 

namespace Ptdi\Mpub\Core\Contracts;

interface Brex {
  
  public function getDOMDocument();
  public function validateToBrex();
}