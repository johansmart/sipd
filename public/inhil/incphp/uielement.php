<?php

/******************************************************************************
 *
 * Purpose: create User Interface HTML elelemnts 
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
 * UI elements to be inserted into main doc
 */
class UiElement
{

   /**
    * Map zone
    */
    public static function mapZone()
    {
        $html = "<div id=\"map\" class=\"baselayout\">
                <!-- MAIN MAP -->
                <div id=\"mapimgLayer\">
                        <img id=\"mapImg\" src=\"images/pixel.gif\"  style=\"overflow:hidden;\" alt=\"\" />
                </div>
                <div id=\"measureLayer\" class=\"measureLayer\"></div>
                <div id=\"measureLayerTmp\" class=\"measureLayer\"></div>
                <div id=\"zoombox\" class=\"zoombox\"></div>
                <div id=\"helpMessage\"></div>
                <div id=\"scalebar\"></div>
                <div id=\"iqueryContainer\"></div>
                <div id=\"loading\"><img id=\"loadingimg\" src=\"images/loading.gif\" alt=\"loading\" /></div>
            </div>
        ";
        return $html;
    }


   /**
    * Toolbar
    */
    public static function toolBar($buttons, $toolbarTheme="default", $toolBarOrientation="v", $toolbarImgType="gif", $cellspacing="4")
    {
        $html  = "<div id=\"toolBar\" class=\"pm-toolframe\">";
        $html .= "<table class=\"pm-toolbar\" border=\"0\" cellspacing=\"$cellspacing\" cellpadding=\"0\">\n";
        $html .= ($toolBarOrientation == "v" ? "" : "<tr>");

        foreach ($buttons as $b => $ba) {
            $html .= ($toolBarOrientation == "v" ? "<tr>" : "");

            if (preg_match("/^space/i", $b)) {
                $html .= "<td class=\"pm-tsepspace\" style=" . ($toolBarOrientation == "v" ? "height:" : "width:") . $ba . "px\"> </td> ";
                
            } elseif (preg_match("/^separator/i", $b)) {
                $iewa = ($_SESSION['userAgent'] == "ie" ? "<img alt=\"separator\" src=\"images/blank.gif\" />" : "");
                if ($toolBarOrientation == "v") {
                    $html .= "<td class=\"pm-tsepv\">$iewa</td> ";
                } else {
                    $html .= "<td class=\"pm-tseph\">$iewa</td> ";
                } 

            } else {
                $html .= "<td class=\"pm-toolbar-td\" id=\"tb_$b\"  " . 
                        //($ba[1] == "0" ?  "onmousedown=\"setTbTDButton('$b');domouseclick('$b')\"" : "onmousedown=\"TbDownUp('$b','d')\" onmouseup=\"TbDownUp('$b','u')\"") .
                        ($ba[1] == "0" ?  "onmousedown=\"setTbTDButton('$b');\"" : "onmousedown=\"TbDownUp('$b','d')\" onmouseup=\"TbDownUp('$b','u')\"") .
                        " onclick=\"" . ($ba[1] == "0" ? "domouseclick('$b')" : "$ba[1]()") .  "\">" .
                        "<img id=\"img_$b\"  src=\"images/buttons/$toolbarTheme/$b"."_off.$toolbarImgType\" title=\"$ba[0]\" alt=\"$ba[0]\"  /></td>" ;
            }

            $html .= ($toolBarOrientation == "v" ? "</tr> \n" : "\n");
        }
        $html .= ($toolBarOrientation == "v" ? "" : "</tr> \n");
        $html .= "</table>";
        $html .= "</div>";
        
        return $html;
    }
    
   /**
    * TOC and Legend container
    */
    public static function toolMenu($menu, $menuid, $menuname)
    {    
        $html  = "<div id=\"tool$menuid\">";
        $html .= "<a id=\"pm_". $menuid ."_start\" class=\"pm-menu-button\"   onclick=\"pmMenu_toggle('pm_$menuid');\">" . _p($menuname) . "<img src=\"images/menudown.gif\" alt=\"\" /></a>\n";
        $html .= "<ul id=\"pm_" . $menuid . "\" class=\"pm-menu\" >";
        foreach ($menu as $m => $ma) {
            $html .= "<li id=\"pmenu_" . $ma[1] . "\">" . $ma[0] . "</li>\n";      
        }
        $html .= "</ul>";
        $html .= "</div>";
        
        return $html;  
    }
    
    
    /**
    * Tool links
    */
    public static function toolLinks($menu)
    {    
        $html  = "<div id=\"toolLinkContainer\"><ul class=\"pm-tool-links\">";
        foreach ($menu as $m => $ma) {
            $html .= "<li>";
            $html .= "<a id=\"plink_$m\" href=\"javascript:{$ma[1]}()\">\n";
            $html .= "<img style=\"background:transparent url(images/menus/{$ma[2]}) no-repeat;height:16px;width:16px\" src=\"images/transparent.png\" alt=\"$m\" />";
            $html .= "<span>{$ma[0]}</span></a></li>\n";
        }
        $html .= "</ul></div>";
        
        return $html;  
    }
    
    
   /**
    * TOC and Legend container
    */
    public static function tocContainer($userAgent)
    {
        $html = "<div id=\"tocContainer\">
              <form id=\"layerform\" method=\"get\" action=\"\">    
                <div id=\"toc\"       class=\"TOC\" style=\"" . ($userAgent == "mozilla" ? "height:100%" : "height:auto") ."; \"></div>
                <div id=\"toclegend\" class=\"TOC\" style=\"" . ($userAgent == "mozilla" ? "height:100%" : "height:auto") . "; display:none;\"></div>
              </form>
            </div>
        ";
        return $html;
    }
    
    
    /**
     * Create tabs for TOC/Legend
     */
    public static function tocTabs($tablist, $enable=false)
    {
        $html = "";
        if ($_SESSION['layerAutoRefresh'] == 0) {
            $html .= "<div id=\"autoRefreshButton\"></div>";
        }
        if ($_SESSION['legendStyle'] == "swap" || $enable) { 
            $html .= "  <div id=\"tocTabs\">\n       <ul class=\"tocTabs\">\n";
            foreach($tablist as $k => $v) {
                $html .= "         <li><a href=\"javascript:" . $v[1] . "()\"  id=\"tab_$k\">" . $v[0] . "</a></li>\n";                
            }
            $html .= "       </ul> \n     </div>\n";
        } else {
            $html .= "";
        }
        return $html;
    }
    
    
   /**
    * Reference map
    */
    public static function refMap($refH, $refW, $refImg, $refH, $refW)
    {
        $html ="<div id=\"refmap\" class=\"refmap\" style=\"width:{$refW}px; height:{$refH}px\" >
                <img id=\"refMapImg\" src=\"images/$refImg\" width=\"$refW\"  height=\"$refH\"  alt=\"\" />
                <div id=\"refsliderbox\" class=\"sliderbox\"></div>
                <div id=\"refbox\" class=\"refbox\"></div>
                <div id=\"refcross\" class=\"refcross\"><img id=\"refcrossimg\" src=\"images/refcross.gif\"  alt=\"\" /> </div>
                <div id=\"refboxCorner\"></div>
            </div>
        ";
        return $html;
    }
    
   /**
    * Search form 
    */
    public static function searchContainer($style)
    {
        $html  = "<div id=\"searchContainer\">";
        $html .= "<form id=\"searchForm\" action=\"blank.html\" onsubmit=\"PM.Query.submitSearch()\" onkeypress=\"return PM.Query.disableEnterKey(event)\">";
        $html .= "<table width=\"100%\" class=\"pm-searchcont pm-toolframe\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">";
        $html .= "<tr>";  
        $html .= "<td id=\"searchoptions\" class=\"pm-searchoptions\" style=\"padding:0px 8px\"></td>";  
        if ($style == "block") $html .= "</tr><tr>";    
        $html .= "<td id=\"searchitems\" class=\"pm_search_$style\"></td>";
        $html .= "</tr>";  
        $html .= "</table>";
        $html .= "</form>";
        $html .= "</div>";
        
        return $html;
    }
    
   /**
    * Scaleform with scale options
    */
    public static function scaleForm()
    {
        $html = "<div id=\"scaleArea\" class=\"TOOLFRAME\">
                <form id=\"scaleform\"  action=\"javascript:PM.Map.zoom2scale($('#scaleinput').val());javascript:PM.Form.scaleMouseOut(true)\">
                    <div id=\"scaleArea2\" class=\"rowdiv\" >
                        <div class=\"celldiv\">" . _p("Scale") . " 1:  </div>
                        <div class=\"celldiv\">
                            <div> <input type=\"text\" id=\"scaleinput\" name=\"scale\" size=\"9\" value=\"\" onkeyup=\"PM.Form.scaleMouseOut()\" onblur=\"PM.Form.scaleMouseOut(true)\" onclick=\"PM.Form.initScaleSelect()\" /></div>
                            <div id=\"scaleSuggest\" onmouseover=\"PM.Form.setScaleMO()\" onmouseout=\"setTimeout('PM.Form.scaleMouseOut()', 1000)\"></div>
                        </div>
                    </div>
                </form>
            </div>
        ";
        return $html;
    }
    
    
   /**
    * Slider for zooming
    */
    public static function zoomSlider()
    {
        $html = "<div id=\"sliderArea\" class=\"sliderAreaOut\" >
                <div id=\"sliderTool\">
                    <div class=\"slider-top\"><img id=\"sl_imgplus\" src=\"images/zoomplus.gif\" alt=\"\" title=\"" . _p("Zoom in") . "\"  onclick=\"PM.Map.zoompoint(2, '');\"/></div>
                    <div id=\"zslider\"></div>
                    <div class=\"slider-bottom\"><img id=\"sl_imgminus\" src=\"images/zoomminus.gif\" alt=\"\" title=\"" . _p("Zoom out") . "\"  onclick=\"PM.Map.zoompoint(-2, '');\"/></div>
                </div>
            </div>
        ";
        return $html;
    }
    
    
   /**
    * Dialog Container for dynwin
    
    public static function dialogContainer()
    {
        $html = "<div style=\"visibility:hidden\"><img id=\"pmMapRefreshImg\" src=\"images/pixel.gif\" alt=\"\" /></div>
            <div id=\"pmDlgContainer\"   class=\"jqmDialog hide\"></div>
            <div id=\"pmQueryContainer\" class=\"jqmDialog hide\"></div>
        ";
        return $html;
    }*/
    
    
   /**
    * Header in ui-north
    */
    public static function pmHeader()
    {
        $pmLogoUrl = array_key_exists('pmLogoUrl', $_SESSION) ? $_SESSION['pmLogoUrl'] : "http://www.pmapper.net";
        $pmLogoTitle = array_key_exists('pmLogoTitle', $_SESSION) ? $_SESSION['pmLogoTitle'] : "p.mapper homepage";
        $pmLogoSrc = array_key_exists('pmLogoSrc', $_SESSION) ? $_SESSION['pmLogoSrc'] : "images/logos/logo-black.png";
        $pmVersion = array_key_exists('version', $_SESSION) ? ", v" . $_SESSION['version'] : "";
        $pmHeading = array_key_exists('pmHeading', $_SESSION) ? $_SESSION['pmHeading'] : "<a href=\"http://mapserver.gis.umn.edu\" id=\"mshref_1\" title=\"UMN MapServer homepage\" onclick=\"this.target = '_new';\">MapServer</a>&nbsp; 
                            <a href=\"http://www.dmsolutions.ca\" id=\"dmsol_href\" title=\"DM Solutions homepage\" onclick=\"this.target = '_new';\">PHP/MapScript</a>&nbsp; 
                            Framework$pmVersion";
        
        $html = "<div class=\"pm-header\"><div><a href=\"$pmLogoUrl\" 
                    title=\"$pmLogoTitle\" onclick=\"this.target = '_blank';\">
                    <img class=\"pm-logo-img\" src=\"$pmLogoSrc\" alt=\"logo\" /></a>    
                    </div>
                    <div class=\"HEADING1\">$pmHeading</div>
                </div>
        ";
        return $html;
    }
    
   /**
    * Footer in ui-south
    */
    public static function pmFooter()
    {
        $html = "<div class=\"pm-footer\">
                <div style=\"float:right;\">
                    <a href=\"http://validator.w3.org/check?uri=referer\"><img
                        src=\"images/logos/valid-xhtml10-small-blue.png\"
                        alt=\"XHTML 1.0 Strict\"  /></a>
                </div>
                <div style=\"float:right;\"><a href=\"http://mapserver.gis.umn.edu\" id=\"mapserver_href_2\" onclick=\"this.target = '_blank';\">
                    <img src=\"images/logos/mapserver-small.png\" title=\"UMN MapServer homepage\" alt=\"MapServer\" /></a>
                </div>
                <div style=\"float:right;\"><a href=\"http://www.pmapper.net\"  title=\"p.mapper homepage\" onclick=\"this.target = '_blank';\">
                    <img src=\"images/logos/pmapper.png\" title=\"p.mapper\" alt=\"p.mapper\" /></a></div>
            </div>
        ";
        return $html;
    }
    
   /**
    * Coordinates display
    */
    public static function displayCoordinates()
    {
        $html = "<div id=\"showcoords\" class=\"showcoords1\"><div id=\"xcoord\"></div><div id=\"ycoord\" ></div></div>";
        return $html;
    }
    
   /**
    * Mandatory form for handling update events
    */
    public static function addUpdateEventForm()
    {
        $html = "<form id=\"pm_updateEventForm\" action=\"\">
                    <p><input type=\"hidden\" id=\"pm_mapUpdateEvent\" value=\"\" /></p>
                </form>";
        return $html;
    }

}



?>
