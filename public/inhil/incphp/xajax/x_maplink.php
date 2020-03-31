<?php

/******************************************************************************
 *
 * Purpose: get the URL for current map
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
header('Content-Type: text/plain');

require_once("../pmsession.php");
require_once("../globals.php");
require_once("../common.php");

$url_points    = isset($_SESSION['url_points']) ? $_SESSION['url_points'] : false;
   

// Serialize url_points
$urlPntStr = '';
if (is_array($url_points)) {
    foreach ($url_points as $up) {
        $urlPntStr .= $up[0] . "@@" . $up[1] . "@@" . urlencode($up[2]) . '@@@'; 
    }
    $urlPntStr = addslashes(substr($urlPntStr, 0, -3));
    //error_log($urlPntStr);
}

// return JS object literals "{}" for XMLHTTP request 
echo "{\"urlPntStr\":\"$urlPntStr\"}";



?>