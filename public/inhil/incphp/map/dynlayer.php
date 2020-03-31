<?php
/******************************************************************************
 *
 * Purpose: initialization and creation of dynamic layers
 * Author:  Armin Burger
 *
 ******************************************************************************
 *
 * Copyright (c) 2003-2011 Armin Burger
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


class DynLayer
{
    public $map;
    protected $layerSrcType;
     
    public function __construct($map)
    {
        $this->map = $map;
    }

    
    /**
     * Initialize dynamic layers 
     * layer is then added to map object
     */
    public function initDynLayers($rewriteLegend=true)
    {
        if (!isset($_SESSION['dynLayers'])) {
            $_SESSION['dynLayers'] = array();
        }
        
        // Get layer definitions
        $layerList = $this->getLayerList();
        $_SESSION['dynLayers'][$this->layerSrcType] = $layerList;
        
        // Create layers once in order to add it to various session variables
        $layerNames = $this->createDynLayers();
        $this->layerNames = $layerNames;
        
        $this->postProcessDynLayers();
        
        $this->registerDynLayers($layerNames, $rewriteLegend);
        
    }
    
    
    /**
     * Register added dynamic layers in p.mapper group/layer objects
     * is called from initDynLayers()
     */ 
    protected function registerDynLayers($layerNames, $rewriteLegend)
    {
        $allGroups = array_merge($_SESSION['allGroups'], $layerNames);
        $_SESSION['allGroups'] = array_unique($allGroups);
        
        if ($rewriteLegend) {
            $initMap = new Init_map($this->map, false, false, $_SESSION['gLanguage']);
            $initMap->createLegendList();
        }
        $iG = new Init_groups($this->map, $allGroups, $_SESSION['language'], false); 
        $iG->createGroupList(); 
    }
    
    
    /**
     * Required standard method for creating dynamic layers
     * is called in globals.php
     */ 
    public function createDynLayers()
    {
        $layerNames = array();
        $layerList = $_SESSION['dynLayers'][$this->layerSrcType];
        foreach ($layerList as $layerName=>$dynLayer) {
            $layerNames[] = $layerName;
            $this->createDynLayer($layerName, $dynLayer['layerDefinition']);
        }
        return $layerNames;
    }
    
    /**
     * Abstract method to get lisy of dyn layers
     * Required!
     */
    protected function getLayerList() {}
    
    
    /**
     * create dynamic layer based on definition in $layerString
     * Required!
     */
    protected function createDynLayer($layerString) {}
    
    
    /**
     * Abstract method for some post-processing of dynamic layers
     */
    protected function postProcessDynLayers() {}
    
    
    /**
     * Method to set additional layer parameters
     */
    protected function setLayerParameters($dynLayer) {}
    
    
    /**
     * Set layer drawing order to set dynlayer to pre-defined index
     */
    protected function getNewDrawingOrder($a, $ci, $ti)
    {
        $alen = count($a);
        $na = array();
        for ($i=0; $i<$alen; $i++) {
            if ($i<$ti) {
                $na[] = $i;
            } elseif ($i==$ti) {
                $na[] = $ci;
            } elseif ($i>$ti) {
                $na[] = $i-1;
            }
        }
        return $na;
    }

}



?>