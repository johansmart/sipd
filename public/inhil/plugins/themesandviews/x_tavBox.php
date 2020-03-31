<?php

/******************************************************************************
 *
 * Purpose: ThemesAndViews plugin
 * Author:  Thomas Raffin, SIRAP
 *
 ******************************************************************************
 *
 * Copyright (c) 2007 SIRAP
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version. See the COPYING file.
 *
 * This program is distributed in the hope that it will be useful,
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

/******************************************************************************
 * Generation of the boxes witch permit to apply a Theme or a View
 ******************************************************************************/
require_once('tav.php');

$selStr = '';
$type = 'none';

if (isset($_REQUEST['type'])) {
	if ($_REQUEST['type'] == 'theme') {
		$themes = ThemesAndViewsUtils::getListThemesAndViews(true, false, false);
	
		// Print combo box with all themes
		$selStr = '<form id="selThemesBoxForm" action=""><div>';
		if (count($themes) > 0) {
		
		    $selStr .=  _p('Apply theme') . ' ';
		    $selStr .= ThemesAndViewsUtils::getComboThemesAndViews($themes, 'PM.Plugin.ThemesAndViews.submitSelThemeBox()');
		}
		$selStr .= '</div></form>';
		$type = 'theme';
	
	} else if ($_REQUEST['type'] == 'view') {
		$views = ThemesAndViewsUtils::getListThemesAndViews(false, true, false);
	
		// Print combo box with all views
		$selStr = '<form id="selViewsBoxForm" action=""><div>';
		if (count($views) > 0) {
		
		    $selStr .=  _p('Apply view') . ' ';
		    $selStr .= ThemesAndViewsUtils::getComboThemesAndViews($views, 'PM.Plugin.ThemesAndViews.submitSelViewBox()');
		}
		$selStr .= '</div></form>';
		$type = 'view';
	}
	$selStr = addcslashes($selStr, "'");
	$selStr = addcslashes($selStr, "\"");
}
// return JS object literals '{}' for XMLHTTP request 
header("Content-Type: text/plain; charset=$defCharset");
echo "{\"selStr\":\"$selStr\",\"type\":\"$type\"}";
?>