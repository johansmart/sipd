<?php

/******************************************************************************
 *
* Purpose: OGR export class
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

class ExportOGR extends ExportQuery
{
	// Current OGR driver
	protected $driverOGRDest = null;
	// OGR Shape driver 
	protected $driverSHP = NULL;

	// Files list that will be exported
	protected $fileList = array();
	
	// geometry types array
	protected $typeList = array(wkbPoint, wkbMultiLineString, wkbMultiPolygon);
	
	protected $nbFieldBase = 0;

	// arrays for remove accents and spaces:
	// adpated from http://www.lecoindunet.com/zone_php/scripts_utiles/remplacer-les-caracteres-accentues-dune-chaine-en-php-72
	private $bad = array(
			'@', '²',
			'À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î',
			'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß',
			'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î',
			'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'A',
			'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd',
			'Ð', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G',
			'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i',
			'I', 'i', 'I', 'i', '?', '?', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L',
			'l', '?', '?', 'L', 'l', 'N', 'n', 'N', 'n', 'N', 'n', '?', 'O', 'o', 'O',
			'o', 'O', 'o', 'Œ', 'œ', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's',
			'S', 's', 'Š', 'š', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U',
			'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Ÿ', 'Z', 'z', 'Z',
			'z', 'Ž', 'ž', '?', 'ƒ', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o',
			'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', '?', '?', '?', '?', '?', '?');

	private $good = array(
			'a', '2',
			'A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I',
			'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's',
			'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i',
			'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a',
			'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd',
			'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g',
			'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i',
			'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l',
			'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R',
			'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't',
			'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y',
			'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I',
			'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');


	function __construct($driverName, $exportExt, $json, $map, $bExportAtt=false, $optDataSourceCreation=NULL, $bSearchSpatialRef = false) {

		$bOk = false;
		$msVersion = null;  // MapServer version
		$groups = null;     // Layers list that will be exported
			
		
		parent::__construct($json);
		
		OGRRegisterAll();

		// looking for destination driver
		for ($iDriver = 0 ; $iDriver < OGRGetDriverCount() && !$bOk ; $iDriver++) {
			if (!strcasecmp(OGR_DR_GetName(OGRGetDriver($iDriver)) , $driverName)) {
				$this->driverOGRDest = OGRGetDriver($iDriver);
				$bOk = true;
				break;
			}
		}

		if ($bOk) {
			// is it possible to create new data source with current driver
			if(!OGR_Dr_TestCapability($this->driverOGRDest, ODrCCreateDataSource)){
				$this->driverOGRDest = null;
				$bOk = false;
				return;
			}
		}

		if ($bOk) {
		
			$msVersion = $_SESSION['MS_VERSION'];
			$groups = (array)$this->jsonList[0];
			
			// Create the destination directory 
			@mkdir($this->tempFilePath, 0700);
	
	
			// for each layers that will be exported
			foreach ($groups as $grp){
				
				$baseDest = null;
				$layerList = array();
					
				$attNameList = $grp->header;
				$ObjList =  $grp->values;
					
				if (!$ObjList[0][0]->shplink)
					continue;
				
				// if we need to export attributs
				if ($bExportAtt) {	
					include_once('../common/pluginsMapUtils.inc.php');
						
					for ($iAtt = 0 ; $iAtt < count($attNameList) ; $iAtt++){
						if ($attNameList[$iAtt] == '@'){
							array_splice($attNameList, $iAtt, 1);
							$iAtt--;
							continue;
						}
						
						// remove accents and spaces:
						$newAttName = PluginsMapUtils::decodeMapFileVal($map, $attNameList[$iAtt], 'UTF-8'); 
						$newAttName = str_replace($this->bad, $this->good, $attNameList[$iAtt]);
						if (is_numeric($newAttName{0}))
							$newAttName = 'z' . $newAttName;
						$attNameList[$iAtt] = $newAttName;
					}
				}
				
				// for each objects that we want to export
				foreach ($ObjList as $Obj) {
					$layerSrc = null;  // Source layer
					$layerDest = null; // Destination layer
					$baseDest = null;  // Destination DataBase
					$geoType = 0;
					$shpLink = $Obj[0]->shplink;
					
					$layerName = $shpLink[0];
					$IdObj = $shpLink[1];
					
					// if it's the first time we found current layer
					if (!isset($layerList[$layerName])) {			
						// getting mapserver layer
						$layerSrc = $map->getLayerByName($layerName);

						// use layers with complex queries that are too long to select results
						$newdata = $layerSrc->getMetaData('PM_RESULT_DATASUBSTITION');
						if ($newdata != '') {
							$layerSrc->set('data', $newdata);
						}

						// create destination data base
						$output = $this->tempFilePath . "\\$layerName.$exportExt";
						$output = str_replace('/', '\\', $output);

						$baseDest = OGR_Dr_CreateDataSource($this->driverOGRDest, $output, $optDataSourceCreation);
						if (!$baseDest)
							continue;
							
						// is it possible to create new layers in current data source ?
						if (!OGR_DS_TestCapability($baseDest, ODsCCreateLayer)) {
							if ($baseDest)
								OGR_DS_Destroy($baseDest);
							continue;
						}
						
						// create new layer in the destination data base
						$geoType = $this->typeList[$layerSrc->type];
						$layerDest = $this->createNewLayer($map, $baseDest, $layerName, $geoType, $bSearchSpatialRef);
						
						// add new attribut in destination layer
						if ($bExportAtt) {
							$idAtt = 0;
							foreach ($attNameList as $attName) {
								$att = OGR_Fld_Create($attName, OFTString);
								OGR_L_CreateField($layerDest, $att, $idAtt);
								$idAtt++;
							}	
						}
						
						
						// saving all in layerList
						$layerList[$layerName] = array();
						$layerList[$layerName][] = $layerSrc;
						$layerList[$layerName][] = $baseDest;
						$layerList[$layerName][] = $layerDest;
						$layerList[$layerName][] = $geoType;
						
						// add file to files list will be exorted
						$this->addFileToFileList($output);
					} else {
						$layerSrc = $layerList[$layerName][0];
						$baseDest = $layerList[$layerName][1];
						$layerDest = $layerList[$layerName][2];	
						$geoType = $layerList[$layerName][3];
					}
					
					// gettint shape object
					$srcShp = PMCommon::resultGetShape($msVersion, $layerSrc, null, $IdObj, -1);
					
					// export geometry of the object in WKT
					$geoWKT = $srcShp->toWkt();
					
					// create new geometry OGR object from WKT geometry
					$geoOGR = $this->createNewOGRGeometry($geoWKT);

					
					// create new data line on destination layer
					$layerDestDefn = OGR_L_GetLayerDefn($layerDest);
					$Data = OGR_F_Create($layerDestDefn);
						
					// add geometry in data line
					OGR_F_SetGeometry($Data, $geoOGR);
					
					// if we need to export attributs
					if ($bExportAtt){
						// add attributs values in data line
						for ($iAtt = 1 ; $iAtt < count($Obj) ; $iAtt++) {
							$newAttVal = PluginsMapUtils::decodeLayerVal($map, $layerName, $Obj[$iAtt], 'UTF-8'); 
							OGR_F_SetFieldString($Data, $iAtt - 1 + $this->nbFieldBase, $newAttVal);
						}
					}
					
					$this->insertSpecialFields($geoType, $layerDestDefn, $Data);
					
					// add data line in destination layer
					OGR_L_CreateFeature($layerDest, $Data);
					
					OGR_F_Destroy($Data);
					OGR_G_DestroyGeometry($geoOGR);
				}
				
			
				foreach ($layerList as $l){
					if (isset($l[1]) && $l[1])
						OGR_DS_Destroy($l[1]);
				}
				
			}
				
			// files compression
			$this->zipFiles();
	
			// remove directory
			rmdir($this->tempFilePath);
		}
	}


	protected function addFileToFileList($file) {
		$this->fileList[] = $file;
	}

	protected function zipFiles() {
		$this->tempFileLocation .= '.zip' ;
		$zipFilePath = "$this->tempFilePath.zip";

		PMCommon::packFilesZip($zipFilePath, $this->fileList, true, true);
	}
	
	protected function createNewLayer($map, $driver, $layerName, $geoType, $bSearchSpatialRef){
		$spatialRef = NULL;
		
		// Looking for spatial référence handle
		if ($bSearchSpatialRef === true){
			$shpFile = dirname(__FILE__). "\\shp\\";
			
			if (!isset($this->driverSHP)){
				// looking for MapInfo driver
				for ($iDriver = 0; $iDriver < OGRGetDriverCount(); $iDriver++) {
					if (!strcasecmp(OGR_DR_GetName(OGRGetDriver($iDriver)) , 'ESRI Shapefile')) {
						$this->driverSHP = OGRGetDriver($iDriver);
						break;
					}
				}
			}
			
			if (isset($this->driverSHP)){
				// reading layer pojection
				if ($map){
					$layerSrc = $map->getLayerByName($layerName);
					$proj = $layerSrc->getProjection();
					if (empty($proj)){
						$proj = $map->getProjection();
					}
						
					if (!empty($proj)){
						$pos = strpos($proj, 'epsg:');
						if ($pos !== false){
							$proj = substr($proj, $pos + 5);
							$shpFile .= "$proj.shp";
						}
					}
				}
					
				// if MapInfo file with same pojection exists
				if (file_exists($shpFile) && !is_dir($shpFile)){
					// opening file with OGR and reading OGR projection
					$projDataSource = ogr_dr_open($this->driverSHP, $shpFile, 0);
						
					if ($projDataSource && OGR_DS_GetLayerCount($projDataSource) > 0){
						$layer = OGR_DS_GetLayer($projDataSource, 0);
							
						if ($layer){
							$spatialRef = OGR_L_GetSpatialRef($layer);
						}
					}
				}
			
			}
		}
			
		$layerDest = OGR_DS_CreateLayer($driver,
				                        $layerName,
										$spatialRef,
										$geoType,
										NULL);
		
		return $layerDest;
	}
	
	protected function createNewOGRGeometry($geoWkt){
		
		$geoOGR = NULL;
		$geoOGR = OGR_G_CreateFromWkt($geoWkt, NULL, NULL);
		return $geoOGR;
	}
	
	protected function insertSpecialFields($geoType, $dfnLayerDest, $dataLine){
		return true;	
	}
}

?>