# mpub


you can add index.php in application path and try to:
$dmodule = new DModule("demo12.xml");
$dmodule::validateToSchema($dmodule->getDOMDocument());

dd($dmodule);


you can resolve data module name by:
DModule::resolveDMName($dmodule, 1);