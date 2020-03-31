<?php

/******************************************************************************
 *
 * Purpose: main class for queries (identify, select, search)
 * Author:  Armin Burger
 *
 ******************************************************************************
 *
 * Copyright (c) 2003-2007 Armin Burger
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

require_once (dirname(__FILE__) . "/squery.php");

class Query
{
	protected $query = NULL;
	protected $qStr = '';
	protected $resultindexes = NULL;
	protected $resulttileindexes = NULL;

    // ======================== PUBLIC FUNCTIONS =====================//
    public function __construct($map)
    {
        $this->map = $map;
        
        if (isset($_REQUEST["mode"])) {
            $this->mode = $_REQUEST["mode"];
        } else {
            $this->mode= "search";
        }
        
        if (isset($_REQUEST["imgxy"])) {
            $imgxy_str = $_REQUEST["imgxy"];
            $this->imgxy_arr = explode(" ", $imgxy_str);
        }
        
        // Take groups for query from URL or SESSION-ID
        if (isset($_REQUEST["groups"])) {
            $this->querygroups[] = $_REQUEST["groups"];
        } else {
            $this->querygroups = $_SESSION["groups"];
        }
        
        $this->highlightSelected = $_SESSION["highlightSelected"];
        $this->limitResult = $_SESSION["limitResult"];
        
        $this->scale     = $_SESSION["geo_scale"];
        $this->infoWin   = $_SESSION["infoWin"];
        $this->mapwidth  = $_SESSION["mapwidth"];
        $this->mapheight = $_SESSION["mapheight"];
        $this->GEOEXT    = $_SESSION["GEOEXT"];
        
        $this->autoZoom = $_SESSION["autoZoom"];
        $this->zoomAll = $_SESSION["zoomAll"];
        
        $this->grouplist = $_SESSION["grouplist"];

        $_SESSION["mode"] = $this->mode;
        
        
        // RESTRICT QUERY TO VISIBLE LAYERS IN THE SCOPE OF CURRENT SCALE
        if ($this->mode != "search") {
            PMCommon::setGroups($this->map, $this->querygroups, $this->scale, 0, 1);
        }
    }

    
    function q_processQuery()
    {
        if ($this->mode != "search") {
            $this->q_checkZoomSettings();
            $this->q_setExtents();
            $this->q_execMapQuery();
            $this->q_printGroups();
        } else {
            $this->q_checkZoomSettings();
            $this->q_execAttributeQuery();
            $this->q_printAttributeQuery();
        }
        
        // Write result to session
        if ($this->mode != "iquery") {
            $_SESSION['JSON_Results'] = $this->numResultsTotal > 0 ? $this->allResStr : 0;
        }
    }
    
    
    function q_returnQueryResult()
    {
        if ($this->numResultsTotal > 0) {
            return $this->allResStr;
        } else {
            return 0;
        }
    }
    
    
    function q_returnNumResultsTotal()
    {
        return $this->numResultsTotal;
    }
    
    
    
    
    // ======================== PRIVATE FUNCTIONS =====================//
    
    /**
     * CHECK SETTINGS FOR ZOOM/AUTOZOOM
     */
    function q_checkZoomSettings()
    {
        // SELECTION (nquery) and SEARCH: check for zoom settings
        $this->doAutoZoom = 0;
        $this->zoomFull = 0;
        $this->doZoomAll = 0;
        if ($this->mode != "query") {
            // Check for autozoom
            if (is_array($this->autoZoom)) {
                if (in_array($this->mode, $this->autoZoom)) {
                    $this->doAutoZoom = 1;
                    $this->zoomFull = 1;
                }
            } else {
                if (preg_match("/$this->mode/i", $this->autoZoom)) {
                    $this->doAutoZoom = 1;
                    $this->zoomFull = 1;
                }
            }
            // Check for adding 'Zoom2All' button
            if (is_array($this->zoomAll)) {
                if (in_array($this->mode, $this->zoomAll)) {
                    $this->doZoomAll = 1;
                    $this->zoomFull = 1;
                }
            } else {
                if (preg_match("/$this->mode/i", $this->zoomAll)) {
                    $this->doZoomAll = 1;
                    $this->zoomFull = 1;
                }
            }
            $_SESSION["activegroup"] = $this->querygroups[0];
        }
    }



    /**
     * SET MAP EXTENTS
     */
    function q_setExtents()
    {
        $this->minx_geo = $this->GEOEXT["minx"];
        $this->maxx_geo = $this->GEOEXT["maxx"];
        $this->miny_geo = $this->GEOEXT["miny"];
        $this->maxy_geo = $this->GEOEXT["maxy"];
        
        $this->xdelta_geo = $this->maxx_geo - $this->minx_geo;
        $this->ydelta_geo = $this->maxy_geo - $this->miny_geo;
    }


    /**
     * EXECUTE QUERY
     */
    function q_execMapQuery()
    {
        // use layers with complex queries that are too long to select results
        // cause maxscaledenom is not used...
		$oldDatas = array();
		for ($i = 0; $i < $this->map->numlayers; $i++){
	    	$qLayer = $this->map->getLayer($i);
	        if ($qLayer->getMetaData('PM_RESULT_DATASUBSTITION') != '') {
	            $oldDatas[$i] = $qLayer->data;
	         	$qLayer->set('data', $qLayer->getMetaData('PM_RESULT_DATASUBSTITION'));
	        }
	    }

	    // Patch from Alessandro Pasotti for fixing problems with result returned
	    $this->map->setSize($this->mapwidth, $this->mapheight);
	    // Set $this->map->extent to values of current map extent
        // otherwise values of TOLERANCE in map file are not interpreted correctly
        $this->map->setExtent($this->GEOEXT['minx'], $this->GEOEXT['miny'], $this->GEOEXT['maxx'], $this->GEOEXT['maxy']);
        $this->map->preparequery();
            
        // query by point
        if (count($this->imgxy_arr) == 2) {
            $this->pointQuery = 1;
            
            $this->x_pix = $this->imgxy_arr[0];
            $this->y_pix = $this->imgxy_arr[1];
        
            $x_geo = $this->minx_geo + (($this->x_pix/$this->mapwidth) * $this->xdelta_geo);
            $y_geo = $this->maxy_geo - (($this->y_pix/$this->mapheight) * $this->ydelta_geo);
        
            $XY_geo = ms_newPointObj();
            $XY_geo->setXY($x_geo, $y_geo);
        
            $searchArea = -1;   // ===> USE TOLERANCE IN MAP FILE FOR EACH LAYER <===
            
            // Use '@' to avoid warning if query found nothing
            $qtype = $this->mode != "iquery" ? MS_MULTIPLE : MS_SINGLE;  // MS_MULTIPLE for normal query, MS_SINGLE for iquery
            @$this->map->queryByPoint($XY_geo, $qtype, $searchArea);
            PMCommon::freeMsObj($XY_geo);
        
        // query by Rectangle
        } else {
            $this->pointQuery = 0;
            
            $minx_pix = $this->imgxy_arr[0];
            $miny_pix = $this->imgxy_arr[1];
            $maxx_pix = $this->imgxy_arr[2];
            $maxy_pix = $this->imgxy_arr[3];
            
            if ($minx_pix == $maxx_pix) $maxx_pix = $maxx_pix + 2;  // increase max extent if min==max
            if ($miny_pix == $maxy_pix) $maxy_pix = $maxy_pix - 2;  // -- " --
            
            $this->minx_sel_geo = $this->minx_geo + (($minx_pix/$this->mapwidth)  * $this->xdelta_geo);
            $this->miny_sel_geo = $this->maxy_geo - (($maxy_pix/$this->mapheight) * $this->ydelta_geo);
            $this->maxx_sel_geo = $this->minx_geo + (($maxx_pix/$this->mapwidth)  * $this->xdelta_geo);
            $this->maxy_sel_geo = $this->maxy_geo - (($miny_pix/$this->mapheight) * $this->ydelta_geo);
        
            $selrect = ms_newrectObj();
            $selrect->setExtent($this->minx_sel_geo, $this->miny_sel_geo, $this->maxx_sel_geo, $this->maxy_sel_geo);
                 
            @$this->map->queryByRect($selrect);
            PMCommon::freeMsObj($selrect);
            
            //$queryFile = "d:/webdoc/tmp/qresults.txt";
            //$savedq = $this->map->savequery($queryFile);
            //printDebug($this->map->loadquery($queryFile));
            //error_log(
        }
        
        // use layers with complex queries that are too long to select results
        // cause maxscaledenom is not used...
        // reset data tag
        if (count($oldDatas)) {
        	foreach ($oldDatas as $i => $v) {
        		$qLayer = $this->map->getLayer($i);
	        	$qLayer->set('data', $v);
        	}
        }
    }
    

    
    /**
     * PRINT RESULTS FOR GROUP
     */
    function q_printGroups()
    {
        $this->numResultsTotal = 0;
        $this->allResStr = "[ [";
        
        // Write results for all query groups
        $c = 0;
        
        foreach ($this->grouplist as $grp){
            if (in_array($grp->getGroupName(), $this->querygroups, TRUE)) {
        
                $this->selHeaders = $grp->getResHeaders();
                $this->selStdHeaders = $grp->getResStdHeaders();
                $glayerList = $grp->getLayers();
        
                $this->grpNumResults = 0;
                $lp = 1;
                
                // Process all layers for group
                $this->layersResStr = "";
                $glayerListCount = count($glayerList);
                $this->lc = 0;
                foreach ($glayerList as $l) {
                    $this->glayer = $l;
                    $layType = $this->q_printLayer();      //@@@@@@//
                    
                    // Add comma separator if more than one layer per group adds result row
                    if ($this->query && $layType != 4) {
                        if ($this->query->getLayNumResults() > 0 ) {
                            $this->lc++;
                        }
                    }
                    
                    // Unset query variable (otherwise duplicated values in some cases)
                    //unset($this->query);
                    $this->query = NULL;   ## changed from unset to setting to NULL
                }
                
                if ($this->grpNumResults > 0) {
                    //error_log($this->grpNumResults);
                    if ($c > 0) $this->allResStr .= ", ";
                    $this->numResultsTotal += $this->grpNumResults;
                    $this->printGrpResString($grp);
                    //unset($this->query);
                    $c++;
                }
                
            }
        }
        
        $zp = $this->q_printZoomParameters();
        
        $this->allResStr .= "], $zp ]";

    }
 
 
    function printGrpResString($grp)
    {
        $grpName = $grp->getGroupName();
        $grpDesc = $grp->getDescription();
        
        $grpResStr  = "{\"name\": \"$grpName\", \"description\": \"$grpDesc\", \"numresults\": $this->grpNumResults, ";
        $grpResStr .= $this->fieldHeaderStr;
        $grpResStr .= $this->layersResStr;
        $grpResStr .= "]} ";

        $this->allResStr .= $grpResStr;
    }

 
    /**
     * PRINT RESULTS GROUP LAYER
     */
    function q_printLayer()
    {
        $this->qLayer = $this->map->getLayer($this->glayer->getLayerIdx());
        $resFldList = $this->glayer->getResFields();
        $qLayerType = $this->qLayer->type;
        $qLayerConnType = $this->qLayer->connectiontype;
        $this->qLayerName = $this->qLayer->name;
        //error_log("lay  " .$this->qLayerName);

        // Exclude WMS and annotation layers and layers with no result fields defined
        if ($qLayerConnType != 7  &&  $qLayerType != 4  && (isset($resFldList[0]) && $resFldList[0] != '0')) {
            $XYLayerProperties = $this->glayer->getXYLayerProperties();
            
            // Normal Layer
            if (!$XYLayerProperties) {
                $this->q_printStandardLayer();  //@@@@@@//
            
            // XY Layer
            } else {
                $this->q_printXYLayer();        //@@@@@@//
            }
            
            if ($this->query) {
                $this->fieldHeaderStr = $this->query->getFieldHeaderStr($this->selHeaders, $this->selStdHeaders);
                $this->colspan = count($this->selHeaders) + 1;
            }
            
            
        // WMS layer
        //} elseif ($qLayerType == 3 && $qLayerConnType == 7 ) {
        } elseif ($qLayerConnType == 7 ) {
            //error_log('pippo');
            $this->fieldHeaderStr = "";
            $this->q_printWMSLayer();           //@@@@@@//

        }
        
        if ($this->query && $qLayerType != 4) {
            //if ($this->qLayer->getNumResults() > 0) {
            
            if ($this->query->getLayNumResults() > 0) {
                $sep = $this->lc > 0 ? ", " : "";
                $this->layersResStr .= $sep . $this->query->getResultString();
                $this->grpNumResults += $this->query->getLayNumResults();
            }
        }
        
        return $qLayerType;

    }
 
 
    /**
     * PRINT RESULTS FOR STANDARD LAYER
     */
    function q_printStandardLayer()
    {
        if ($this->qLayer->getNumResults() > 0) {
                                            
            $this->query = new DQuery($this->map, $this->qLayer, $this->glayer, $this->zoomFull);
            
            // For Select function (nquery): get indexes of result shapes and max extent of all shapes
            if ($this->mode == "nquery") {
                $this->resultlayers[$this->qLayerName] = $this->query->returnResultindexes();
                $resulttilelayers[$this->qLayerName] = $this->query->returnResultTileindexes();
                $_SESSION['resulttilelayers'] = $resulttilelayers;
                $this->Extents[] = $this->query->returnMaxExtent();
            }
        }
    }
    
    
    /**
     * PRINT RESULTS FOR XY LAYER
     */
    function q_printXYLayer()
    {
        $x_geo = $this->minx_geo + (($this->x_pix/$this->mapwidth) * $this->xdelta_geo);
        $y_geo = $this->maxy_geo - (($this->y_pix/$this->mapheight) * $this->ydelta_geo);
        $xyLayQueryList["x_geo"]= $x_geo;
        $xyLayQueryList["y_geo"]= $y_geo;
        if ($this->pointQuery) {
            $pixGeoSize = ($this->xdelta_geo/$this->mapwidth);
            $eAdd = $this->qLayer->tolerance > -1 ? $this->qLayer->tolerance : 5; // search radius in map units
            $xyLayQueryList["xmin"] = round($x_geo - ($pixGeoSize * $eAdd));
            $xyLayQueryList["ymin"] = round($y_geo - ($pixGeoSize * $eAdd));
            $xyLayQueryList["xmax"] = round($x_geo + ($pixGeoSize * $eAdd));
            $xyLayQueryList["ymax"] = round($y_geo + ($pixGeoSize * $eAdd));
        } else {
            $xyLayQueryList["xmin"] = $this->minx_sel_geo;
            $xyLayQueryList["ymin"] = $this->miny_sel_geo;
            $xyLayQueryList["xmax"] = $this->maxx_sel_geo;
            $xyLayQueryList["ymax"] = $this->maxy_sel_geo;
        }
        
        $this->query = new XYQuery($this->map, $this->qLayer, $this->glayer, $xyLayQueryList, 0, $this->zoomFull);

        if ($this->mode == "nquery") {
            $resultlayers[$this->qLayerName] = $this->query->returnResultindexes();
            $resulttilelayers[$this->qLayerName] = $this->query->returnResultTileindexes();
            $this->Extents[] = $this->query->returnMaxExtent();
        }
    
        if ($this->query) {
            $fieldHeaderStr = $this->query->getFieldHeaderStr($this->selHeaders, $this->selStdHeaders);
            $this->colspan = count($this->selHeaders) + 1;
        }
    }
    
    
    /**
     * PRINT RESULTS FOR WMS LAYER
     */
    function q_printWMSLayer()
    {
        // Set map width, height and extent for use in WMS query
        $this->map->set("width", $this->mapwidth);
        $this->map->set("height", $this->mapheight);
        $this->map->setExtent($this->minx_geo, $this->miny_geo, $this->maxx_geo, $this->maxy_geo);
        
        // Run query and print put results
        $this->query = new WMSQuery($grp, $this->qLayer, $this->x_pix, $this->y_pix );
        $fieldHeaderStr = $this->query->getFieldHeaderStr($this->selHeaders, $this->selStdHeaders);
        $this->colspan = $this->query->colspan;
    }
    
    
    /**
     *  PROCESS ZOOM INFO AND SET RESULTLAYERS
     */
    function q_printZoomParameters()
    {
        
        $zp = "{";
        
        // Get the maximum extent for more than 1 layer if 'autoZoom' or button 'zoomAll' selcted in config
        //print_r($this->Extents);
        $allExtStr = "";
        if ($this->zoomFull && $this->numResultsTotal > 0) {
            if (is_array($this->Extents) ) {
	        	if (count($this->Extents) < 2) {
	        		$allExtStr = join("+", $this->Extents[0]);
	            } else {
	                $minx = $this->Extents[0][0];
	                $miny = $this->Extents[0][1];
	                $maxx = $this->Extents[0][2];
	                $maxy = $this->Extents[0][3];
	        
	                for($i=1; $i<count($this->Extents); $i++) {
	                    $minx = min($minx, $this->Extents[$i][0]);
	                    $miny = min($miny, $this->Extents[$i][1]);
	                    $maxx = max($maxx, $this->Extents[$i][2]);
	                    $maxy = max($maxy, $this->Extents[$i][3]);
	                }
	                $allExtStr = $minx .'+'. $miny .'+'. $maxx .'+'. $maxy;
	            }
            }
        }
        
        
        $zp .= "\"allextent\": \"$allExtStr\", ";
        
        // Add 'Zoom To All' button if 'zoomAll' selcted in config
        if ($this->doZoomAll && $this->numResultsTotal > 1) {
            $zp .= "\"zoomall\": true, ";
        } else {
            $zp .= "\"zoomall\": false, ";
        }
        
        
        
        // Message for more records found than limit set in ini file
        if ($this->numResultsTotal == $this->limitResult) {
            
        }
        
        // Autozoom to selected fatures if 'autoZoom' selcted in config
        if ($this->mode != "query"  && $this->doAutoZoom && $this->numResultsTotal > 0) {
            $zp .= "\"autozoom\": \"auto\", ";
        // Re-load map frame to highlight selected features
        } elseif ($this->mode != "query"  && $this->highlightSelected) {
            $zp .= "\"autozoom\": \"highlight\", ";
        } else {
            $zp .= "\"autozoom\": false, ";
        }
        
        $zp .= "\"infoWin\": \"". $_SESSION['infoWin'] ."\"";
        
        $zp .= "}";
        
        // Register resultlayers for highlight in case of nquery (selection)
        if ($this->mode != "query"  &&  $this->mode != "iquery" && $this->highlightSelected && $this->numResultsTotal > 0) {
            $_SESSION["resultlayers"] = $this->resultlayers;
        }
        
        //$this->allResStr .= $zStr;
        return $zp;

    }
    
    

    
    
    /**
     * RETURNS THE RESULT STRING (FROM VARABLE $qStr)
     */
    function getResultString()
    {
        return $this->qStr;
    }
    
    
    /**
     * RETURNS THE NUMBER OF RECORDS OF THE QUERY RESULT FOR A LAYER
     */
    function getLayNumResults()
    {
        return $this->numResults;
    }
    
    
    // Abstract methods: returnNumResults
    function setNumResults(){}
    
    
    function returnResultindexes()
    {
        return $this->resultindexes;
    }
    
    function returnResultTileindexes()
    {
        return $this->resulttileindexes;
    }
    
    
    
    /**
     * Return maximum extent for all shapes found in query
     * used for NQUERY
     */
    function returnMaxExtent()
    {
        $bufX = 0.05 *($this->mExtMaxx - $this->mExtMinx);
        $bufY = 0.05 *($this->mExtMaxy - $this->mExtMiny);
        $ExtMinx = $this->mExtMinx - $bufX;
        $ExtMiny = $this->mExtMiny - $bufY;
        $ExtMaxx = $this->mExtMaxx + $bufX;
        $ExtMaxy = $this->mExtMaxy + $bufY;
        
        if ($ExtMinx == $ExtMaxx || $ExtMiny == $ExtMaxy) {
            $ExtMinx = $ExtMinx - (abs($bufX * $ExtMinx));
            $ExtMiny = $ExtMiny - (abs($bufY * $ExtMiny));
            $ExtMaxx = $ExtMaxx + (abs($bufX * $ExtMaxx));
            $ExtMaxy = $ExtMaxy + (abs($bufY * $ExtMaxy));
        }

        $maxExtent = array($ExtMinx, $ExtMiny, $ExtMaxx, $ExtMaxy);
        
        return $maxExtent;
    }


   
    
    /**
     * RESULT TABLE: FIELD HEADER
     * Print the table header for every single group/layer
     */
    function getFieldHeaderStr($selHeaders, $selStdHeaders)
    {
        $slink = $this->qLayerType != 3 ? "@" : "#";
        
        // TABLE HEADER: ATTRIBUTE NAMES...
        $h = "\"header\": [\"$slink\"";
        $sh = "\"stdheader\": [\"$slink\"";
        
        for ($iField=0; $iField < sizeof($selHeaders); $iField++) {
            $h .= ",\"" . $selHeaders[$iField] . "\"";
            $sh .= ",\"" . $selStdHeaders[$iField] . "\"";
        }
        
        $h .= "]";
        $sh .= "]";
        
        return "$h, $sh, \"values\": [ ";
    }
    
    
    /**
     * RESULT TABLE: FIELD VALUES
     * Print all field values (except shapeindex) for single layer
     */
    function printFieldValues($fldName, $fldValue)
    {
        // Change format for decimal field values
        /*
        if (is_numeric($fldValue)) {
            if (preg_match('/\./', $fldValue)) {
                $fldValue = number_format($fldValue, 2, ',', '');
            }
        }
        */
        
        // !!!! ENCODE ALL STRINGS IN UTF-8 !!!!
        if ($this->layerEncoding) {
            if ($this->layerEncoding != "UTF-8") {
                $fldValue = iconv($this->layerEncoding, "UTF-8", $fldValue);
            }
        } else {
            $fldValue = utf8_encode($fldValue);
        }
        
        // Escape double quotes & backslashes; replace carriage return
        //$fldValue=preg_replace("/\r?\n/", "\\n", addslashes($fldValue));
        $fldValue=preg_replace(array('/\r?\n/', '/\\\/', '/"/'), array('\\n', '\\\\\\', '\\"'), $fldValue);
        
        
        $valStr = ", ";
    
        $hyperFieldList = $this->glayer->getHyperFields();
        // Check for hyperlinks
        if (count($hyperFieldList) > 0) {
            $hyperFieldsValues = $hyperFieldList[0];
            $hyperFieldsAlias = $hyperFieldList[1];
            if (in_array($fldName, $hyperFieldsValues) && $fldValue) {
                $valStr .= "{\"hyperlink\": [\"$this->qLayerName\",\"$fldName\",\"$fldValue\",\"";
                //$valStr .= "{\"hyperlink\": [\"$this->qLayerName\",\"$fldName\",\"" . addslashes($fldValue) . "\",\"";
                // Print ALIAS if defined
                if (isset($hyperFieldsAlias[$fldName]) && $hyperFieldsAlias[$fldName] !== false) {
                    $valStr .= $hyperFieldsAlias[$fldName];
                // else field VALUE
                } else {
                    $valStr .= $fldValue;
                }
                
                $valStr .= "\"]}";
           } else {
                $valStr .= "\"$fldValue\"";
           }
           
        // NO hyperlink so just print normal output
        } else {
            $valStr .= "\"$fldValue\"";
        }
        
        return $valStr;
    }
    
    
    
    /**
     * Connect to DB with jointable, return connection handler
     */
    function dbConnect($dsn)
    {
        $dbh = DB::connect($dsn);
        if (DB::isError($dbh)) {
            #die ($dbh->getMessage());
            PMCommon::db_logErrors($dbh);
            return NULL;
        } else {
            return $dbh;
        }
    }



    /**
     * SEARCH VIA ATTRIBUTES
     */
    function q_execAttributeQuery()
    {
        // Definition of search parameters with external methods
        if (isset($_REQUEST['externalSearchDefinition'])) {
            $this->qLayerName = $_REQUEST['layerName'];
            $this->qSearchLayerType = $_REQUEST['layerType'];
            $fldName          = $_REQUEST['fldName'];
            $this->qStr       = $_REQUEST['qStr'];
            pm_logDebug(3, $_REQUEST, "Parameters for REQUEST array \nfile: query.php->q_execAttributeQuery \n");
            
        // Default using search.xml definitions
        } else {
            $searchitem = $_REQUEST['searchitem'];
            foreach ($_REQUEST as $key=>$val) {
                if ($key != "findlist" && $key != "searchitem") {
                    $searchArray[$key] = urldecode($val); //utf8_encode($val);
                }
            }
            
            $search = new XML_search($this->map, $_SESSION['PM_SEARCH_CONFIGFILE']);
            $searchParams = $search->getSearchParameters($this->map, $searchitem, $searchArray);
            $this->qLayerName = $searchParams['layerName'];
            $this->qSearchLayerType = $searchParams['layerType'];
            $fldName          = $searchParams['firstFld'];
            $this->qStr       = $searchParams['qStr'];
            
            pm_logDebug(2, $searchArray, "Parameters for searchArray \nfile: query.php->q_execAttributeQuery \n");
            pm_logDebug(2, $searchParams, "Parameters for searchParams \nfile: query.php->q_execAttributeQuery");
        }
        
        // Return layer type
        $this->qLayer = $this->map->getLayerByName($this->qLayerName);
        //$this->qSearchLayerType = $this->qLayer->connectiontype;
        
        // Get group and glayer objects
        $GroupGlayer = PMCommon::returnGroupGlayer($this->qLayerName);
        $this->grp = $GroupGlayer[0];
        $this->glayer = $GroupGlayer[1];
        $this->XYLayerProperties = $this->glayer->getXYLayerProperties();
        
        $this->layerEncoding = $this->glayer->getLayerEncoding();

        if ($this->qSearchLayerType == "shape" || $this->qSearchLayerType == "ms" || $this->qSearchLayerType == "oracle") {
            if ($layFilter = $this->qLayer->getFilterString()) {
				$mapLayerFilterItem = $this->qLayer->filteritem;
				if ($layFilter[0] == '/') {
					$operator = '=~';
				} else {
					$operator = '=';
				}
				$this->qStr = "(\"[$mapLayerFilterItem]\" $operator $layFilter AND ($this->qStr) )";
                pm_logDebug(3, $this->qStr, "query string including FILTER -- query.php->q_execAttributeQuery");
            }
            if ($this->qSearchLayerType == "oracle") {
                 @$this->qLayer->queryByAttributes(null, $this->qStr, MS_MULTIPLE);
            } else {
                 @$this->qLayer->queryByAttributes($fldName, $this->qStr, MS_MULTIPLE);
            }
        }
    }
    
    /**
     * Print search
     */
    function q_printAttributeQuery()
    {
        $selHeaders = $this->grp->getResHeaders();
        $selStdHeaders = $this->grp->getResStdHeaders();
        $this->colspan = count($selHeaders) + 1;
        
        // PROCESS QUERY DEPENDING ON LAYER TYPE
        if ($this->qSearchLayerType == "postgis") {
            $this->query = new PGQuery($this->map, $this->qLayer, $this->glayer, $this->qStr, $this->zoomFull);
        } else {
            // Normal Layer
            if (!$this->XYLayerProperties) {
                $this->query = new DQuery($this->map, $this->qLayer, $this->glayer, $this->zoomFull);
            } else {
                //echo $this->qStr;
                $this->query = new XYQuery($this->map, $this->qLayer, $this->glayer, $this->qStr, 1, $this->zoomFull);
            }
        }
        
        
        $this->layersResStr = $this->query->getResultString();
        $this->numResultsTotal = $this->query->getLayNumResults();
        $this->fieldHeaderStr = $this->query->getFieldHeaderStr($selHeaders, $selStdHeaders);
        if ($this->numResultsTotal > 0) {
            $this->resultlayers[$this->qLayerName] = $this->query->returnResultindexes();
            $resulttilelayers[$this->qLayerName] = $this->query->returnResultTileindexes();
            $_SESSION['resulttilelayers'] = $resulttilelayers;
        }
        $this->Extents[] = $this->query->returnMaxExtent();
        
        $this->allResStr = "[ [";
        
        if ($this->numResultsTotal > 0) {
            $this->grpNumResults = $this->numResultsTotal;
            $this->printGrpResString($this->grp);
        }
        $zp = $this->q_printZoomParameters();
        
        $this->allResStr .= "], $zp ]";

        //echo "total : $this->numResultsTotal ";
            
    }

    /**
     * decode from utf
     */
    function q_strDecode($inval)
    {
        // DECODE QUERY STRING FROM UTF-8
        if ($this->layerEncoding) {
            if ($this->layerEncoding != "UTF-8") {
                return iconv("UTF-8", $this->layerEncoding, $inval);
            } else {
                return $inval;
            }
        } else {
            return utf8_decode($inval);
        }
    }


   /**
    * Test if layer has the same projection as map
    */
    function checkProjection()
    {
        $mapProjStr = $this->map->getProjection();
        $qLayerProjStr = $this->qLayer->getProjection();
    
        if ($mapProjStr && $qLayerProjStr && $mapProjStr != $qLayerProjStr) {
            $changeLayProj = 1;
            if ($_SESSION['MS_VERSION'] < 6) {
            	$this->mapProjObj = ms_newprojectionobj($mapProjStr);
            	$this->qLayerProjObj = ms_newprojectionobj($qLayerProjStr);
            } else {
            	$this->mapProjObj = new projectionObj($mapProjStr);
            	$this->qLayerProjObj = new projectionObj($qLayerProjStr);
            }
        } else {
            $changeLayProj = 0;
        }
        
        return $changeLayProj;
    }



} // END CLASS




?>