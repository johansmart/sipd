<?php
/******************************************************************************
 *
 * Purpose: AJAX call for suggest (auto-complete)
 * Author:  Armin Burger
 *
 ******************************************************************************
 *
 * Copyright (c) 2003-2006 Armin Burger
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
 
require_once("../pmsession.php");

//Send some headers to keep the user's browser from caching the response.
header("Cache-Control: no-cache, must-revalidate, private, pre-check=0, post-check=0, max-age=0");
header("Expires: " . gmdate('D, d M Y H:i:s', time()) . " GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");      
header("Pragma: no-cache"); 

header("Content-Type: text/plain; charset=utf-8");

// MapObj modifiers
require_once("../globals.php");

require_once("../common.php");
require_once("../query/suggest.php");

$searchGet  = isset($_REQUEST['q']) ? $_REQUEST['q'] : '';
$searchitem = $_REQUEST['searchitem'];
$fldname    = $_REQUEST['fldname'];
//error_log("$searchitem  $fldname  $searchGet ");
pm_logDebug(3, $_REQUEST, "request");

// initialize return value
$ret = "";

// Run suggest query
if (isset($searchGet) && strlen($searchGet) > 0) {
    $search = addslashes($searchGet);
    $suggest = new Suggest($map, $search, $searchitem, $fldname, $_REQUEST);
    $ret = $suggest->returnJson();
}

//error_log("return: " . $ret);
//echo "{searchGet:'$searchGet', retvalue:$ret, fldname:'$fldname'}";

if ($ret) echo $ret;

?>