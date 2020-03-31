<?php

/******************************************************************************
 *
 * Purpose: create TOC/legend
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

require_once("../group.php");
require_once("../pmsession.php");
require_once("../common.php");
require_once("../globals.php");
require_once("../layerview.php");


$categories = $_SESSION['useCategories'] ? $_SESSION['categories'] : false;

$layerView = new LayerView($map, $categories, false);
$groupList = json_encode($layerView->getGroupList());
pm_logDebug(3, $groupList, "Group list: ");

//$categoryList = $layerView->getCategoryList();
//pm_logDebug(3, $categoryList, "category List: ");

//$groupList = $layerView->getGroupNameList();
//pm_logDebug(3, $groupList, "Group list: ");

header("Content-Type: text/plain; charset=$defCharset");

// return JS object literals "{}" for XMLHTTP request 
echo "{\"groupList\":$groupList}";

?>
