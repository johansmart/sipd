<?php

/******************************************************************************
 *
 * Purpose: AJAX for export
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

require_once("../../incphp/pmsession.php");
require_once("../../incphp/globals.php");
require_once("../../incphp/common.php");

$format = $_REQUEST['format'];
$formatExt = str_replace("export", "", strtolower($format));

include_once("exportquery.php");
include_once("export.$formatExt.php");

$exp = new $format($_SESSION['JSON_Results'], $map);
$expFileLocation = $exp->getTempFileLocation();

header("Content-Type: text/plain; charset=$defCharset");
echo "{\"expFileLocation\":\"$expFileLocation\"}";

?>