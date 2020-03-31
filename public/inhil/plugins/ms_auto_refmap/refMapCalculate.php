<?php

/******************************************************************************
 *
 * Purpose: Reference map auto calculate
 * Author:  Thomas Raffin, SIRAP
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

// includes:
require_once(dirname(__FILE__) . '/autoRefMap.inc.php');

// need to recalculate ?
$recalculate = (!isset($_SESSION['mapObjModifierFirstInclude']) || $_SESSION['mapObjModifierFirstInclude'] === true);
if (!$recalculate) {
	$recalculate =  !file_exists($_SESSION['ms_auto_refmap']['image']);
}

$pluginConfig = array();
// recalculate:
if ($recalculate) {
	// here $_SESSION['pluginsConfig'] is not set or contains previous values
	if (isset($ini['pluginsConfig'])) {
		$iniPluginConfig = (array)$ini['pluginsConfig'];
		if (isset($iniPluginConfig['ms_auto_refmap'])) {
			$pluginConfig = $iniPluginConfig['ms_auto_refmap'];
		}
	}
}

$autoRefMap = new AutoRefMap($map, $pluginConfig);

if ($recalculate) {
	$autoRefMap->doIt();
}

$autoRefMap->applyToGlobalMap();

?>