<?php 

namespace Ptdi\Mpub\Validation;

interface GeneralValidationInterface 
{
  public function validate();
  public function result();
  public function isReady();
}