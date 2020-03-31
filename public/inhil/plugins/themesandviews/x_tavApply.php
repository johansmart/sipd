<?php

/******************************************************************************
 *
 * Purpose: ThemesAndViews plugin
 * Author:  Thomas Raffin, SIRAP
 *
 ******************************************************************************
 *
 * Copyright (c) 2007 SIRAP
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version. See the COPYING file.
 *
 * This program is distributed in the hope that it will be useful,
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

/******************************************************************************
 * Apply a Theme or a View
 ******************************************************************************/

require_once('tav.php');

require_once($_SESSION['PM_INCPHP'] . '/common.php');
//require_once($_SESSION['PM_PLUGIN_REALPATH'] . '/common/common.php');

$tavLayers = '';
$transparencies = '';
$strExtent = '';
$reload = false;

if (isset($_REQUEST['type']) && ($_REQUEST['type'] == 'theme' || $_REQUEST['type'] == 'view') ) {
	// If or theme or a view has been passed to be applied:
	if (isset($_REQUEST['selected']) && $_REQUEST['selected']) {
		$tavLayers = ThemesAndViewsUtils::getListLayers($_REQUEST['selected'], ($_REQUEST['type'] == 'theme'));

		if ($_REQUEST['type'] == 'view') {
			$extentTmp = ThemesAndViewsUtils::getExtent($_REQUEST['selected']);
			if ($extentTmp) {
				if (count($extentTmp) == 4) {
					$strExtent .= str_replace(',', '.', $extentTmp['minx']) . ' ';
					$strExtent .= str_replace(',', '.', $extentTmp['miny']) . ' ';
					$strExtent .= str_replace(',', '.', $extentTmp['maxx']) . ' ';
					$strExtent .= str_replace(',', '.', $extentTmp['maxy']);
				}
			}
		}
	// if transparency / opacity values have been passed to be applied:
	} else if (isset($_REQUEST['groupsandopacities']) && $_REQUEST['groupsandopacities']) {
		$groupsAndOpacities = explode(',', $_REQUEST['groupsandopacities']);
		foreach ($groupsAndOpacities as $groupandopacity) {
			 $groupandopacityTmp = explode(':', $groupandopacity);
			 $tavGrp = Array();
			 if ($groupandopacityTmp[0]) {
			 	$tavGrp['name'] = $groupandopacityTmp[0];
				 if ($groupandopacityTmp[1]) {
				 	$tavGrp['opacity'] = $groupandopacityTmp[1];
				 }
			 }
			 if (count($tavGrp)) {
			 	$tavLayers[] = $tavGrp;
			 }
		}
		$extentTmp = $_REQUEST['extent'];
		if ($extentTmp) {
			$strExtent = str_replace(',', '.', $extentTmp);
		}
	}
}

if ($tavLayers) {
	$grouplist = $_SESSION['grouplist'];
	$oldGroups = $_SESSION['groups'];
	
	$groups = Array();

	foreach ($tavLayers as $tavLayer) {
		if ($tavLayer['name']) {
			$groupname = $tavLayer['name'];
			if (strlen($groupname) > 0) {
				// reload if layer was not visible before:
				if (!$reload) {
					$reload = in_array($groupname, $oldGroups) ? 1 : 0;
				}
				// Activate group:
				$groups[] = $groupname;

				// opacity / transparency:
				if (is_numeric($tavLayer['opacity'])) {
					$opacity = $tavLayer['opacity'];
					$transparency = 100 - $opacity;

					// Apply opacities to each group:
					$grp = $grouplist[$groupname];
					$glayerList = $grp->getLayers();
					foreach ($glayerList as $glayer) {
						// reload if opacity was different before
						if (!$reload) {
							if ($opacity != $glayer->getOpacity()) {
								$reload = true;
							}
						}
					    $glayer->setOpacity($opacity); 
					}
				} 
				// if transparency is 'map'
				// (this possibility will be used later to allow the use of the value in the mafile, or maybe in the current map)
				else {
					$transparency = 100;
					$grp = $grouplist[$groupname];
					$glayerList = $grp->getLayers();
					$glayer = $glayerList[0];
					if ($glayer) {
						$opacity = $glayer->getOpacity();
						$transparency = 100 - $opacity;
					}
				}
				$transparencies .= "\"" . $groupname . "\":" . $transparency . ",";
			}
		}
	}
	$_SESSION['groups'] = $groups;
}


if (strlen($transparencies) > 0) {
	$transparencies = substr($transparencies, 0, -1);
	$transparencies = '{'.$transparencies.'}';
} else {
	$transparencies = '{}';
}

header("Content-Type: text/plain; charset=$defCharset");
echo "{\"transparencies\":$transparencies,\"extent\":\"$strExtent\",\"reload\":\"$reload\"}";

?>