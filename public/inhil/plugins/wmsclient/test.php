<?php

require_once("wmsclient.php");

$wmsurl = "http://labs.metacarta.com/wms/vmap0?";
$wmsurl = "http://wms.jpl.nasa.gov/wms.cgi?";
$wms = new WMSClient($wmsurl);

//print_r($wms->returnCapabilities()->asXML());
$layers = $wms->getLayerList();
print_r(json_encode($layers));

echo "\n";
$imgFormats = $wms->getImgFormats();
print_r(json_encode($imgFormats));

echo "\n";
$srsList = $wms->getSrsList();
print_r(json_encode($srsList));


?>