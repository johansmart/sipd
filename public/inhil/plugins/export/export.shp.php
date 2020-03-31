<?php

/******************************************************************************
 *
 * Purpose: export query results as Shapefile
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


/**
 * Export results to CSV files
 * 1 file per result group
 */
class ExportSHP extends ExportQuery
{
    
    /**
     * Init function
     */
    function __construct($json, $map)
    {
        parent::__construct($json);
        
        $msVersion = $_SESSION['MS_VERSION']; 
        $grouplist   = $_SESSION['grouplist'];
        //pm_logDebug(3, $grouplist, 'grouplist in exportSHP');
        
        $groups = (array)$this->jsonList[0];
        $fileList = array();
        $valueList = array();
        $dbfFieldList = array();
        $cpyPrjFile = '';
        
        @mkdir($this->tempFilePath, 0700);
        
        foreach ($groups as $grp) {
            $layerNameList = array();
            $layerList = array();

            $values = $grp->values;
            if (!$values[0][0]->shplink) continue;
            
			// headers with invalid characters
			include_once('../common/pluginsMapUtils.inc.php');
			$headerListTmp = $grp->header;
			$headerList = array();
	        foreach ($headerListTmp as $n => $h) {
	            if ($h != '@') {
	                $newHeader = PluginsMapUtils::decodeMapFileVal($map, $h, 'ISO-8859-1');

	                // remove accents and spaces:
	                // adpated from http://www.lecoindunet.com/zone_php/scripts_utiles/remplacer-les-caracteres-accentues-dune-chaine-en-php-72
					$bad = array(
					' ',
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
					
					$good = array(
					'_',
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

	                $newHeader = str_replace($bad, $good, $newHeader);
	                
	                // upper case
	                $newHeader = strtoupper($newHeader);
	                
	                // if header already exists :'FIELD_' + index
	                if (in_array($newHeader, $headerList)) {
	                	$newHeader = "FIELD_$n";
	                }
	                $headerList[] = $newHeader;
	            // if '@' not at the beginning
	            } else if ($n != 0) {
	                $headerList = array();
	                break;
	            }
	        }
	        
            foreach ($values as $vList) {
                $dbfRow = array();
                
                foreach ($vList as $n=>$v) {
                    if ($shplink = $v->shplink) {
                        //pm_logDebug(3, $shplink, "shplink");
                        $layerName = $shplink[0];
                        $shpIdxTmp = $shplink[1];
                        $shpIdx = preg_replace('/^.*@/', '', $shpIdxTmp);
		                $tileShpIdx = -1;
		                
                        if (!in_array($layerName, $layerNameList)) {
                            $typeList = array(MS_SHP_POINT, MS_SHP_ARC, MS_SHP_POLYGON);
                            $srcLayer = $map->getLayerByName($layerName);

                            // use layers with complex queries that are too long to select results
                            $newdata = $srcLayer->getMetaData('PM_RESULT_DATASUBSTITION');
                            if ($newdata != '') {
                            	$srcLayer->set('data', $newdata);
                            }

                            $srcLayerType = $srcLayer->type;
                            //error_log("type: $srcLayerType");
                            $layerNameList[] = $layerName;
                            $layerList[] = $srcLayer;
                            $valueList[$layerName] = array();
                            
                            $srcLayer->open();
                            //pm_logDebug(3, $layerItems);

                            $outShpFname = "{$this->tempFilePath}/$layerName";
                            $shpFile = ms_newShapeFileObj($outShpFname, $typeList[$srcLayerType]);
                            $dbfFileName = "$outShpFname.dbf";
                            
                            $cpyPrjFile = '';
                            unset($proj);
                            $proj = $srcLayer->getProjection();
                            if (empty($proj)){
                            	$proj = $map->getProjection();
                            }
                            
                            if (!empty($proj)){
                            	$prjFile = dirname(__FILE__). "\\shp\\";
                            	$pos = strpos($proj, 'epsg:');
                            	if ($pos !== false){
                            		$proj = substr($proj, $pos + 5);
                            		$prjFile .= "$proj.prj";
                            	}
                            	
                            	if (file_exists($prjFile) && !is_dir($prjFile)){
                            		$cpyPrjFile = "$outShpFname.prj";
                            		copy($prjFile, $cpyPrjFile);
                            	}
                            }
                          	
                            
                            $fileList[$layerName] = array();
                            $fileList[$layerName][] = "$outShpFname.shp";
                            $fileList[$layerName][] = "$outShpFname.shx";
                            $fileList[$layerName][] = $dbfFileName;
                            if (file_exists($cpyPrjFile)){
                            	$fileList[$layerName][] = $cpyPrjFile;	
                            }
                        }
                        $srcShp = PMCommon::resultGetShape($msVersion, $srcLayer, null, $shpIdx, -1);  // changed for compatibility with PG layers and MS >= 5.6
                        $shpFile->addShape($srcShp);
                    } else if ($headerList) {
                        $hyperlink = $v->hyperlink;
                        if ($hyperlink) {
                            $val = $hyperlink[2];            
                        } else {
                            $val = $v;
                        }

                        $fldName = $headerList[$n-1];     
                        
                        $dbfRow[] = utf8_decode($val);

                        if (!isset($dbfFieldList[$layerName])) {
                        	$dbfFieldList[$layerName] = array();
                        }
                        if (!isset($dbfFieldList[$layerName][$fldName])) {
                        	 $dbfFieldList[$layerName][$fldName] = array();
                        }
                        $dbfFieldList[$layerName][$fldName] = $this->getFieldType(trim($val), $dbfFieldList[$layerName][$fldName]);
                    }
                }
                
                $valueList[$layerName][] = $dbfRow;
            }
            
            // some clean up
//            PMCommon::freeMsObj($shpFile);
			// shapefileObj: changes are committed only when the object is destroyed
            $shpFile->free();
            unset($shpFile);
            foreach ($layerList as $l) {
                $l->close();
            }
        }

		// write dBase files
		foreach ($fileList as $layerName => $files) {
			$dbfFileName = $fileList[$layerName][2];
			// Modified by Thomas RAFFIN (SIRAP)
			// bug export for groups (with many layers)
			$dbfFieldListTmp = $dbfFieldList[$layerName];
			$valueListTmp = $valueList[$layerName];
            $this->writeDbf($dbfFileName, $dbfFieldListTmp, $valueListTmp);
		}
                 
        // Write all files to zip and remove tmp dir
        $this->tempFileLocation .= '.zip' ;
        $zipFilePath = "{$this->tempFilePath}.zip";
        
        $filesToZip = array();
		foreach ($fileList as $files) {
			foreach ($files as $file) {
				$filesToZip[] = $file;
			}
		}
        PMCommon::packFilesZip($zipFilePath, $filesToZip, true, true);
        
        rmdir($this->tempFilePath);
    }

    
    
    function writeDbf($dbfFileName, $dbfFieldList, $valueList)  {
    	$defList = array();
        foreach ($dbfFieldList as $name=>$def) {
            $defList[] = array_merge(array(substr($name, 0, 10)), $def);
        }
        pm_logDebug(3, $defList, 'defList');
        
        $dbfFile = dbase_create($dbfFileName, $defList); //array(array('PROG_ID', 'N', 5, 0)));
        
        foreach ($valueList as $row) {
            pm_logDebug(3, $row, 'row');
            dbase_add_record($dbfFile, $row);
        }
        
        dbase_close($dbfFile);
    }
    
    function getFieldType($val, $fldList) {
    	$list = array();
        if (is_numeric($val) && (!isset($fldList[0]) || $fldList[0] != 'C')) {
            if (intval($val) == $val) {
                $int_len = max(strlen(strval($val)), $fldList[1]) ;
                $list = array('N', $int_len, 0); 
            } else {
                $dL = explode('.', $val);
                $list = array('N', strlen(strval($dL[0]))+strlen(strval($dL[1]))+1, strlen(strval($dL[1]))); 
            }
        } elseif (is_bool($val)) {
            $list = array('L');
        } elseif (strtotime($str)) {
            $list = array('D');
        } else {
            $str_len = max(strlen($val), $fldList[1]);
            if (!$fldList[1] && $str_len == 0) {
            	$str_len = 2;
            }
            $list = array('C', $str_len); 
        }
        
        return $list;
    }
    
    

   
}

?>