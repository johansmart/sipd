<?php
/******************************************************************************
 *
 * Purpose: initialization and creation of dynamic layers
 * Author:  Armin Burger
 *
 ******************************************************************************
 *
 * Copyright (c) 2003-2009 Armin Burger
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


class DynLayerJson
{
    protected $map;
    protected $json;
    protected $dynLayerList;
    protected $activeLayers;
     
    public function __construct($map, $jsonString)
    {
        $this->map = $map;
        // JSON string to decode
		if (is_string($jsonString)) {
			$this->json = json_decode($jsonString);
        // object or array from previously decoded JSON string
		} else {
			$this->json = $jsonString;
		}
    }
    
    
    public function initDynLayers($rewriteLegend=true)
    { 
        $this->createDynLayers();
        require_once($_SESSION['PM_INCPHP'] . "/initgroups.php");
        $allGroups = array_merge($_SESSION['allGroups'], $this->dynLayerList);
        $_SESSION['allGroups'] = array_unique($allGroups);
        
        if ($rewriteLegend) {
            require_once($_SESSION['PM_INCPHP'] . "/init/initmap.php");
            $initMap = new Init_map($this->map, false, false, $_SESSION['gLanguage']);
            $initMap->createLegendList();
        }
        $iG = new Init_groups($this->map, $allGroups, $_SESSION['language'], false); 
        $iG->createGroupList(); 
    }
    
    
   /**
    * Create dynamic layers based on JSON definition
    */
    public function createDynLayers()
    {
        foreach ($this->json as $dObj) {
            require_once($dObj->require);
            
            foreach ($dObj->layerlist as $dl) {
            	$this->createDynLayer($dl);
            }
        }
        
        
        return $this->dynLayerList;
    }
    
    /**
     * Create each dynamic layer
     */
    public function createDynLayer($dl) {
		$templateList = $dl->TEMPLATE;
		$this->dynLayerList[] = $dl->name;
		if ($templateList) {
			$template = $templateList[0];
			$newLayer = ms_newLayerObj($this->map, $this->map->getLayerByName($template)); 
			if ($templateList[1]) {
				$numclasses = $newLayer->numclasses;
				if ($numclasses > 0) {
					for ($cl=0; $cl < $numclasses; $cl++) {
						$newLayer->removeClass($cl);
						$cl--;
						$numclasses--;
					}
				}
            }
        } else {
			$newLayer = ms_newLayerObj($this->map);
		}
		$this->setLayerProperties($dl, $newLayer);

		return $this->dynLayerList;
    }

   /**
    * Set properties of new layer (class, style, etc.)
    */ 
    protected function setLayerProperties($p, $obj)
    {
        if (is_object($p)) {
            //print_r($p);
            $oList = (array)$p;
            //print_r($p);
            foreach ($oList as $k=>$v) {
                if ($k == "TEMPLATE") {
                
                // status: for default activated layers
                } elseif ($k == "status") {
                    if ($v == "MS_ON") 
                        $this->activeLayers[] = $p->name;
                        
                // METADATA tag
                } elseif ($k == "METADATA") {
                    foreach ((array)$v as $mk=>$mv) {
                        $obj->setMetadata($mk, $mv);
                    }

                // PROCESSING
                } elseif ($k == "PROCESSING") {
                    foreach ((array)$v as $str) {
                        $obj->setProcessing($str);
                    }

                // attribute binding
                } elseif ($k == "BINDING") {
                    foreach ((array)$v as $k2=>$v2) {
                        $obj->setBinding($k2, $v2);
                    }

                // layer type
                } elseif ($k == "TYPE") {
                	$found = true;
                	switch($v) {
                		case "POINT":
                			$v2 = MS_LAYER_POINT;
                			break;
                		case "LINE":
                			$v2 = MS_LAYER_LINE;
                			break;
						case "POLYGON":
	                		$v2 = MS_LAYER_POLYGON;
	                		break;
                   		case "ANNOTATION":
    	            		$v2 = MS_LAYER_ANNOTATION;
                			break;
        				case "CHART":
                			$v2 = MS_LAYER_CHART;
                			break;
               			default:
                			$found = false;
                			break;
                	}
					if ($found) {
                       	$obj->set("type", $v2);
                    }
                    
                // class labels    
                } elseif ($k == "label") {
                    $this->createClsLabel($obj, (array)$v);
                
                // new MS Object
                } elseif (is_object($v)) {
                    // one class or style:
                    if ($k == "class" || $k == "style") {
                        $newObj = $this->createMSObj($k, $obj);
                        $this->setLayerProperties($v, $newObj);
                    } else {
                        $this->setLayerProperties($v, $k);
                    }
                    
                // many classes and styles (they could be arrays)
                } elseif (is_array($v) && ($k == "classes" || $k == "styles") ) {
                	$type = false;
                	if ($k ==  "classes") {
                		$type = "class";
                	} else if ($k ==  "styles") {
                		$type = "style";
                	}	
                	foreach ($v as $v2) {
	                	if ($type) {
	               			$newObj = $this->createMSObj($type, $obj);
	                        $this->setLayerProperties($v2, $newObj);
	                    } else {
	                        $this->setLayerProperties($v2, $obj);
	                    }
                	}
                
                } else {
                    // lower case: $layer->set(x,y) 
                    if (preg_match("/color/", $k)) {
                        $obj->$k->setRGB($v[0],$v[1],$v[2]);
                    // normal property
                    } elseif (ctype_lower($k)) {
                        $obj->set($k, $v);
                    // UPPER case tags, set with function setXYZTag()
                    } elseif (ctype_upper($k{0})) {
                        $this->setMSTag($obj, $k, $v);
                    } 
                }
            }
        }
    }
    
   /**
    * Create class and style object
    */ 
    private function createMSObj($type, $pObj)
    {
        switch($type) {
            case "class":
                return ms_newClassObj($pObj);
            case "style":
                return ms_newStyleObj($pObj);
        }
    }
    
   /**
    * Set MS tages that require specific "setXYZ()" function instead of "set(x, y)"
    */ 
    private function setMSTag($obj, $k, $v)
    {
        switch($k) {
            case "PROJECTION":
                $obj->setProjection($v);
                break;
            case "FILTER":
                $obj->setFilter($v);
                break;
            case "EXPRESSION":
                $obj->setExpression($v);
                break;
        }
    }
   
   /**
    * create label with all properties
    */   
    private function createClsLabel($pObj, $lblList)
    {
        foreach ($lblList as $p=>$v) {
            if (preg_match("/color/", $p)) {
                $pObj->label->$p->setRGB($v[0],$v[1],$v[2]);
            } else {
                $pObj->label->set($p, $v);
            }
        }
    }
    
   /**
    * return parsed JSON string for debug reasons
    */ 
    public function returnJson()
    {
        return $this->json;
    }
    
    public function getActiveLayers()
    {
        return $this->activeLayers;
    }
    
    

}



?>