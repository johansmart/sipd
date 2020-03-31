<?php

require_once("../../incphp/pmsession.php");
require_once("../../incphp/globals.php");

$group = $_REQUEST['group'];
$xmlFile = $_SESSION['pluginsConfig']['layerinfo']['configfile'];
$xml = simplexml_load_file($_SESSION['PM_BASECONFIG_DIR'] . "/$xmlFile");
$grpNode = $xml->xpath("//group[@name='$group']");
if (!$grpNode) {
    $grpXml = "No information available for selected layer";
} else {
    $grpXml = $grpNode[0]->asXML();
}

header("Content-Type: text/html; charset=$defCharset");
// return JS object literals "{}" for XMLHTTP request 
echo $grpXml;
?>