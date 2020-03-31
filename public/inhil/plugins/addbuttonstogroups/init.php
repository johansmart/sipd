<?php

/******************************************************************************
 *
 * Purpose: Additionnal buttons for each groups / layer in TOC plugin
 * Author:  Thomas Raffin, SIRAP
 *          Niccolo Rigacci <niccolo@rigacci.org>
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

// Get plugin configuration from the config file.
$abtgList = 0;
if (isset($ini['pluginsConfig'])) {
    $iniPluginConfig = (array)$ini['pluginsConfig'];
    if (isset($iniPluginConfig['addbuttonstogroups'])) {
        $pluginConfig = $iniPluginConfig['addbuttonstogroups'];
        if (isset($pluginConfig['abtgList'])) {
            $abtgList = $pluginConfig['abtgList'];
        }
    }
}
//error_log("init.php: \$abtgList = " . $abtgList);

$abtgArray = array();
$plugins = preg_split('/[\s,]+/', $ini["plugins"]);

if ($abtgList) {
	$abtgElements = explode(",", $abtgList);
	if ($abtgElements) {
		foreach ($abtgElements as $abtgElement) {
			$abtgElementParts = explode("|", $abtgElement);

			// too many parameters :
			if (count($abtgElementParts) > 6) {
				continue;
			}

			// exclusion plugin :
			if (count($abtgElementParts) == 6) {
				if ($abtgElementParts[5]) {
					if (strlen($abtgElementParts[5]) > 0) {
						if (in_array($abtgElementParts[5], $plugins)) {
							continue;
						}
					}
				}
			}

			// required plugin :
			if (count($abtgElementParts) >= 5) {
				if ($abtgElementParts[4]) {
					if (strlen($abtgElementParts[4]) > 0) {
						if (!in_array($abtgElementParts[4], $plugins)) {
							continue;
						}
					}
				}
			}

			// buttons elements :
			if (count($abtgElementParts) >= 4) {
				$nbElementsOK = 0;
				$nbTested = 0;
				foreach ($abtgElementParts as $abtgElementPart) {
					$nbTested++;
					if ($abtgElementPart) {
						if (strlen($abtgElementPart) > 0) {
							$nbElementsOK++;
						}
					}
					if ($nbTested >= 4) {
						break;
					}
				}
				if ($nbElementsOK == 4) {
					$abtgArrayElement = Array("prefix" => $abtgElementParts[0],
											"hrefjsfunction" => $abtgElementParts[1],
											"titleandimgalttext" => _p($abtgElementParts[2]),
											"imgsrc" => $abtgElementParts[3],
					);
					$abtgArray[] = $abtgArrayElement;
				}
			}
		}
	}
}

$_SESSION['abtgArray'] = $abtgArray;

?>
