<?php

/******************************************************************************
 *
 * Purpose: Manage client dynamic layers to map
 * Author:  Thomas Raffin, SIRAP
 *
 ******************************************************************************
 *
 * Copyright (c) 2008 SIRAP
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


require_once($_SESSION['PM_INCPHP'] . '/common.php');
		
/**
 * 
 * Main class to manage all the different types of client data (GeoJson, ...)
 * Only instanciate the other classes...
 * 
 */
class clientDynamicLayers
{
	/**
	 *
	 * Constructor
	 * 
	 * @param $map main msMapObj object  
	 * @param $layers layers definitions and data. See clientDynamicLayers.txt for different possibilities
	 */
    public function __construct($map, $layers) {
        $this->map = $map;

        $dynLayers = array();
        foreach ($layers as $layer) {
        	$layerDef = $layer->def;
        	$layerData = $layer->data;
        	$dataType = $layer->datatype;
        	$layerClass = "clientDynamicLayer_$dataType";
        	$dynLayer = new $layerClass($map, $layerDef, $layerData);
        	$dynLayers[] = $dynLayer;
        }
        
        $this->updateToc($dynLayers);
    }
    

	/**
	 * 
	 * Update legend and pmapper variables:
	 * 
	 * - add or remove layers to $_SESSION['allGroups']
	 * - create legend (call Init_map::createLegendList)
	 * - update groups and glayers definitions (instanciate Init_groups)
	 */
    private function updateToc($dynLayers) {
    	
		$allGroups = $_SESSION['allGroups'];
		$initialCount = count($allGroups);
		if ($initialCount > 0) {
			foreach ($dynLayers as $dynLayer) {
				if ($layerName = $dynLayer->getLayerName()) {

					// update the groups lists:
					if ($dynLayer->hasFeatures()) {
						$allGroups = array_merge($allGroups, array($layerName));
		    		} else {
		    			$newGroups = array();
		    			foreach ($allGroups as $groupName) {
		    				if ($groupName != $layerName) {
		    					$newGroups[] = $groupName;
		    				}
		    			}
		    			$allGroups = $newGroups;
		    		}
				}
				$allGroups = array_unique($allGroups);
			}
			$_SESSION['allGroups'] = $allGroups;
	    		
			// get original opacities:
// this require should be done before session_start!!! 
//			require_once($_SESSION['PM_INCPHP'] . '/group.php');
			$grouplist = $_SESSION['grouplist'];
			$opacities = array();
	        foreach ($grouplist as $grp) {
				// to avoid bad incphp/group.php inclusion, like in map.phtml for instance 
				// for instance :
				// refresh a page 
				// -> call map.phtml 
				// -> session_start(), then include group.php 
				// -> error : __PHP_Imcomplete_Class
	        	if (is_object($grp)) {
		            $glayerList = $grp->getLayers();
		            foreach ($glayerList as $glayer) {
		            	$opacities[$glayer->getLayerName()] = $glayer->getOpacity();
		            }
	        	}
	        }
			
	        // update legend:
			if ($initialCount != count($allGroups)) {
				require_once($_SESSION['PM_INCPHP'] . '/init/initmap.php');
				$initMap = new Init_map($this->map, false, false, $_SESSION['gLanguage']);
				$initMap->createLegendList();
			}
			
			// update groups and glayer definitions
			require_once($_SESSION['PM_INCPHP'] . '/initgroups.php');
			$iG = new Init_groups($this->map, $allGroups, $_SESSION['gLanguage'], true);
			$iG->createGroupList();
			
			// restore opacity:
			$grouplist = $_SESSION['grouplist'];
	        foreach ($grouplist as $grp) {
	            $glayerList = $grp->getLayers();
	            foreach ($glayerList as $glayer) {
	            	$name = $glayer->getLayerName();
	            	if (isset($opacities[$name])) {
	            		$opacity = $opacities[$name]; 
	            		$glayer->setOpacity($opacity);
	            	}
	            }
	        }
    	}
    }

}

/**
 * 
 * Base class (abstract).
 * 
 * Use DynLayer class.
 * 
 * 3 functions :
 * createLayer: instanciate a DynLayer object and create the msLayerObj.
 * addFeatures: add data. This function has to be written it each derivated class
 * updateToc: add the layer to the rest of pmapper, update groups and glayers definitions.
 */
abstract class clientDynamicLayer {

	/**
	 * 
	 * @var msLayerObj
	 */
	protected $layer;

	/**
	 * 
	 * @var msMapObj
	 */
	protected $map;

	/**
	 * 
	 * Does this layer really contains features ?
	 *  
	 * @var boolean
	 */
	protected $oneOrMoreFeatures;

	/**
	 *
	 * Constructor
	 * 
	 * Initiate members, then call createLayer, addFeatures and updateToc members functions.
	 * 
	 * @param $map msMapObj
	 * @param $layerDef Layer definition. See clientDynamicLayers.txt
	 * @param $layerData Layer data (geometry and attributes). See clientDynamicLayers.txt 
	 */
	public function __construct($map, $layerDef, $layerData) {
        $this->map = $map;
        $this->layer = null;
        $this->oneOrMoreFeatures = false;

        $ok = $this->createLayer($layerDef);
        if ($ok) {
        	$ok = $this->addFeatures($layerData);
        }
	}

	/**
	 * 
	 * Create the msLayerObj object.
	 * 
	 * @param $layerDef layer definition as a JSON string, or the object created 
	 * by jsondecode function for instance
	 * 
	 * @return boolean true if layer succesfully created
	 */
	protected function createLayer($layerDef) {
    	$layerDefToUse = is_array($layerDef) ? json_decode($layerDef) : $layerDef;
    	$addToCategories = true;
    	if ($layerDefToUse->type == 'json') {
    		require_once($_SESSION['PM_INCPHP'] . '/map/dynlayer_json.php');
    		$dyn = new DynLayerJson($this->map, json_encode($layerDefToUse->jsondef));
    		$layers = $dyn->createDynLayer($layerDefToUse->jsondef);
    		if (count($layers) > 0) {
    			$layername = $layers[0];
    			$this->layer = $this->map->getLayerByName($layername);
    		} 
/*
    		$layername = $layerDefToUse->jsondef->name;
    		$this->layer = $this->map->getLayerByName($layername);
*/
    	} else if ($layerDefToUse->type == 'tplMapFile') {
	        if (is_file($_SESSION['PM_TPL_MAP_FILE'])) {
		        $tplMap = ms_newMapObj($_SESSION['PM_TPL_MAP_FILE']);
		        $tplLayer = $tplMap->getLayerByName($layerDefToUse->tplname);


		        $layer = ms_newLayerObj($this->map, $tplLayer);
		        
		        // bug for angle auto (not set to auto in new layer)
		        $numClasses = $tplLayer->numclasses;
		        for ($iClass = 0 ; $iClass < $numClasses ; $iClass++) {
		        	$class = $tplLayer->getClass($iClass);
		        	$numStyles = $class->numstyles;
		        	for ($iStyle = 0 ; $iStyle < $numStyles ; $iStyle++) {
		        		$style = $class->getStyle($iStyle);
		        		if ($_SESSION['MS_VERSION'] >= 6.2) {
		        			$autoangle = $style->autoangle;
		        			if ($autoangle === MS_TRUE) {
		        				$layer->getClass($iClass)->getStyle($iStyle)->set('autoangle', MS_TRUE);
		        			}
		        		}
		        	}
		        }
		        
		        $layer->set('name', $layerDefToUse->layername);
		        $layer->setMetaData('CATEGORY', $layerDefToUse->category);
/*
		        $layerIdx = $layer->index;
		        $layNum = count($this->map->getAllLayerNames());
		    	while ($layerIdx < ($layNum-1)) {
					$this->map->moveLayerUp($layerIdx);
				}
				$layer->set('status', MS_ON);
*/
		        $this->layer = $layer;
	        } else {
	            error_log("P.MAPPER ERROR (createLayer): cannot find template map. Check INI settings for 'tplMapFile'");
	        }
    	}

		if ($addToCategories) {
			$catName = $this->layer->getMetaData('CATEGORY');

			if (!in_array($catName, $_SESSION['categories'])) {
				$_SESSION['categories'][$catName] = array('groups' => array());
				$_SESSION['categories'][$catName]['description'] = _p($catName);
			}

			$grpName = $this->layer->name;
			if (!in_array($grpName, $_SESSION['categories'][$catName]['groups'])) {
				$_SESSION['categories'][$catName]['groups'][] = $grpName;
			}
		}

    	return ($this->layer != null);
    }


  	/**
	 * 
	 * Add features to the previously created layer.
	 * This function has to be written in each derivated class. 
	 * 
	 * @param $layerData
	 * @return unknown_type
	 */
	abstract protected function addFeatures($layerData);


	public function getLayerName() {
		$ret = false;
		
		if ($this->layer) {
			if ($this->layer->group) {
				$ret = $this->layer->group;
			} else if ($this->layer->name) {
				$ret = $this->layer->name;
			}
		}
		
		return $ret;
	}
	
	public function hasFeatures() {
		return $this->oneOrMoreFeatures;
	}

}

/**
 * 
 * Manage client data in JSON
 *
 * Extends the clientDynamicLayer class and implement the addFeatures function.
 *
 */
class clientDynamicLayer_GeoJson extends clientDynamicLayer
{
/*
	public function __construct($map, $layerDef, $geoData) {
        parent::__construct($map, $layerDef, $geoData);
    }
*/

    /**
     * 
     * @see incphp/map/clientDynamicLayer#addFeatures()
     * 
     * Encode the data in GeoJSON format and write it in mapserver temp directory
     * 
     * @param $geoData Layer data (geometry and attributes) as Object or json
     * 
     * @return boolean true if 1 or more features, else false
     */
    protected function addFeatures($geoData) {
    	$this->oneOrMoreFeatures = false;
    	
    	if ($this->layer != null) {
    		// re-encode the string
    		$newStrObjects = is_array($geoData) ? $geoData : json_encode($geoData);

    		// write the file containing data in mapserver temp directory
    		$hash = md5($newStrObjects);
    		$filename = $this->map->web->imagepath . $hash . '.json';
    		if (!file_exists($filename)) {
    			$fh = @fopen($filename, 'w');
				if ($fh) {
			        $tmp = fwrite($fh, $newStrObjects);
				}
				fclose($fh);
    		}
    		// update layer mapserver properties to use data
    		if (file_exists($filename)) {
    			$this->layer->setConnectionType(MS_OGR);

    			$ret1 = $this->layer->set('connection', $filename);

    			// test the data:
				$status = $this->layer->open();
				if ($status == MS_SUCCESS) {
//	       			$status = $this->layer->whichShapes($this->map->extent);
//					if ($status == MS_SUCCESS) {
//						if ($this->layer->nextShape()) {
							$this->oneOrMoreFeatures = true;
//						}
//					}
					$this->layer->close();
				}
//$this->oneOrMoreFeatures = true;
    		}
/*
// Old version: does not work now cause msShpObj couldn't recieve attribute dynamically...
			$changeLayProj = false;
			$inputProjStr = $this->map->getProjection();
//			$inputProjStr = 'proj=latlong';
			$layerProjStr = $this->layer->getProjection();
			if (!$layerProjStr) {
				$layerProjStr = $this->map->getProjection();
			}
			if ($layerProjStr) {
	        	if ($_SESSION['MS_VERSION'] < 6) {
	        		$inputProjObj = ms_newprojectionobj($inputProjStr);
	        		$layerProjObj = ms_newprojectionobj($layerProjStr);
	        	} else {
	        		$inputProjObj = new projectionObj($inputProjStr);
	        		$layerProjObj = new projectionObj($layerProjStr);
	        	}
				$changeLayProj = true;
			}
	
			$newObjectsTmp = is_array($geoData) ? json_decode($geoData, true) : $geoData;
//			$newObjectsTmp = json_decode($geojsonDataToUse, true);
			$newObjects = isset($newObjectsTmp->features) ? $newObjectsTmp->features : array();
			foreach ($newObjects as $newObject) {
				$wkt = arrayToWkt($newObject->geometry);
				$msShape = ms_shapeObjFromWkt($wkt);
				if ($changeLayProj) {
//					$msShape->project($layerProjObj, $mapProjObj);
					$msShape->project($inputProjObj, $layerProjObj);
				}
//				$msShape->values = PMCommon::object2array($newObject->properties);
				if ($this->layer->addFeature($msShape) == 0) {
					$this->oneOrMoreFeatures = true;
				}

			}
*/
    	}

    	return $this->oneOrMoreFeatures;
    }

}

/**
 * 
 * Manage client data already declared in MapServer mapfile
 *
 * Extends the clientDynamicLayer class and implement the addFeatures function.
 *
 */
class clientDynamicLayer_ms extends clientDynamicLayer
{
    /**
     * 
     * @see incphp/map/clientDynamicLayer#addFeatures()
     */
    protected function addFeatures($geoData) {
    	$this->oneOrMoreFeatures = true;
    	return $this->oneOrMoreFeatures;
    }

}


/**
 * TODO
 * 
 */
class clientDynamicLayer_KML extends clientDynamicLayer
{
/*
	public function __construct($map, $layerDef, $geoData) {
        parent::__construct($map, $layerDef, $geoData);
    }
*/

    protected function addFeatures($geoData) {
    	return false;
    }
}


/**
 * 
 * for the old version
 * in = 
array(3) { 
	["type"]=>  string(7) "Feature" 
	["properties"]=>  array(8) { 
		["NAME"]=>  string(4) "TEST" 
		["ISO2_CODE"]=>  string(2) "AD"
		["ISO3_CODE"]=>  string(3) "AND" 
		["ISO_NUM"]=>  int(20) 
		["FIPS_CODE"]=>  string(2) "AN" 
		["CAPITAL"]=>  string(16) "Andorra la Vella" 
		["POPULATION"]=>  int(70549) 
		["AREA_KM2"]=>  int(468) } 
	["geometry"]=>  array(2) { 
		["type"]=>  string(7) "Polygon" 
		["coordinates"]=>  array(1) { 
			[0]=>  array(3) { 
				[0]=>  array(2) { 
					[0]=>  float(19.501152) 
					[1]=>  float(40.962296) 
				} 
				[1]=>  array(2) { 
					[0]=>  float(16) 
					[1]=>  float(48.777752) 
				} 
				[2]=>  array(2) { 
					[0]=>  float(1.439922) 
					[1]=>  float(42.606491) 
				}
			}
		}
	}
} 
 * out = POLYGON((1 1,5 1,5 5,1 5,1 1),(2 2, 3 2, 3 3, 2 3,2 2))
*/
/*
function arrayGeoJsonToWkt($in) {
	$out = "";
	
	$coords = $in->coordinates;
	switch($in->type) {
		case "Point":
			$point = $coords;
			if (count($point) == 2) {
				$pointStr = implode(" ", $point);
			}
			if (strlen($pointStr) > 0) {
				$out = "POINT ($pointStr)";
			}
			break;
		case "LineString":
			$line = $coords;
			$lineStr = array();
			foreach ($line as $point) {
				if (count($point) == 2) {
					$pointStr = implode(" ", $point);
					if (strlen($pointStr) > 0) {
						$lineStr[] = $pointStr;
					}
				}
			}
			if (count($lineStr)) {
				$out = "LINESTRING (" . implode(",", $lineStr) . ")";
			}
			break;
		case "Polygon":
			$polygon = $coords;
			$polygonStr = array();
			foreach ($polygon as $line) {
				if (count($line) > 0) {
					$lineStr = array();
					foreach ($line as $point) {
						if (count($point) == 2) {
							$pointStr = implode(" ", $point);
							if (strlen($pointStr) > 0) {
								$lineStr[] = $pointStr;
							}
						}
					}
					if (count($lineStr)) {
						$polygonStr[] = "(" . implode(",", $lineStr) . ")";
					}
				}
			}
			if (count($polygonStr)) {
				$out = "POLYGON (" . implode(",", $polygonStr) . ")";
			}
			break;
		case "MultiPolygon":
			$polygons = $coords;
			$polygonsStr = array();
			foreach ($polygons as $polygon) {
				$polygonStr = array();
				foreach ($polygon as $line) {
					if (count($line) > 0) {
						$lineStr = array();
						foreach ($line as $point) {
							if (count($point) == 2) {
								$pointStr = implode(" ", $point);
								if (strlen($pointStr) > 0) {
									$lineStr[] = $pointStr;
								}
							}
						}
						if (count($lineStr)) {
							$polygonStr[] = "(" . implode(",", $lineStr) . ")";
						}
					}
				}
				if (count($polygonStr)) {
					$polygonsStr[] = "(" . implode(",", $polygonStr) . ")";
				}
			}
			if (count($polygonsStr)) {
				$out = "MULTIPOLYGON (" . implode(",", $polygonsStr) . ")";
			}
			break;
		case "MultiPoint":
		case "MultiLineString":
		case "GeometryCollection":
		default:
			break;
	}
	
	return $out;
}
*/

?>