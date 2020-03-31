<?php
require_once("../../incphp/pmsession.php");
require_once("../../incphp/globals.php");
require_once("../../incphp/common.php");
require_once("projection.php");

$clickX = $_REQUEST['x'];
$clickY = $_REQUEST['y'];

$fromPrj = $map->getProjection();

if (!isset($_SESSION['pluginsConfig']['coordinates'])) {
    pm_logDebug(0, "P.MAPPER-ERROR: Configuration under '<pluginsConfig><coordinates>' is missing. Check your config.xml file.");
    return false;
}

$prjCfg = $_SESSION['pluginsConfig']['coordinates'];
$mapPrj = $prjCfg['mapPrj'];
$mapPrjName = _p($mapPrj['name']);

$showX = round($clickX, $mapPrj['roundTo']);
$showY = round($clickY, $mapPrj['roundTo']);

$prjJson = "[";
$prjJson .= "{\"prjName\": \"$mapPrjName\", \"x\": $showX, \"y\": $showY},";

$prjList = $prjCfg['prj'];
$prjTmp = array();
foreach ($prjList as $p) {
    $prjName = _p($p['name']);
    $roundTo = $p['roundTo'];
    $toPrj = $p['definition'];
    $prj = new Projection($clickX, $clickY, $fromPrj, $toPrj);
    $x = $prj->getX();
    $y = $prj->getY();
    
    //round values
    $x = round($x, $roundTo);
    $y = round($y, $roundTo);
    
    $prjTmp[] = "{\"prjName\": \"$prjName\", \"x\": $x, \"y\": $y}";
}
$mapPrjName = _p($mapPrjName);
$prjTmpStr = implode(',', $prjTmp);
if (!$prjTmpStr) {
	$prjTmpStr = '[]';
}

$prjJson .= " $prjTmpStr]";


header("Content-Type: text/plain; charset=$defCharset");

// return JS object literals "{}" for XMLHTTP request 
echo "{\"prjJson\": $prjJson}";
?>