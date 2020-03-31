<?php

/******************************************************************************
 *
 * Purpose: increase or decrease object's size
 * Author:  Christophe Arioli, SIRAP
 *
 *****************************************************************************
 *
 * Copyright (c) 2011 SIRAP
 *
 * p.mapper is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version. See the COPYING file.
 *
 * p.mapper is distributed in the hope that it will be useful,
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
if (isset($_REQUEST['PM_INCPHP'])) exit();

require_once('../../incphp/pmsession.php');

require_once('../../incphp/globals.php');


/**
 * return code :
 *   0 = OK
 *   1 = maximum number of application of the factor reached
 *   2 = minimum number of application of the factor reached
*/
$codeRet = 0;

$layer = isset($_REQUEST['layer']) ? $_REQUEST['layer'] : null;
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 0;

$layerArray = $_SESSION['sizeUpDownObj']['layers'];

// clear array $_SESSION['layerToSizeUpDown']
if ($action == 'clear'){
	$layerArray = array();

// remove layer in array $_SESSION['layerToSizeUpDown']
} elseif ($action == 'reset'){
	if (isset($layerArray[$layer])) {
		unset($layerArray[$layer]);
	}

// increase or decrease size
} else {
	// if layer not in array $_SESSION['layerToSizeUpDown'] -> add it
	if (!isset($layerArray[$layer])) {
		$layerArray[$layer] = $action;
	
	// if layer is already in the list (= already been increased or decreased)
	// --> increase or decrease the numbrer of application of the factor
	} else {
		$layerArray[$layer] += $action;

		$maxSizeUpIterator = $_SESSION['sizeUpDownObj']['maxSizeUpIterator'];
		$minSizeDownIterator = -abs($_SESSION['sizeUpDownObj']['maxSizeUpIterator']);

		// if numbrer of application of the factor = 0
		// --> remove layer
		if ($layerArray[$layer] == 0) {
			unset($layerArray[$layer]);
			if (count($layerArray) == 0) {
				$layerArray = array();
			}
		// check numbrer of application
		} else {
			if ($layerArray[$layer] > $maxSizeUpIterator) {
				$layerArray[$layer] = $maxSizeUpIterator;
				$codeRet = 1;
			} elseif ($layerArray[$layer] < $minSizeDownIterator) {
				$layerArray[$layer] = $minSizeDownIterator;
				$codeRet = 2;
			}
		}
	}
}

$_SESSION['sizeUpDownObj']['layers'] = $layerArray;

$returnJson = json_encode(array('coderet' => $codeRet));

header("Content-Type: text/plain; charset=$defCharset");
echo $returnJson;

?>

