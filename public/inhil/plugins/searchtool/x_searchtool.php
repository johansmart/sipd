<?php

/******************************************************************************
 *
 * Purpose: SearchTool plugin
 * Author:  Thomas Raffin, SIRAP
 *
 ******************************************************************************
 *
 * Copyright (c) 2007 SIRAP
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
// prevent XSS
if (isset($_REQUEST['_SESSION'])) exit();

require_once('../../incphp/pmsession.php');

include_once("../../incphp/uielement.php");

// Generate search form
$style = (isset($_REQUEST['style']) && $_REQUEST['style'] == 'block') ? 'block' : 'inline';

$searchForm = UiElement::searchContainer($style) . "\n";

header("Content-Type: text/plain");
echo $searchForm;

?>