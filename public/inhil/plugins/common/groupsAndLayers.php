<?php

/******************************************************************************
 *
 * Purpose: Common functions used in plugins
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
 * The software is distributed in the hope that it will be useful,
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
 * Return an array of layers, depending of the name of a layer or a group
 * It is possible to pass an array in parameter
 */
function getLayersByGroupOrLayerName($map, $groupOrLayer) {
	$mapLayers = Array();

	if ($map) {
		if (is_array($groupOrLayer)) {
			$manyGroupOrLayer = $groupOrLayer;
		} else {
			$manyGroupOrLayer = explode(",", $groupOrLayer);
		}
		foreach($manyGroupOrLayer as $oneGroupOrLayer) {
			$oneGroupOrLayer = trim($oneGroupOrLayer);
			if ($oneGroupOrLayer) {
				// If we are searching a group (not a layer) :
				$mapLayersIndexes = $map->getLayersIndexByGroup($oneGroupOrLayer);
				if ($mapLayersIndexes) {
					foreach ($mapLayersIndexes as $iLayerIndex) {
						$mapLayer = $map->getLayer($iLayerIndex);
						if ($mapLayer) {
							$mapLayers[] = $mapLayer;
						}
					}
				} else {
					$mapLayer = @$map->getLayerByName($oneGroupOrLayer);
					if ($mapLayer) {
						$mapLayers[] = $mapLayer;
					}
				}
			}
		}
	}

	return $mapLayers;
}

/**
 * highlight object :
 * (from map.php : PMAP::pmap_addResultLayer)
 */
function addResultLayer($map, $reslayer, $shpindexes, $shptileindexes=-1) {
//    $qLayer = $map->getLayerByName($reslayer);
	$qLayers = getLayersByGroupOrLayerName($map, $reslayer);
	if ($qLayers) {
		$qLayer = $qLayers[0];
		if ($qLayer) {
		    $qlayType = $qLayer->type;
		    $layNum = count($map->getAllLayerNames());
		
		    // Test if layer has the same projection as map
		    $mapProjStr = $map->getProjection();
		    $qLayerProjStr = $qLayer->getProjection();
			$changeLayProj = false;
		
		    if ($mapProjStr && $qLayerProjStr && $mapProjStr != $qLayerProjStr) {
		        $changeLayProj = true;
				if ($_SESSION['MS_VERSION'] < 6) {
		        	$mapProjObj = ms_newprojectionobj($mapProjStr);
		        	$qLayerProjObj = ms_newprojectionobj($qLayerProjStr);
				} else {
		        	$mapProjObj = new projectionObj($mapProjStr);
		        	$qLayerProjObj = new projectionObj($qLayerProjStr);
				}
		    }
		
		    // New result layer
		    if ($_SESSION['PM_TPL_MAP_FILE']) {
		        // load from template map file
		        $hlDynLayer = 0;
		        $hlMap = ms_newMapObj($_SESSION['PM_TPL_MAP_FILE']);
		        $hlMapLayer = $hlMap->getLayerByName("highlight_$qlayType");
		        $newResLayer = ms_newLayerObj($map, $hlMapLayer);
		        
		    } else {
		        // create dynamically
		        $hlDynLayer = 1;
		        $newResLayer = ms_newLayerObj($map);
		        $newResLayer->set("name", "reslayer");
		        if ($qlayType == 0) {
		            $newResLayer->set("type", 0);  // Point for point layer
		        } elseif ($qlayType == 1 || $qlayType == 2) {
		            $newResLayer->set("type", 1);  // Line for line && polygon layers
		        }
		        ##$newResLayer->set("type", $qlayType);  // Take always same layer type as layer itself
		    }
		
		    // Add selected shape to new layer
		    //# when layer is an event theme
		    if ($qLayer->getMetaData("XYLAYER_PROPERTIES") != "") {
		        foreach ($shpindexes as $cStr) {
		            $cList = preg_split('/@/', $cStr);
		            $xcoord = $cList[0];
		            $ycoord = $cList[1];
		            $resLine = ms_newLineObj();   // needed to use a line because only a line can be added to a shapeObj  
		            $resLine->addXY($xcoord, $ycoord);
		            $resShape = ms_newShapeObj(1);
		            $resShape->add($resLine);
		            $newResLayer->addFeature($resShape);
		        }
	        //# specific for PG layers  <==== required for MS >= 5.6 !!!
	        } elseif ($qLayer->connectiontype == 6) {
	            $newResLayer->set("connection", $qLayer->connection);
	            if (method_exists($newResLayer, "setConnectionType")) {
	                $newResLayer->setConnectionType($qLayer->connectiontype);
	            } else {
	                $newResLayer->set("connectiontype", $qLayer->connectiontype);
	            }
				$data = $qLayer->data;
				// use layers with complex queries that are too long to select results 
	            // cause maxscaledenom is not used...
	            if ($qLayer->getMetaData("PM_RESULT_DATASUBSTITION") != "") {
	                $data = $qLayer->getMetaData("PM_RESULT_DATASUBSTITION");
	            }
	            
	            $newResLayer->set("data", $data);
	            if ($qLayerProjStr) $newResLayer->setProjection($qLayerProjStr);
	            
	            $glList = PMCommon::returnGroupGlayer($reslayer);
	            $glayer = $glList[1];
	            $layerDbProperties = $glayer->getLayerDbProperties();
	            $uniqueField = $layerDbProperties['unique_field'];
	            
				$indexesStr = implode(",", $shpindexes);
            	$idFilter = "($uniqueField IN ($indexesStr))";
	            $newResLayer->setFilter($idFilter);
			    //# 'normal' layers
		    } else {
		        // Add selected shape to new layer
				
				// use layers with complex queries that are too long to select results 
	            // cause maxscaledenom is not used...
				$olddata = false;
	            if ($qLayer->getMetaData("PM_RESULT_DATASUBSTITION") != "") {
	                $olddata = $qLayer->data;
	            	$qLayer->set("data", $qLayer->getMetaData("PM_RESULT_DATASUBSTITION"));
	            }

                $msVersion = $_SESSION['MS_VERSION']; 
		        $qLayer->open();
		        foreach ($shpindexes as $resShpIdx) {
		            if (preg_match("/@/", $resShpIdx)) {
		                $idxList = explode("@", $resShpIdx);
		                $resTileShpIdx = $idxList[0];
		                $resShpIdx = $idxList[1];
		            } else {
		                $resTileShpIdx = $shptileindexes;
		            }
		
                    $resShape = PMCommon::resultGetShape($msVersion, $qLayer, null, $resShpIdx, $resTileShpIdx);
		            
		            // Change projection to map projection if necessary
		            if ($changeLayProj) {
		                // If error appears here for Postgis layers, then DATA is not defined properly as:
		                // "the_geom from (select the_geom, oid, xyz from layer) AS new USING UNIQUE oid USING SRID=4258" 
		                $resShape->project($qLayerProjObj, $mapProjObj);
		            }
		            
		            $newResLayer->addFeature($resShape);
		        }
		        
		        $qLayer->close();
				
				// use layers with complex queries that are too long to select results 
	    	    // cause maxscaledenom is not used...
	            // reset data tag
	            if ($olddata) {
	            	$qLayer->set('data', $olddata);
	            }
		    }
		    
		    
		    $newResLayer->set("status", MS_ON);
		    $newResLayerIdx = $newResLayer->index;
		
		    if ($hlDynLayer) {
		        // SELECTION COLOR
		        $iniClrStr = trim($_SESSION["highlightColor"]);
		        $iniClrList = preg_split('/[\s,]+/', $iniClrStr);
		        $iniClr0 = $iniClrList[0];
		        $iniClr1 = $iniClrList[1];
		        $iniClr2 = $iniClrList[2];
		    
		        // CREATE NEW CLASS
		        $resClass = ms_newClassObj($newResLayer);
		        $clStyle = ms_newStyleObj($resClass);
		        $clStyle->color->setRGB($iniClr0, $iniClr1, $iniClr2);
		        $clStyle->set("symbolname", "circle");
		        $symSize = ($qlayType < 1 ? 10 : 5);
		        $clStyle->set("size", $symSize);
		    }
		
		    // Move layer to top (is it working???)
		    //$layOrder = $map->getLayersDrawingOrder();
		    while ($newResLayerIdx < ($layNum-1)) {
		        $map->moveLayerUp($newResLayerIdx);
		    }
		}
	}
}

/**
 * Return groups available in array
 */
function getAvailableGroups($map, $onlyChecked, $onlyNonRasters, $onlyVisibleAtScale, $onlyQueryable = true, $onlyInCategories = false) {
	// only checked groups :
	if ($onlyChecked) {
		$groupsStep1 = $_SESSION["groups"];
	} else {
		$groupsStep1 = $_SESSION["allGroups"];
	}

	$groupsStep2 = Array();
	$scale = $_SESSION["geo_scale"];
	$grouplist = $_SESSION["grouplist"];

	foreach ($grouplist as $grp){
	    if (in_array($grp->getGroupName(), $groupsStep1, TRUE)) {
	        $glayerList = $grp->getLayers();
	        foreach ($glayerList as $glayer) {
	            $mapLayer = $map->getLayer($glayer->getLayerIdx());

	            $groupOk = true;

	            // no raster layers / groups :
	            if ($groupOk && $onlyNonRasters) {
	            	if ($mapLayer->type >= 3) {
	            		$groupOk = false;
	            	}
		        }

            	// only visible layers / groups depending on scale :
            	if ($groupOk && $onlyVisibleAtScale) {
            		if (PMCommon::checkScale($map, $mapLayer, $scale) <> 1) {
            			$groupOk = false;
            		}
            	}

            	if ($groupOk && $onlyQueryable) {
			        $hasTemplate = 0;
		            if ($mapLayer->template) {
		            	$hasTemplate = 1;
		            }
		            if (!$hasTemplate) {
			            $numclasses = $mapLayer->numclasses;
			            for ($cl=0; $cl < $numclasses; $cl++) {
			                $class = $mapLayer->getClass($cl);
			                $classTemplate = $class->template;
			                if ($class->template) {
			                	$hasTemplate = 1;
			                	break;
			                }
			            }
		            }
		            $groupOk = $hasTemplate;
            	}
            
            	if ($groupOk) {
                	$groupsStep2[] = $grp;
	                break;
	            }
	        }
	    }
	}

	// only layers in categories
	$groupsStep3 = Array();
	if ($onlyInCategories) {
		// get Categories
	    require_once($_SESSION['PM_INCPHP'] . "/layerview.php");
		$layerView = new LayerView($map, false, false);
		$categoryList = $layerView->getCategoryList();

		if (count($categoryList) > 0) {
			foreach ($categoryList as $cat) {
				$catGrps = $cat['groups'];
				$categoriesGroups = Array();
				foreach ($catGrps as $catGrp) {
					if ($catGrp) {
						$catGrpName = $catGrp['name'];
						foreach ($groupsStep2 as $grp) {
							$grpName = $grp->getGroupName();
							if ($catGrpName === $grpName) {
								$groupsStep3[] = $grp;
							}
						}
					}
				}
			}
		}
	} else {
		$groupsStep3 = $groupsStep2;
	}

	return $groupsStep3; 
}


/**
 * Return the layer list with opacity to use
 *
 * $retVal =  (in INI configuration file) defining the name of the paremeter to read
 *
 * return an array of "name => opacity"
 */
function getConfigLayersAndOpacities($ini, $paramName) {
	$retVal = Array();	
	if ($paramName) {
		$list = isset($ini[$paramName]) ? $ini[$paramName] : "";
		if ($list) {
			$layersAndOpacities = explode(",", $list);
			if ($layersAndOpacities) {
				foreach ($layersAndOpacities as $layerAndOpacity) {
					$arrayTmp = explode(":", $layerAndOpacity);
					if ($arrayTmp[0]) {
						$retVal[$arrayTmp[0]] = $arrayTmp[1] ? (integer) $arrayTmp[1] : "map";
					}
				}
			}
		}
	}

	return $retVal;
}

/**
 * Return name and header fields for the pmapper layer specified
 * 
 * The result array contain:
 * - string lists (use valuesToUseTxt and valuesToShowTxt keys)
 * - arrays (use valuesToUse and valuesToShow keys)
 */
function getAttributsRealAndReadNames($layerName) {
	$retVal = array(); 
	$valuesToUse = array();
	$valuesToShow = array();

	if (strlen($layerName) > 0) {
		$grouplist = $_SESSION['grouplist'];
		if ($grouplist) {
			$group = false;
			if (array_key_exists($layerName, $grouplist)) {
				$group = $grouplist[$layerName];
			} else {
				$found = false;
				foreach ($grouplist as $grp) {
					if ($grp && is_a($grp, 'GROUP')) {
						$glayerList = $grp->getLayers();
						foreach ($glayerList as $glayer) {
							if ($glayer->getLayerName() == $layerName) {
								$group = $grp;
								$found = true;
								break;
							}
						}
						if ($found) {
							break;
						}
					}
				}
			}
			if ($group && is_a($group, 'GROUP')) {
				$valuesToShow = $group->getResHeaders();
				$layers = $group->getLayers();
				if ($layers) {
					$firstLayer = $layers[0];
					if ($firstLayer) {
						$valuesToUse = $firstLayer->getResFields();
					}
				}
			}
		}
	}
	$valuesToUseTxt = implode(',', $valuesToUse);
	$valuesToShowTxt = implode(',', $valuesToShow);

	$retVal['valuesToUseTxt'] = $valuesToUseTxt;
	$retVal['valuesToShowTxt'] = $valuesToShowTxt;
	$retVal['valuesToUse'] = $valuesToUse;
	$retVal['valuesToShow'] = $valuesToShow;
	
	return $retVal;
}


/*
 * change SESSION['grouplist']:
 */
function updateGroupList($map) {
	// get original opacities:
	// this require should be done before session_start!!! 
	//require_once($_SESSION['PM_INCPHP'] . '/group.php');
	$grouplist = $_SESSION['grouplist'];
	$opacities = array();
	foreach ($grouplist as $grp) {
		$glayerList = $grp->getLayers();
		foreach ($glayerList as $glayer) {
			$opacities[$glayer->getLayerName()] = $glayer->getOpacity();
		}
	}

	
	// change SESSION['grouplist']:
	require_once($_SESSION['PM_INCPHP'] . '/initgroups.php');
	$iG = new Init_groups($map, $_SESSION['allGroups'], $_SESSION['gLanguage'], true);
	$iG->createGroupList();
	
	//restore opacity:
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
?>