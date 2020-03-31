<?php
/******************************************************************************
 *
 * Purpose: execute query
 * Author:  Armin Burger
 *
 ******************************************************************************
 *
 * Copyright (c) 2003-2007 Armin Burger
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
require_once("../query/squery.php");
require_once("../query/search.php");

header("Content-type: text/plain; charset=$defCharset");

$mode = $_REQUEST['mode'];

//pm_logDebug(3, $_REQUEST, "REQUEST array in x_info.php");

// Run QUERY
$mapQuery = new Query($map);
$mapQuery->q_processQuery();
$queryResult = $mapQuery->q_returnQueryResult();
//$numResultsTotal = $mapQuery->q_returnNumResultsTotal();


echo "{\"mode\":\"$mode\", \"queryResult\":$queryResult}";
?>