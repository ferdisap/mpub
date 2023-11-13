<?php

use Ptdi\Mpub\CSDB;
use Ptdi\Mpub\Object\DML;
use Ptdi\Mpub\Object\DModule;
use Ptdi\Mpub\Pdf2\male\PMC_male;
use Ptdi\Mpub\Pdf\Afm\PMC;
use Ptdi\Mpub\Pdf\Afm\PMC_N219;
use Ptdi\Mpub\Pdf2\PMC_PDF;
use Ptdi\Mpub\Publisher\Element;
use Ptdi\Mpub\Schema\Schema;

require __DIR__.'/vendor/autoload.php';


// ini dilakukan di index.php
// $pmc = PMC_PDF::instance($csdb_path,'male');
// $pmc->setAA_Approved("DGCA approved", " 23 May 2023");
// $pmc->importDocument($csdb_path, "/PMC-MALE-0001Z-A0001-00_000-01_EN-EN.xml",'');
// CSDB::validate('BREX', $pmc->getDOMDocument(), null, __DIR__."/tes_brdp");
// dump(CSDB::get_errors(false,'validateByBrex'));

// ini dilakukan di inex2.php
// hasilnya, get_errors tidak double. Artinya, setiap request akan menginstance memory baru, sehingga static class itu disiapkan untuk setiap request.
// saat di laravel, ketika ada request baru, itu akan menginstance controller baru, sehingga (saya pastikan) alokasi memory juga baru.
$csdb_path = __DIR__."/csdb_male";
$pmc = PMC_PDF::instance($csdb_path,'male');
$pmc->setAA_Approved("DGCA approved", " 23 May 2023");
$pmc->importDocument($csdb_path, "/PMC-MALE-0001Z-A0002-00_000-01_EN-EN.xml",'');
CSDB::validate('BREX', $pmc->getDOMDocument(), null, __DIR__."/tes_brdp");
dump(CSDB::get_errors(false,'validateByBrex'));

// dd(CSDB::get_errors(true,'validateByBrex'));
dd('aa');