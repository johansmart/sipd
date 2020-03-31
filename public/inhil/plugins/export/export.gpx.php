<?php

/******************************************************************************
 *
* Purpose: GPX export class
* Author:  Julien BONNET, SIRAP
*
******************************************************************************
*
* Copyright (c) 2012 SIRAP
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

require_once('exportogr.php');

class ExportGPX extends ExportOGR
{
	function __construct($json, $map)
	{
		// Export polygon like Multiline in GPX
		$this->typeList[2] = wkbMultiLineString;

		/*$opt = array();
		$opt["GPX_USE_EXTENSIONS"] = 'YES';*/

		parent::__construct("GPX", "GPX", $json, $map, false, NULL);
	}

	protected function createNewOGRGeometry($geoWkt) {
			
		$geoWkt = $this->transformWKTPolygonToMultilineString($geoWkt);

		return parent::createNewOGRGeometry($geoWkt);
	}

	protected function transformWKTPolygonToMultilineString($geoWkt){

		$wkt = $geoWkt;

		if (strpos($wkt, 'POLYGON') === 0){
			$wkt = str_replace('POLYGON', 'MULTILINESTRING', $wkt);
		} else if (strpos($wkt, 'MULTIPOLYGON') === 0) {
			$wkt = substr($wkt, 14, -2);
			$wkt = str_replace(array('((', '))'), array('(', ')'), $wkt);
			$wkt = "MULTILINESTRING($wkt)";
		}

		return $wkt;
	}
}

?>