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

require_once("layerview.php");

class TOC
{
    protected $map;         
    protected $categories;    
    protected $tocStyle;    
    protected $legendStyle;  
    protected $grouplist;      
    protected $icoW;        
    protected $icoH;        
    protected $scaleLayers;      
    protected $catWithCheckbox;
       
    
    /**
     * Constructor
     * @param object $map
     */
    public function __construct($map)
    {
        $this->map         = $map;
        $this->tocStyle    = $_SESSION["tocStyle"];
        $this->legendStyle = $_SESSION["legendStyle"];
        $this->icoW        = $_SESSION["icoW"];  // Width in pixels
        $this->icoH        = $_SESSION["icoH"];  // Height in pixels
        $this->scaleLayers = $_SESSION["scaleLayers"];
        $this->catWithCheckbox = $_SESSION['catWithCheckbox'];
  
    }
    
    /**
     * Create HTML output for categories
     * @return string
     */
    public function writeCategories()
    {
        $layerView = new LayerView($this->map, false);
        $categoryList = $layerView->getCategoryList();
        
        $html = "<ul>";
        foreach ($categoryList as $cat) {
            $name = $cat['name'];
            //$description = addslashes($cat['description']);
            $description = $cat['description'];
            $groups = $cat['groups'];
            if (count($groups) < 1) continue;
            
            $html .= "<li id=\"licat_$name\" class=\"toccat\">";
            if ($this->catWithCheckbox)
                $html .= "<input type=\"checkbox\" name=\"catscbx\" value=\"$name\" id=\"cinput_$name\" \" />";
            $html .= "<span class=\"vis cat-label\" id=\"spxg_$name\">$description</span>";
            // Add groups
            if (count($groups) > 0) {
                $html .= $this->writeGroups($groups);
            }
            $html .= "</li>";
        }
        $html .= "</ul>";
        
        return $html;
    }
    
    /**
     * Create HTML output for groups
     * @return string
     */
    public function writeGroups($groupList=false)
    {
        if ($groupList == false) {
            $layerView = new LayerView($this->map, false, false);
            $groupList = $layerView->getGroupList();
        }
        
        $html = "<ul>";
        foreach ($groupList as $grp){  
            $grpName = $grp['name'];
            //$grpDescr = addslashes($grp['description']);
            $grpDescr = $grp['description'];
            $classList = $grp['classList'];
            $numcls = count($classList);

            $html .= "<li id=\"ligrp_$grpName\" class=\"tocgrp\">";
            $html .= "<input type=\"checkbox\" name=\"groupscbx\" value=\"$grpName\" id=\"ginput_$grpName\"/>"; 
            $html .= "<span class=\"vis\" id=\"spxg_$grpName\"><span class=\"grp-title\">$grpDescr</span></span>";
            // Add classes
            if ($numcls > 0 && $this->legendStyle == "attached") {
                $html .= $this->writeClasses($grpName, $classList);
            }
            $html .= "</li>";
        }
        $html .= "</ul>";
        
        //error_log($html);
        return $html;
    }
      
    /**
     * Create HTML output for layer/group classes
     * @return string
     */  
    protected function writeClasses($grpName, $classList)
    {
        $html = "<ul>";
        foreach ($classList as $count=>$cls) {
            //$clsName = addslashes($cls['name']);
            $clsName = $cls['name'];
            $iconUrl = $cls['iconUrl'];                
            
            $html .= "<li class=\"toc-layer-classes\" style=\"background-image: url($iconUrl);\">";
            $html .= "<div>";
            $html .= " <div>&nbsp;</div>";
            $html .= "  <span class=\"vis\" id=\"spxg_$grpName$count\">$clsName</span>";
            $html .= "</div>";
            $html .= "</li>";
        }
        $html .= "</ul>";
        
        return $html;
    }
        
    /**
     * Return the style of the TOC
     * @return string
     */     
    public function getTocStyle()
    {
        return $this->tocStyle;
    }
        
  
    

}





?>