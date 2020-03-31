<?php

/******************************************************************************
 *
* Purpose: DXF export class
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

class ExportDXF extends ExportOGR
{
	function __construct($json, $map) {
		// Export polygon like Multiline in DXF
		$this->typeList[2] = wkbMultiLineString;
		
		$opt = array();
		$opt['DXF_INLINE_BLOCKS'] = FALSE;
		
		parent::__construct('DXF', 'dxf', $json, $map, false, $opt);
	}

	protected function createNewOGRGeometry($geoWkt) {
		$geoWkt = $this->transformWKTPolygonToMultilineString($geoWkt);
		
		return parent::createNewOGRGeometry($geoWkt);
	}
	
	protected function createNewLayer($map, $driver, $layerName, $geoType, $bSearchSpatialRef){
	    // if layer contains point objects
		if ($geoType == wkbPoint) {
			// before creating layer "entities" we create layer "blocks"
			$layerBlocks = OGR_DS_CreateLayer($driver, 'blocks', NULL, wkbMultiLineString, NULL);
			
			// in this layer we export representaion of all point objects (it's a square)
			$wktLine = 'MULTILINESTRING((-0.5 -0.5, 0.5 -0.5, 0.5 0.5, -0.5 0.5, -0.5 -0.5))';
			$geoOGR = ogr_g_createfromwkt($wktLine, NULL, NULL);
			
			$dfnLayerBlocks = OGR_L_GetLayerDefn($layerBlocks);
			$dataLine = OGR_F_Create($dfnLayerBlocks);
			
			OGR_F_SetGeometry($dataLine, $geoOGR);
			
			for ($i = 0 ; $i < OGR_FD_GetFieldCount($dfnLayerBlocks) ; $i++){
				$fieldDfn = OGR_FD_GetFieldDefn($dfnLayerBlocks, $i);
				if (OGR_Fld_GetNameRef($fieldDfn) == 'BlockName') {
					OGR_F_SetFieldString($dataLine, $i, 'SQUARE');
				}
			}
				
			OGR_L_CreateFeature($layerBlocks, $dataLine);
			OGR_F_Destroy($dataLine);
			OGR_G_DestroyGeometry($geoOGR);
		}
		
		return parent::createNewLayer($map, $driver, 'entities', $geoType, false);
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
/*
		} else if (strpos($wkt, "MULTIPOLYGON") == 0){
			$wkt = substr($wkt, 14, strlen($wkt) - 14 - 1);
			$wkt = str_replace("((", "(", $wkt);
			$wkt = str_replace("))", ")", $wkt);
			$wkt = "MULTILINESTRING(" .$wkt .")";
		}
*/
		return $wkt;
	}
	
	protected function insertSpecialFields($geoType, $dfnLayerDest, $dataLine){
		// if "dataLine" contains point object
		if ($geoType == wkbPoint) {
			// we inform the name of representation
			for ($i = 0 ; $i < OGR_FD_GetFieldCount($dfnLayerDest) ; $i++) {
				$fieldDfn = OGR_FD_GetFieldDefn($dfnLayerDest, $i);
				if (OGR_Fld_GetNameRef($fieldDfn) == 'BlockName'){
					OGR_F_SetFieldString($dataLine, $i, 'SQUARE');
				}
			}	
		}
		
		return true;
	}

}

?>