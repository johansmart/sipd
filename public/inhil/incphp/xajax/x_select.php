<?php

/******************************************************************************
 *
 * Purpose: used for updating select tool options
 * Author:  Armin Burger
 *
 ******************************************************************************
 *
 * Copyright (c) 2003-2008 Armin Burger
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

require_once("../group.php");
require_once("../pmsession.php");
require_once("../globals.php");
require_once("../common.php");
require_once("../query/query.php");

header("Content-Type: text/plain; charset=$defCharset");


$scale = $_SESSION["geo_scale"];
$grouplist = $_SESSION["grouplist"];

// Get the groups
if (isset($_REQUEST["groups"])) {
    $drawGroupStr = $_REQUEST["groups"];
    $groups = explode(" ", $drawGroupStr);
} else {
    $groups = $_SESSION["groups"];
}

if (isset($_REQUEST["autoidentify"])){
	$aiGroupsAll = isset($_SESSION["autoIdentifyGroups"]) ? $_SESSION["autoIdentifyGroups"] : $_SESSION["allGroups"];
	$aiGroups = array();
	foreach ($groups as $g) {
        if (in_array($g, $aiGroupsAll)) {
            $aiGroups[] = $g;
        }
	}
	$groups = $aiGroups;
}

// Check for active group for selection
if (isset($_REQUEST["activegroup"])) {
    $activegroup = $_REQUEST["activegroup"];
} elseif (isset($_SESSION["activegroup"])) {
    $activegroup = $_SESSION["activegroup"];
    $_SESSION["activegroup"] = $activegroup;
} else {
    $activegroup = "";
}



// APPLY ON LAYERS DEFINED IN MAP FILE AND VISIBLE AT CURRENT SCALE
foreach ($grouplist as $grp){
    if (in_array($grp->getGroupName(), $groups, TRUE)) {
        $glayerList = $grp->getLayers();
        foreach ($glayerList as $glayer) {
            $resFldList = $glayer->getResFields();
            $mapLayer = $map->getLayer($glayer->getLayerIdx());
            
            // Check for template
            $hasTemplate = 0;
            if ($mapLayer->template) $hasTemplate = 1;
            $numclasses = $mapLayer->numclasses;
            for ($cl=0; $cl < $numclasses; $cl++) {
                $class = $mapLayer->getClass($cl);
                $classTemplate = $class->template;
                if ($class->template) $hasTemplate = 1;
            }
            if ($XYLayerProperties = $glayer->getXYLayerProperties()) {
                if (!$XYLayerProperties['noQuery']) {
                    $hasTemplate = 1;
                }
            }

            if ($mapLayer->type < 3 && PMCommon::checkScale($map, $mapLayer, $scale) == 1 &&  $resFldList[0] != '0' && $hasTemplate) {
                $showgroups[] = $grp;
                break;
            }
        }
    }
}


// Print combo box with all visible groups
$gstr = "<form id=\"selform\"><div class=\"pm-selectbox\">";
if (count($showgroups) > 0) {

    $gstr .=  _pjs("Apply on Layer") . "";
    $gstr .= "";
    $gstr .= "<select name=\"selgroup\" >";
    
    foreach ($showgroups as $g) {
       $gstr .= "<option value=\"" . $g->getGroupName() . "\" ";
       if ($g->getGroupName() == $activegroup) $gstr .= " selected=\"selected\" ";
       $gstr .= ">" . addslashes($g->getDescription()) . "</option> ";
    }
    $gstr .= "</select>";
}
$gstr .= "</div></form>";


// return JS object literals "{}" for XMLHTTP request 
echo "$gstr";
?>