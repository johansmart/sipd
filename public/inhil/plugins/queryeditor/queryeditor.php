<?php

/******************************************************************************
 *
 * Purpose: 
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

require_once($_SESSION['PM_PLUGIN_REALPATH'] . "/common/groupsAndLayers.php");

function queryeditorGetGroups($map) {
	$groups = array();
	$layersType = isset($_SESSION['pluginsConfig']['queryeditor']['layersType']) ? $_SESSION['pluginsConfig']['queryeditor']['layersType'] : 3;
		
	$groups = Array();
	$groupsToTransform = Array();
	$bTransformGroupsArray = true;
	switch ($layersType) {
		// checked and visible non raster layers only :
		case 4:
			$groupsToTransform = getAvailableGroups($map, true, true, true, true, true);
			break;
		// checked non raster layers only :
		case 3 :
			$groupsToTransform = getAvailableGroups($map, true, true, false, true, true);
			break;
		// pre-defined list :
		case 2 :
			if (isset($_SESSION['pluginsConfig']['queryeditor']['queryableLayers'])
				&& $_SESSION['pluginsConfig']['queryeditor']['queryableLayers']['queryableLayer']
				&& count($_SESSION['pluginsConfig']['queryeditor']['queryableLayers']['queryableLayer']) > 0) {
				$layers = $_SESSION['pluginsConfig']['queryeditor']['queryableLayers']['queryableLayer'];
				
				if ($layers && array_key_exists('name', $layers)) {
					$layers = array($layers);
				}

				foreach ($layers as $layer) {
					if (isset($layer['name']) && $layer['name']) {
						$description = $layer['name'];
						if (isset($layer['description']) && $layer['description']) {
							$description = $layer['description'];
						} else {
							$groupList = $_SESSION['grouplist'];
							$group = $groupList["$groupName"];
							$description = $group->getDescription();
						}
						$groups[$layer['name']] = $description;
					}
				}
				$bTransformGroupsArray = false;
			}
			break;
		// all non raster layers :
		case 1 :
		default:
			$groupsToTransform = getAvailableGroups($map, false, true, false, true, true);
			break;
	}
	if ($bTransformGroupsArray && $groupsToTransform) {
		foreach ($groupsToTransform as $groupToTransform) {
			if ($groupToTransform->getGroupName() && $groupToTransform->getDescription()) {
				$groups[$groupToTransform->getGroupName()] = $groupToTransform->getDescription();
			}
		}
	}

	return $groups;
}
?>