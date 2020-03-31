<?php

/******************************************************************************
 *
 * Purpose: class for TOC and legend creation
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
require_once("toc.php");

class Legend extends TOC
{
    var $catStyle;
    var $grpStyle;
       
    
    function __construct($map)
    {
        TOC::__construct($map);
    }
    
    
    /**
     * Create HTML output for groups
     */
    public function writeGroups($groupList=false, $mapExt=null)
    {
        $layerView = new LayerView($this->map, true, true);
        if ($mapExt) {
            $layerView->setMapExt($mapExt);
        }
        //$groupList = $layerView->getGroupList();
        $categoryList = $layerView->getCategoryList();
        //pm_logDebug(3, $categoryList, "writeGroups()");
        
        $html = "<ul>";
        foreach ($categoryList as $cat) {
            $groupList = $cat['groups'];
            if (count($groupList) > 0) {
                foreach ($groupList as $grp){
                    $grpName = $grp['name'];
                    $grpDescr = $grp['description'];
                    $classList = $grp['classList'];
                    $numcls = count($classList);
                    if ($numcls > 0) {
                        $html .= "<li class=\"tocgrp\">";
                        $html .= "<span class=\"pm-leg-grp-title\">$grpDescr</span>";
                        // Add classes
                        $html .= $this->writeClasses($grpName, $classList);
                    	$html .= "</li>";
                    }
                }
            }
        }
        $html .= "</ul>";
        
        //error_log($html);
        return $html;
    }
  

}





?>