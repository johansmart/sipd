<?php

/******************************************************************************
 *
 * Purpose: selectionManagement
 * Author:  Vincent Mathis, SIRAP
 *
 ******************************************************************************
 *
 * Copyright (c) 2009 SIRAP
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

require_once('../../incphp/group.php');
require_once('../../incphp/pmsession.php');
require_once('../../incphp/globals.php');
require_once('../../incphp/common.php');
require_once($_SESSION['PM_PLUGIN_REALPATH'] . '/common/selectTools.inc.php');

$operation = $_REQUEST['operation'];

$queryResult = false;

// Remove selection
if ($operation == 'remove_selection') {
	SelectTools::removeCurrentSelection();

// Reload selection
} else if ($operation == 'reload_selection') {
	$queryResult = SelectTools::reloadSelection();
	$_REQUEST['mode'] = 'nquery';

// Remove 1
} else if ($operation == 'remove_selected') {
	$layerName = isset($_REQUEST['layerName']) ? trim($_REQUEST['layerName']) : false;
	$objIndex = isset($_REQUEST['objIndex']) ? $_REQUEST['objIndex'] : -1;

	if ($layerName && $objIndex != -1) {
		$msLayer = $map->getLayerByName($layerName);
		$groupName = $layerName;
		if ($msLayer) {
			$groupNameTmp = $msLayer->group;
			if ($groupNameTmp && in_array($groupNameTmp, $_SESSION['allGroups'])) {
				$groupName = $groupNameTmp;
			}
		}

		$queryResult .= '[ [';
		$queryResult .= '{"name": "' . $groupName . '", "description": "", "numresults":1, ';
		$queryResult .= '"header": [], "stdheader": [],';
		$queryResult .= '"values": [ ';
		$queryResult .= '[ {"shplink": ["' . $layerName . '","' . $objIndex . '","",1]}';
		$queryResult .= ']';
		$queryResult .= ']} ';
		$queryResult .= '], {} ]';
		
		// current selection
		$jsonPMResult = $_SESSION['JSON_Results'];
		$queryResult = SelectTools::del($jsonPMResult, $queryResult);
	}


	if ($queryResult) {
		// update selection
		$_SESSION['JSON_Results'] = $queryResult;
		SelectTools::updateHighlightJson($queryResult);
	} else {
		$queryResult = false;
		unset ($_SESSION['JSON_Results']);
		unset ($_SESSION['resultlayers']);
	}
}

$mode = $_REQUEST['mode'];

header("Content-type: text/plain; charset=$defCharset");
// reload:
if ($queryResult !== false) {
	echo "{\"mode\":\"$mode\", \"queryResult\":$queryResult}";
// remove:
} else {
	echo "{\"mode\":\"$mode\", \"queryResult\":0}";
}
?>