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

require_once(dirname(__FILE__) . '/../../incphp/pmsession.php');

class SelectTools
{
    // ======================== PUBLIC FUNCTIONS =====================//

    /**
     *  Remove current selection
     */
	public static function removeCurrentSelection() {
		// save  selection (to reopen later)
		if ($_SESSION['JSON_Results']) {
			if (!isset($_SESSION['OLD_JSON_Results'])) {
				$_SESSION['OLD_JSON_Results'] = array();
			}
			$_SESSION['OLD_JSON_Results'][0] = $_SESSION['JSON_Results'];
		}
		unset($_SESSION['JSON_Results']);

		// remove highlight
		unset($_SESSION['resultlayers']);
	}


	/**
	 * Reload selection (if no current, try the previous one)
	 */
	public static function reloadSelection() {
		// if no current selection -> reload the previous one
		if (!$_SESSION['JSON_Results']) {
			if (isset($_SESSION['OLD_JSON_Results']) && isset($_SESSION['OLD_JSON_Results'][0])) {
				$_SESSION['JSON_Results'] = $_SESSION['OLD_JSON_Results'][0];
			}
		}
		
		self::updateHighlightJson($_SESSION['JSON_Results']);

		return $_SESSION['JSON_Results'];
	}
	

	/**
	 * updating highlight
	 */
	public static function updateHighlightJson($jsonSelect) {
		if ($jsonSelect) {
			$object = self::jsonToObject($jsonSelect);
			self::updateHighlightObj($object);			
		}
	}	
	
	public static function updateHighlightObj($object) {
		if ($object) {
			unset($_SESSION['resultlayers']);
			
			foreach ($object[0] as $tmpLayer) {
				foreach ($tmpLayer->values as $tmpObject) {
					$layerName = $tmpObject[0]->shplink[0];
					if (!isset($_SESSION['resultlayers'][$layerName])) {
						$_SESSION['resultlayers'][$layerName] = array();
					}
					$_SESSION['resultlayers'][$layerName][] = $tmpObject[0]->shplink[1];
				}
			}
		}
	}

	/**
	 *
	 * mix two Object selection in json format ...
	 *
	 * @param string $mixType ('add': add new selection in current selection
	 *                         'del': delete new selection in current selection
	 *                         'intersec': select only objects present in two selections
	 *                         other string : return new selection)
	 * @param json string $jsonSelect1 (for instance: current selection)
	 * @param json string $jsonSelect2 (for instance: new selection to add)
	 *
	 * return the new selection
	 */
	public static function mixSelection($mixType, $jsonSelect1, $jsonSelect2 = false) {
		switch ($mixType) {
			case 'add' :
				return self::add($jsonSelect1, $jsonSelect2);
			case 'del' :
				return self::del($jsonSelect1, $jsonSelect2);
			case 'intersec' :
				return self::intersect($jsonSelect1, $jsonSelect2);
			default :
				return $jsonSelect2;
		}	 
	}
	
	
	/**
	 * 
	 * Add two Object selection in json format ...
	 * 
	 * @param json string $jsonSelect1 (for instance: current selection)
	 * @param json string $jsonSelect2 (for instance: new selection to add)
	 * 
	 * return the new selection
	 */
	public static function add($jsonSelect1, $jsonSelect2 = false) {
		if ($jsonSelect1 && !$jsonSelect2) {
			$ret = $jsonSelect1;
		} else if (!$jsonSelect1 && $jsonSelect2) {
			$ret = $jsonSelect2;
		} else {
			$ret = '';
		}
		
    	// something to add + existing results
		if ($jsonSelect1 && $jsonSelect2) {	
			$selectObj = self::jsonToObject($jsonSelect1);
			$objToAdd = self::jsonToObject($jsonSelect2);

			if ($selectObj && $selectObj[0] && $objToAdd && $objToAdd[0]) {
				// Loop on layers of new selection
				// 1. the layer already exists in the current selection
				//    --> add values and check already existing values
				// TODO check headers
				// 2. the layer doesn't exists
				//    --> add it to the end of the array
				foreach ($objToAdd[0] as $tmpLayer) {
					$iLayer = 0;
					$sizeSelectObj = count($selectObj[0]);
					while ($selectObj[0][$iLayer]->name != $tmpLayer->name && $iLayer < $sizeSelectObj){
						$iLayer++;
					}
					
					// layer doesn't exists
					if ($iLayer == $sizeSelectObj) {
						$selectObj[0][] = $tmpLayer;				
					// layer exists --> add objets
					} else {
						// avoid already existing values
						$tmpListIdObjSelect = array();
						foreach ($selectObj[0][$iLayer]->values as $tmpObject) {
							$tmpListIdObjSelect[] = $tmpObject[0]->shplink[1];
						}
		
						foreach ($tmpLayer->values as $tmpObject){
							if (!in_array($tmpObject[0]->shplink[1], $tmpListIdObjSelect)) {
								$selectObj[0][$iLayer]->values[] = $tmpObject;	
							}					
						}
						$selectObj[0][$iLayer]->numresults = count($selectObj[0][$iLayer]->values);
					}
				}
				
				// update extent
				$selectObj[1]->allextent = self::mergeExtent($selectObj[1]->allextent, $objToAdd[1]->allextent);
				
				// add 'Zoom2All' button
				if (in_array($_SESSION["mode"], (array) $_SESSION["zoomAll"])) {
					$selectObj[1]->zoomall = true;
				}
				
				$ret = self::objectToJson($selectObj);
			}
		} 
		
    	return $ret;
    }
    
    
    /**
     * Delete in $jsonSelect1 the objects who are in $jsonSelect2
     * 
	 * @param unknown_type $jsonSelect1 (for instance: current selection)
	 * @param unknown_type $jsonSelect2 (for instance: new selection to remove)
     * 
     * return the new selection
     */
    public static function del($jsonSelect1, $jsonSelect2 = false) {
		if ($jsonSelect1 && !$jsonSelect2) {
			$ret = $jsonSelect1;
		} else {
			$ret = null;
		}
		
    	// something to add + existing results
		if ($jsonSelect1 && $jsonSelect2) {
			// init extent
			$xMin = $xMax = $yMin = $yMax = -1;
			
			$selectObj = self::jsonToObject($jsonSelect1);
			$objToDel  = self::jsonToObject($jsonSelect2);
			
			if ($selectObj && $selectObj[0] && $objToDel && $objToDel[0]) {
				// Loop on layers of "old" current selection
				// 1. Layer exists in the 2d selection
				//    --> Loop on removable object
				//        If object exists in both selections
				//            --> remove it
				//        Else 
				//            --> copy it in new selection
				//            --> add its extent
				// 2. layer doesn't exist in the 2d selection
				//    --> copy layer to the new selection
				//    --> Loop on all object to add theirs extent
				$newSelectObjTmp = array();
				foreach ($selectObj[0] as $selectLayer) {
					$iLayer = 0;
					$sizeTabSelectDelObj = count($objToDel[0]);
					while ($objToDel[0][$iLayer]->name != $selectLayer->name && $iLayer < $sizeTabSelectDelObj) {
						$iLayer++;
					}
					
					// layer doesn't exists
					if ($iLayer == $sizeTabSelectDelObj) {
						
						$newSelectObjTmp[] = clone($selectLayer);	
						// loop on objects to calculate extent
						foreach($selectLayer->values as $tmpObject){ 
							$tmpTab1 = explode("+", $tmpObject[0]->shplink[2]);
							$xMin = $xMin == -1 ? $tmpTab1[0] : min($xMin, $tmpTab1[0]);
							$yMin = $yMin == -1 ? $tmpTab1[1] : min($yMin, $tmpTab1[1]);
							$xMax = $xMax == -1 ? $tmpTab1[2] : max($xMax, $tmpTab1[2]);
							$yMax = $yMax == -1 ? $tmpTab1[3] : max($yMax, $tmpTab1[3]);
						}
						
						// Number of objects in this layer
						$selectLayer->numresults = count($selectLayer->values);
						
					// layer exists --> add its objets
					} else {
						// array objects to delete 
						$tmpListIdObjDelete = array();
						
						foreach ($objToDel[0][$iLayer]->values as $tmpObject){
							$tmpListIdObjDelete[] = $tmpObject[0]->shplink[1];
						}
		
						$tmpLayer = clone($selectLayer);
						$tmpLayer->values = array();				
		
						foreach ($selectLayer->values as $tmpObject) {
							// object isn't in the list to remove --> keep it
							if (!in_array($tmpObject[0]->shplink[1], $tmpListIdObjDelete)) {
								$tmpLayer->values[] = $tmpObject;	
								// extent
								$tmpTab1 = explode("+", $tmpObject[0]->shplink[2]);
								$xMin = $xMin == -1 ? $tmpTab1[0] : min($xMin, $tmpTab1[0]);
								$yMin = $yMin == -1 ? $tmpTab1[1] : min($yMin, $tmpTab1[1]);
								$xMax = $xMax == -1 ? $tmpTab1[2] : max($xMax, $tmpTab1[2]);
								$yMax = $yMax == -1 ? $tmpTab1[3] : max($yMax, $tmpTab1[3]);
							}					
						}
						$nbResult = count($tmpLayer->values);
						if ($nbResult > 0) {
							$tmpLayer->numresults = $nbResult;
							$newSelectObjTmp[] = $tmpLayer;	
						}
					}
				}
			
				// result
				if ($newSelectObjTmp){
					$newSelectObj = array();
					
					// objects:
					$newSelectObj[0] = $newSelectObjTmp;
					
					// extent:
					$tmpTab2[0] = $xMin;
					$tmpTab2[1] = $yMin;
					$tmpTab2[2] = $xMax;
					$tmpTab2[3] = $yMax;
					
					$newSelectObj[1] = new stdClass();
					$newSelectObj[1]->allextent = implode("+", $tmpTab2);
					$newSelectObj[1]->zoomall = $selectObj[1]->zoomall;
					$newSelectObj[1]->autozoom = $selectObj[1]->autozoom;
					$newSelectObj[1]->infoWin = $selectObj[1]->infoWin;
						
					$ret = self::objectToJson($newSelectObj);
				}
			}
		}
		
    	return $ret;
    }
	
    
   
    /**
     * 
     * Calculate the extent of the selection
     * 
     */
    public static function calculateExtent($jsonSelect) {
		$objSelect = self::jsonToObject($jsonSelect);
		return self::calculateExtentObj($objSelect);
    }
    
	public static function calculateExtentObj($objSelect) {
    	// init extent
		$xMin = $xMax = $yMin = $yMax = -1;
			
    	// layers
    	foreach ($objSelect[0] as $layer) {
    		// objects
			foreach ($layer->values as $tmpObject) {
				$tmpTab1 = explode('+', $tmpObject[0]->shplink[2]);
				$xMin = $xMin == -1 ? $tmpTab1[0] : min($xMin, $tmpTab1[0]);
				$yMin = $yMin == -1 ? $tmpTab1[1] : min($yMin, $tmpTab1[1]);
				$xMax = $xMax == -1 ? $tmpTab1[2] : max($xMax, $tmpTab1[2]);
				$yMax = $yMax == -1 ? $tmpTab1[3] : max($yMax, $tmpTab1[3]);
			}
		}
			
    	return "$xMin+$yMin+$xMax+$yMax";
    }
    

    /**
     * 
     * Merge the extents of 2 selections
     * 
     */
	public static function mergeExtent($extent1, $extent2) {
		// [0]=>xMin [1]=>yMin [2]=>xMax [3]=>yMax;
		$tmpTab1 = explode('+', $extent1);
		$tmpTab2 = explode('+', $extent2);

		$tmpTab1[0] = min($tmpTab1[0], $tmpTab2[0]);
		$tmpTab1[1] = min($tmpTab1[1], $tmpTab2[1]);
		$tmpTab1[2] = max($tmpTab1[2], $tmpTab2[2]);
		$tmpTab1[3] = max($tmpTab1[3], $tmpTab2[3]);
		
		return implode('+', $tmpTab1);
    }
    
    
    // ======================== INTERNAL FUNCTIONS =====================//
    
    // OBJECT -> JSON
    protected static function objectToJson($objSelect) {
    	$str = 'AAAAAA';
    	$jsonSelect = json_encode($objSelect);
		$jsonSelect = str_replace($str, '@', $jsonSelect);
//		$jsonSelect = str_replace("'", "\\'", $jsonSelect);
		
    	return $jsonSelect;
    }
    
    // JSON -> OBJECT	
    protected static function jsonToObject($jsonSelect) {
		$str = "AAAAAA";
		$jsonSelect = str_replace("@", $str, $jsonSelect);
//		$jsonSelect = str_replace("\\'", "'", $jsonSelect);
		$objSelect = json_decode($jsonSelect);
		
    	return $objSelect;
    }

    /**
     * 
     * select objects who are in the two selections
     * 
	 * @param unknown_type $jsonSelect1 (for instance: current selection)
	 * @param unknown_type $jsonSelect2 (for instance: new selection to remove)
     * 
     * return the new selection
     */
    public static function intersect($jsonSelect1, $jsonSelect2) {
    	$ret = null;
    	
		if ($jsonSelect1 && $jsonSelect2) {
			// init extent:
			$xMin = $xMax = $yMin = $yMax = -1;
			
			$selectObj = self::jsonToObject($jsonSelect1);
			$selectObj2 = self::jsonToObject($jsonSelect2);
			
			if ($selectObj && $selectObj[0] && $selectObj2 && $selectObj2[0]) {
				// Loop on layers of current selection
				// 1. Layer exists in the 2d selection
				//    --> Loop on objects
				//        If object exists in both selections
				//            --> copy it in new selection
				//            --> add its extent
				//        Else
				//            --> continue
				// 2. layer doesn't exist in the 2d selection
				//    --> continue
				$newSelectObjTmp = array();
				foreach ($selectObj[0] as $selectObjLayer1) {
								
					$iLayer = 0;
					$sizeTabSelectDelObj = count($selectObj2[0]);
					while ($selectObj2[0][$iLayer]->name != $selectObjLayer1->name && $iLayer < $sizeTabSelectDelObj){
						$iLayer++;
					}
					
					// layer exist in 2d selection 
					if ($iLayer != $sizeTabSelectDelObj) {
						// array objects in the new select 
						$tmpListIdObjDelete = array();
						foreach ($selectObj2[0][$iLayer]->values as $tmpObject) {
							$tmpListIdObjDelete[] = $tmpObject[0]->shplink[1];
						}
		
						$tmpLayer = clone($selectObjLayer1);
						$tmpLayer->values = array();				
		
						foreach ($selectObjLayer1->values as $tmpObject) {
							// object is in the 2d selection -> keep it
							if (in_array($tmpObject[0]->shplink[1], $tmpListIdObjDelete)) {
								$tmpLayer->values[] = $tmpObject;	
								// extent
								$tmpTab1 = explode("+", $tmpObject[0]->shplink[2]);
								$xMin = $xMin == -1 ? $tmpTab1[0] : min($xMin, $tmpTab1[0]);
								$yMin = $yMin == -1 ? $tmpTab1[1] : min($yMin, $tmpTab1[1]);
								$xMax = $xMax == -1 ? $tmpTab1[2] : max($xMax, $tmpTab1[2]);
								$yMax = $yMax == -1 ? $tmpTab1[3] : max($yMax, $tmpTab1[3]);
							}					
						}
						$nbResult = count($tmpLayer->values);
						if ($nbResult > 0) {
							$tmpLayer->numresults = $nbResult;
							$newSelectObjTmp[] = $tmpLayer;	
						}
					}
				}
				
				// result
				if ($newSelectObjTmp){
					$newSelectObj = array();
					
					// objects:
					$newSelectObj[0] = $newSelectObjTmp;
					
					// extent:
					$tmpTab2[0] = $xMin;
					$tmpTab2[1] = $yMin;
					$tmpTab2[2] = $xMax;
					$tmpTab2[3] = $yMax;
					
					$newSelectObj[1] = new stdClass();
					$newSelectObj[1]->allextent = implode('+', $tmpTab2);
					$newSelectObj[1]->zoomall = $selectObj[1]->zoomall;
					$newSelectObj[1]->autozoom = $selectObj[1]->autozoom;
					$newSelectObj[1]->infoWin = $selectObj[1]->infoWin;
						
					$ret = self::objectToJson($newSelectObj);
				}
			}
		}
		
    	return $ret;
    }

} // END CLASS




?>