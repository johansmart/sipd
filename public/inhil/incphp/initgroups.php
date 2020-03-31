<?php

/******************************************************************************
 *
 * Purpose: initialize groups and glayers and save definitions in session
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


require_once("group.php");

class Init_groups
{
    var $map;
    var $allGroups;
    var $gLanguage;
    var $map2unicode;
    var $existsXYLayer = 0;
    var $ini;
    
    function Init_groups($map, $allGroups, $gLanguage, $ini)
    {
        $this->map = $map;
        $this->allGroups = $allGroups;
        $this->gLanguage = $gLanguage;
        $this->ini = $ini;
        $this->map2unicode = is_array($ini) ? $ini['locale']['map2unicode'] : $ini;
    }
    
    
    protected function defineLists()
    {
        $initGroups = array();
        
        $groupOrder = $this->allGroups;
        
        $mapGroupsNames = $this->map->getAllGroupNames();
        $mapLayers = $this->map->getAllLayerNames();
        //printDebug($mapLayers);
        
        // Create array for groups in map file
        foreach ($mapGroupsNames as $mgn) {
            $mapGroups[$mgn] = $this->map->getLayersIndexByGroup($mgn);
        }
        
        //Add layers as groups if not assigned to any group
        foreach($mapLayers as $l) {
            $layer = $this->map->getLayerByName($l);
            $layIdx = $layer->index;
            $layGrp = $layer->group;
            if ($layGrp == "") {
                $mapGroups[$l] = array($layIdx);
            }
        }
        
        // Sort group array according to order of $groupOrder
        foreach($groupOrder as $g) {
            if (isset($mapGroups[$g]) && count($mapGroups[$g]) > 0) {
                $initGroups[$g] =  $mapGroups[$g];
            } else {
                pm_logDebug(0, "Could not create group '$g' defined in groupOrder in '{$_SESSION['PM_BASECONFIG_DIR']}/config_{$_SESSION['config']}.xml'. Check if name is correct.");
            }
        }
        
        return $initGroups;
    }

    
   /**
    * Initialize GROUPS
    * set group and layer properties
    *
    */
    public function createGroupList()
    {
        $initGroups = $this->defineLists();
        $grouplist = array();
        foreach ($initGroups as $grpName=>$layerList) {
            $grouplist[$grpName] = $this->createGroup($grpName, $layerList);
        }
        
        // Save everything in session
        $_SESSION["existsXYLayer"] = $this->existsXYLayer;
        $_SESSION["grouplist"] = $grouplist;
            
    }
    
    
    public function createGroup($grpName, $layerList)
    {
        $group = new GROUP($grpName);
        $i = 1;
    
        // Loop through LAYERS of current group
        foreach ($layerList as $layIdx) {
            // Get layer info from map file
            $mapLay = $this->map->getLayer($layIdx);
            $mapLayName = $mapLay->name;
            $mapLayType = $mapLay->type;
            $mapLayConnType = $mapLay->connectiontype;
            //error_log("$mapLayName - $mapLayConnType");
    
            // Write layer properties to glayer object
            $glayer = new GLAYER($mapLayName);
            $glayer->setLayerIdx($layIdx);
            $glayer->setLayerType($mapLayType);
    
            // Add result field list
            if ($mapLayType <= 4 && $mapLayConnType != MS_WMS) {    // result fields only for queryable layers point (0), line (1), polygon (2), annotation (4)
                $selFields0 = $this->_initResultFields($this->map, $mapLay, $mapLayType);
                
                // Trim spaces
                if (is_array($selFields0)) {
                    if (count($selFields0) > 0) {
                        //pm_logDebug(3, $selFields0, "P.MAPPER-DEBUG: initgroups.php/createGroup()->selFields0");
                        $selFields = array();
                        foreach ($selFields0 as $sf0) {
                            // If field name starts with '&' then translate
                            $sf = (substr(trim($sf0), 0, 1) == '&' ? _p(trim($sf0)) : trim($sf0));
                            $selFields[] = $sf;
                        }
                        $glayer->setResFields($selFields);
                    }
                }
            }
            
            // add to categories array if defined in map file
            if ($category = $this->getLayerMetaTag($mapLay, "CATEGORY")) {
                $this->_initCategories($mapLayName, $category);
            }
            
            // Add hyperlink fields
            if ($this->_getHyperFieldList($mapLay)) {
                $glayer->setHyperFields($this->_getHyperFieldList($mapLay));
            }
            
            // Add JOIN properties if defined
            if ($this->_getJoinProperties($mapLay)) {
                $glayer->setTableJoin($this->_getJoinProperties($mapLay));
            }
            
            // Add classes
            $numclasses = $mapLay->numclasses;
            $classes = array();
            for ($cl=0; $cl < $numclasses; $cl++) {
                $class = $mapLay->getClass($cl);
                $className = $this->mapStringEncode($class->name);
                if (strlen($className) > 0) {
                    $classname = _p(trim($className));
                    $classes[] = $classname; //str_replace("'", "\\'", $classname);
                }
            }
            $glayer->setClasses($classes);
    
            // Check/Set labelitems if defined
            if ($mapLay->labelitem) {
                $labelItem = $mapLay->labelitem{0} == "@" ? _p(substr($mapLay->labelitem, 1)) : $mapLay->labelitem;
                $glayer->setLabelItem($labelItem);
            }
            
            // Check/Set layer transparency (opacity)
            if (floatval($_SESSION['MS_VERSION']) >= 5) {
                $glayer->setOpacity($mapLay->opacity);
            } else {
                $glayer->setOpacity($mapLay->transparency);
            }
                            
            
            // Check if layer is XY layer
            if ($XYLayerPropStr = $this->getLayerMetaTag($mapLay, "XYLAYER_PROPERTIES")) {
                $glayer->setXYLayerAttribute();
                $XYLayerPropList = $this->_getXYLayerPropList($XYLayerPropStr);
                $glayer->setXYLayerProperties($XYLayerPropList);
                pm_logDebug(3, $XYLayerPropList, "P.MAPPER-DEBUG: initgroups.php/_createGroups() - XYLayerProperties for layer $mapLayName");
                
                // Set in sessionid that XYLayer exists
                $this->existsXYLayer = 1;
            }
            
            //Check for skipLegend
            // 1: only for TOC_TREE, 2: always skip legend
            $skipLegend = $this->getLayerMetaTag($mapLay, "SKIP_LEGEND");
            $glayer->setSkipLegend($skipLegend);
            
            // Layer Encoding
            $glayer->setLayerEncoding($this->getLayerMetaTag($mapLay, "LAYER_ENCODING"));

            // DB properties
            if ($mapLayConnType == 6) {
                $glayer->setLayerDbProperties($this->pgLayerParseData($mapLay));
            }
            
            if ($mapLayConnType == 8) {
                $glayer->setLayerDbProperties($this->oraGetDbProperties($mapLay));
            }
            
            // now add layer to group
            $group->addLayer($glayer);
    
            // set group description and result headers, process only for 1st layer of group
            if ($i == 1) {
                // Set group description
                $description  = $this->_initDescription($mapLay);
                $group->setDescription($description);
    
                // Set result group headers
                if ($mapLayType <= 4) {
                    $selHeadersList  = $this->_initResultHeaders($this->map, $mapLay, $mapLayType, $this->gLanguage);
                    $group->setResHeaders($selHeadersList[0]);
                    $group->setResStdHeaders($selHeadersList[1]);
                }
                $i = 0;
            }
    
        }
            
        return $group;
    }
    
    
    /**
     * init fields used for query result
     */
    function _initResultFields($map, $mapLay, $mapLayType) {
        if ($metaString = $this->getLayerMetaTag($mapLay, "RESULT_FIELDS")) {
            $metaList = array();
            $metaList0 = explode(",", $metaString);
            foreach ($metaList0 as $i) {
                $i = trim($i);
                // Check for locale-specific field
                if ($i{0} == "@") {
                    // From hash
                    if (strpos($i, ":")) {
                        $fldDict = array();
                        $flocaleList = explode("@", $i);
                        foreach ($flocaleList as $fldDef) {
                            $fo = explode(":", $fldDef);
                            if (isset($fo[1])) {
                                $fldDict[$fo[0]] = $fo[1];
                            }
                        }
                        if (array_key_exists($this->gLanguage, $fldDict)) {
                            $resultField = $fldDict[$this->gLanguage];
                        } else {
                            $resultField = $fldDict['default'];
                        }
                    // via translation from language_xy.php locale file
                    } else {
                        $resultField = _p(substr($i, 1));
                    }
                // Otherwise take normal one
                } else {
                    $resultField = $i;
                }
                $metaList[] = $resultField;
            }
        } else {
            $hasTemplate = 0;
            if ($mapLay->template) $hasTemplate = 1;
            $numclasses = $mapLay->numclasses;
            for ($cl=0; $cl < $numclasses; $cl++) {
                $class = $mapLay->getClass($cl);
                $classTemplate = $class->template;
                if ($class->template) $hasTemplate = 1;
            }
            if ($mapLayType != 3 && $hasTemplate) {
                $mapLay->open();
                $metaList = $mapLay->getItems();
                $mapLay->close();
                //pm_logDebug(3, $metaList, "P.MAPPER-DEBUG: initgroups.php/createGroup()->metaList");
            } else {
                $metaList = array();
            }
        }
        return $metaList;
    }
    
    /**
     * init headers used for query result
     */
    function _initResultHeaders($map, $mapLay, $mapLayType) {
        if ($metaString = $this->getLayerMetaTag($mapLay, "RESULT_HEADERS")) {
            $metaList = array();
            $metaListStd = array();
            $metaList0 = explode(",", $metaString);
            foreach ($metaList0 as $m) {
                $metaList[] = _p(trim($this->mapStringEncode($m)));
                $metaListStd[] = trim($this->mapStringEncode($m));
            }
            
        } else {
            if ($mapLayType != 3) {
                $mapLay->open();
                $metaList = $mapLay->getItems();
                $mapLay->close();
                $metaListStd = $metaList;
            } else {
                $metaList = array();
                $metaListStd = array();
            }
        }
        return array($metaList, $metaListStd);
    }
    
    
    /**
     * init layer description
     */
    function _initDescription($mapLay) {
        if ($metaString = $this->getLayerMetaTag($mapLay, "DESCRIPTION")) {
            $descriptionTag = _p($this->mapStringEncode($metaString));
        } else {
            if ($mapLay->group) {
                $descriptionTag = _p($this->mapStringEncode($mapLay->group));
            } else {
                $descriptionTag = _p($this->mapStringEncode($mapLay->name));
            }
        }
        return preg_replace(array("/\\\/", "/\|/"), array("", ""), trim($descriptionTag));  // ESCAPE APOSTROPHES (SINGLE QUOTES) IN NAME WITH BACKSLASH
    }
    
    /**
     * init categories
     */
    function _initCategories($mapLayName, $category) {
        if (preg_match("/:/", $category)) {
            $cL = explode(":", $category);
            $category = $cL[0];
            $categoryDescr = $cL[1];
        } else {
            $categoryDescr = $category;
        }
        
        $catLayerList = $_SESSION['categories'][$category];
        if (!is_array($catLayerList))
            $_SESSION['categories'][$category] = array("description"=>$categoryDescr, "groups"=>array());
           
        if (!in_array($mapLayName, $_SESSION['categories'][$category]))
            $_SESSION['categories'][$category]['groups'][] = $mapLayName;
    }
    
    
   /**
    * Check for HYPERLINK FIELDS
    * Check if hyperlink fields have been declared in map file
    */
    function _getHyperFieldList($glayer)
    {
    	$ret = NULL;
        // First split string into field arrays, then the chunks into field name and alias for link
        if ($hyperMeta = $this->getLayerMetaTag($glayer, "RESULT_HYPERLINK")) {
        	$hyperFieldsValues = array();
        	$hyperFieldsAlias = array();
        	
        	$hyperStr = preg_split('/,/', $hyperMeta);
            foreach ($hyperStr as $hs) {
                if (preg_match ('/\|\|/', $hs)) {
                    $hfa = preg_split('/\|\|/', $hs);
                    $hfa[0] = trim($hfa[0]);
                    $hfa[1] = trim($hfa[1]);
                    $hyperFieldsAlias[$hfa[0]] = _p($this->mapStringEncode($hfa[1]));
                    $hyperFieldsValues[] = $hfa[0];
                } else {
                	$hs = trim($hs);
                    $hyperFieldsValues[] = trim($hs);
                    $hyperFieldsAlias[$hs] = false;
                }
            }
            if ($hyperFieldsValues && $hyperFieldsAlias) {
            	$ret = array($hyperFieldsValues, $hyperFieldsAlias);
            }
        }
        
       	return $ret;
    }
    
    
   /**
    * Check for XY Layers
    */
    function _getXYLayerPropList($XLLayerMetaStr)
    {
        $XYLayerList = preg_split("/\|\|/", $XLLayerMetaStr);
        
        $XYLayerProperties["dsn"] = $XYLayerList[0];
        $XYLayerProperties["xyTable"] = $XYLayerList[1];
                
        $XYLayerFldList = preg_split("/,/", $XYLayerList[2]);
        $XYLayerProperties["x_fld"]        = $XYLayerFldList[0];
        $XYLayerProperties["y_fld"]        = $XYLayerFldList[1];
        $XYLayerProperties["classidx_fld"] = $XYLayerFldList[2];
        $XYLayerProperties["oid_fld"]      = $XYLayerFldList[3];
        
        $XYLayerProperties["noQuery"] = $XYLayerList[3];
        
        return $XYLayerProperties;
    }
    
    
    
   /**
    * Check for DB JOINS
    * Check if DB joins have been declared in map file
    */
    function _getJoinProperties($qLayer)
    {
        if ($joinStrMeta = $this->getLayerMetaTag($qLayer, "RESULT_JOIN")) {
            $joinList = preg_split("/\|\|/", $joinStrMeta);
            
            $joinPropList["dsn"] = $joinList[0];
    
            // Join table properties
            $tableProp  = preg_split("/\@/", $joinList[1]);
            $joinPropList["fromTable"]     = $tableProp[0];
            $joinPropList["fromField"]     = $tableProp[1];
            $joinPropList["fromFieldType"] = $tableProp[2];
            $joinFieldStr = $tableProp[3];
            $joinPropList["joinFields"] = $joinFieldStr;
    
            // Field in Shapefile to join to
            $joinPropList["toField"] =  $joinList[2];
    
            // Join type: one-to-one (0) or one-to-many (1)
            $joinPropList["one2many"] = $joinList[3];
            
            return $joinPropList;
        } else {
            return false;
        }
    }
    
    /**
     * check for map file encoding and encode strings accordingly
     */
    function mapStringEncode($inString)
    {
        if ($this->map2unicode) {
            $mapfile_encoding = trim($this->map->getMetaData("MAPFILE_ENCODING"));
            if ($mapfile_encoding) {
                if ($mapfile_encoding != "UTF-8") {
                    $outString = iconv($mapfile_encoding, "UTF-8", $inString);
                } else {
                    $outString = $inString;
                }
            } else {
                $outString = utf8_encode($inString);
            }
        } else {
            $outString = $inString;
        }
        return $outString;
    }
	
    /**
     * Try to get values from layer.ini.  setting
     * or from map file METADATA tags
     */
    private function getLayerMetaTag($layer, $tag)
    {
        $iniTagStr = "layer." . $layer->name . ".$tag";
        /*if ($this->ini == false) {
            return $_SESSION["layer_" . $layer->name . "_$tag"];
        } elseif (isset($this->ini[$iniTagStr])) {*/
        if (isset($this->ini[$iniTagStr])) {
            $_SESSION["layer_" . $layer->name . "_$tag"] = $this->ini[$iniTagStr];
            return $this->ini[$iniTagStr];
        } elseif ($metaData = $layer->getMetaData($tag)) {
            return $metaData;
        } else {
            return false;
        }
    }
    
    
    /**
     * Parse DATA tag from PostGIS layer
     */
    private function pgLayerParseData($layer)
    {
        $pg_data = $layer->data; //"the_geom from images";
        $dl = preg_split('/ from /i', $pg_data);
        $data_list['geocol'] = trim($dl[0]);
        $flds = trim($dl[1]);
        
        if (substr($flds, 0, 1) == '(') {
            // is of type "the_geom from (select the_geom, oid, from mytable) AS foo USING UNIQUE gid USING SRID=4258"
            $tabl = preg_split('/as ([a-z]|_|[A-Z]|[0-9])+ using unique /i', $dl[2]);
            $unique_list = preg_split('/[\s,]+/', $tabl[1]);
            
            $data_list['dbtable'] = $flds . " from " . trim($tabl[0]) . " as foo ";
            $data_list['unique_field'] = trim($unique_list[0]);
        } else {
            $tabl = preg_split('/using unique/i', $dl[1]);
            if (count($tabl) > 1) {
                // is of type "the_geom from mytable USING UNIQUE gid "
                $data_list['dbtable'] = trim($tabl[0]);
                //$data_list['unique_field'] = trim($tabl[1]);
                $unique_list = preg_split('/[\s]+/', trim($tabl[1]));
                $data_list['unique_field'] = trim($unique_list[0]);
            } else {
                // is of type "the_geom from mytable"
                $dbtable = trim($dl[1]);
                $data_list['dbtable'] = $dbtable;
                $data_list['unique_field'] = "oid";
                pm_logDebug(2,"P.MAPPER Warning: no UNIQUE field specified for PostGIS table '$dbtable'. Trying using OID field...");
            }
        }
        return $data_list;
    }
    
    /**
     * Return properties of OracleSpatial layer
     */
    private function oraGetDbProperties($layer)
    {
        
    }
    


} // class

?>