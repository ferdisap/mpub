<?php 

namespace Ptdi\Mpub\Fop;

use Ptdi\Mpub\Main\Helper;

class Pdf 
{
  protected bool $isReady;
  public string $config;

  /**
   * @param string $input berupa xsl fo, belum bisa csdb object langsung
   * @param string $output berupa uri dengan extension pdf
   */
  public function __construct(public string $input,public string $output)
  {
    $this->isReady = file_exists($input);
  }

  /**
   * @return bool
   */
  public function create():bool
  {
    if(!$this->isReady) return false;

    $input = Helper::getRelativePath(__DIR__, $this->input);
    $output = Helper::getRelativePath(__DIR__, $this->output);
    $config = isset($this->config) && file_exists($this->config) ? Helper::getRelativePath(__DIR__, $this->config) : 'conf/fop.xconf';

    $is_OS_Windows = PHP_OS === 'WINNT' || PHP_OS === 'Windows' || PHP_OS === 'WIN32';
    $command = ($is_OS_Windows ? "fop" : './fop') . " -c $config -fo $input -pdf $output";
    chdir(__DIR__);
    shell_exec($command);

    return file_exists($output);
  }
}