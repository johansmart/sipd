<?php

/******************************************************************************
 *
 * Purpose: AJAX for dynlayersample
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
// prevent XSS
if (isset($_REQUEST['_SESSION'])) exit();

//require_once("../../incphp/group.php");
require_once("../../incphp/pmsession.php");
require_once("../../incphp/globals.php");
require_once("../../incphp/common.php");
require_once("../../incphp/map/dynlayer.php");

// get dynlayer definition from text file
$jsonFile = "dynlayer_def.txt";
$dynLayers = preg_replace(array("/\s{2,}/", "/\n/"), array(" ", ""), file_get_contents($jsonFile));
$_SESSION['dynLayers'] = json_decode($dynLayers, true);


// create and initialize dynlayers
##error_log("ajax");
$dyn = new DynLayer($map, $dynLayers);
//$dyn = new DynLayer($map, json_encode($_SESSION['dynLayers']));
$dyn->initDynLayers(true);

$activeLayers = json_encode($dyn->getActiveLayers());

//$map->save("D:/webdoc/tmp/dynlayer.map");

header("Content-type: text/plain; charset=$defCharset");
echo "{\"activeLayers\":$activeLayers}";

?>