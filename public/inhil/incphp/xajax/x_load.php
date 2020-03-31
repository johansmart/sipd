<?php

/******************************************************************************
 *
 * Purpose: main class for updating/loading map
 * Author:  Armin Burger
 *
 ******************************************************************************
 *
 * Copyright (c) 2003-2011 Armin Burger
 *
 * This file is part of p.mapper.
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


// Send header for XMLHTTP request
header("Cache-Control: no-cache, must-revalidate, private, pre-check=0, post-check=0, max-age=0");
header("Expires: " . gmdate('D, d M Y H:i:s', time()) . " GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");      
header("Pragma: no-cache"); 

header('Content-Type: text/plain');


require_once("../group.php");
require_once("../pmsession.php");

// Check if PHP session still exists
if (!isset($_SESSION['session_alive'])) {
    echo "{method:'updateMap', sessionerror:'true'}";
   
} else {

    require_once("../globals.php");
    require_once("../common.php");
    require_once("../map/map.php");
    
    if (isset($_REQUEST['mapW'])) {
        $_SESSION['mapwidth']  = $_REQUEST['mapW'];
        $_SESSION['mapheight'] = $_REQUEST['mapH'];
    }
    
    // GET SESSION VARS 
    $old_geo_scale = $_SESSION['geo_scale'];
    $scaleLayers = $_SESSION["scaleLayers"];
    $grouplist  = $_SESSION['grouplist'];
    $url_points = isset($_SESSION['url_points']) ? $_SESSION['url_points'] : false;
    $groups = isset($_SESSION["groups"]) ? $_SESSION["groups"] : array();                    ## ???????????? to be checked !!!
    $oldGroups = isset($_SESSION["groups"]) ? $_SESSION["groups"] : array();
    $legendStyle = $_SESSION['legendStyle'];
    $legendDynamicUpdate = (bool)$_SESSION['legendDynamicUpdate'];
    
    
    // CREATE NEW MAP
    $pmap = new PMAP($map);
    $pmap->pmap_create();
    
    $mapURL      = $pmap->pmap_returnMapImgURL();
    $scalebarURL = $pmap->pmap_returnScalebarImgURL();
    $mapJS       = $pmap->pmap_returnMapJSParams();
    $mapwidth    = $pmap->pmap_returnMapW();
    $mapheight   = $pmap->pmap_returnMapH();
    $geo_scale   = $pmap->pmap_returnGeoScale();
    
    
    // Check if layers in TOC should be refreshed
    $visGroupsBefore = array();
    $visGroupsAfter = array();
    if ($scaleLayers) {
        foreach ($grouplist as $grp) {
            $grpName = $grp->getGroupName();
            $layerList = $grp->getLayers();
            foreach ($layerList as $glayer) {
                $layName = $glayer->getLayerName();
                $qLayer = $map->getLayerByName($layName);
                if (PMCommon::checkScale($map, $qLayer, $geo_scale)) { 
                    $visGroupsAfter[] = $layName;
                }
                if (PMCommon::checkScale($map, $qLayer, $old_geo_scale)) { 
                    $visGroupsBefore[] = $layName;
                }
            }
        }
        
        if ($visGroupsAfter == $visGroupsBefore) {
            $refreshToc = 0;
        } else {
            $refreshToc = 1;
        }
        
        // original code to determinate when legend has to be refreshed
        if ($refreshToc && $oldGroups != $groups) {
            $refreshLegend = 0;
        } else {
            $refreshLegend = 1;
        }

/*
// other version to determinate when legend has to be refreshed
		$refreshLegend = 0;
		if (!$oldGroups) {
			$refreshLegend = 1;
		} else {
			$newGroups = $_SESSION['groups'];
			if (!$refreshToc) {
				$refreshLegend = ($newGroups != $oldGroups) ? 1 : 0;
			} else {
				foreach ($newGroups as $grp) {
					// group is now and before selected:
					// - either both were visible, eithor none were visible --> do not refresh
					// - else --> refresh
					if (in_array($grp, $oldGroups)) {
						if (in_array($grp, $visGroupsAfter) != in_array($grp, $visGroupsBefore)) {
							$refreshLegend = 1;
							break;
						}

					// group is selected now but not before:
					// - not visible now --> do nt refresh
					// - visible now --> refresh
					} else if (in_array($grp, $visGroupsAfter)) {
						$refreshLegend = 1;
						break;
					}
				}

				if (!$refreshLegend) {
					// groups that were selected before but not selected now:
					// - visible before --> refresh
					// - not visible before --> do not refresh
					foreach ($oldGroups as $grp) {
						if (!in_array($grp, $newGroups) && in_array($grp, $visGroupsBefore)) {
							$refreshLegend = 1;
							break;
						}
					}
				}
			}
		}
*/
        
        if (isset($_SESSION['zoom_extparams'])) {
            unset($_SESSION['zoom_extparams']);
            //error_log("pippo");
            $refreshToc = 1;
        } 
        
        if (isset($_REQUEST['groups'])) {   // rfresh because active groups/layers changed
            $refreshToc = 1;
        }
    } else {
        $refreshToc = 0;
        $refreshLegend = 0;
    }
    
    // update legend if set in config file 
    if ($legendStyle == "swap" && $legendDynamicUpdate) $refreshLegend = 1;
    
    
    // JS objects from map creation
    $strJS  = '"mapW":"' . $mapJS['mapW'] . '", ';
    $strJS .= '"mapH":"' . $mapJS['mapH'] . '", ';
    $strJS .= '"refW":"' . $mapJS['refW'] . '", ';
    $strJS .= '"refH":"' . $mapJS['refH'] . '", ';
    $strJS .= '"minx_geo":"' . $mapJS['minx_geo'] . '", ';
    $strJS .= '"maxy_geo":"' . $mapJS['maxy_geo'] . '", ';
    $strJS .= '"xdelta_geo":"' . $mapJS['xdelta_geo'] . '", ';
    $strJS .= '"ydelta_geo":"' . $mapJS['ydelta_geo'] . '", ';
    $strJS .= '"refBoxStr":"' . $mapJS['refBoxStr'] . '" ';
    
    
    // Serialize url_points
    $urlPntStr = '';
    if (is_array($url_points)) {
        foreach ($url_points as $up) {
            $urlPntStr .= $up[0] . "@@" . $up[1] . "@@" . urlencode($up[2]) . '@@@'; 
        }
        $urlPntStr = addslashes(substr($urlPntStr, 0, -3));
    }
    
    // return JS object literals "{}" for XMLHTTP request 
    echo "{\"sessionerror\":\"false\",  \"mapURL\":\"$mapURL\", \"scalebarURL\":\"$scalebarURL\", \"geo_scale\":\"$geo_scale\", \"refreshToc\":\"$refreshToc\", \"refreshLegend\":$refreshLegend, \"urlPntStr\":\"$urlPntStr\", $strJS}";

}

?>