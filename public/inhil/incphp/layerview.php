<?php

/******************************************************************************
 *
 * Purpose: support class for TOC, legend and printing
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
 * Get layers for view in TOC, legend and printing 
 */
class LayerView
{
    /** @var object */
    private $map;
    
    /** @var array */
    private $categories; 

    /** @var bool */
    private $checkGroupVisibility = false;  
    
    /** @var bool */
    private $checkCatVisibility = false;  
    
    /** @var string */
    private $legPath = "images/legend/";   
    
    /** @var array */
    private $grouplist;   
    
    /** @var array */
    private $defGroups; 

    /** @var array */
    private $allGroups;
    
    /** @var array */
    private $groups;   

    /** @var string */
    private $imgFormat;

    /** @var int */
    private $scale;

    /** @var object */
    private $mapExt; 
       
   /**
    * Class Constructor
    * @param object $map
    * @param array $categories
    * @param bool $checkGroupVisibility
    * @param bool $checkCatVisibility
    */    
    public function __construct($map, $checkGroupVisibility=false, $checkGroupActivation=false, $scale=false)
    {
        $this->map           = $map;
        $this->categories    = $_SESSION['categories'];
        $this->checkGroupVisibility = $checkGroupVisibility;
        $this->checkGroupActivation = $checkGroupActivation;
        $this->grouplist     = $_SESSION["grouplist"];
        $this->defGroups     = $_SESSION["defGroups"];
        $this->allGroups     = $_SESSION["allGroups"];
        $this->imgExt        = $_SESSION["imgFormatExt"];
        $this->scale         = $scale ? $scale : $_SESSION['geo_scale'];
        $this->mapExt        = null;
    
        // GET LAYERS FOR DRAWING AND IDENTIFY
        if (isset ($_SESSION["groups"]) && count($_SESSION["groups"]) > 0){
            $this->groups = $_SESSION["groups"];
        } else {
            $this->groups = $this->defGroups;
        }    
    }
    
    
   /**
    * Returns category list with included groups
    * @return array
    */
    public function getCategoryList()
    {         
        //pm_logDebug(3, $this->categories, "Cat list: ");
        $allGroupsList = $this->getGroupList();
        
        $categoryList = array();
        foreach ($this->categories as $cat=>$cL) {
            $category['name'] = $cat;
            $category['description'] = _p($cL['description']);
            $catGL = $cL['groups'];
            $catGroupsList = array();
            foreach ($catGL as $grp) {
                if (isset($allGroupsList[$grp])) {
                    $catGroupsList[$grp] = $allGroupsList[$grp];
                }
            }
            $category['groups'] = $catGroupsList;
            
            $categoryList[$cat] = $category;
        }
        
        return $categoryList;
    }
    
   /**
    * Returns group list
    * @return array
    */
    public function getGroupList()
    {
        $legendGroups = array();
        foreach ($this->grouplist as $grp){
            $groupName = $grp->getGroupName();
            if ($this->checkGroupVisibility) {
                $groupVisible = $this->checkGroup($this->map, $grp, $this->scale);
                if ($this->checkGroupActivation) {
                    if (in_array($grp->getGroupName(), $this->groups) && $groupVisible) {
                        $legendGroups[$groupName] = $this->getGroup($grp);
                        //$legendGroups[] = $this->getGroup($grp);
                    }
                } else {
                    if ($groupVisible) {
                        $legendGroups[$groupName] = $this->getGroup($grp);
                    }
                }
            } else {
                $legendGroups[$groupName] = $this->getGroup($grp);
                //$legendGroups[] = $this->getGroup($grp);
            }
        }
        return $legendGroups;
    }
    
   /**
    * Returns group names list
    * @return array
    */    
    public function getGroupNameList()
    {
        return array_keys($this->getGroupList());
    }
    
    
    
   /**
    * Get the group properties
    * @param object $grp
    * @return array
    */
    protected function getGroup($grp)
    {
        $legendGroup = array();
        $legendGroup['name'] = $grp->getGroupName();
        $legendGroup['description'] = $grp->getDescription();

        $glayerList = $grp->getLayers();
        //pm_logDebug(3, $glayerList, "P.MAPPER-DEBUG: layerview.php/getGroup()->glayerList");
        
        // Settings for groups with raster layer without classes
        $ltype = 0; 
        $numClassesGrp = 0;       
        foreach ($glayerList as $glayer) {
            $skipLegend = $glayer->getSkipLegend();
            $numClassesGrp += count($glayer->getClasses());
            $legLayer = $this->map->getLayer($glayer->getLayerIdx());
        }
        
        $classList = array();
        foreach ($glayerList as $glayer) {
            $legLayer = $this->map->getLayer($glayer->getLayerIdx());
            $legLayerName = $legLayer->name;
            $legLayerType = $legLayer->type;
            $legIconPath = $legLayer->getMetadata("LEGENDICON");
            $skipLegend = $glayer->getSkipLegend();
            $numClassesLay = count($glayer->getClasses());
            
            if ($this->checkGroupVisibility) {
                $ret = PMCommon::checkScale($this->map, $legLayer, $this->scale, $this->mapExt); 
                if (! $ret ) { 
                    continue;
                } else {
                    if ($ret === 1) {
                        $dynamicClasses = False;
                    } else {
                        $dynamicClasses = $ret;
                    }   
                }
            }
                                        
            // All layers but RASTER layers WITHOUT class definitions
            if ((($legLayer->type < 3 && $skipLegend < 1) || $numClassesLay > 0 || $legIconPath) && $skipLegend != 2) {
                $classes = $glayer->getClasses();
                foreach ($classes as $clno=>$cl) {
                    // skip class if dynamic legend is set and class not in visible map extent 
                    if ($dynamicClasses) {   /// && $legLayerType < 3) {   // probably check for layer type not needed
                        if ( !in_array($clno, $dynamicClasses)) {
                            continue;
                        }
                    }
                    
                    $legIconPath = $legLayer->getClass($clno)->keyimage;
                    // class->keyimage --> generate icon too
                    if (isset($_SESSION['legendKeyimageRewrite']) && $_SESSION['legendKeyimageRewrite'] == 1) {
	                	$keyimage = dirname($_SESSION['PM_MAP_FILE']) . '/' . $legIconPath;
	                    if (file_exists($keyimage)) {
	                    	$legIconPath = false;
	                    }
                    }
                    $iconUrl = $legIconPath ? $legIconPath : $this->legPath.$legLayerName.'_i'.$clno.'.'.$this->imgExt;
                    
                    $legendClass['name'] = $cl;
                    $legendClass['iconUrl'] = $iconUrl;
                    $classList[] = $legendClass;
                }
            }
        }
        //pm_logDebug(3, $classList, "P.MAPPER-DEBUG: layerview.php/getGroup()-> classList");
        $legendGroup['classList'] = $classList;
        
        return $legendGroup;
    }
    


   /**
    * Check if group has visible layer at current scale
    * @param object $map
    * @param object $grp
    * @param int $scale
    */
    public function checkGroup($map, $grp, $scale)
    {
        $printGroup = 0;
        $glayerList = $grp->getLayers();
		if (is_array($glayerList)) {
	        foreach ($glayerList as $glayer) {
	            $tocLayer = $map->getLayer($glayer->getLayerIdx());
	            if ((PMCommon::checkScale($map, $tocLayer, $scale) == 1) && $tocLayer->type != 5) {
	                $printGroup = 1;
	                //pm_logDebug(3, "Layer: $tocLayer->name; scale: $scale; PrintGrp: $printGroup");
	            }
	        }
		}
        return $printGroup;
    } 
    
   /**
    * set map extent variable used for class list
    * @param object $mapExt map extent
    */
    public function setMapExt($mapExt)
    {
        $this->mapExt = $mapExt;
    }
    

}





?>