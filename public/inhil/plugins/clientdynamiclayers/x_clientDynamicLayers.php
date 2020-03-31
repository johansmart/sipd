<?php

/******************************************************************************
 *
 * Purpose: Add / update / remove dynamic layers to pmapper
 * Author:  Thomas Raffin, SIRAP
 *
 ******************************************************************************
 *
 * Copyright (c) 2008 SIRAP
 *
 * This is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version. See the COPYING file.
 *
 * This software is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with p.mapper; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 ******************************************************************************/

// prevent XSS
if (isset($_REQUEST['_SESSION'])) exit();

require_once("../../incphp/group.php");
require_once('../../incphp/pmsession.php');

// get json layers definition and data, then decode them:
$newLayersJson = isset($_REQUEST["layers"]) ? $_REQUEST["layers"] : '{}';
$newLayersJson = str_replace(array("\\'", '\\"', "\\\\\"", "\r", "\n", "\t"), array("'", "\"", "\\\"", "", "", ""), $newLayersJson);
$reqLayers = json_decode($newLayersJson);

$layersAtBegenning = $actualLayers = isset($_SESSION['clientDynamicLayers']) ? $_SESSION['clientDynamicLayers'] : array();
$clientDynamicLayers = array();

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

// remove layers:
if ($action == 'remove') {
	// if 1 or more dynamic layers exist
	if (is_array($actualLayers) && count($actualLayers)) {
		// search the layer to remove in the actual list
		// and reconstruct the list
		$newLayers = array();
		foreach ($actualLayers as $actualLayer) {
			$found = false;
			foreach ($reqLayers as $reqLayer) {
				if ($actualLayer->def->layername == $reqLayer->def->layername) {
					$found = true;
					break;
				}
			}
			if (!$found) {
				$newLayers[] = $actualLayer;
			}
		}
		$clientDynamicLayers = $newLayers;
	}

// add or replace layers:
} else if ($action == 'addOrReplace') {
	// if 1 or more dynamic layers exist
	if (is_array($actualLayers) && count($actualLayers)) {
		$newLayers = array();
		foreach ($actualLayers as $actualLayer) {
			$found = false;
			// replace each existing layer:
			foreach ($reqLayers as $reqLayer) {
				if ($actualLayer->def->layername == $reqLayer->def->layername) {
					$newLayers[] = $reqLayer;
					$found = true;
					break;
				}
			}
			// keep actual layer that should not be replaced:
			if (!$found) {
				$newLayers[] = $actualLayer;
			}
		}
		// add a new layers:
		foreach ($reqLayers as $reqLayer) {
			if ($reqLayer->def->layername) {
				$found = false;
				foreach ($actualLayers as $actualLayer) {
					if ($actualLayer->def->layername == $reqLayer->def->layername) {
						$found = true;
						break;
					}
				}
				if (!$found) {
					$newLayers[] = $reqLayer;
				}
			}
		}
		$clientDynamicLayers = $newLayers;
	} else {
		$clientDynamicLayers = $reqLayers;
	}

// add / replace layers and remove others
} else if ($action == 'replaceAll') {
	$clientDynamicLayers = $reqLayers;
}

$_SESSION['clientDynamicLayers'] = $clientDynamicLayers;

// list of all dynamic layers:
$activeLayers = array();
// list of dynamic layers to remove
$addedLayers = array();
if (count($clientDynamicLayers) > 0) {
	foreach ($clientDynamicLayers as $clientDynamicLayer) {
		$found = false;
		if (count($layersAtBegenning) > 0) {
			foreach ($layersAtBegenning as $layerAtBegenning) {
				if ($layerAtBegenning->def->layername == $clientDynamicLayer->def->layername) {
					$found = true;
					break;
				}
			}
		}
		if (!$found && ($action == 'addOrReplace')) {
			$addedLayers[] = $clientDynamicLayer->def->layername;
			$activeLayers[] = $clientDynamicLayer->def->layername;
		}
	}
}
$activeLayersStr = json_encode($activeLayers);
$addedLayersStr = json_encode($addedLayers);

// list of dynamic layers to remove
$removedLayers = array();
if (count($layersAtBegenning) > 0) {
	foreach ($layersAtBegenning as $layerAtBegenning) {
		$found = false;
		if (count($clientDynamicLayers) > 0) {
			foreach ($clientDynamicLayers as $clientDynamicLayer) {
				if ($layerAtBegenning->def->layername == $clientDynamicLayer->def->layername) {
					$found = true;
					break;
				}
			}
		}
		if (!$found) {
			$removedLayers[] = $layerAtBegenning->def->layername;
		}
	}
}
$removedLayersStr = json_encode($removedLayers);

// send layers lists 
$defCharset = $_SESSION["defCharset"];
header("Content-type: text/plain; charset=$defCharset");
echo "{\"activeLayers\":$activeLayersStr,\"addedLayers\":$addedLayersStr,\"removedLayers\":$removedLayersStr}";

// update groups/layers by removing layers:
if (count($removedLayers) > 0) {
	$allGroups = $_SESSION["allGroups"];
	$newAllGroups = array();
	foreach ($allGroups as $grp) {
		$found = false;
		foreach ($removedLayers as $removedLayer) {
			if ($grp == $removedLayer) {
				$found = true;
				break;
			}
		} if (!$found) {
			$newAllGroups[] = $grp;
		}		
	}
	$_SESSION["allGroups"] = $newAllGroups;
}



// Add new / remove old layers to group list:
if ( ($addedLayersStr != '[]') || ($removedLayersStr != '[]')) {
	require_once($_SESSION['PM_INCPHP'] . '/common.php');
	require_once($_SESSION['PM_INCPHP'] . '/globals.php');
	
	require_once(dirname(__FILE__) . '/../common/groupsAndLayers.php');
	updateGroupList($map);
}


// (re-) regenrate legend :
if (count($activeLayers) > 0) {
	require_once($_SESSION['PM_INCPHP'] . '/common.php');
	require_once($_SESSION['PM_INCPHP'] . '/globals.php');
	require_once($_SESSION['PM_INCPHP'] . '/initgroups.php');
	require_once($_SESSION['PM_INCPHP'] . '/init/initmap.php');
	$initMap = new Init_map($map, false, false, $_SESSION['gLanguage'], true);
	$initMap->createLegendList();
}

?>