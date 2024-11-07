<?php

namespace Ptdi\Mpub\Main;

use DOMDocument;
use JsonSerializable;
// use Ptdi\Mpub\Fop\Pdf;
use Ptdi\Mpub\Transformer\Pdf as TransformerPdf;
use Ptdi\Mpub\Transformer\Transformator;
use Serializable;

// class CSDBObject implements JsonSerializable
class CSDBObject
// implements Serializable
{
  protected string $version = "5.0";

  public bool $preserveWhiteSpace = false;
  public bool $formatOutput = false;

  protected string $filename = '';

  /**
   * biasanya kan di setiap document ada dmRef, pmRef. Nah {dm}Ref dm nya adalah initial
   */
  protected string $initial = '';

  protected string $path = '';
  protected array $breakDownURI = [];

  /**
   * @deprecated, karnea akan Validation sudah ada class nya sendiri
   */
  public bool $XSIValidationResult = false; // sepertinya ini deprecated saja karena ga dipakai
  /**
   * @deprecated, karnea akan Validation sudah ada class nya sendiri
   */
  public bool $BREXValidationResult = false; // sepertinya ini deprecated saja karena ga dipakai

  /**
   * @deprecated, karnea akan transformation sudah ada class nya sendiri
   * dipakai di CsdbServiceController untuk transform
   * dipakai di setiap model, khususnya comment untuk createXML
   * nanti diubah mungkin berbeda antara pdf dan html meskupun harusnya SAMA. 
   * Nanti ConfigXML mungkin tidak diperlukan jika fitur BREX sudah siap sepenuhnya.
   * Soalnya Brex terdiri dari banyak config
   */
  protected \DOMDocument $ConfigXML;

  /**
   *  bisa berupa getID3 array or \DOMDocument
   */
  protected mixed $document = null;

  /**
   * @deprecated, karnea akan applicability sudah ada class nya sendiri
   */
  protected \DOMDocument $ACTdoc;
  /**
   * @deprecated, karnea akan applicability sudah ada class nya sendiri
   */
  protected \DOMDocument $CCTdoc;
  /**
   * @deprecated, karnea akan applicability sudah ada class nya sendiri
   */
  protected \DOMDocument $PCTdoc;

  /**
   * @deprecated, karnea akan transformation sudah ada class nya sendiri
   * sejauh ini pmEntryTitle digunakan di header PDF
   */
  protected string $pmEntryTitle = '';
  
  /**
   * @deprecated, karnea akan transformation sudah ada class nya sendiri
   * what entryType (@pmEntryType) used currently of transformatting
   * digunakan maintPlanning (scheduleXsd) karena table-table nya beda style. Mungkin akan digunakan di schema lainnya nanti
   * value string sebaiknya bukan berupa S1000D standard attribute value, melainkan sudah di interpretasikan, misal pmt01 adalah 'TP' atau 'Title Page'
   */
  protected string $pmEntryType = '';

  /**
   * CSDBError
   */
  public CSDBError $errors;

  /**
   * @param string $filename include absolute path
   */
  public function __construct($version = "5.0")
  {
    $this->version = $version;
    $this->ACTdoc = new DOMDocument();
    $this->CCTdoc = new DOMDocument();
    $this->PCTdoc = new DOMDocument();
    $this->errors = new CSDBError();
  }

  public function setConfigXML($filename)
  {
    $this->ConfigXML = new DOMDOcument();
    $this->ConfigXML->load($filename);
  }

  public function __get($props)
  {
    if ($props === 'filename') {
      return !empty($this->filename) ? $this->filename : $this->getFilename();
    }
    elseif($props === 'initial'){
      return !empty($this->initial) ? $this->initial : $this->getInitial();
    }
    elseif($props === 'path'){
      return !empty($this->path) ? $this->path : $this->getPath();
    }
    return $this->$props;
  }

  /**
   * Belum mencakup seluruh S1000D doctype. Tinggal tambahkan di array nya
   */
  public function isS1000DDoctype()
  {
    if(($this->document instanceof \DOMDocument) AND ($this->document->doctype) AND in_array($this->document->doctype->nodeName, ['dmodule', 'pm', 'dml', 'icnmetadata', 'ddn', 'comment'])){
      return true;
    } else {
      $this->errors->set('s1000d_doctype', ['document must be be S1000D standard type.']);
      return false;
    }
  }

  /**
   * awalnya digunakan untuk membuat DML
   * harus set property $path dulu 
   * @return void
   */
  public function createDML($params = []) :void
  {
    $this->document = new DOMDocument('1.0', 'UTF-8');
    $this->document->preserveWhiteSpace = $this->preserveWhiteSpace;
    $this->document->formatOutput = $this->formatOutput;
    $identAndStatusSetion = $this->create_dml_identAndStatusSection($params);
    $dmlString = <<<EOL
    <!DOCTYPE dml []>
    <dml>
      $identAndStatusSetion
      <dmlContent></dmlContent>
    </dml>
    EOL;
    // $dmlString = preg_replace("/\n|\s{2,}/m",'',$dmlString); // sudah tidak ada \n walaupun code ini tidak dijalankan
    $this->document->loadXML($dmlString);
    $this->document->documentElement->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'xsi:noNamespaceSchemaLocation', 'http://www.s1000d.org/S1000D_5-0/xml_schema_flat/dml.xsd');
  }

  public function createCOM($params = [])
  {
    $this->document = new DOMDocument('1.0', 'UTF-8');
    $this->document->preserveWhiteSpace = $this->preserveWhiteSpace;
    $this->document->formatOutput = $this->formatOutput;

    $identAndStatusSetion = $this->create_com_identAndStatusSetion($params);
    $commentConcent = $this->create_com_content($params);
    $comString = <<<EOL
    <!DOCTYPE comment[]>
    <comment>
      $identAndStatusSetion
      $commentConcent
    </comment>
    EOL;
    // $comString = preg_replace("/\n|\s{2,}/m",'',$comString); // sudah tidak ada \n walaupun code ini tidak dijalankan
    $this->document->loadXML($comString);
    $this->document->documentElement->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'xsi:noNamespaceSchemaLocation', 'http://www.s1000d.org/S1000D_5-0/xml_schema_flat/comment.xsd');
  }

  public function createDDN($params = [])
  {
    $this->document = new DOMDocument('1.0', 'UTF-8');
    $this->document->preserveWhiteSpace = $this->preserveWhiteSpace;
    $this->document->formatOutput = $this->formatOutput;
    $identAndStatusSection = $this->create_ddn_identAndStatusSection($params);
    $ddnContent = $this->create_ddn_content($params);
    $ddnString = <<<EOL
    <!DOCTYPE ddn[]>
    <ddn>{$identAndStatusSection}{$ddnContent}</ddn>
    EOL;
    $this->document->loadXML($ddnString);
    $this->document->documentElement->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'xsi:noNamespaceSchemaLocation', 'http://www.s1000d.org/S1000D_5-0/xml_schema_flat/ddn.xsd');
  }

  /**
   * element yang belum di aplikasikan: ddnStatus/controlAuthorityGroup, ddnStatus/dataRestriction, security/@commericalClassification, security/@caveat, security/@derivativeClassification
   * @param Array key yang wajib: 'modelIdentCode, senderIdent, receiverIdent 
   * brexDmRef, securityClassification, authorization,
   * dispatchTo_enterpriseName, dispatchTo_lastName, dispatchTo_country, dispatchTo_city, 
   * dispatchFrom_enterpriseName, dispatchFrom_lastName, dispatchFrom_country, dispatchFrom_city'
   */
  private function create_ddn_identAndStatusSection(Array $params)
  {
    $modelIdentCode = strtoupper($params['modelIdentCode']);
    $senderIdent = strtoupper($params['senderIdent']);
    $receiverIdent = strtoupper($params['receiverIdent']);
    $year = date('Y');
    $month = date('m');
    $day = date('d');
    $seqNumber = strtoupper($params['seqNumber']);

    // ddnStatus
    $securityClassification = $params['securityClassification'];
    $authorization = $params['authorization'];
    $brexDmRef = CSDBStatic::decode_dmIdent($params['brexDmRef'])['xml_string'];
    $remarks = array_map((fn ($v) => $v ? "<simplePara>{$v}</simplePara>" : ''), $params['remarks'] ?? []);
    $remarks = join("", $remarks);
    $remarks = (empty($remarks)) ? '' :
    <<<EOD
    <remarks>{$remarks}</remarks>
    EOD;

    // dispatchTo
    $dispatchTo_enterpriseName = $params['dispatchTo_enterpriseName'];
    $dispatchTo_division = $params['dispatchTo_division'] ? "<division>{$params['dispatchTo_division']}</division>" : '';
    $dispatchTo_enterpriseUnit = $params['dispatchTo_enterpriseUnit'] ? "<enterpriseUnit>{$params['dispatchTo_enterpriseUnit']}</enterpriseUnit>" : '';
    $dispatchTo_lastName = $params['dispatchTo_lastName'];
    $dispatchTo_firstName = $params['dispatchTo_firstName'] ? "<firstName>{$params['dispatchTo_firstName']}</firstName>" : '';
    $dispatchTo_jobTitle = $params['dispatchTo_jobTitle'] ? "<jobTitle>{$params['dispatchTo_jobTitle']}</jobTitle>" : '';

    $dispatchTo_department = $params["dispatchTo_department"] ? "<department>{$params['dispatchTo_department']}</department>" : '';
    $dispatchTo_street = $params["dispatchTo_street"] ? "<street>{$params['dispatchTo_street']}</street>" : '';
    $dispatchTo_postOfficeBox = $params["dispatchTo_postOfficeBox"] ? "<postOfficeBox>{$params['dispatchTo_postOfficeBox']}</postOfficeBox>" : '';
    $dispatchTo_postalZipCode = $params["dispatchTo_postalZipCode"] ? "<postalZipCode>{$params['dispatchTo_postalZipCode']}</postalZipCode>" : '';
    $dispatchTo_city = $params['dispatchTo_city'];
    $dispatchTo_country = $params['dispatchTo_country'];
    $dispatchTo_postalZipCode = $params["dispatchTo_postalZipCode"] ? "<postalZipCode>{$params['dispatchTo_postalZipCode']}</postalZipCode>" : '';
    $dispatchTo_state = $params["dispatchTo_state"] ? "<state>{$params['dispatchTo_state']}</state>" : '';
    $dispatchTo_province = $params["dispatchTo_province"] ? "<province>{$params['dispatchTo_province']}</province>" : '';
    $dispatchTo_building = $params["dispatchTo_building"] ? "<building>{$params['dispatchTo_building']}</building>" : '';
    $dispatchTo_room = $params["dispatchTo_room"] ? "<room>{$params['dispatchTo_room']}</room>" : '';
    $dispatchTo_phoneNumber = '';
    if(!empty($params['dispatchTo_phoneNumber'])){
      foreach($params['dispatchTo_phoneNumber'] as $no){
        $dispatchTo_phoneNumber .= <<<EOL
        <phoneNumber>{$no}</phoneNumber>
        EOL;
      }
    }
    $dispatchTo_faxNumber = '';
    if(!empty($params["dispatchTo_faxNumber"])){
      foreach($params["dispatchTo_faxNumber"] as $no){
        $dispatchTo_faxNumber .= <<<EOL
        <faxNumber>{$no}</faxNumber>
        EOL;
      }
    }
    $dispatchTo_email = '';
    if(!empty($params["dispatchTo_email"])){
      foreach($params["dispatchTo_email"] as $no){
        $dispatchTo_email .= <<<EOL
        <email>{$no}</email>
        EOL;
      }
    }
    $dispatchTo_internet = '';
    if(!empty($params["dispatchTo_internet"])){
      foreach($params["dispatchTo_internet"] as $no){
        $dispatchTo_internet .= <<<EOL
        <internet>{$no}</internet>
        EOL;
      }
    }
    $dispatchTo_SITA = $params["dispatchTo_SITA"] ? "<SITA>{$params['dispatchTo_SITA']}</SITA>" : '';

    // dispatchFrom
    $dispatchFrom_enterpriseName = $params['dispatchFrom_enterpriseName'];
    $dispatchFrom_division = $params['dispatchFrom_division'] ? "<division>{$params['dispatchFrom_division']}</division>" : '';
    $dispatchFrom_enterpriseUnit = $params['dispatchFrom_enterpriseUnit'] ? "<enterpriseUnit>{$params['dispatchFrom_enterpriseUnit']}</enterpriseUnit>" : '';
    $dispatchFrom_lastName = $params['dispatchFrom_lastName'];
    $dispatchFrom_firstName = $params['dispatchFrom_firstName'] ? "<firstName>{$params['dispatchFrom_firstName']}</firstName>" : '';
    $dispatchFrom_jobTitle = $params['dispatchFrom_jobTitle'] ? "<jobTitle>{$params['dispatchFrom_jobTitle']}</jobTitle>" : '';

    $dispatchFrom_department = $params["dispatchFrom_department"] ? "<department>{$params['dispatchFrom_department']}</department>" : '';
    $dispatchFrom_street = $params["dispatchFrom_street"] ? "<street>{$params['dispatchFrom_street']}</street>" : '';
    $dispatchFrom_postOfficeBox = $params["dispatchFrom_postOfficeBox"] ? "<postOfficeBox>{$params['dispatchFrom_postOfficeBox']}</postOfficeBox>" : '';
    $dispatchFrom_postalZipCode = $params["dispatchFrom_postalZipCode"] ? "<postalZipCode>{$params['dispatchFrom_postalZipCode']}</postalZipCode>" : '';
    $dispatchFrom_city = $params['dispatchFrom_city'];
    $dispatchFrom_country = $params['dispatchFrom_country'];
    $dispatchFrom_postalZipCode = $params["dispatchFrom_postalZipCode"] ? "<postalZipCode>{$params['dispatchFrom_postalZipCode']}</postalZipCode>" : '';
    $dispatchFrom_state = $params["dispatchFrom_state"] ? "<state>{$params['dispatchFrom_state']}</state>" : '';
    $dispatchFrom_province = $params["dispatchFrom_province"] ? "<province>{$params['dispatchFrom_province']}</province>" : '';
    $dispatchFrom_building = $params["dispatchFrom_building"] ? "<building>{$params['dispatchFrom_building']}</building>" : '';
    $dispatchFrom_room = $params["dispatchFrom_room"] ? "<room>{$params['dispatchFrom_room']}</room>" : '';
    $dispatchFrom_phoneNumber = '';
    if(!empty($params['dispatchFrom_phoneNumber'])){
      foreach($params['dispatchFrom_phoneNumber'] as $no){
        $dispatchFrom_phoneNumber .= <<<EOL
        <phoneNumber>{$no}</phoneNumber>
        EOL;
      }
    }
    $dispatchFrom_faxNumber = '';
    if(!empty($params["dispatchFrom_faxNumber"])){
      foreach($params["dispatchFrom_faxNumber"] as $no){
        $dispatchFrom_faxNumber .= <<<EOL
        <faxNumber>{$no}</faxNumber>
        EOL;
      }
    }
    $dispatchFrom_email = '';
    if(!empty($params["dispatchFrom_email"])){
      foreach($params["dispatchFrom_email"] as $no){
        $dispatchFrom_email .= <<<EOL
        <email>{$no}</email>
        EOL;
      }
    }
    $dispatchFrom_internet = '';
    if(!empty($params["dispatchFrom_internet"])){
      foreach($params["dispatchFrom_internet"] as $no){
        $dispatchFrom_internet .= <<<EOL
        <internet>{$no}</internet>
        EOL;
      }
    }
    $dispatchFrom_SITA = $params["dispatchFrom_SITA"] ? "<SITA>{$params['dispatchFrom_SITA']}</SITA>" : '';

    $identAndStatusSection = <<<DDN
    <identAndStatusSection>
      <ddnAddress>
        <ddnIdent>
          <ddnCode modelIdentCode="{$modelIdentCode}" senderIdent="{$senderIdent}" receiverIdent="{$receiverIdent}" yearOfDataIssue="{$year}" seqNumber="{$seqNumber}" />
        </ddnIdent>
        <ddnAddressItems>
          <issueDate year="{$year}" month="{$month}" day="{$day}"/>
          <dispatchTo>
            <dispatchAddress>
              <enterprise>
                <enterpriseName>{$dispatchTo_enterpriseName}</enterpriseName>
                {$dispatchTo_division}
                {$dispatchTo_enterpriseUnit}
              </enterprise>
              <dispatchPerson>
                <lastName>{$dispatchTo_lastName}</lastName>
                {$dispatchTo_firstName}
                {$dispatchTo_jobTitle}
              </dispatchPerson>
              <address>
                {$dispatchTo_department}
                {$dispatchTo_street}
                {$dispatchTo_postOfficeBox}
                {$dispatchTo_postalZipCode}
                <city>{$dispatchTo_city}</city>
                <country>{$dispatchTo_country}</country>
                {$dispatchTo_state}
                {$dispatchTo_province}
                {$dispatchTo_building}
                {$dispatchTo_room}
                {$dispatchTo_phoneNumber}
                {$dispatchTo_faxNumber}
                {$dispatchTo_email}
                {$dispatchTo_internet}
                {$dispatchTo_SITA}
              </address>
            </dispatchAddress>
          </dispatchTo>
          <dispatchFrom>
            <dispatchAddress>
              <enterprise>
                <enterpriseName>{$dispatchFrom_enterpriseName}</enterpriseName>
                {$dispatchFrom_division}
                {$dispatchFrom_enterpriseUnit}
              </enterprise>
              <dispatchPerson>
                <lastName>{$dispatchFrom_lastName}</lastName>
                {$dispatchFrom_firstName}
                {$dispatchFrom_jobTitle}
              </dispatchPerson>
              <address>
                {$dispatchFrom_department}
                {$dispatchFrom_street}
                {$dispatchFrom_postOfficeBox}
                {$dispatchFrom_postalZipCode}
                <city>{$dispatchFrom_city}</city>
                <country>{$dispatchFrom_country}</country>
                {$dispatchFrom_state}
                {$dispatchFrom_province}
                {$dispatchFrom_building}
                {$dispatchFrom_room}
                {$dispatchFrom_phoneNumber}
                {$dispatchFrom_faxNumber}
                {$dispatchFrom_email}
                {$dispatchFrom_internet}
                {$dispatchFrom_SITA}
              </address>
            </dispatchAddress>
          </dispatchFrom>
        </ddnAddressItems>
      </ddnAddress>
      <ddnStatus>
        <security securityClassification="{$securityClassification}"/>
        <authorization>{$authorization}</authorization>
        <brexDmRef>{$brexDmRef}</brexDmRef>
      {$remarks}        
      </ddnStatus>
    </identAndStatusSection>
    DDN;
    return $identAndStatusSection;
  }
  private function create_ddn_identAndStatusSection_xx(Array $params)
  {
    $modelIdentCode = $params['modelIdentCode'];
    $senderIdent = strtoupper($params['senderIdent']);
    $receiverIdent = strtoupper($params['receiverIdent']);
    $year = date('Y');
    $month = date('m');
    $day = date('d');
    $seqNumber = function ($path) use ($modelIdentCode, $senderIdent, $receiverIdent, $year) {
      $dir = scandir($path);
      $collection = [];
      foreach ($dir as $file) {
        if (str_contains($file, strtoupper("DDN-{$modelIdentCode}-{$senderIdent}-{$receiverIdent}-{$year}"))) {
          $collection[] = $file;
        }
      }
      if (!empty($collection)) {
        rsort($collection, SORT_STRING);
        $c = array_map(function ($v) {
          $v = str_replace(".xml", '', $v);
          $v = explode("-", $v);
          return $v;
        }, $collection);
        $max_seqNumber = $c[0][5];
        $max_seqNumber++;
        $max_seqNumber = str_pad($max_seqNumber, 5, '0', STR_PAD_LEFT);
      }
      return $max_seqNumber ?? str_pad(1, 5, '0', STR_PAD_LEFT);
    };
    $seqNumber = strtoupper($seqNumber($this->path));

    // ddnStatus
    $securityClassification = $params['securityClassification'];
    $authorization = $params['authorization'];
    $brexDmRef = CSDBStatic::decode_dmIdent($params['brexDmRef']);
    $brexDmRef_learnCode = ($brexDmRef['dmCode']['learnCode'] == '') ? '' : 'learnCode=' . '"' . $brexDmRef['dmCode']['learnCode'] . '"';
    $brexDmRef_learnEventCode = ($brexDmRef['dmCode']['learnEventCode'] == '') ? '' : 'learnEventCode=' . '"' . $brexDmRef['dmCode']['learnEventCode'] . '"';
    $remarks = array_map((fn ($v) => $v ? "<simplePara>{$v}</simplePara>" : ''), $params['remarks'] ?? []);
    $remarks = join("", $remarks);
    $remarks = (empty($remarks)) ? '' :
    <<<EOD
    <remarks>{$remarks}</remarks>
    EOD;

    // dispatchTo
    $dispatchTo_enterpriseName = $params['dispatchTo_enterpriseName'];
    $dispatchTo_division = $params['dispatchTo_division'] ? "<division>{$params['dispatchTo_division']}</division>" : '';
    $dispatchTo_enterpriseUnit = $params['dispatchTo_enterpriseUnit'] ? "<enterpriseUnit>{$params['dispatchTo_enterpriseUnit']}</enterpriseUnit>" : '';
    $dispatchTo_lastName = $params['dispatchTo_lastName'];
    $dispatchTo_firstName = $params['dispatchTo_firstName'] ? "<firstName>{$params['dispatchTo_firstName']}</firstName>" : '';
    $dispatchTo_jobTitle = $params['dispatchTo_jobTitle'] ? "<jobTitle>{$params['dispatchTo_jobTitle']}</jobTitle>" : '';

    $dispatchTo_department = $params["dispatchTo_department"] ? "<department>{$params['dispatchTo_department']}</department>" : '';
    $dispatchTo_street = $params["dispatchTo_street"] ? "<street>{$params['dispatchTo_street']}</street>" : '';
    $dispatchTo_postOfficeBox = $params["dispatchTo_postOfficeBox"] ? "<postOfficeBox>{$params['dispatchTo_postOfficeBox']}</postOfficeBox>" : '';
    $dispatchTo_postalZipCode = $params["dispatchTo_postalZipCode"] ? "<postalZipCode>{$params['dispatchTo_postalZipCode']}</postalZipCode>" : '';
    $dispatchTo_city = $params['dispatchTo_city'];
    $dispatchTo_country = $params['dispatchTo_country'];
    $dispatchTo_postalZipCode = $params["dispatchTo_postalZipCode"] ? "<postalZipCode>{$params['dispatchTo_postalZipCode']}</postalZipCode>" : '';
    $dispatchTo_state = $params["dispatchTo_state"] ? "<state>{$params['dispatchTo_state']}</state>" : '';
    $dispatchTo_province = $params["dispatchTo_province"] ? "<province>{$params['dispatchTo_province']}</province>" : '';
    $dispatchTo_building = $params["dispatchTo_building"] ? "<building>{$params['dispatchTo_building']}</building>" : '';
    $dispatchTo_room = $params["dispatchTo_room"] ? "<room>{$params['dispatchTo_room']}</room>" : '';
    $dispatchTo_phoneNumber = '';
    if(!empty($params['dispatchTo_phoneNumber'])){
      foreach($params['dispatchTo_phoneNumber'] as $no){
        $dispatchTo_phoneNumber .= <<<EOL
        <phoneNumber>{$no}</phoneNumber>
        EOL;
      }
    }
    $dispatchTo_faxNumber = '';
    if(!empty($params["dispatchTo_faxNumber"])){
      foreach($params["dispatchTo_faxNumber"] as $no){
        $dispatchTo_faxNumber .= <<<EOL
        <faxNumber>{$no}</faxNumber>
        EOL;
      }
    }
    $dispatchTo_email = '';
    if(!empty($params["dispatchTo_email"])){
      foreach($params["dispatchTo_email"] as $no){
        $dispatchTo_email .= <<<EOL
        <email>{$no}</email>
        EOL;
      }
    }
    $dispatchTo_internet = '';
    if(!empty($params["dispatchTo_internet"])){
      foreach($params["dispatchTo_internet"] as $no){
        $dispatchTo_internet .= <<<EOL
        <internet>{$no}</internet>
        EOL;
      }
    }
    $dispatchTo_SITA = $params["dispatchTo_SITA"] ? "<SITA>{$params['dispatchTo_SITA']}</SITA>" : '';

    // dispatchFrom
    $dispatchFrom_enterpriseName = $params['dispatchFrom_enterpriseName'];
    $dispatchFrom_division = $params['dispatchFrom_division'] ? "<division>{$params['dispatchFrom_division']}</division>" : '';
    $dispatchFrom_enterpriseUnit = $params['dispatchFrom_enterpriseUnit'] ? "<enterpriseUnit>{$params['dispatchFrom_enterpriseUnit']}</enterpriseUnit>" : '';
    $dispatchFrom_lastName = $params['dispatchFrom_lastName'];
    $dispatchFrom_firstName = $params['dispatchFrom_firstName'] ? "<firstName>{$params['dispatchFrom_firstName']}</firstName>" : '';
    $dispatchFrom_jobTitle = $params['dispatchFrom_jobTitle'] ? "<jobTitle>{$params['dispatchFrom_jobTitle']}</jobTitle>" : '';

    $dispatchFrom_department = $params["dispatchFrom_department"] ? "<department>{$params['dispatchFrom_department']}</department>" : '';
    $dispatchFrom_street = $params["dispatchFrom_street"] ? "<street>{$params['dispatchFrom_street']}</street>" : '';
    $dispatchFrom_postOfficeBox = $params["dispatchFrom_postOfficeBox"] ? "<postOfficeBox>{$params['dispatchFrom_postOfficeBox']}</postOfficeBox>" : '';
    $dispatchFrom_postalZipCode = $params["dispatchFrom_postalZipCode"] ? "<postalZipCode>{$params['dispatchFrom_postalZipCode']}</postalZipCode>" : '';
    $dispatchFrom_city = $params['dispatchFrom_city'];
    $dispatchFrom_country = $params['dispatchFrom_country'];
    $dispatchFrom_postalZipCode = $params["dispatchFrom_postalZipCode"] ? "<postalZipCode>{$params['dispatchFrom_postalZipCode']}</postalZipCode>" : '';
    $dispatchFrom_state = $params["dispatchFrom_state"] ? "<state>{$params['dispatchFrom_state']}</state>" : '';
    $dispatchFrom_province = $params["dispatchFrom_province"] ? "<province>{$params['dispatchFrom_province']}</province>" : '';
    $dispatchFrom_building = $params["dispatchFrom_building"] ? "<building>{$params['dispatchFrom_building']}</building>" : '';
    $dispatchFrom_room = $params["dispatchFrom_room"] ? "<room>{$params['dispatchFrom_room']}</room>" : '';
    $dispatchFrom_phoneNumber = '';
    if(!empty($params['dispatchFrom_phoneNumber'])){
      foreach($params['dispatchFrom_phoneNumber'] as $no){
        $dispatchFrom_phoneNumber .= <<<EOL
        <phoneNumber>{$no}</phoneNumber>
        EOL;
      }
    }
    $dispatchFrom_faxNumber = '';
    if(!empty($params["dispatchFrom_faxNumber"])){
      foreach($params["dispatchFrom_faxNumber"] as $no){
        $dispatchFrom_faxNumber .= <<<EOL
        <faxNumber>{$no}</faxNumber>
        EOL;
      }
    }
    $dispatchFrom_email = '';
    if(!empty($params["dispatchFrom_email"])){
      foreach($params["dispatchFrom_email"] as $no){
        $dispatchFrom_email .= <<<EOL
        <email>{$no}</email>
        EOL;
      }
    }
    $dispatchFrom_internet = '';
    if(!empty($params["dispatchFrom_internet"])){
      foreach($params["dispatchFrom_internet"] as $no){
        $dispatchFrom_internet .= <<<EOL
        <internet>{$no}</internet>
        EOL;
      }
    }
    $dispatchFrom_SITA = $params["dispatchFrom_SITA"] ? "<SITA>{$params['dispatchFrom_SITA']}</SITA>" : '';

    $identAndStatusSection = <<<DDN
    <identAndStatusSection>
      <ddnAddress>
        <ddnIdent>
          <ddnCode modelIdentCode="{$modelIdentCode}" senderIdent="{$senderIdent}" receiverIdent="{$receiverIdent}" yearOfDataIssue="{$year}" seqNumber="{$seqNumber}" />
        </ddnIdent>
        <ddnAddressItems>
          <issueDate year="{$year}" month="{$month}" day="{$day}"/>
          <dispatchTo>
            <dispatchAddress>
              <enterprise>
                <enterpriseName>{$dispatchTo_enterpriseName}</enterpriseName>
                {$dispatchTo_division}
                {$dispatchTo_enterpriseUnit}
              </enterprise>
              <dispatchPerson>
                <lastName>{$dispatchTo_lastName}</lastName>
                {$dispatchTo_firstName}
                {$dispatchTo_jobTitle}
              </dispatchPerson>
              <address>
                {$dispatchTo_department}
                {$dispatchTo_street}
                {$dispatchTo_postOfficeBox}
                {$dispatchTo_postalZipCode}
                <city>{$dispatchTo_city}</city>
                <country>{$dispatchTo_country}</country>
                {$dispatchTo_state}
                {$dispatchTo_province}
                {$dispatchTo_building}
                {$dispatchTo_room}
                {$dispatchTo_phoneNumber}
                {$dispatchTo_faxNumber}
                {$dispatchTo_email}
                {$dispatchTo_internet}
                {$dispatchTo_SITA}
              </address>
            </dispatchAddress>
          </dispatchTo>
          <dispatchFrom>
            <dispatchAddress>
              <enterprise>
                <enterpriseName>{$dispatchFrom_enterpriseName}</enterpriseName>
                {$dispatchFrom_division}
                {$dispatchFrom_enterpriseUnit}
              </enterprise>
              <dispatchPerson>
                <lastName>{$dispatchFrom_lastName}</lastName>
                {$dispatchFrom_firstName}
                {$dispatchFrom_jobTitle}
              </dispatchPerson>
              <address>
                {$dispatchFrom_department}
                {$dispatchFrom_street}
                {$dispatchFrom_postOfficeBox}
                {$dispatchFrom_postalZipCode}
                <city>{$dispatchFrom_city}</city>
                <country>{$dispatchFrom_country}</country>
                {$dispatchFrom_state}
                {$dispatchFrom_province}
                {$dispatchFrom_building}
                {$dispatchFrom_room}
                {$dispatchFrom_phoneNumber}
                {$dispatchFrom_faxNumber}
                {$dispatchFrom_email}
                {$dispatchFrom_internet}
                {$dispatchFrom_SITA}
              </address>
            </dispatchAddress>
          </dispatchFrom>
        </ddnAddressItems>
      </ddnAddress>
      <ddnStatus>
        <security securityClassification="{$securityClassification}"/>
        <authorization>{$authorization}</authorization>
        <brexDmRef>
          <dmRef>
          <dmRefIdent>
            <dmCode assyCode="{$brexDmRef['dmCode']['assyCode']}" disassyCode="{$brexDmRef['dmCode']['disassyCode']}" disassyCodeVariant="{$brexDmRef['dmCode']['disassyCodeVariant']}" infoCode="{$brexDmRef['dmCode']['infoCode']}" infoCodeVariant="{$brexDmRef['dmCode']['infoCodeVariant']}" itemLocationCode="{$brexDmRef['dmCode']['itemLocationCode']}" modelIdentCode="{$brexDmRef['dmCode']['modelIdentCode']}" subSubSystemCode="{$brexDmRef['dmCode']['subSubSystemCode']}" subSystemCode="{$brexDmRef['dmCode']['subSystemCode']}" systemCode="{$brexDmRef['dmCode']['systemCode']}" systemDiffCode="{$brexDmRef['dmCode']['systemDiffCode']}" 
              {$brexDmRef_learnCode} {$brexDmRef_learnEventCode}/>
            <issueInfo inWork="{$brexDmRef['issueInfo']['inWork']}" issueNumber="{$brexDmRef['issueInfo']['issueNumber']}"/>
            <language countryIsoCode="{$brexDmRef['language']['countryIsoCode']}" languageIsoCode="{$brexDmRef['language']['languageIsoCode']}"/>
          </dmRefIdent>
        </dmRef>
      </brexDmRef>
      {$remarks}        
      </ddnStatus>
    </identAndStatusSection>
    DDN;
    return $identAndStatusSection;
  }

  /**
   * @param Array element yang sunnah: deliveryListItems[]
   * belum mengaplikasikan element ddnContent/mediaIdent
   */
  private function create_ddn_content(Array $params)
  {
    $content = "<ddnContent>";
    if(!empty($params['deliveryListItemsFilename'])){
      $content .= '<deliveryList>';
      foreach($params['deliveryListItemsFilename'] as $filename){
        $content .= <<<CNT
        <deliveryListItem>
          <dispatchFileName>{$filename}</dispatchFileName>
        </deliveryListItem>
        CNT;
      }
      $content .= '</deliveryList>';
    }
    $content .= "</ddnContent>";
    return $content;
  }

  /**
   * tidak support seqNumber yang ada letter nya 
   * element security belum bisa mengcover @commercialSecurityAttGroup dan @derivativeClassificationRefId
   * jika ingin menaruh <dmlRef> pada <dmlStatus>, maka gunakan otherOptions = ['dmlRef' = ['DML...', 'DML...]];
   * @return string identAndStatusSection
   */
  private function create_dml_identAndStatusSection(Array $params)
  {
    $modelIdentCode = $params['modelIdentCode'];
    $yearOfDataIssue = $params['yearOfDataIssue'];
    $dmlType = strtolower($params['dmlType']);
    $originator = $params['originator'];
    $securityClassification = $params['securityClassification'];
    $seqNumber = $params['seqNumber'];
    
    $inWork = $params['inWork'] ?? '01';
    $day = $params['day'] ?? date('d');
    $month = $params['month'] ?? date('m');

    $remarks = array_map((fn ($v) => "<simplePara>{$v}</simplePara>"), $params['remarks']);
    $remarks = join("", $remarks);
    $remarks = (empty($remarks)) ? '' : "<remarks>{$remarks}</remarks>";

    $brexDmRef = CSDBStatic::decode_dmIdent($params['brexDmRef'])['xml_string'];
    $dmlRef = '';
    if (isset($params['dmlRef']) and is_array($params['dmlRef'])) {
      $dmlRef = array_map(function ($filename) {
        $filename = CSDBStatic::decode_dmlIdent($filename);
        return $filename = $filename['xml_string'];
      }, $params['dmlRef']);
      $dmlRef = join("", $dmlRef);
    }

    $identAndStatusSection = <<<EOL
      <identAndStatusSection>
        <dmlAddress>
          <dmlIdent>
            <dmlCode dmlType="{$dmlType}" modelIdentCode="{$modelIdentCode}" senderIdent="{$originator}" seqNumber="{$seqNumber}" yearOfDataIssue="{$yearOfDataIssue}"></dmlCode>
            <issueInfo inWork="{$inWork}" issueNumber="000"></issueInfo>
          </dmlIdent>
          <dmlAddressItems>
            <issueDate day="{$day}" month="{$month}" year="{$yearOfDataIssue}"></issueDate>
          </dmlAddressItems>
        </dmlAddress>
        <dmlStatus>
          <security securityClassification="{$securityClassification}"/>
          {$dmlRef}
          <brexDmRef>{$brexDmRef}</brexDmRef>
          {$remarks}
        </dmlStatus>
      </identAndStatusSection>
    EOL;

    return $identAndStatusSection;    
  }
  private function create_dml_identAndStatusSection_xx(string $modelIdentCode, string $originator, string $dmlType, string $securityClassification, string $brexDmRef, array $remarks = [], $otherOptions = [])
  {
    $year = date('Y');
    $dmlCode = [strtolower($dmlType) == 's' ? 'CSL' : 'DML', $modelIdentCode, $originator, $dmlType, $year, ''];
    $dmlCode = strtoupper(join('-', $dmlCode)); // DML-MALE-0001Z-P-2024-
    $seqNumber = function ($path) use ($dmlCode) {
      $dir = scandir($path);
      $collection = [];
      foreach ($dir as $file) {
        if (str_contains($file, $dmlCode)) {
          $collection[] = $file;
        }
      }
      $c = array_map(function ($v) {
        $v = preg_replace("/_.+/", '', $v); // menghilangkan issueInfo dan languange yang menempel di filename
        $v = explode("-", $v);
        return $v;
      }, $collection);
      if (!empty($c)) {
        $max_seqNumber = $c[0][5];
        // foreach ini harusnya tidak perlukan lagi, cukup lakukan rsort($collection, SORT_STRING);
        foreach ($c as $dmlCode_array) {
          if ((int)$max_seqNumber < (int)$dmlCode_array[5]) {
            $max_seqNumber = $dmlCode_array[5];
          }
        }
        $max_seqNumber = str_pad(((int)$max_seqNumber) + 1, 5, '0', STR_PAD_LEFT);
      }
      return $max_seqNumber ?? str_pad(1, 5, '0', STR_PAD_LEFT);
      // inWork number pasti 01 jika buat BARU DML
      // $c = array_map(function($v){
      //   $v = preg_replace("/DML-[\w-]+_/", '',$v);
      //   $v = preg_replace("/.xml/", '',$v);
      //   $v = explode("-",$v);
      //   return $v;
      // }, $collection);
      // $iw_max = str_pad(max($iw) + 1, 2, '0', STR_PAD_LEFT);
      // if(!empty($c)){
      //   $iw = array_map((fn($v) => (int)($v[1])), $c);
      //   $iw_max = str_pad(max($iw) + 1, 2, '0', STR_PAD_LEFT);
      // }
      // return [$max_seqNumber ?? '00001', $iw_max ?? '01'];
    };
    $modelIdentCode = strtoupper($modelIdentCode);
    $originator = strtoupper($originator);
    $dmlType = strtolower($dmlType);
    $seqNumber = strtoupper($seqNumber($this->path));
    $inWork = '01';
    $day = date('d');
    $month = date('m');

    $brexDmRef = CSDBStatic::decode_dmIdent($brexDmRef);

    $remarks = array_map((fn ($v) => "<simplePara>{$v}</simplePara>"), $remarks);
    $remarks = join("", $remarks);
    $remarks = (empty($remarks)) ? '' :
      <<<EOD
    <remarks>{$remarks}</remarks>
    EOD;

    $learnCode = ($brexDmRef['dmCode']['learnCode'] == '') ? '' : 'learnCode=' . '"' . $brexDmRef['dmCode']['learnCode'] . '"';
    $learnEventCode = ($brexDmRef['dmCode']['learnEventCode'] == '') ? '' : 'learnEventCode=' . '"' . $brexDmRef['dmCode']['learnEventCode'] . '"';

    $dmlRef = '';
    if (isset($otherOptions['dmlRef']) and is_array($otherOptions['dmlRef'])) {
      $dmlRef = array_map(function ($filename) {
        $filename = CSDBStatic::decode_dmlIdent($filename);
        return $filename = $filename['xml_string'];
      }, $otherOptions['dmlRef']);
      $dmlRef = join("", $dmlRef);
    }

    $identAndStatusSection = <<<EOL
      <identAndStatusSection>
        <dmlAddress>
          <dmlIdent>
            <dmlCode dmlType="{$dmlType}" modelIdentCode="{$modelIdentCode}" senderIdent="{$originator}" seqNumber="{$seqNumber}" yearOfDataIssue="{$year}"></dmlCode>
            <issueInfo inWork="{$inWork}" issueNumber="000"></issueInfo>
          </dmlIdent>
          <dmlAddressItems>
            <issueDate day="{$day}" month="{$month}" year="{$year}"></issueDate>
          </dmlAddressItems>
        </dmlAddress>
        <dmlStatus>
          <security securityClassification="{$securityClassification}"></security>
          {$dmlRef}
          <brexDmRef>
            <dmRef>
              <dmRefIdent>
                <dmCode assyCode="{$brexDmRef['dmCode']['assyCode']}" disassyCode="{$brexDmRef['dmCode']['disassyCode']}" disassyCodeVariant="{$brexDmRef['dmCode']['disassyCodeVariant']}" infoCode="{$brexDmRef['dmCode']['infoCode']}" infoCodeVariant="{$brexDmRef['dmCode']['infoCodeVariant']}" itemLocationCode="{$brexDmRef['dmCode']['itemLocationCode']}" modelIdentCode="{$brexDmRef['dmCode']['modelIdentCode']}" subSubSystemCode="{$brexDmRef['dmCode']['subSubSystemCode']}" subSystemCode="{$brexDmRef['dmCode']['subSystemCode']}" systemCode="{$brexDmRef['dmCode']['systemCode']}" systemDiffCode="{$brexDmRef['dmCode']['systemDiffCode']}" 
                  {$learnCode} {$learnEventCode}/>
                <issueInfo inWork="{$brexDmRef['issueInfo']['inWork']}" issueNumber="{$brexDmRef['issueInfo']['issueNumber']}"/>
                <language countryIsoCode="{$brexDmRef['language']['countryIsoCode']}" languageIsoCode="{$brexDmRef['language']['languageIsoCode']}"/>
              </dmRefIdent>
            </dmRef>
          </brexDmRef>
          {$remarks}
        </dmlStatus>
      </identAndStatusSection>
    EOL;

    return $identAndStatusSection;    
  }

  /**
   * jika commentType = Q, seqNumber 2digit terakhir adalah 'xxx00', else ++ dan 3digit pertama 
   * element yang belum diaplikasikan: commentStatus/controlAuthorityGroup, commentStatus/dataRestriction, security/@commericalClassification, security/@caveat, security/@derivativeClassification
   * @param Array key yang wajib: 'modelIdentCode, senderIdent, commentType, languageIsoCode, countryIsoCode, brexDmRef, enterpriseName, lastName, country, city, 
   * 'securityClassification, commentPriorityCode, responseType'
   */
  private function create_com_identAndStatusSetion(Array $params)
  {
    // ident
    $modelIdentCode = $params['modelIdentCode'];
    $senderIdent = strtoupper($params['senderIdent']);
    $seqNumber = strtoupper($params['seqNumber']);
    $commentType = strtolower($params['commentType']);
    $yearOfDataIssue = $params['yearOfDataIssue'];
    $languageIsoCode = strtolower($params['languageIsoCode']);
    $countryIsoCode = strtoupper($params['countryIsoCode']);

    // address    
    $commentTitle = $params['commentTitle'];
    $enterpriseName = $params['enterpriseName'];
    $division = $params['division'] ?? '';
    $enterpriseUnit = $params['enterpriseUnit'] ?? '';    
    $lastName = $params['lastName'];
    $firstName = $params['firstName'] ?? '';
    $jobTitle = $params['jobTitle'] ?? '';

    $department = $params["department"] ?? '';
    $street = $params["street"] ?? '';
    $postOfficeBox = $params["postOfficeBox"] ?? '';
    $postalZipCode = $params["postalZipCode"] ?? '';
    $city = $params["city"];
    $country = $params["country"];
    $state = $params["state"] ?? '';
    $province = $params["province"] ?? '';
    $building = $params["building"] ?? '';
    $room = $params["room"] ?? '';
    $phoneNumber = '';
    if(!empty($params["phoneNumber"])){
      foreach($params["phoneNumber"] as $no){
        $phoneNumber .= "<phoneNumber>{$no}</phoneNumber>";
      }
    }
    $faxNumber = '';
    if(!empty($params["faxNumber"])){
      foreach($params["faxNumber"] as $no){
        $faxNumber .= "<faxNumber>{$no}</faxNumber>";
      }
    }
    $email = '';
    if(!empty($params["email"])){
      foreach($params["email"] as $no){
        $email .= "<email>{$no}</email>";
      }
    }
    $internet = '';
    if(!empty($params["internet"])){
      foreach($params["internet"] as $no){
        $internet .= "<internet>{$no}</internet>";
      }
    }
    $SITA = $params["SITA"] ?? '';

    $remarks = array_map((fn ($v) => "<simplePara>{$v}</simplePara>"), $params['remarks'] ?? []);
    $remarks = join("", $remarks);
    $remarks = (empty($remarks)) ? '' :
      <<<EOD
    <remarks>{$remarks}</remarks>
    EOD;
    $day = date('d');
    $month = date('m');

    // commentStatus
    $securityClassification = $params['securityClassification'];
    $commentPriorityCode = $params['commentPriorityCode'];
    $responseType = $params['responseType'];
    $commentResponse = '';
    if($responseType) $commentResponse = '<commentResponse responseType="'.$params['responseType'].'"/>';
    $commentRefsContent = '';
    if(empty($params['commentRefs'])) $commentRefsContent = "<noReferences/>";
    else {
      $commentRefsArray = [];
      foreach($params['commentRefs'] as $ref){
        if($ref){
          $ident = CSDBStatic::decode_ident($ref);
          switch ($ident['prefix']) {
            case 'DMC-': $commentRefsArray['dmRefGroup'] = $ident['xml_string']; break;
            case 'PMC-': $commentRefsArray['pmRefGroup'] = $ident['xml_string']; break;
            case 'DML-': $commentRefsArray['dmlRefGroup'] = $ident['xml_string']; break;
            case 'DDN-': $commentRefsArray['ddnRefGroup'] = $ident['xml_string']; break;
          }
        }
      }
      foreach($commentRefsArray as $groupName => $ref){
        $commentRefsContent = "<{$groupName}>{$ref}</{$groupName}>";
      }
    }
    $commentRefs = "<commentRefs>{$commentRefsContent}</commentRefs>";

    $brexDmRef = CSDBStatic::decode_dmIdent($params['brexDmRef'])['xml_string'];

    $identAndStatusSection = <<<EOL
    <identAndStatusSection>
      <commentAddress>
        <commentIdent>
          <commentCode modelIdentCode="{$modelIdentCode}" senderIdent="{$senderIdent}" yearOfDataIssue="{$yearOfDataIssue}" seqNumber="{$seqNumber}" commentType="{$commentType}"/>
          <language languageIsoCode="{$languageIsoCode}" countryIsoCode="{$countryIsoCode}"/>
        </commentIdent>
        <commentAddressItems>
          <commentTitle>{$commentTitle}</commentTitle>
          <issueDate day="{$day}" month="{$month}" year="{$yearOfDataIssue}" />
          <commentOriginator>
            <dispatchAddress>
              <enterprise>
                <enterpriseName>{$enterpriseName}</enterpriseName>
                <division>{$division}</division>
                <enterpriseUnit>{$enterpriseUnit}</enterpriseUnit>
              </enterprise>
              <dispatchPerson>
                <lastName>{$lastName}</lastName>
                <firstName>{$firstName}</firstName>
                <jobTitle>{$jobTitle}</jobTitle>
              </dispatchPerson>
              <address>
                <department>{$department}</department>
                <street>{$street}</street>
                <postOfficeBox>{$postOfficeBox}</postOfficeBox>
                <postalZipCode>{$postalZipCode}</postalZipCode>
                <city>{$city}</city>
                <country>{$country}</country>
                <state>{$state}</state>
                <province>{$province}</province>
                <building>{$building}</building>
                <room>{$room}</room>
                {$phoneNumber}
                {$faxNumber}
                {$email}
                {$internet}
                <SITA>{$SITA}</SITA>
              </address>
            </dispatchAddress>
          </commentOriginator>
        </commentAddressItems>
      </commentAddress>
      <commentStatus>
        <security securityClassification="{$securityClassification}"/>
        <commentPriority commentPriorityCode="{$commentPriorityCode}"/>
        {$commentResponse}
        {$commentRefs}
        <brexDmRef>{$brexDmRef}</brexDmRef>
        {$remarks}
      </commentStatus>
    </identAndStatusSection>
    EOL;
    return $identAndStatusSection;
  }
  
  /**
   * masih belum mengaplikasikan attachment untuk comment
   */
  private function create_com_content(Array $params)
  {
    $simpleParas = '';
    foreach($params['commentContentSimplePara'] as $simplePara){
      $simpleParas .= "<simplePara>{$simplePara}</simplePara>";
    }
    return "<commentContent>{$simpleParas}</commentContent>";
    // selanjutnya buat attachment.
  }

  /**
   * Set the object document, wheter it result is DOMDcoument or Array (if ICN file)
   * @param string $filename dengan absolute path 
   * @return bool true or false
   */
  public function load(string $filename = '')
  {
    // $doc->preserveWhiteSpace = false;
    // $doc->formatOutput = true;

    libxml_use_internal_errors(true);
    $mime = file_exists($filename) ? mime_content_type($filename) : 'undefined';
    if (str_contains($mime, 'text')) {
      $dom = new \DOMDocument('1.0');
      // $dom->preserveWhiteSpace = $this->preserveWhiteSpace;
      $dom->preserveWhiteSpace = false;
      // $dom->formatOutput = $this->formatOutput;
      $dom->formatOutput = true;
      @$dom->load($filename, LIBXML_PARSEHUGE);
      $errors = libxml_get_errors();
      if(count($errors)){
        $this->errors->set('file_exist', []);
        foreach ($errors as $e) {
          $this->errors->append('file_exist', CSDBError::display_xml_error($e));
        }
      }
      if(!$dom->documentElement) return false;
      $this->document = $dom;
      return true;
    } 
    elseif($mime === 'undefined'){
      $this->errors->set('load', ["Undefined mime content type or file doesn't exist."]);
      return false;
    }
    else {
      $this->document = new ICNDocument();
      if($this->document->load($filename)) return true;
      return false;
    }
    return false;
  }

  public function loadByString(string $text)
  {
    libxml_use_internal_errors(true);
    $dom = new \DOMDocument('1.0');
    $dom->preserveWhiteSpace = $this->preserveWhiteSpace;
    $dom->formatOutput = $this->formatOutput;
    @$dom->loadXML($text, LIBXML_PARSEHUGE);
    $errors = libxml_get_errors();
    if(count($errors)){
      $this->errors->set('file_exist', []);
      foreach ($errors as $e) {
        $this->errors->append('file_exist', CSDBError::display_xml_error($e));
      }
    }
    if(!$dom->documentElement) return false;
    $this->document = $dom;
    return true;
  }

  /**
   * @param \DOMElement $element
   * @param string $doctype berupa 'dmodule', 'dml', 'pm', 'infoEntity'
   */
  public function loadByElement(\DOMElement $element, string $doctype)
  {
    $path = Helper::analyzeURI($element->ownerDocument->baseURI)['path'];
    $filename = '';
    switch ($doctype) {
      case 'dmodule':
        $filename = CSDBStatic::resolve_dmIdent($element);
        break;
      case 'dml':
        $filename = CSDBStatic::resolve_dmlIdent($element);
        break;
      case 'infoEntity':
        $filename = CSDBStatic::resolve_infoEntityIdent($element);
        break;
      case 'pm':
        $filename = CSDBStatic::resolve_pmIdent($element);
        break;
    }
    if ($filename) {
      return $this->load($path . DIRECTORY_SEPARATOR . $filename);
    }
  }

  public function getSchema($option = '')
  {
    if ($this->document->doctype AND $this->document instanceof \DOMDocument) {
      if (!$option) {
        return $this->document->documentElement->getAttribute("xsi:noNamespaceSchemaLocation"); // kalau document di loadXML, tidak bisa pakai fungsi getAttributeNS().
      } elseif ($option === 'filename') {
        $schema = $this->document->documentElement->getAttribute("xsi:noNamespaceSchemaLocation"); // kalau document di loadXML, tidak bisa pakai fungsi getAttributeNS().
        preg_match("/\w+.xsd/", $schema, $schema);
        if (!empty($schema)) $schema = $schema[0];
        return $schema;
      }
    }
  }

  /**
   * get and set Filename
   * @return string
   */
  public function getFilename(): string
  {
    if ($this->document instanceof \DOMDocument) {
      $initial = $this->getInitial();
      $domXpath = new \DOMXPath($this->document);
      $ident = $domXpath->evaluate("//{$initial}Address/{$initial}Ident");
      if ($ident[0]) {
        // go to function resolve_dmlIdent, resolve_pmIdent, resolve_dmIdent, resolve_imfIdent
        $docIdent = call_user_func(CSDBStatic::class . "::resolve_" . $initial . "Ident", [$ident[0]]); //  argument#0 domElement / array, argument#1 prefix, argument#2 format
      }
      return $this->filename = $docIdent;
    } 
    elseif ($this->document instanceof ICNDocument) {
      return $this->filename = $this->document['filename'];
    }
    else {
      return $this->filename = '';
    }
  }

  /**
   * biasanya kan di setiap document ada dmRef, pmRef. Nah {dm}Ref dm nya adalah initial
   * get and set Initial.
   * @return string
   */
  public function getInitial() :string
  {
    if ($this->document instanceof \DOMDocument) {
      if($this->document->doctype) $initial = $this->document->doctype->nodeName;
      else $initial = $this->document->documentElement->nodeName;
      $initial = $initial === 'dmodule' ? 'dm' : (($initial === 'icnMetadataFile' || $initial === 'icnmetadata' ) ? 'imf' : $initial);
      return $this->initial = $initial;
    }
  }
  
  /**
   * get and set path
   * @return string
   */
  public function getPath() :string
  {
    if(empty($this->breakDownURI)){
      $this->breakDownURI = Helper::analyzeURI($this->document->baseURI);
    }
    return $this->path = $this->breakDownURI['path'];
  }

  /**
   * awalnya dibuat untuk create DML
   */
  public function setPath(string $path) :void
  {
    $this->path = $path;
  }

  public function getSC($return = 'text'): string
  {
    // if ($this->document instanceof \DOMDocument) {
    //   $domXpath = new \DOMXpath($this->document);
    //   $sc = $domXpath->evaluate("string(//identAndStatusSection/descendant::security/@securityClassification)");
    // } elseif ($this->document instanceof \DOMElement) {
    //   $sc = $this->document->getAttribute('securityClassification');
    // } else {
    //   return '';
    // }
    $domXpath = new \DOMXpath($this->document);
    $sc = $domXpath->evaluate("string(//identAndStatusSection/descendant::security/@securityClassification)");

    if ($return === 'number') {
      return $sc;
    } elseif ($return === 'integer') {
      return (int) $sc;
    } elseif ($return === 'text') {
      $a = [
        '01' => 'Unclassified',
        '02' => 'Restricted',
        '03' => 'Confidential',
        '04' => 'Secret',
        '05' => 'Top Secret',
      ];
      return $a[$sc] ?? '';
    }
  }

  public function getQA(int $index = null, $qa= null) :string
  {
    if(!$qa){
      $domXpath = new \DOMXpath($this->document);
      if($index) $qa = $domXpath->evaluate("//identAndStatusSection/descendant::qualityAssurance/*[position() = '{$index}']")[0];
      else $qa = $domXpath->evaluate("//identAndStatusSection/descendant::qualityAssurance/*[last()]")[0];
    }
    if($qa){
      if($qa->tagName === 'unverified') return 'unverified';
      elseif($qa->tagName === 'firstVerification') {
        return 'First Verification - ' . $qa->getAttribute('verificationType');
      }
      elseif($qa->tagName === 'secondVerification') {
        return 'Second Verification - ' . $qa->getAttribute('verificationType');
      }
    }
    return '';
  }

  public function getTitle($child = ''): string
  {
    if (!$this->document or !($this->document instanceof DOMDocument)) return '';
    $domXpath = new \DOMXPath($this->document);
    $title = '';
    $initial = '';
    switch ($this->document->doctype) {
      case 'dmodule':
        $initial = 'dm';
        break;
      case 'pm':
        $initial = 'pm';
        break;
      default:
        return '';
        break;
    }
    $title = $domXpath->evaluate("//identAndStatusSection/{$initial}Address/{$initial}Title")[0];
    $title = call_user_func_array(CSDBStatic::class . "::resolve_{$initial}Title", [$title, $child]);
    return $title;
  }

  /**
   * @deprecated
   */
  public function getStatus($child = ''): string
  {
    switch ($child) {
      case 'applic':
        // return $this->getApplicability();
        return '';
        break;
      case 'qualityAssurance': // return json
        $doctype = $this->document->doctype;
        $doctype = $doctype === 'dmodule' ? 'dm' : $doctype;
        $qas = $this->document->getElementsByTagName("{$doctype}Status")[0]->getElementsByTagName($child);
        $r = [];
        foreach ($qas as $qa) {
          $applicRefId = $qa->getAttribute('applicRefId');
          $stt = $qa->firstElementChild;
          $verificationType = $stt->getAttribute('verificationType');
          $r[] = [
            'applicRefId' => $applicRefId,
            'status' => $stt->tagName,
            'verificationType' => $verificationType,
          ];
        }
        return json_encode($r);
      default:
        return '';
        break;
    }
  }

  /**
   * saat membuat BREX dm, tidak bisa getBrexDm karena yang dibuat adalah BREX.
   * Soalnya saat pembuatan,inWork tidak 00 padahal kalau mau nulis brexDmRef, harusnya brexDmRef tidak boleh inWork non-00 (tidak resmi release)
   * @return self
   */
  public function getBrexDm() :self
  {
    if(!($this->document instanceof \DOMDocument) OR !$this->document->doctype){
      return new CSDBObject("5.0");
    }
    $domXpath = new \DOMXPath($this->document);
    $brexDmRef = $domXpath->evaluate("//identAndStatusSection/descendant::brexDmRef")[0];
    $brexDmRef = CSDBStatic::resolve_dmIdent($brexDmRef);
    if(!$brexDmRef) return new CSDBObject("5.0");
    
    $BREXObject = new CSDBObject("5.0");
    $BREXObject->load($this->path . DIRECTORY_SEPARATOR . $brexDmRef);
    return $BREXObject;
  }

  /**
   * @return string
   */
  public function getRemarks(mixed $remarks, $output = 'string')
  {
    if(!$remarks){
      $domXpath = new \DOMXPath($this->document);
      $remarks = $domXpath->evaluate("//identAndStatusSection/descendant::remarks")[0];
    }
    if($output === 'string'){
      $str = '';
      if($remarks){
        $simpleParas = $remarks->getElementsByTagName('simplePara');
        foreach($simpleParas as $p){
          $str .= '\n' . $p->nodeValue;  
        }
      }
      $str = trim($str,"\\n");
      return $str;
    } else {
      $str = [];
      if($remarks){
        $simpleParas = $remarks->getElementsByTagName('simplePara');
        foreach($simpleParas as $p){
          $str[] = $p->nodeValue;  
        }
      }
      return $str;
    }
    
  }

  /**
   * awalnya dibuat untuk fungsi CSDBObject@getBrexDm, class ini
   * @return bool
   */
  public function isBrex() :bool
  {
    $decode = CSDBStatic::decode_dmIdent($this->filename);
    if($decode['dmCode']['infoCode'] === '022') return true;
    return false;
  }

  public function query($xpath = '')
  {
    if(!($this->document instanceof \DOMDocument)) return '';
    if(!($xpath)) return '';
    $domXpath = new \DOMXpath($this->document);
    return [...$domXpath->query($xpath)];
  }

  public function evaluate($xpath = '')
  {
    if(!($this->document instanceof \DOMDocument)) return '';
    if(!($xpath)) return '';
    $domXpath = new \DOMXpath($this->document);
    return $domXpath->evaluate($xpath);
  }

  /**
   * resolving applic element
   * @param DOMElement $doc berupa applic
   * @param int $useDisplayText 0,1,2. jika satu itu string HARUS pakai display Text. Jika dua itu optional. Artinya jika displayText tidak ada, akan mengambil assert untuk test
   * @return string
   */
  public function getApplicability(mixed $applic, bool $keppOneByOne = false, bool $useDisplayName = true ,int $useDisplayText = 2) :string
  {
    $Applicability = new Applicability($this->document->baseURI);
    $result = $Applicability->get($applic, $keppOneByOne, $useDisplayName, $useDisplayText);
    if(count($Applicability->errors)) $this->errors->set('applicability', $Applicability->errors->get());
    return $result;
  }

  /**
   * @deprecated dipindah ke Applicability.php
   * @return array
   */
  private function arrayify_applic(\DOMElement $applic, $keepOneByOne = false, $useDisplayName = true) :array
  {
    $doc = $this->document;
    $path = $this->getPath();
    $domXpath = new \DOMXPath($doc);
    $dmRefIdent = $domXpath->evaluate("//identAndStatusSection/descendant::applicCrossRefTableRef/descendant::dmRefIdent")[0];
    $ACTFilename = CSDBStatic::resolve_dmIdent($dmRefIdent);
    $this->ACTdoc = new \DOMDocument();
    if($this->ACTdoc->load($path . DIRECTORY_SEPARATOR . $ACTFilename)){
      $domxpath = new \DOMXPath($this->ACTdoc);
      $dmRefIdent = $domxpath->evaluate("//identAndStatusSection/descendant::applicCrossRefTableRef/descendant::dmRefIdent")[0];
      $CCTFilename = CSDBStatic::resolve_dmIdent($dmRefIdent);
      $this->CCTdoc->load($path . DIRECTORY_SEPARATOR . $CCTFilename);
    }

    $id = $applic->getAttribute('id');
    $childApplic = Helper::children($applic);
    $result = [];
    foreach ($childApplic as $child) {
      $result[$child->tagName] = $this->resolve_childApplic($child, $keepOneByOne, $useDisplayName);
    }
    return ($id) ? ($applicability[$id] = $result) : $applicability[] = $result;
  }

  /**
   * @deprecated dipindah ke Applicability.php
   * @return array containing ['text' => String, ...]
   */
  private function resolve_childApplic(\DOMElement $child, $keepOneByOne, $useDisplayName)
  {
    switch ($child->tagName) {
      case 'displayText':
        $displayText = '';
        foreach ($child->childNodes as $simplePara) {
          $displayText .= ', ' . $simplePara->textContent;
        }
        $displayText = rtrim($displayText, ", ");
        $displayText = ltrim($displayText, ", ");
        return ["text" => $displayText];
        break;
      case 'assert':
        return $this->test_assert($child, $keepOneByOne, $useDisplayName);
        break;
      case 'evaluate':
        return $this->test_evaluate($child, $keepOneByOne, $useDisplayName);
        break;
      default: 
        return '';        
    }
  }

  /**
   * @deprecated dipindah ke Applicability.php
   * saat ini, $PCT doc masih useless
   * kalau test fail, key 'text' akan di isi oleh <assert> text content dan status menjadi 'success'. Sehingga saat di <evaluate> akan true;
   * @param bool $keepOneByOne 
   * @return array ['text' => String, '%STATUS' => String ('success' or 'fail'), '%APPLICPROPERTYTYPE' => String, '%APPLICPROPERTYIDENT' => String, %APPLICPROPERTYVALUES' => String];
   */
  private function test_assert(\DOMElement $assert, bool $keepOneByOne = false, bool $useDisplayName = true) :array
  {
    foreach ($assert->attributes as $att) {
      if (!in_array($att->nodeName, ['applicPropertyIdent', 'applicPropertyType', 'applicPropertyValues'])) {
        return ['text' => $assert->textContent];
      }
    }

    $applicPropertyIdent = $assert->getAttribute('applicPropertyIdent');
    $applicPropertyType = $assert->getAttribute('applicPropertyType');
    $applicPropertyValues = $assert->getAttribute('applicPropertyValues');


    // #1 getApplicPropertyValuesFromCrossRefTable
    // validation CCTdoc
    $crossRefTable = ($applicPropertyType === 'prodattr') ? $this->ACTdoc : $this->CCTdoc;
    if (!$crossRefTable->documentElement) {
      $message = ($applicPropertyType === 'prodattr' ? "ACT " : "CCT")."document is not available in CSDB,";
      CSDBError::setError('getApplicability', $message);
      return ['text' => ''];
    }

    $crossRefTableDomXpath = new \DOMXPath($crossRefTable);
    if (str_contains(($schema = $crossRefTable->firstElementChild->getAttribute('xsi:noNamespaceSchemaLocation')), 'appliccrossreftable.xsd')) {
      $query_enum = "//enumeration[parent::*/@id = '{$applicPropertyIdent}']/@applicPropertyValues";
      $valueDataType = $crossRefTableDomXpath->evaluate("//productAttribute[@id = '{$applicPropertyIdent}']");
      $valueDataType = (count($valueDataType) > 0) ? ($valueDataType[0]->getAttribute('valueDataType') ?? null) : null;

      $displayName = $crossRefTableDomXpath->evaluate("//productAttribute[@id = '{$applicPropertyIdent}']/displayName");
      $displayName = isset($displayName[0]) ? $displayName[0]->textContent : '';
    } elseif (str_contains(($schema = $crossRefTable->firstElementChild->getAttribute('xsi:noNamespaceSchemaLocation')), 'condcrossreftable.xsd')) {
      $query_condTypeRefId = "//cond[@id = '{$applicPropertyIdent}']/@condTypeRefId";
      $condTypeRefId = $crossRefTableDomXpath->evaluate($query_condTypeRefId);
      $condTypeRefId = $condTypeRefId[0]->value;
      $query_enum = "//enumeration[parent::*/@id = '{$condTypeRefId}']/@applicPropertyValues";

      $valueDataType = $crossRefTableDomXpath->evaluate("//condType[@id = '{$condTypeRefId}']");
      $valueDataType = (count($valueDataType) > 0) ? ($valueDataType[0]->getAttribute('valueDataType') ?? null) : null;

      $displayName = $crossRefTableDomXpath->evaluate("//condType[@id = '{$condTypeRefId}']/displayName");
      $displayName = isset($displayName[0]) ? $displayName[0]->textContent : '';
    } else {
      return ['text' => ''];
    }
    $enums = $crossRefTableDomXpath->evaluate($query_enum);
    $applicPropertyValuesFromCrossRefTable = '';
    $pattern = $crossRefTableDomXpath->evaluate("//@valuePattern[parent::*/@id = '$applicPropertyIdent']");
    $pattern = (count($pattern) > 0) ? $pattern[0]->nodeValue : null;
    if (count($enums) == 0) {
      // isexistValuePattern()
      if ($pattern) {
        $propertyValue = trim($pattern);
        $propertyValue = substr_replace($propertyValue, "", 0, 1);
        $propertyValue = substr_replace($propertyValue, "", strlen($propertyValue) - 1, 1);
        $applicPropertyValuesFromCrossRefTable = $propertyValue;
      }
    } else {
      $applicPropertyValuesFromCrossRefTable = $enums[0]->value;
    }

    // #2 generateValue for Nominal and Produced/actual value
    $generateValue = function (string $applicPropertyValues) use ($valueDataType, $pattern) {
      $values_generated = array();
      // breakApplicPropertyValues()
      // $applicPropertyValues = "N071|N001N005`N010|N015throughN020|N020|N030~N035|N001~N005~N010";
      // $regex[0] untuk match ->N030~N035<- ->N001~N005~N010<-
      // $regex[1] untuk match ->N071<- ->N015throughN020<- ->N020<-
      // semua value yang akan di cek terhadap @valuePattern (jika @valueDataType is string) ada dalam match-group ke 1(index ke 1) atau 2 atau 3
      // jika range (tilde) maka $start = group 1; $end = group 2
      // jika singe value maka group 3
      $regex = ["([A-Za-z0-9\-\/]+)~([A-Za-z0-9\-\/]+)(?:[~`!@#$%^&*()\-_+={}\[\]\\;:'" . '",<.>\/? A-Za-z0-9]+)*', "|", "(?<![`~!@#$%^&*()-_=+{}\[\]\\;;'" . '",<.>\/? ])([A-Za-z0-9\-\/]+)(?![`~!@#$%^&*()-_=+{}\[\]\\;;' . "',<.>\/? ])"]; // https://regex101.com/r/vKhlJB/3 account ferdisaptoko@gmail.com
      $regex = "/" . implode($regex) . "/";
      preg_match_all($regex, $applicPropertyValues, $matches, PREG_SET_ORDER, 0); // matches1 = "N003~N005", matches2 = "N010~N015"
      foreach ($matches as $values) {
        // get start value for iterating
        $start = null;
        $end = null;
        $singleValue = null;
        if ($valueDataType != 'string') {
          $start = $values[1];
          $end = $values[2];
          $singleValue = (isset($values[3]) and $values[3]) ? $values[3] : null;
        } else {
          if (!empty($pattern)) { // jika mau di iterate
            preg_match_all($pattern, $values[1], $matches, PREG_SET_ORDER);
            $start = isset($matches[0][0]) ? $matches[0][1] : null;
            preg_match_all($pattern, $values[2], $matches, PREG_SET_ORDER);
            $end = isset($matches[0][0]) ? $matches[0][1] : null;
            if ((isset($values[3]) and $values[3])) {
              preg_match_all($pattern, $values[2], $matches, PREG_SET_ORDER);
              $singleValue = isset($matches[0][0]) ? $matches[0][1] : null;
            }
          }
        }
        if ($start and $end) {
          $range = range($start, $end);
          foreach ($range as $v) ($values_generated[] = $v);
        }
        if ($singleValue) {
          $values_generated[] = $singleValue;
        }
      }
      return $values_generated;
    };

    $nominalValues = $generateValue($applicPropertyValuesFromCrossRefTable);
    $producedValues = $generateValue($applicPropertyValues);
    $testedValues = array();
    $successValues = array();
    $failValues = array();
    if (!empty($nominalValues) and !empty($producedValues)) {
      $status = 'success';
      foreach ($producedValues as $value) {
        // walaupun aday ang ga match antara produced dan nominal values, tidak membuat semuanya false
        // $testedValues[] = $value;
        // if (!in_array($value, $nominalValues)) $status = 'fail'; // jika ada yang tidak sama, maka dikasi status fail, tapi tetap masuk ke testedValue. Intinya testedValues = produced Values

        // jika ada yang tidak sama, maka dikasi status fail. Value yang tidak sama akan di pisah;
        if (!in_array($value, $nominalValues)) {
          $status = 'fail';
          $failValues[] = $value;
        } else {
          $successValues[] = $value;
        }
      }

      if (in_array($applicPropertyIdent, ['SERIALNUMBER', 'Serialnumber', 'serialnumber', 'serialNumber', 'SerialNumber', 'SERIAL_NUMBER', 'Serial_umber', 'serial_number', 'serial_Number', 'Serial_Number'])) {
        $translator = function ($values) use ($keepOneByOne, $pattern) {
          // ubah keep nya jika ingin oneByOne atau tidak
          $oneByOne = false;
          $length = count($values);
          $s = [];
          $i = 0;
          $span = '-';
          while (isset($values[$i])) {
            $s[] = $values[$i];
            if ($keepOneByOne and ($i < $length - 1)) $s[] = ', ';

            if (
              isset($values[$i + 1]) and
              (($values[$i + 1] - $values[$i]) >= 1)
            ) {
              if ((count($s) > 1) and !$oneByOne) {
                array_pop($s);
                if ($keepOneByOne) $s[] = ', ';
                $oneByOne = false;
              } else {
                // $keepOneByOne ? null : ($s[] = ' through ');
                $keepOneByOne ? null : ($s[] = $span);
              }
              if (($values[$i + 1] - $values[$i]) >= 2) {
                if (!$keepOneByOne) $s[] = $values[$i];
                if (!$keepOneByOne) $s[] = ', ';
                $oneByOne =  true;
              } else {
                $oneByOne = ($keepOneByOne) ? true : false;
              }
            }
            $i++;
          }
          foreach ($s as $k => $v) {
            if ($v === $span) {
              // maksudnya jika N011 ~ N012, akan diganti menjadi N011, N012 (ganti tilde pakai comma);
              if (abs($s[$k + 1] - $s[$k - 1]) === 1) {
                $s[$k] = ', ';
              }
              // maksudnya jika N011 ~ N011, akan diganti menjadi N011, (hapus salah satu karena sama-sama N011);
              elseif (abs($s[$k + 1] - $s[$k - 1]) === 0) {
                $s[$k] = '';
                $s[$k + 1] = '';
              }
            }
          }
          if ($pattern) {
            $regex = "/.*(\(.*\)).*/"; // akan match dengan yang didalam kurungnya /N(219)/ akan match dengan 219
            preg_match_all($regex, $pattern, $structure, PREG_SET_ORDER, 0);
          }
          foreach ($s as $n => $v) {
            if (!is_string($v)) {
              $s[$n] = sprintf('%03d', $s[$n]);
              if ($pattern) {
                if ($structure) {
                  $newValue = str_replace($structure[0][1], $s[$n], $structure[0][0]); // $newValue = "/N001/"
                  $newValue = trim($newValue);
                  $newValue = substr_replace($newValue, "", 0, 1); // delete "/" di depan
                  $newValue = substr_replace($newValue, "", strlen($newValue) - 1, 1); // delete "/" dibelakang
                  $s[$n] = $newValue;
                }
              }
            }
          }
          $s = (join("", $s));
          return $s;
        };
        $s = $translator($successValues);
        if ($status === 'fail') {
          $testedValues['%MESSAGE'] = "ERROR: '$applicPropertyIdent' only contains $s and does not contains such ";
          $s = $translator($failValues);
          $testedValues['%MESSAGE'] .= $s;
          $testedValues['text'] = '';
        } 
        else {
          $testedValues['text'] = $s;
        }
      } else {
        $r = join(", ", $successValues);
        $testedValues['text'] = $r;
        if ($status === 'fail') {
          $r = join(", ", $failValues);
          $testedValues['%MESSAGE'] = "ERROR: For '$applicPropertyIdent' does not contains such $r";
        }
      }

      if($useDisplayName AND $status === 'success'){
        $testedValues['text'] = $displayName ? ($displayName. ": " . $testedValues['text']) : $testedValues['text'];
      }
      if($status === 'fail'){
        $testedValues['text'] = !empty($assert->textContent) ? $assert->textContent: $testedValues['text'];
        $status = empty($testedValues['text']) ? $status : 'success';
      }
      $testedValues['%STATUS'] = $status;
      $testedValues['%APPLICPROPERTYTYPE'] = $applicPropertyType;
      $testedValues['%APPLICPROPERTYIDENT'] = $applicPropertyIdent;
      $testedValues['%APPLICPROPERTYVALUES'] = $applicPropertyValues;
    }
    // $ret = array($applicPropertyIdent => $testedValues);
    // return $ret;
    // dump($testedValues);
    return $testedValues;
  }

  /**
   * @deprecated dipindah ke Applicability.php
   * DEPRECIATED. Dipindah ke ./Main/Helper class
   * @return array ['text' => string, 'andOr' => String, 'children' => array contain evaluated child]
   */
  private function test_evaluate(\DOMElement $evaluate, bool $keepOneByOne = false, $useDisplayName = true)
  {
    $children = Helper::children($evaluate);
    foreach ($children as $child) {
      $resolved[] = $this->resolve_childApplic($child, $keepOneByOne, $useDisplayName);
    }
    $andOr = $evaluate->getAttribute('andOr');
    $text = '';
    $loop = 0;
    $failtext = '';

    if ($andOr === 'and') {
      $isFail = array_filter($resolved, (fn($r) => isset($r['%STATUS']) AND $r['%STATUS']  === 'fail' ? $r : false));
      if (!empty($isFail)) {
        return ['text' => '', 'andOr' => $andOr, 'children' => $resolved];
      }
    }

    $evaluatedElement = [];
    while (isset($resolved[$loop])) {
      if (count($resolved) > 2) {
        $separator = isset($resolved[$loop + 1]) ? ', ' : ", {$andOr} ";
      } else {
        $separator = " {$andOr} ";
      }

      if ($andOr === 'or') {
        if ($resolved[$loop]['%STATUS'] === 'success') {
          if ($resolved[$loop]['text']) {
            $text .= $separator . $resolved[$loop]['text'];
          }
        } else {
          if ($resolved[$loop]['text']) {
            $failtext .= $separator . $resolved[$loop]['text'];
          }
        }
      } else {
        $text .= $separator . $resolved[$loop]['text'];
      }
      $evaluatedElement[] = $resolved[$loop];
      $loop++;
    }

    $text = ltrim($text, $separator);
    return ['text' => '('.$text.')', 'andOr' => $andOr, 'children' => $evaluatedElement];
  }

  /**
   * @return string infoEntityIdent eg: ICN...-01.jpeg,hot-001 atau (tanpa hot-001) eg: ICN...-01.jpeg
   * @return Array ['ICN....-01.jpeg', 'hot-001'];
   * fungsi ini lebih diperuntukkan untuk hot
   * example: input $id = fig-001-gra-001-hot-001 (menggunakan hotspot dari IMF) 
   * pada data module: 
   * <figure id="fig-001">
   *    <graphic id="fig-001-gra-001" infoEntityIdent="..."/>
   * </figure> 
   * <internalRef internalRefTargetType="irtt51" internalRefId="fig-001-gra-001-hot-001">tes hotspot</internalRef>
   */
  public function getEntityIdentFromId(string $id, $return = 'string') :array|string
  {
    if(!$this->document) return [];
    $domXpath = new \DOMXPath($this->document);
    $res = $domXpath->evaluate("//*[@id = '{$id}']");
    // evaluasi id secara langsung, return
    if ($res->length > 0) {
      $infoEntityIdent = $res[0]->getAttribute('infoEntityIdent');
      return $return == 'string' ? $infoEntityIdent : [$infoEntityIdent, ''];
    } else {
      $id_array = explode('-', $id); //ex: [gra,001,hot,001] 

      // jika ganjil maka return ''. Harusnya genap karena ada dash pada setiap id fig-001-gra-001
      if (($length_id_array = count($id_array)) % 2) {
        return '';
      }

      // jika > 2 artinya levelled internalRefid="fig-001-gra-001" (cari graphic 1 yang parentnya fig-001);
      elseif ($length_id_array > 2) {
        $descendant_id = [$id_array[$length_id_array - 2], $id_array[$length_id_array - 1]];
        unset($id_array[$length_id_array - 2]);
        unset($id_array[$length_id_array - 1]);

        $try_id = join('-', $id_array);
        $res = $domXpath->evaluate("//*[@id = '{$try_id}']");
        if ($res->length > 0) {
          $infoEntityIdent = $res[0]->getAttribute('infoEntityIdent');
          return $return == 'string' ? ($infoEntityIdent . ',' . join('-', $descendant_id)) : [$infoEntityIdent, join('-', $descendant_id)];
        }
      }

      /**
       * jika cara penamaan id pada <graphic> tidak levelled maka xpath = "//descendant::*[@id = 'fig-001'/descendant::*[@id = 'gra-001']]"
       * <graphic id="gra-001" infoEntityIdent="..."/>
       */
      $xpath = '/';
      array_filter($id_array, function ($v, $k) use ($id_array, &$xpath) {
        if ($k % 2) {
          $id_array[$k - 1] .= "-{$v}";
          $xpath .= "/descendant::*[@id = '{$id_array[$k - 1]}']";
          unset($id_array[$k]);
        }
      }, ARRAY_FILTER_USE_BOTH); // ex: $id_array = [gra-001,hot-001] 
      $res = $domXpath->evaluate($xpath);
      if ($res->length <= 0) {
        return '';
      }
      $infoEntityIdent = $res[0]->getAttribute('infoEntityIdent');

      // jika ada $descendant_id artinya levelled id
      if ($return == 'string') {
        return  isset($descendant_id) ? ($infoEntityIdent . ',' . join('-', $descendant_id)) : ($infoEntityIdent);
      } else {
        return  isset($descendant_id) ? [$infoEntityIdent, $descendant_id] : [$infoEntityIdent, ''];
      }
    }
  }

  /**
   * belum mengakomodir element <answer>
   * @param string $name adalah filename sebuah csdb object
   * @return Array contains all dmlEntry or array contain decode dmlEntry result
   */
  public function identifyDmlEntries(string $name = '') :array
  {
    if(!$this->document) return [];
    $dmlEntries = $this->document->getElementsByTagName('dmlEntry');
    $entries = [];
    foreach ($dmlEntries as $position => $dmlEntry) {
      $dmlEntryType = $dmlEntry->getAttribute('dmlEntryType');
      $issueType = $dmlEntry->getAttribute('issueType');

      $initial = str_replace("Ref", '', $dmlEntry->firstElementChild->tagName);
      $code = call_user_func_array(self::class . "::resolve_{$initial}Ident", [$dmlEntry->firstElementChild]);
      if ($name and (str_contains($name, $code) or str_contains($code, $name))) $found = true; // untuk ngecek $name, extension filename tidak dipermasalahkan karena dicheck dua arah. Jika filename ICN-...jpeg dicheck terhadap ICN-...png, $found = false;
      preg_match('/\.\w+$/', $code, $matches);
      $code_extension = $matches[0] ?? '';
      $code = str_replace($code_extension, '', $code);

      $security = $dmlEntry->getElementsByTagName('security')[0];
      $securityClassification = ($security ? $security->getAttribute('securityClassification') : '');
      $commercialClassification = ($security ? $security->getAttribute('commercialClassification') : '');
      $caveat = ($security ? $security->getAttribute('caveat') : '');
      $derivativeClassificationRefId = ($security ? $security->getAttribute('derivativeClassificationRefId') : '');

      $responsiblePartnerCompany = $dmlEntry->getElementsByTagName('responsiblePartnerCompany')[0];
      $enterpriseCode = $responsiblePartnerCompany->getAttribute('enterpriseCode');
      $enterpriseName = $responsiblePartnerCompany->firstElementChild->nodeValue;

      $remarks = $dmlEntry->getElementsByTagName('remarks')[0];
      if ($remarks) {
        $simpleParas = $remarks->childNodes;
        $remark = [];
        foreach ($simpleParas as $simplePara) {
          $remark[] = $simplePara->nodeValue;
        }
        $remark = join(" ", $remark);
      }

      $ret = [
        'code' => $code,
        'extension' => $code_extension,
        'position' => $position,
        // 'objects' => $objects,
        'dmlEntryType' => $dmlEntryType,
        'issueType' => $issueType,
        'security' => [
          'securityClassification' => $securityClassification,
          'commercialClassification' => $commercialClassification,
          'caveat' => $caveat,
          'derivativeClassificationRefId' => $derivativeClassificationRefId,
        ],
        'responsiblePartnerCompany' => [
          'enterpriseCode' => $enterpriseCode,
          'enterpriseName' => $enterpriseName,
        ],
        'remark' => $remark ?? ''
      ];

      if (isset($found) and $found) return $ret;
      $entries[] = $ret;
    }
    return $entries;
  }

  /**
   * @deprecated
   * @return bool
   */
  public function commit() :bool
  {
    if(!$this->document) return false;
    $initial = $this->getInitial();
    $domxpath = new \DOMXPath($this->document);
    $issueInfo = $domxpath->evaluate("//identAndStatusSection/{$initial}Address/{$initial}Ident/issueInfo")[0];
    $inWork = (int)$issueInfo->getAttribute('inWork');
    if ($inWork == 0) {
      CSDBError::setError('commit', "{$this->filename} cannot be commited due to the current inWork is '00'.");
      return false;
    }
    if ($inWork == 99) ($inWork = 'AA');
    else ($inWork++);
    $inWork = str_pad($inWork, 2, '0', STR_PAD_LEFT);
    $issueInfo->setAttribute('inWork', $inWork);

    if($ident = $domxpath->evaluate("identAndStatusSection/{$initial}Address/{$initial}Ident") AND !empty($ident[0])){
      $new_filename = call_user_func(
        CSDBStatic::class . "::resolve_" . $initial . "Ident", 
        $ident[0]
      );
      $this->filename = $new_filename;
    } else return false;

    return true;
  }

  /**
   * @deprecated, transformation sudah ada class nya sendiri
   * helper function untuk crew.xsl
   * ini tidak bisa di pindah karena bukan static method
   * * sepertinya bisa dijadikan static, sehingga fungsinya lebih baik ditaruh di CsdbModel saja
   */
  public function setLastPositionCrewDrillStep(int $num)
  {
    $this->lastPositionCrewDrillStep = $num;
  }

  /**
   * @deprecated, transformation sudah ada class nya sendiri
   * helper function untuk crew.xsl
   * ini tidak bisa di pindah karena bukan static method
   * sepertinya bisa dijadikan static, sehingga fungsinya lebih baik ditaruh di CsdbModel saja
   */
  public function getLastPositionCrewDrillStep()
  {
    return $this->lastPositionCrewDrillStep ?? 0;
  }

  /**
   * @deprecated, transformation sudah ada class nya sendiri
   * @param string $xslFile is absolute path of xsl file
   * @param array $params is associative array where is inclusion for XSL processor
   * @param string $output is 'html', 'pdf'
   * @return string
   */
  public function transform_to_xml(string $xslFile, array $params = [], string $output = 'html') :string
  {
    $xsl = new \DOMDocument();
    if(!$xsl->load($xslFile)){
      return '';
    }
    $xsltproc = new \XSLTProcessor();
    $xsltproc->importStylesheet($xsl);

    $xsltproc->registerPHPFunctions((fn () => array_map(fn ($name) => CSDBStatic::class . "::$name", get_class_methods(CSDBStatic::class)))());
    $xsltproc->registerPHPFunctions((fn () => array_map(fn ($name) => Helper::class . "::$name", get_class_methods(Helper::class)))());
    $xsltproc->registerPHPFunctions((fn () => array_map(fn ($name) => self::class . "::$name", get_class_methods(self::class)))());
    $xsltproc->registerPHPFunctions();    

    foreach ($params as $key => $param) {
      $xsltproc->setParameter('', $key, $param);
    }
    // dd($xsl);
    $xsltproc->setParameter('', 'ConfigXML_uri', $this->ConfigXML->baseURI);
    // dd($this->document);
    $transformed = $xsltproc->transformToDoc($this->document);
    if(!$transformed) return '';
    $bookmarkTree_el = $transformed->getElementsByTagNameNS('http://www.w3.org/1999/XSL/Format', 'bookmark-tree')[0];
    
    $new_bookmarks = CSDBStatic::transformBookmark_to_xml();
    if($new_bookmarks){
      $new_bookmarks = $new_bookmarks->documentElement->cloneNode(true);
      $imported = $bookmarkTree_el->ownerDocument->importNode($new_bookmarks, true);
      $bookmarkTree_el->replaceWith($imported);
    } else {
      $bookmarkTree_el ? $bookmarkTree_el->remove() : null;
    }
    
    $transformed->preserveWhiteSpace = false;
    $transformed = $transformed->saveXML(null,LIBXML_NOXMLDECL);
    $transformed = preg_replace("/\s+/m", ' ', $transformed);
    return $transformed;    
  }

  /**
   * @param string $inputFile is absolute path of xsl file
   * @param array $params is associative array where is inclusion for XSL processor
   * @return string
   */
  public function transform_to_fo(string $inputFile, string $outputFile) :string
  {
    $pdf = new TransformerPdf(
      input: $inputFile,
      output: $outputFile
    ); 
    $pdf->config = Transformator::config_uri();
    $pdf->configurableValues = Transformator::configurableValues_uri();
    $pdf->CSDBObject = $this;
    $create = $pdf->createFo($this->document->baseURI);
    return $create ? $outputFile : '';
  }

  /**
   * @param string $inputFile is absolute path of fo file
   * @param string $outputFile is uri for pdf
   * @return string
   */
  public function transform_to_pdf(string $inputFile, string $outputFile) :string
  {
    $pdf = new TransformerPdf(
      input: $inputFile,
      output: $outputFile
    );
    $create = $pdf->create();
    return $create ? $outputFile : '';
  }

  /**
   * Nanti dipikirkan apakah cukup pakai BREX atau BREX nya perlu di transform ke ConfigXML.
   */
  // public function translateS1000DAttributeCodeToValue()
  // {
    
  // }

  // public function transform_to_foxml(string $xslFile, array $params = [], string $output = 'html') :string
  // {
  //   $xsl = new \DOMDocument();
  //   if(!$xsl->load($xslFile)){
  //     return '';
  //   }

  //   $xsltproc = new \XSLTProcessor();
  //   $xsltproc->importStylesheet($xsl);

  //   $xsltproc->registerPHPFunctions((fn () => array_map(fn ($name) => CSDBStatic::class . "::$name", get_class_methods(CSDBStatic::class)))());
  //   $xsltproc->registerPHPFunctions((fn () => array_map(fn ($name) => Helper::class . "::$name", get_class_methods(Helper::class)))());
  //   $xsltproc->registerPHPFunctions((fn () => array_map(fn ($name) => self::class . "::$name", get_class_methods(self::class)))());
  //   $xsltproc->registerPHPFunctions();    

  //   foreach ($params as $key => $param) {
  //     $xsltproc->setParameter('', $key, $param);
  //   }

  //   $transformed = str_replace("#ln;", chr(10), $xsltproc->transformToXml($this->document));
  //   $transformed = $xsltproc->transformToXml($this->document);
  //   return $transformed;    
  // }
  

  // fungsi transform_HTML // sepertinya di taruh di Main\CSDB class saja 
  // fungsi transfrom_PDF // sepertinya di taruh di Main\CSDB class saja

  // fungsi autoGeneratedUniqueIdentifier for ICN
  
  /**
   * @deprecated
   * dipindah ke Transformer\Pdf
   * diperlukan untuk di XSL
   */
  public function set_pmEntryTitle(string $text)
  {
    $this->pmEntryTitle = $text;
  }

  /**
   * @deprecated
   * dipindah ke Transformer\Pdf
   * diperlukan untuk di XSL
   */
  public function get_pmEntryTitle()
  {
    return $this->pmEntryTitle;
  }

  /**
   * @deprecated
   * dipindah ke Transformer\Pdf
   */
  public function get_pmEntryType()
  {
    return $this->pmEntryType;
  }

  /**
   * @deprecated
   * dipindah ke Transformer\Pdf
   */
  public function set_pmEntryType(string $text)
  {
    $this->pmEntryType = $text;
  }

  // public function toPdf(string $input, string $output):bool
  // {
  //   $pdf = new Pdf($input,$output);
  //   return $pdf->create();
  // }

  /**
   * return URI of document
   */
  // public function jsonSerialize(): mixed
  // {
  //   return ["URI"=>(($this->document instanceof \DOMDocument) && $this->document->baseURI ? $this->document->baseURI : (
  //     ($this->document instanceof ICNDocument) && $this->document->filename ? $this->document->getURI() : ([])
  //   ))];
  // }
  
}
