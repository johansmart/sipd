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


require_once(str_replace('\\', '/', realpath(dirname(__FILE__) . "/../../incphp/map/dynlayer.php")));

class DynLayerTxt extends DynLayer
{
     
    public function __construct($map)
    {
        parent::__construct($map);
        $this->layerSrcType = "TXT";
    }

    
    /**
     * Sample implementation to read new layers from text files with map file syntax
     */
    protected function getLayerList()
    {
        $layerList = array();
        $sampleLayer1Txt = file_get_contents("sample1.lyr");
        //error_log($sampleLayer1Txt);
        $layerList["dynsample_cities100000eu"] = array("layerDefinition"=>$sampleLayer1Txt);
        return $layerList;
    }
    
    
    protected function postprocessDynLayers()
    {
        //$sessionMapFile = str_replace('\\', '/', $this->map->web->imagepath) . session_id() . ".map";
        //error_log($sessionMapFile);
        //$this->map->save($sessionMapFile, 1);
        //$_SESSION['PM_MAP_FILE'] = $sessionMapFile;
        //$this->map = ms_newMapObj($sessionMapFile);
    }
    
    /**
     * creates dynamic layer based on text definition (map file syntax)
     * layer is then added to map object
     */
    protected function createDynLayer($layerName, $layerString)
    {
        //error_log($layerString);
        $dynLayer = ms_newLayerObj($this->map);
        $dynLayer->updateFromString($layerString);

        $this->setLayerParameters($dynLayer);
    }
    
    
    /**
     * Method to set additional layer parameters
     */
    protected function setLayerParameters($dynLayer)
    {
        // Move layer to configured index
        $newLayerCurrentIdx = $dynLayer->index;
        $newLayerTargetIdx = $this->config['layeridx'];
        $newDrawingOrder = $this->getNewDrawingOrder($this->map->getLayersDrawingOrder(), $newLayerCurrentIdx, $newLayerTargetIdx);
        $this->map->setLayersDrawingOrder($newDrawingOrder);
    }
    

}



?>