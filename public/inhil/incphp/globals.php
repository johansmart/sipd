<?php

/******************************************************************************
 *
 * Purpose: globally used variables
 * Author:  Armin Burger
 *
 ******************************************************************************
 *
 * Copyright (c) 2003-2009 Armin Burger
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

// prevent XSS
if (isset($_REQUEST['_SESSION'])) exit();


// Set character set for correct display of special characters
$defCharset = $_SESSION['defCharset'];


/**
 * LANGUAGE
 */
$gLanguage = $_SESSION["gLanguage"];
include_once($_SESSION['PM_INCPHP'] . "/locale/language_" . $gLanguage . ".php");



/**
 * LOAD MAPSCRIPT MODULE
 */
$msVersion = $_SESSION['msVersion']; 
if (!extension_loaded('MapScript')) {
    if( function_exists( "dl" ) ) {
        dl("php_mapscript$msVersion." . PHP_SHLIB_SUFFIX);            
    } else {
        error_log("P.MAPPER: This version of PHP does support the 'dl()' function. Please enable 'php_mapscript.dll' in your php.ini");
        return false;
    }
    
}



/**
 * INITIALIZE MAP
 */
$PM_MAP_FILE = $_SESSION['PM_MAP_FILE'];
$map = ms_newMapObj($PM_MAP_FILE);
//$mapTmpFile = $_SESSION['web_imagepath'] . session_id() . ".map";
//$map->save($mapTmpFile);


// MapObj modifiers
if (isset($_SESSION['plugin_phpMapObjModifierFileList'])) {
	require_once(dirname(__FILE__) . '/common.php');
	foreach($_SESSION['plugin_phpMapObjModifierFileList'] as $phpFile) {
		include_once($phpFile);
	}
}


/** ========== DEPRECATED ==============
 * DEFINE ZOOM STEPS FOR ZOOM SLIDER 
 */
//$gSlide = preg_split('/[\s,]+/', $ini["gSlide"]);


/**
 * Add dynamic layers from definition in SESSION
 */
if (isset($_SESSION['dynLayers'])) {
    $dynLayers = $_SESSION['dynLayers'];
    foreach ($dynLayers as $dynLayerType => $dynLayer) {
        $dynLayerPath = strtolower($dynLayerType);
        require_once($_SESSION['PM_PLUGIN_REALPATH'] . "/dynlayer" . $dynLayerPath . "/dynlayer" . $dynLayerPath . ".php"); 
        $dynLayerClassName = "DynLayer$dynLayerType";
        $dyn = new $dynLayerClassName($map);
        $dyn->createDynLayers();
    }
    //#$map->save("/home/www/tmp/dynlayer.map");
}


// client dynamic layer
if (isset($_SESSION['clientDynamicLayers'])) {
	$clientDynamicLayerFile = $_SESSION['PM_PLUGIN_REALPATH'] . "/clientdynamiclayers/clientDynamicLayers.php";
	if (file_exists($clientDynamicLayerFile)) {
		require_once($clientDynamicLayerFile);
		$cdLayers = new clientDynamicLayers($map, $_SESSION['clientDynamicLayers']);
	}
}

?>