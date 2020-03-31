<?php

$pluginNameTmp = 'unitAndProj';

if (!isset($_SESSION['pluginsConfig'][$pluginNameTmp])) {
	$_SESSION['pluginsConfig'][$pluginNameTmp] = array();
}
if (!isset($_SESSION['pluginsConfig'][$pluginNameTmp]['projections'])) {
	$_SESSION['pluginsConfig'][$pluginNameTmp]['projections'] = array();
}

$projs = array();
if (is_array($_SESSION['pluginsConfig'][$pluginNameTmp]['projections'])
&& count($_SESSION['pluginsConfig'][$pluginNameTmp]['projections'])
&& isset($_SESSION['pluginsConfig'][$pluginNameTmp]['projections']['prj'])) {
	if (!is_array($_SESSION['pluginsConfig'][$pluginNameTmp]['projections']['prj'])) {
		$projs = array($_SESSION['pluginsConfig'][$pluginNameTmp]['projections']['prj']);
	} else {
		$projs = $_SESSION['pluginsConfig'][$pluginNameTmp]['projections']['prj'];
	}
} else if (isset($_SESSION['pluginsConfig']['coordinates']) 
&& isset($_SESSION['pluginsConfig']['coordinates']['prj'])) {
	$projs = $_SESSION['pluginsConfig']['coordinates']['prj'];
}
$_SESSION['pluginsConfig'][$pluginNameTmp]['projections']['prj'] = $projs;

?>