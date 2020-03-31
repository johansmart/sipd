<?php

/******************************************************************************
 *
 * Purpose: classes for internal groups/layers to support grouping of layers
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


/*
 * CLASS FOR GROUPS
 *******************************************************************/
class GROUP
{
	public $groupName;
	public $description;
	public $layerList;
	public $selHeaders;
	public $selStdHeaders;
		
    public function __construct($groupName)
    {
        $this->groupName = $groupName;
        $this->selHeaders = array();
        $this->layerList = array();	
		$this->description = '';
		$this->selStdHeaders = '';
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function addLayer($layer)
    {
        array_push($this->layerList, $layer);
    }

    public function setResHeaders($selHeaders)
    {
        $this->selHeaders = $selHeaders;
    }
    
    public function setResStdHeaders($selStdHeaders)
    {
        $this->selStdHeaders = $selStdHeaders;
    }

    public function printGroupName()
    {
        echo $this->groupName;
    }


    //*** RETURN FUNCTIONS ***//
    public function getGroupName()
    {
        return $this->groupName;
    }

    public function getDescription()
    {
        return $this->description;
    }
    
    public function getLayers()
    {
        return $this->layerList;
    }
    
    public function getResHeaders()
    {
        return $this->selHeaders;
    }
    
    public function getResStdHeaders()
    {
        return $this->selStdHeaders;
    }

}


/*
 * CLASS FOR GROUP LAYERS
 *******************************************************************/

class GLAYER
{
	public $glayerIdx = -1;
	public $glayerType = -1;
 	public $selFields = array();
 	public $hyperFields = array();
	public $joinList = NULL;
	public $classes = NULL;
	public $labelItem = NULL;
	public $isXYLayer = false;
	public $XYLayerProperties = NULL;
	public $skipLegend = NULL;
	public $glayerName = NULL;
	public $glayerOpacity = 100;
	public $layerEncoding = NULL;
	protected $layerDbProperties = NULL;
	
    public function __construct($glayerName)
    {
        $this->glayerName = $glayerName;
        $this->selFields = array();
        $this->hyperFields = array();
    }

    function setLayerIdx($glayerIdx)
    {
        $this->glayerIdx = $glayerIdx;
    }

    function setLayerType($glayerType)
    {
        $this->glayerType = $glayerType;
    }

    function setResFields($selFields)
    {
        $this->selFields = $selFields;
    }

    function setHyperFields($hyperFields)
    {
        $this->hyperFields = $hyperFields;
    }

    function setTableJoin($joinList)
    {
        $this->joinList = $joinList;
    }

    function setClasses($classes)
    {
        $this->classes = $classes;
    }
    
    function setLabelItem($labelItem)
    {
        $this->labelItem = $labelItem;
    }

    function setXYLayerAttribute()
    {
        $this->isXYLayer = 1;
    }
    
    function setXYLayerProperties($XYLayerProperties)
    {
        $this->XYLayerProperties = $XYLayerProperties;
    }
    
    function setSkipLegend($skipLegend)
    {
        $this->skipLegend = $skipLegend;
    }
    
    function setOpacity($opacity)
    {
        $this->glayerOpacity = $opacity;
    }
    
    function setLayerEncoding($encoding)
    {
        $this->layerEncoding = $encoding;
    }
    
    function setLayerDbProperties($layerDbProperties)
    {
        $this->layerDbProperties = $layerDbProperties;
    }
    

    //*** GLOBAL RETURN FUNCTIONS ***//

    function getLayerName()
    {
        return $this->glayerName;
    }

    function getLayerIdx()
    {
        return $this->glayerIdx;
    }

    function getLayerType()
    {
        return $this->glayerType;
    }

    function getResFields()
    {
        return $this->selFields;
    }

    function getHyperFields()
    {
        return $this->hyperFields;
    }

    function getTableJoin()
    {
        if (isset($this->joinList)) {
            return $this->joinList;
        } else {
            return false;   
        }
    }

    function getClasses()
    {
        return $this->classes;
    }
    
    function getLabelItem()
    {
        if (isset($this->labelItem)) {
            return $this->labelItem;
        } else {
            return false;   
        }
    }
    
    function checkForXYLayer()
    {
        return $this->isXYLayer;
    }
    
    function getXYLayerProperties()
    {
        if (isset($this->XYLayerProperties)) {
            return $this->XYLayerProperties;
        } else {
            return false;   
        }
    }
    
    function getSkipLegend()
    {
        return $this->skipLegend;
    }
    
    function getOpacity()
    {
        return $this->glayerOpacity;
    }
    
    function getLayerEncoding()
    {
        return $this->layerEncoding;
    }
    
    function getLayerDbProperties()
    {
        return $this->layerDbProperties;
    }
}



function getGLayerByName($gLayerName) 
{
    $grouplist = $_SESSION["grouplist"];
    foreach ($grouplist as $grp) {
        $glayerList = $grp->getLayers();
        foreach ($glayerList as $glayer) {
            if ($glayer->getLayerName() == $gLayerName) {
                return $glayer;
            }
        }
    }
}


?>
