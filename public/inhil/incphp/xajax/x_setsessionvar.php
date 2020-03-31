<?php

/******************************************************************************
 *
 * Purpose: generic AJAX to return PHP session variable as JSON string
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
require_once("../pmsession.php");
require_once("../common.php");
require_once("../globals.php");

$sessionvar = $_REQUEST['sessionvar'];
$val = $_REQUEST['val'];

// avoid invalid JSON string
$val = str_replace(array("\\'", '\\"', "\r", "\n", "\t"), array("'", "\"", "", "", ""), $val);

if (strtolower($val) == 'null') {
    unset($_SESSION[$sessionvar]);
} else {
    $_SESSION[$sessionvar] = json_decode($val, true);
}

pm_logDebug(3, json_decode($val, true), "x_setsessionvar.php, for sessionvar $sessionvar: ");

header("Content-Type: text/plain; charset=$defCharset");
echo "{\"ok\":1}";
?>