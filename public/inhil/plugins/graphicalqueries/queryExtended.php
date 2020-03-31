<?php

/******************************************************************************
 *
 * Purpose:
 * Author:  Vincent Mathis, SIRAP
 *
 ******************************************************************************
 *
 * Copyright (c) 2009 SIRAP
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

// copy from incphp/query/query.php

require_once (dirname(__FILE__) . '/../../incphp/query/query.php');
require_once($_SESSION['PM_PLUGIN_REALPATH'] . '/common/groupsAndLayers.php');

class QueryExtended extends Query
{
	private $poly;
	private $queryType;
	
    // ======================== PUBLIC FUNCTIONS =====================//
    public function __construct($map, $queryType, $poly) {
	   	$_REQUEST['mode'] = 'nquery';
    	parent::__construct($map);
        $this->queryType = $queryType ? $queryType : 'simple';
        $this->poly = $poly;
    }

    /**
     * EXECUTE QUERY
     */
    public function q_execMapQuery() {
    	//--------------------------------
		// Initialize tolerance to 0
		//--------------------------------
		
    	$msLayers = array();
		// query in specified groups only:
    	if (isset($_REQUEST['groups']) && $_REQUEST['groups']) {
    		$msLayers = getLayersByGroupOrLayerName($this->map, $_REQUEST['groups']);
    	// all groups:
		} else {
		    for ($i = 0; $i < $this->map->numlayers; $i++) {
		    	$msLayers[] = $this->map->getLayer($i);
		    }
		}
		foreach ($msLayers as $msLayer) {
			// change tolerance only for buffer zone search.
			if ($this->queryType == 'object') {
				$msLayer->set('tolerance', 0);
			}
		}
	    

// Modified by Thomas RAFFIN (SIRAP)
// to test (substitution)
        // query by point
        // Modified by Thomas RAFFIN (SIRAP)
        // use layers with complex queries that are too long to select results
        // cause maxscaledenom is not used...
		$oldDatas = array();
		for ($i = 0 ; $i < $this->map->numlayers ; $i++) {
	    	$qLayer = $this->map->getLayer($i);
	    	$layerName = $qLayer->name;
	        if ($qLayer->getMetaData('PM_RESULT_DATASUBSTITION') != '') {
	            $oldDatas[$layerName] = $qLayer->data;
	         	$qLayer->set('data', $qLayer->getMetaData('PM_RESULT_DATASUBSTITION'));
	        }
	    }

	    // few lines from incphp/query/query.php
		// Patch from Alessandro Pasotti for fixing problems with result returned
		$this->map->setSize($this->mapwidth, $this->mapheight);
	    // Set $this->map->extent to values of current map extent
        // otherwise values of TOLERANCE in map file are not interpreted correctly
        $this->map->setExtent($this->GEOEXT['minx'], $this->GEOEXT['miny'], $this->GEOEXT['maxx'], $this->GEOEXT['maxy']);
        $this->map->preparequery();

    	if ($this->queryType == 'object') {
			if (isset($_SESSION['pluginsConfig']['graphicalqueries'])
			&& isset($_SESSION['pluginsConfig']['graphicalqueries']['bufferOnlyWithScale']) ) {
				$bufferOnlyWithScale = $_SESSION['pluginsConfig']['graphicalqueries']['bufferOnlyWithScale'] == 1;
			} else {
				$bufferOnlyWithScale = true;
			}
			
			if (!$bufferOnlyWithScale ) {
//				PMCommon::setGroups($this->map, $this->querygroups, $this->scale, 0, 1);
				PMCommon::setGroups($this->map, $this->querygroups, 0, 0, 1);
			}
		
    		// Search selected object
    		$jsonPMResult = $_SESSION['JSON_Results']; // "[...]" ou "{...}"
			$PMResult = json_decode($jsonPMResult);  // array ou class
			
			if ($PMResult) {
				$objUnion = false;
				
				// List layer of selected object
				$layersWithResult = $PMResult[0];
				// be carreful : 0.000000001 is too small !!
				$smallBuffer = 0.00000001;
				
				$mapProjStr = $this->map->getProjection();
				if ($_SESSION['MS_VERSION'] < 6) {
					$mapProjObj = ms_newprojectionobj($mapProjStr);
				} else {
					$mapProjObj = new projectionObj($mapProjStr);
				}

				// Loop on layers
				$layerName = '';
				foreach ($layersWithResult as $layerWithResult) {
					$objUnionLayer = false;
					
					// init layer to close the last open one
					$layer = false;
					$layerProjObj = false;

					// Loop on layer objects
					foreach ($layerWithResult->values as $selectedObject) {
						// layer name
						$currentLayerName = $selectedObject[0]->shplink[0];
						if ($currentLayerName != $layerName) {
							if ($layer) {
								$layer->close();
							}
							$layerName = $currentLayerName;
							$layer = $this->map->getLayerByName($layerName);
							
							$layerProjObj = false;
							$layerProjStr = $layer->getProjection();
							if ($mapProjStr && $layerProjStr && $mapProjStr != $layerProjStr) {
								if ($_SESSION['MS_VERSION'] < 6) {
									$layerProjObj = ms_newprojectionobj($layerProjStr);
								} else {
									$layerProjObj = new projectionObj($layerProjStr);
								}
							}
							
							$layer->open();
						}

						$shpIndex = $selectedObject[0]->shplink[1];
						// Retrieve shape by its index.
						$objShape = PMCommon::resultGetShape($_SESSION['MS_VERSION'], $layer, null, $shpIndex, -1);  // changed for compatibility with PG layers and MS >= 5.6
							
						// reproject
						if ($layerProjObj) {
							$objShape->project($layerProjObj, $mapProjObj);
						}
						
						// Union of the shapes
						if ($objUnionLayer) {
							$objUnionLayer = $objShape->union($objUnionLayer);
						} else {
							$objUnionLayer = $objShape;
						}
					} // End foreach : Loop of the layer
					
					// close layer if exists
					if ($layer) {
						$layer->close();
					}

					if ($objUnionLayer) {
						// Line : Buffer to convert Line or Point to Polygon
						if (($objUnionLayer->type == MS_SHAPE_LINE && isset($_REQUEST['select_buffer']) && $_REQUEST['select_buffer'] > 0)
						|| ($objUnionLayer->type == MS_SHAPE_POINT)) {
							$objUnionLayer = $objUnionLayer->buffer($smallBuffer);
	
						// if polygon -> the outline is removed to not select contiguous objects
						} else if ($objUnionLayer->type == MS_SHAPE_POLYGON && !isset($_REQUEST['select_buffer'])) {
							$objBoundary = $objUnionLayer->boundary();
							$objBoundary = $objBoundary->buffer($smallBuffer);
							$objUnionLayer = $objUnionLayer->difference($objBoundary);
						}
					}
					
					if ($objUnionLayer) {
						if ($objUnion) {
							$objUnion = $objUnion->union($objUnionLayer);
						} else {
							$objUnion = $objUnionLayer;
						}
					} // End if($objUnionLayer)
				} // End foreach : loop of layers

				// Buffer -> Buffer zone
				if (isset($_REQUEST['select_buffer'])) {
					if ($objUnion) {
						$objUnion = $objUnion->buffer($_REQUEST['select_buffer']);
						unset($_REQUEST['select_buffer']);
					}
				}
				if ($objUnion) {
					// Query
					@$this->map->queryByShape($objUnion);
				}
			}

        // Query by point
    	} else if ($this->queryType == 'point') {
    		$tmpPoint = ms_newPointObj();
        	
        	$tmpPoint->setXYZ($_REQUEST['select_pointX'], $_REQUEST['select_pointY'], 0);
        	
    		if (isset($_REQUEST['select_buffer'])) {
				@$this->map->queryByPoint($tmpPoint, MS_MULTIPLE, $_REQUEST['select_buffer']);
        	} else {
	        	@$this->map->queryByPoint($tmpPoint, MS_MULTIPLE, -1);
			}
        	
        	PMCommon::freeMsObj($tmpPoint);
    		
    	// query by Shape
        } else if ($this->queryType == 'polygon') { //'shape')
        	$poly = $_REQUEST['select_poly'];
		
			$tmpLine = ms_newLineObj();
			$tmpPoly = explode(',', $poly);
			
        	foreach ($tmpPoly as $point) {
				$tmpTab = explode(' ', $point);
				$tmpLine->addXY($tmpTab[0], $tmpTab[1]);
			}
			$objPoly = ms_newShapeObj(MS_SHAPE_POLYGON);
			$objPoly->add($tmpLine);
			
			// Buffer -> Buffer zone
			if (isset($_REQUEST['select_buffer'])) {
				$objPoly = $objPoly->buffer($_REQUEST['select_buffer']);
				unset($_REQUEST['select_buffer']);
			}

			@$this->map->queryByShape($objPoly);

			PMCommon::freeMsObj($tmpLine);
            PMCommon::freeMsObj($objPoly);

        // query by PolyLine
        } else if ($this->queryType == 'line') {
        	$poly = $_REQUEST['select_line'];
		
			$tmpLine = ms_newLineObj();
			$tmpPoly = explode(',', $poly);
			
        	foreach ($tmpPoly as $point) {
				$tmpTab = explode(' ', $point);
				$tmpLine->addXY($tmpTab[0], $tmpTab[1]);
			}
			
			// Reduce the polygon to a polyline
        	for ($i = count($tmpPoly) -1 ; $i >= 0 ; $i--) {
	    		$tmpTab = explode(' ', $tmpPoly[$i]);
				$tmpLine->addXY($tmpTab[0], $tmpTab[1]);
	    	}
			
			$objPoly = ms_newShapeObj(MS_SHAPE_POLYGON);
			$objPoly->add($tmpLine);
			
        	// Buffer -> Buffer zone
			if (isset($_REQUEST['select_buffer'])) {
				$bufferLocal = $_REQUEST['select_buffer'];
				if ($bufferLocal < 0) {
					$bufferLocal = 0;
				}
				$objPoly = $objPoly->buffer($bufferLocal);
				
				unset($_REQUEST['select_buffer']);
			}

			@$this->map->queryByShape($objPoly);

			PMCommon::freeMsObj($tmpLine);
            PMCommon::freeMsObj($objPoly);
        
        // query by circle
        } else if ($this->queryType == 'circle') {
        	$point = $_REQUEST['select_point'];

        	$radius = $_REQUEST['select_radius'];
        	$tmpPoint = ms_newPointObj();
        	
        	$tmpTab = explode(' ', $point);

        	$tmpPoint->setXYZ($tmpTab[0], $tmpTab[1],0);
        	
        	if (isset($_REQUEST['select_buffer'])) {
				@$this->map->queryByPoint($tmpPoint, MS_MULTIPLE, $radius + $_REQUEST['select_buffer']);
        	} else {
	        	@$this->map->queryByPoint($tmpPoint, MS_MULTIPLE, $radius);
			}
        	
        	PMCommon::freeMsObj($tmpPoint);
        }
		

        // Modified by Thomas RAFFIN (SIRAP)
        // use layers with complex queries that are too long to select results
        // cause maxscaledenom is not used...
        // reset data tag
    	foreach ($oldDatas as $qLayer => $oldData) {
    		$qLayer = $this->map->getLayerByName($qLayer);
        	$qLayer->set('data', $oldData);
    	}

    } // end function q_execMapQuery
    
} // END CLASS

?>