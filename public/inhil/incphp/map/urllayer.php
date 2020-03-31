<?php

/******************************************************************************
 *
 * Purpose: add dynamic custom layers to map object
 * Author:  Armin Burger
 *
 ******************************************************************************
 *
 * Copyright (c) 2003-2006 Armin Burger
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


class UrlLayer
{

    public function __construct($map, $mapImg=false)
    {
        $this->map = $map;
        $this->mapImg = $mapImg;
        
        $this->url_createLayer();
    
    
    }
    
    private function url_createLayer()
    {
        if (!is_file($_SESSION['PM_TPL_MAP_FILE'])) {
            error_log("P.MAPPER ERROR: cannot find template map. Check INI settings for 'tplMapFile'");
            return false;
        }
        $tplMap = ms_newMapObj($_SESSION['PM_TPL_MAP_FILE']);
        $poiLayer = $tplMap->getLayerByName("poi");
        
        $txtLayer = ms_newLayerObj($this->map, $poiLayer);
        $txtLayer->set("name", "url_txtlayer");
        $txtLayer->set("type", 0);
        $txtLayer->set("status", MS_ON);
        
        $url_points = $_SESSION['url_points'];
        
        foreach ($url_points as $upnt) {
            // Create line, add xp point, create shape and add line and text, add shape to layer
            //$pointList = explode(",", $f);
            $px  = $upnt[0];
            $py  = $upnt[1];
            $txt = $upnt[2];
            
            $newLine = ms_newLineObj();
            $newLine->addXY($px, $py);
            
            $newShape = ms_newShapeObj(0);
            $newShape->add($newLine);
            $newShape->set("text", $txt);
            $txtLayer->addFeature($newShape);
        }
    }
}



?>
