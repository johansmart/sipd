<?php

$pluginNameTmp = 'locateXY';

if (!isset($_SESSION['pluginsConfig'][$pluginNameTmp])) {
	$_SESSION['pluginsConfig'][$pluginNameTmp] = array();
}
if (!isset($_SESSION['pluginsConfig'][$pluginNameTmp]['projections'])) {
	$_SESSION['pluginsConfig'][$pluginNameTmp]['projections'] = array();
}

$projs = array();
// this plugin
if (is_array($_SESSION['pluginsConfig'][$pluginNameTmp]['projections'])
&& count($_SESSION['pluginsConfig'][$pluginNameTmp]['projections'])
&& isset($_SESSION['pluginsConfig'][$pluginNameTmp]['projections']['prj'])) {
	if (!is_array($_SESSION['pluginsConfig'][$pluginNameTmp]['projections']['prj'])) {
		$projs = array($_SESSION['pluginsConfig'][$pluginNameTmp]['projections']['prj']);
	} else {
		$projs = $_SESSION['pluginsConfig'][$pluginNameTmp]['projections']['prj'];
	}
// unitAndProj plugin
} else if (isset($_SESSION['pluginsConfig']['unitAndProj']) 
&& isset($_SESSION['pluginsConfig']['unitAndProj']['projections'])
&& isset($_SESSION['pluginsConfig']['unitAndProj']['projections']['prj'])) {
	$projs = $_SESSION['pluginsConfig']['unitAndProj']['projections']['prj'];
// coordinates plugin
} else if (isset($_SESSION['pluginsConfig']['coordinates']) 
&& isset($_SESSION['pluginsConfig']['coordinates']['prj'])) {
	$projs = $_SESSION['pluginsConfig']['coordinates']['prj'];
}
$_SESSION['pluginsConfig'][$pluginNameTmp]['projections']['prj'] = $projs;

$mapProj = '';
// this plugin
if (!$mapProj) {
	if (isset($_SESSION['pluginsConfig'][$pluginNameTmp]['mapPrjDef'])) {
		$mapProjTmp = 'init=' . $_SESSION['pluginsConfig'][$pluginNameTmp]['mapPrjDef'];
		foreach ($projs as $proj) {
			if (strcasecmp($proj['definition'], $mapProjTmp) == 0) {
				$mapProj = $mapProjTmp;
				break;
			}
		}
	}
}
// coordinates plugin
if (!$mapProj) {
	if (isset($_SESSION['pluginsConfig']['coordinates']['mapPrj'])
	&& isset($_SESSION['pluginsConfig']['coordinates']['mapPrj']['name'])) {
		$mapProjTmp = 'init=' . $_SESSION['pluginsConfig']['coordinates']['mapPrj']['name'];
		foreach ($projs as $proj) {
			if (strcasecmp($proj['definition'], $mapProjTmp) == 0) {
				$mapProj = $mapProjTmp;
				break;
			}
		}
	}
}

$_SESSION['pluginsConfig'][$pluginNameTmp]['projections']['prj'] = $projs;

?>