<?php

/******************************************************************************
 *
 * Purpose: XML based search definition functions
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


/**
 * XML based definition and requests for attribute query (Search)
 */
class XML_search
{
    protected $map;
    protected $xmlFN;
    protected $msVersion;
    protected $xml;
    protected $xmlRoot;
    protected $dataRoot;
    
   
    /**
     * initialize XML based search
     */
    public function __construct($map, $xmlFN)
    {
        $this->map = $map;
        $this->xmlFN = $xmlFN;
        $this->msVersion = $_SESSION['MS_VERSION'];
        $this->xml = simplexml_load_file($xmlFN, NULL, LIBXML_NOENT);
        $this->xmlRoot = $this->xml->searchlist ? $this->xml->searchlist : $this->xml;
        $this->dataRoot = $this->getDataRoot(trim((string)$this->xmlRoot->dataroot));
        //error_log($this->dataRoot);
    }
    
    
    /**
     * Validate XML file with an XML schema 
     * called in x_searc.php
     */
    public function validateSearchXML()
    {
        if (class_exists('DOMDocument')) {
            ob_start();        
            $dom = new DOMDocument();
            //$dom->load($this->xmlFN);
            $dom->loadXML($this->xmlRoot->asXML());
            $xsd = "../query/search.xsd";
            if ($dom->schemaValidate($xsd)) {
                pm_logDebug(2, "Validation of search.xml file succeeded");
            } else {
                $output = ob_get_contents();
                pm_logDebug(0, "Validation of search.xml file FAILED: \n$output");
                ob_end_clean();
            }
        }
    }

    
    /**
     * Return data root for files used in search
     */
    protected function getDataRoot($dataRoot)
    {
        if ($dataRoot && isset($dataRoot{0}) && $dataRoot{0} == "$") {
            return $_SESSION['datapath'] . substr($dataRoot, 1);
        } else {
            return $dataRoot;
        }
    }
    
    
    
    /**
     * Compile the search string for queryByAttribute
     * called from query.php
     */
    public function getSearchParameters($map, $searchitem, $searchArray)
    {
        pm_logDebug(3, $searchArray, "Searcharray in search.php->getSearchParameters() ");
        
        $searchitems = $this->xml->xpath("//$this->xmlRoot/searchitem[@name=\"$searchitem\"]");
        foreach ($searchitems as $si) {
            $layer = $si->layer;
            $fields = $layer->field;
            
            $layerType = (string)$layer['type'];
            $layerName = (string)$layer['name'];
            //$exFilters = (int)$layer['existingfilters'];
            $firstFld  = (string)$fields[0]['name'];
            $sql_from  = (string)$layer->sql_from;
            $sql_where = (string)$layer->sql_where;
            
            $mapLayer = $map->getLayerByName($layerName);
            
            
            /**** SQL string explicitely defined ****/
            if (strlen($sql_where) > 1) {
                foreach ($fields as $fo) {
                    $f = (string)$fo['name'];
                    $wildcard = (int)$fo['wildcard'];
                    $val = isset($searchArray[$f]) ? $this->getEncodedVal($mapLayer, trim($searchArray[$f])) : '';
                    $wc1 = '';
					$wc2 = '';
					
                    // Explicitly use wildcards
                    if ($wildcard) {
                        if ($val{0} == "*") {
                            $wc1 = "";
                            $val = substr($val, 1);
                        } else {
                            $wc1 = "^";
                        }
                        if (substr($val, -1) == "*") {
                            $wc2 = "";
                            $val = substr($val, 0, -1);
                        } else {
                            $wc2 = "$";
                        }
                    }
                    // (SQL injection)
                    if ($layerType == 'postgis') {
                    	$val = str_replace("'", "''", $val);
                    } else {
                    	$val = trim($val);
                    	if (!get_magic_quotes_gpc()) {
                    		$val = addslashes($val);
                    	}
                    }
                    $replSearchStr = "$wc1$val$wc2";
                    $sql_where = str_replace("[$f]", $replSearchStr, $sql_where);
                }
                
                //$sql['from']  = $sql_from;
                //$sql['where'] = $sql_where;                
                
                $qs = $sql_where;
            
            
            /**** Create search string from fields ****/
            } else {
                $qs = "";
                $fc = 0;
                foreach ($fields as $fo) {
                    $f = (string)$fo['name'];
                    $fa = !$fo['alias'] ? $f : (string)$fo['alias'];
                    
                    // add only if user entered value
                    if (strlen(trim($searchArray[$fa])) > 0) { 
                        
                        $type = trim((string)$fo['type']);
                        $wildcard = (int)$fo['wildcard'];
                        $operator = trim((string)$fo['operator']);
                        $compare  = trim((string)$fo['compare']);                        
                        $deftype  = (string)$fo->definition['type'];
                        $val = $this->getEncodedVal($mapLayer, $searchArray[$fa]);
                        
                        $valoperator = $fc < 1 ? "" : (strlen($operator) > 0 ? $operator : "AND");
                        
                        //=== PostGIS layers 
                        if ($layerType == "postgis") {
                            $qs .= $this->getSearchParamsPG($searchArray, $f, $fa, $type, $wildcard, $operator, $compare, $deftype, $val, $valoperator);
                        
                        //=== XY layers 
                        } elseif ($layerType == "xy" || $layerType == "oracle") {
                            $qs .= $this->getSearchParamsXY_DB($searchArray, $f, $fa, $type, $wildcard, $operator, $compare, $deftype, $val, $valoperator);
                        
                        //=== Shape etc. layers 
                        } else {
                            $qs .= " " . $valoperator . $this->getSearchParamsShp($searchArray, $f, $fa, $type, $wildcard, $operator, $compare, $deftype, $val, $valoperator);   
                            /* not used
                            $filter = "";
                            if ($exFilters) {
                                if ($layerFilter = $mapLayer->getFilterString()) {
                                    $filter = "($layerFilter) AND ";
                                }
                            }*/
                            $qs = "(" . $qs . ")";
                        }
                        $fc++;
                    }
                }
            }
        }
        
        $searchParams['layerName'] = $layerName;
        $searchParams['layerType'] = $layerType;
        $searchParams['firstFld']  = $firstFld;
        $searchParams['qStr']      = $qs;
        return $searchParams;
    
    }
    
    /**
     * De/Encode search string depending on settings for Layer and defCharset
     * if no METADATA LAYER_ENCODING specified for layer, ISO-8859-1 is assumed
     */
    protected function getEncodedVal($mapLayer, $inVal) 
    {
        if ($layerEncoding = $mapLayer->getMetaData("LAYER_ENCODING")) {
            $outVal = iconv($_SESSION['defCharset'], $layerEncoding, $inVal);
        } else {
            $outVal = iconv($_SESSION['defCharset'], "ISO-8859-1", $inVal);
        }   
        return $outVal;
    }
    
    
    /**
     * Return parameters for Shapefile layers
     */
    protected function getSearchParamsShp($searchArray, $f, $fa, $type, $wildcard, $operator, $compare, $deftype, $val, $valoperator)
    {
        $qs = "";
        //--- String ---
        if ($type == "s") {
            // Create regex for case insensitivity of value if not from OPTION or SUGGEST
            if ($this->msVersion >= 6) {
                $val = $val;
            } else {
                $val = ($wildcard != 2 ? preg_replace ("/\w/ie", "'('. strtoupper('$0') . '|' . strtolower('$0') .')'", $val) : $val);
            }
            
            // Explicitly use wildcards
            $enclose_parenthesis = true;
            if ($wildcard == 1) {
                if ($val{0} == "*") {
                    $wc1 = "";
                    $val = substr($val, 1);
                } else {
                    $wc1 = "^";
                }
                
                if (substr($val, -1) == "*") {
                    $wc2 = "";
                    $val = substr($val, 0, -1);
                } else {
                    $wc2 = "$";
                }
                
                if ($this->msVersion >= 6) {
                    $qs .=  " \"[$f]\" ~* \"$wc1$val$wc2\"" ;
                } else {
                    $qs .=  " \"[$f]\" =~ /$wc1$val$wc2/" ;
                }
                $enclose_parenthesis = false;
            
            // Use exact search because full value from OPTION or SUGGEST
            } elseif ($wildcard == 2) {
                // add slashes if not automatically added via php.ini "magic_quotes"
                if (!get_magic_quotes_gpc()) {
                    $val = addslashes($val);
                }
                
                if (preg_match("/,/", $val)) {
                    $vList = explode(",", $val);
                    $os =  "(";
                    foreach ($vList as $v) {
                        $os .=  " ($valoperator \"[$f]\" = \"$v\") OR" ;
                    }
                    $qs .= substr($os, 0, -2) . ")";                                     
                
                } else {
                    //$qs .=  " $valoperator \"[$f]\" = \"$val\"" ;  //was before
                    $qs .=  " \"[$f]\" = \"$val\"" ;
                }
            
            // Use wildcard-like search
            } else {
                if ($this->msVersion >= 6) {
                    $qs .=  " \"[$f]\" ~* \"" . $val . "\" " ;
                } else {
                    $qs .=  " \"[$f]\" =~ /" . $val . "/ " ;
                }
                $enclose_parenthesis = false;
            }
        
        //--- Number ---    
        } else {
            // Check for select-multiple
            if ($wildcard == 2 && preg_match("/,/", $val)) {
                    $vList = explode(",", $val);
                    $os =  "(";
                    foreach ($vList as $v) {
                        $os .=  " ($valoperator \"[$f]\" = $v) OR" ;
                    }
                    $qs .= substr($os, 0, -2) . ")";    
            } else {
                // Check if there is another comparison operator than '=' defined
                $valcompare = (strlen($compare) > 0 ? $compare : ($searchArray["_fldoperator_$fa"] ? $searchArray["_fldoperator_$fa"] : "=") );
                $qs .=  " [$f] $valcompare $val ";
            }
        }
        
        if ($enclose_parenthesis) $qs = " (" . $qs . ") ";
        return $qs;        
    }
    
    
    
    /**
     * For PostGIS layers
     */
    protected function getSearchParamsPG($searchArray, $f, $fa, $type, $wildcard, $operator, $compare, $deftype, $val, $valoperator)
    {
        $qs = "";
        $val = trim($val);
        if (!get_magic_quotes_gpc()) {
            $val = addslashes($val);
        }

        
        //--- String ---
        if ($type == "s") {
            // Explicitly use wildcards
            if ($wildcard == 1) {
                if ($val{0} != "*") {
                    $wc1 = "";
                } else {
                    $wc1 = "%";
                    $val = substr($val, 1);
                }
                
                if (substr($val, -1) != "*") {
                    $wc2 = "";
                } else {
                    $wc2 = "%";
                    $val = substr($val, 0, -1);
                }
                
                //$qs .=  " $valoperator $f ~* '$wc1$val$wc2' " ;
                $qs .=  " $valoperator $f ILIKE '$wc1$val$wc2' " ;
            
            // Use exact search because full value from OPTION or SUGGEST
            } elseif ($wildcard == 2) {
                if (preg_match("/,/", $val)) {
                    $qs .= " $valoperator $f IN ('" . str_replace(",", "','", $val) . "')";
                } else {
                    $qs .=  " $valoperator $f = '$val' " ;
                }
            
            // Make normal search
            } else {
                //$qs .=  " $valoperator $f ~* '$val' " ;
                $qs .=  " $valoperator $f ILIKE '%$val%' " ;  
            }
        
        //--- Number ---
        } else {
            // Check for select-multiple
            if ($wildcard == 2 && preg_match("/,/", $val)) {
                $qs .= " $valoperator $f IN ($val)";
            } else {
                $valcompare = (strlen($compare) > 0 ? $compare : ($searchArray["_fldoperator_$fa"] ? $searchArray["_fldoperator_$fa"] : "=") );
                $qs .=  " $valoperator $f $valcompare $val ";
            }
        }
        
        return $qs;
    }
    
    /**
     * For XY and Oracle layers
     */
    protected function getSearchParamsXY_DB($searchArray, $f, $fa, $type, $wildcard, $operator, $compare, $deftype, $val, $valoperator)
    {
        $qs = "";
        $val = trim(str_replace("\'", "''", $val));
        
        //--- String ---
        if ($type == "s") {
            // Explicitly use wildcards
            if ($wildcard == 1) {
                if ($val{0} == "*") {
                    $wc1 = "%";
                    $val = substr($val, 1);
                } else {
                    $wc1 = "";
                }
                
                if (substr($val, -1) == "*") {
                    $wc2 = "%";
                    $val = substr($val, 0, -1);
                } else {
                    $wc2 = "";
                }
                $val = strtoupper($val);
                $qs .=  " $valoperator UPPER($f) LIKE '$wc1$val$wc2' " ;
            
            // Use exact search because full value from OPTION or SUGGEST
            } elseif ($wildcard == 2) {
                if (preg_match("/,/", $val)) {
                    $qs .= " $valoperator $f IN ('" . str_replace(",", "','", $val) . "')";
                } else {
                    $qs .=  " $valoperator $f = '$val' " ;
                }
            
            } else {
                $val = strtoupper($val);
                $qs .=  " $valoperator UPPER($f) LIKE '%$val%' " ;
            }
        
        //--- Number ---
        } else {
            if ($wildcard == 2 && preg_match("/,/", $val)) {
                $qs .= " $valoperator $f IN ($val)";
            } else {
                $valcompare = (strlen($compare) > 0 ? $compare : ($searchArray["_fldoperator_$fa"] ? $searchArray["_fldoperator_$fa"] : "=") );
                $qs .=  " $valoperator $f $valcompare $val ";
            }
        }
        
        return $qs;
    }
    
    /**
     * Create OPTION list for selecting the search items available
     * called from x_search.php
     */
    public function createSearchOptions()
    {
        $searchitems = $this->xml->xpath("//$this->xmlRoot/searchitem");

        $json  = '{"selectname": "findlist", "events": "onchange=\"PM.Query.setSearchInput()\"", ' ; 
        $json .= '"options": {"0": "' . _p("Search for") . '"';
        foreach ($searchitems as $si) {
            $description = _p((string)$si['description']);
            $optvalue = (string)$si['name'];
            $json .= ", \"$optvalue\": \"$description\"";
        }
        $json .= "}}"; 
            
        return $json;
    }
    
    
    /**
     * Create single search item, can be simple input, OPTION list, suggest field, checkbox, radio
     * called from x_search.php
     */
    public function createSearchItem($searchitem)
    {
        $searchitems = $this->xml->xpath("//$this->xmlRoot/searchitem[@name=\"$searchitem\"]");
        
        foreach ($searchitems as $si) {
            //$description = _p($si['description']);
            $searchitem = (string)$si['name'];
            
            $json  = "{\"searchitem\": \"$searchitem\", ";
            
            $layer = $si->layer;
            $fields = $layer->field;
            
            $json  .= "\"fields\": [";
            $fc = 0;
            
            foreach ($fields as $f) {
                $fjson = "";
                $description = addslashes(_p((string)$f['description']));
                $fldname = $f['alias'] ? (string)$f['alias'] : (string)$f['name'];
                $fldsize = $f['size'] ? (int)$f['size'] : "false";
                $fldsizedesc = $f['sizedesc'] ? (int)$f['sizedesc'] : "false";
                $fldinline = $f['inline'] ? (bool)$f['inline'] : "false";
                
                $sep = $fc < 1 ? "" : ",";
                $fjson .= "$sep{\"description\": \"$description\", ";
                $fjson .= "\"fldname\": \"$fldname\", ";
                $fjson .= "\"fldsize\": $fldsize, ";
                $fjson .= "\"fldsizedesc\": $fldsizedesc, ";
                $fjson .= "\"fldinline\": $fldinline";

                if ($f->definition['type'] == true) {
                    $retList = $this->getFieldDefinition((string)$layer['name'], $f, $searchitem, $fldname);
                    // Suggest things
                    if ($retList['newSuggest']) {
                        $fieldSuggest[$fldname] = $retList['newSuggest'];
                        $_SESSION['suggestList'][$searchitem] = $fieldSuggest;
                    }
                    
                    $fjson .= ", \"definition\": ";
                    $fjson .= $retList['json'];
                    //error_log($retList['json']);
                } else {
                    // do nothing, for the time being
                }
                $fjson .= "}";
                $fc++;
                
                $json  .= $fjson;
                //error_log($fjson);
                unset($fjson);
                
            }
            
            
            $json  .= "]}";
            
        }
        //error_log($json);
        
        return $json;
    
    }
    
    /**
     * Get the <definitions> for a <field> in the XML
     * called from createSearchItem()
     */
    protected function getFieldDefinition($layername, $field, $searchitem, $fldname)
    {
        $oLayer = $this->map->getLayerByName($layername);
        $definition = $field->definition;
        $def_type = (string)$definition['type'];
        $def_connectiontype = (string)$definition['connectiontype'];
        $sort = (string)$definition['sort'];
        $json = "";
        
        pm_logDebug(3, $definition, "XML->//definition");

        $events = '"events":' . ($definition->events ? '"' . addslashes(trim((string)$definition->events)) . '"' : 'false');
        
        // *** OPTIONS *** //
        if ($def_type == "options") {
            $firstoption = $definition['firstoption'] ? _p((string)$definition['firstoption']) : "*";
            
            // Database
            if ($def_connectiontype == "db") {
                $dsn = (string)$definition->dsn;
                if ($dsn == "@" && $oLayer->connectiontype == MS_POSTGIS) {
                    // 'user=postgres password=postgres dbname=gisdb host=localhost'
                    // pgsql://postgres:postgres@localhost/gisdb
                    $pgConn = array();
                    foreach (preg_split("/\s/", $oLayer->connection) as $kvp) {
                        $kvpList = explode("=", $kvp);
                        $pgConn[$kvpList[0]] = $kvpList[1]; 
                    }
                    if (isset($pgConn['port']) && $pgConn['port']) {
                    	$pgConn['host'] .= ':' . $pgConn['port'];
                    }
                    $dsn = "pgsql://" . $pgConn['user'] . ":" . $pgConn['password'] . "@" . $pgConn['host'] . "/" . $pgConn['dbname'] ;
                    //error_log("dsn: $dsn");
                } 
                $encoding = (string)$definition->dsn['encoding'];
                // take into account possible existing filters from map file
                if ((int)$definition['existingfilters']) {
                	$exFilter = str_replace('"', '', $oLayer->getFilterString());
                	$sql_xml  = (string)$definition->sql;
                	$sqlList  = preg_split("/ WHERE /i", $sql_xml);
					$sqlListWhere = $sqlList[1];
					$sqlListEnd = '';
					$sqlListTmp = preg_split("/ ORDER BY /i", $sqlListWhere);
					if (count($sqlListTmp) == 2) {
						$sqlListWhere = $sqlListTmp[0];
						$sqlListEnd .= ' ORDER BY ' . $sqlListTmp[1];
					}
					$sqlListTmp = preg_split("/ LIMIT /i", $sqlListWhere);
					if (count($sqlListTmp) == 2) {
						$sqlListWhere = $sqlListTmp[0];
						$sqlListEnd .= ' LIMIT ' . $sqlListTmp[1];
					}
                	$sql      = $sqlList[0] . " WHERE ($exFilter) AND ($sqlListWhere) $sqlListEnd";
                } else {
                   $sql      = (string)$definition->sql;
                } 
                $optjson = $this->getOptionsFromDb($dsn, $sql, $encoding);
            
            // CSV file            
            } elseif ($def_connectiontype == "csv") {
                $csvfile   = $this->getDataFilePath((string)$definition->csvfile);
                $separator = (string)$definition->csvfile['separator'];
                $encoding  = (string)$definition->csvfile['encoding'];
                $optjson = $this->getOptionsFromCSV($csvfile, $separator, $encoding, $sort);
            
            // dBase file
            } elseif ($def_connectiontype == "dbase") {
                $dbasefile = $this->getDataFilePath((string)$definition->dbasefile);
                $encoding  = (string)$definition->dbasefile['encoding'];
                $keyfield  = (string)$definition->dbasefile['keyfield'];
                $showfield = (string)$definition->dbasefile['showfield'];
                $optjson = $this->getOptionsFromDbase($dbasefile, $encoding, $keyfield, $showfield, $sort);
                
            // MS layer
            } elseif ($def_connectiontype == "ms") {
                $mslayer   = $layername; 
                $encoding  = (string)$definition->mslayer['encoding'];
                $keyfield  = (string)$definition->mslayer['keyfield'];
                $showfield = (string)$definition->mslayer['showfield'];
                $optjson = $this->getOptionsFromMS($mslayer, $encoding, $keyfield, $showfield, $sort);
            
            // Inline definition as <option> tag
            } elseif ($def_connectiontype == "inline") {
                $optionlist = $definition->option;
                foreach ($optionlist as $o) {
                    $oarray[(string)$o['value']] = (string)$o['name'];
                }
                $optjson = $this->options_array2json($oarray, false);
            }
            
            $size = "\"size\": " . ($definition['size'] == true ?  (int)$definition['size']  : "0");
            $json .= "{\"type\":\"$def_type\", \"selectname\":\"$fldname\", \"firstoption\":\"$firstoption\", $size, $events, \"options\": "; 
            $json .= $optjson;
            $json .= "}";
            
            $retList['newSuggest'] = false;
        
        // *** SUGGEST *** //
        } elseif ($def_type == "suggest") {
            $minlength = (int)$definition['minlength'];
            $regexleft = (string)$definition['regexleft'];
            $startleft = (int)$definition['startleft'];
            $dependfld = (string)$definition['dependfld'];
            $nosubmit  = (int)$definition['nosubmit'];
            
            $newSuggest['type']      = $def_connectiontype;
            $newSuggest['sort']      = $sort;
            $newSuggest['minlength'] = $minlength;
            $newSuggest['regexleft'] = $regexleft;
            $newSuggest['startleft'] = $startleft;
            $newSuggest['dependfld'] = $dependfld;
            
            // Database 
            if ($def_connectiontype == "db") {
                $dsn = (string)$definition->dsn;
                if ($dsn == "@" && $oLayer->connectiontype == MS_POSTGIS) {
                    // 'user=postgres password=postgres dbname=gisdb host=localhost'
                    // pgsql://postgres:postgres@localhost/gisdb
                    $pgConn = array();
                    foreach (preg_split("/\s/", $oLayer->connection) as $kvp) {
                        $kvpList = explode("=", $kvp);
                        $pgConn[$kvpList[0]] = $kvpList[1]; 
                    }
                    if (isset($pgConn['port']) && $pgConn['port']) {
                    	$pgConn['host'] .= ':' . $pgConn['port'];
                    }
                    $dsn = "pgsql://" . $pgConn['user'] . ":" . $pgConn['password'] . "@" . $pgConn['host'] . "/" . $pgConn['dbname'] ;
                }
                $newSuggest['dsn'] = $dsn;
                $newSuggest['encoding'] = (string)$definition->dsn['encoding'];
                // take into account possible existing filters from map file
                if ((int)$definition['existingfilters']) {
                    $exFilter = str_replace('"', '', $oLayer->getFilterString());
                    $sql_xml = (string)$definition->sql;
                    $sqlList = preg_split("/ WHERE /i", $sql_xml);
					$sqlListWhere = $sqlList[1];
					$sqlListEnd = '';
					$sqlListTmp = preg_split("/ ORDER BY /i", $sqlListWhere);
					if (count($sqlListTmp) == 2) {
						$sqlListWhere = $sqlListTmp[0];
						$sqlListEnd .= ' ORDER BY ' . $sqlListTmp[1];
					}
					$sqlListTmp = preg_split("/ LIMIT /i", $sqlListWhere);
					if (count($sqlListTmp) == 2) {
						$sqlListWhere = $sqlListTmp[0];
						$sqlListEnd .= ' LIMIT ' . $sqlListTmp[1];
					}
                    $newSuggest['sql']      = $sqlList[0] . " WHERE ($exFilter) AND ($sqlListWhere) $sqlListEnd";
                } else {
                    $newSuggest['sql']      = (string)$definition->sql;
                }
                
            // TXT file            
            } elseif ($def_connectiontype == "txt") {
                $newSuggest['txtfile']   = $this->getDataFilePath((string)$definition->txtfile);
                $newSuggest['separator'] = (string)$definition->txtfile['separator'];
                $newSuggest['encoding']  = (string)$definition->txtfile['encoding'];
            
            // dBase file
            } elseif ($def_connectiontype == "dbase") {
                $newSuggest['dbasefile']   = $this->getDataFilePath((string)$definition->dbasefile);
                $newSuggest['encoding']    = (string)$definition->dbasefile['encoding'];
                $newSuggest['searchfield'] = (string)$definition->dbasefile['searchfield'];
        
            // MS layer
            } elseif ($def_connectiontype == "ms") {
                $newSuggest['mslayer']     = $layername; 
                $newSuggest['encoding']    = (string)$definition->mslayer['encoding'];
                $newSuggest['searchfield'] = (string)$field['name'];
                $newSuggest['fieldtype']   = (string)$field['type'];
            }
            
            $retList['newSuggest'] = $newSuggest;
            
            $json .= "{\"type\": \"$def_type\", \"searchitem\": \"$searchitem\", $events, \"minlength\": $minlength, \"nosubmit\": $nosubmit"; 
            if ($dependfld) $json .= ",\"dependfld\":\"$dependfld\"";
            $json .= "}";
            
        // Checkbox and radio inputs
        } elseif ($def_type == "checkbox") {
            $value     = addslashes((string)$definition['value']);
            $checked   = (int)$definition['checked'];
            $json .= "{\"type\": \"$def_type\", \"value\": \"$value\", \"checked\": $checked }";
        
        } elseif ($def_type == "radio") {
            $iList = $definition->input;
            $json .= "{\"type\": \"$def_type\", \"inputlist\": {";
            $checked   = $definition['checked'];
            $ic = 0;
            foreach ($iList as $i) {
                $sep = $ic > 0 ? "," : "";
                $name    = (string)$i['name'];
                $value   = (string)$i['value'];
                //$checked = (int)$definition['checked'];
                $json .= "$sep \"$value\": \"$name\"";
                $ic++;
            }
            $json .= "}, \"checked\": \"$checked\" }";
            //error_log($json);
        
        } elseif ($def_type == "operator") { 
                $optionlist = $definition->option;
                foreach ($optionlist as $o) {
                    $oarray[(string)$o['value']] = (string)$o['name'];
                }
                $optjson = $this->options_array2json($oarray, false);
                $json .= "{\"type\": \"$def_type\", \"selectname\": \"_fldoperator_" . $fldname . "\", \"options\": $optjson }"; 
        
        // Checkbox and radio inputs
        } elseif ($def_type == "hidden") {
            $keyval = (string)$definition['value'];
            $connectiontype = (string)$definition['connectiontype'];
            $value = $connectiontype=="session" ? $_SESSION[$keyval] : $keyval;
            $json .= "{\"type\": \"$def_type\", \"value\": '$value'}";
        }
        
        $retList['json'] = $json;
        //error_log($json);
        return $retList;
    }
    
    
    /**
     * Get the option values from a DBMS via PEAR
     */
    protected function getOptionsFromDb($dsn, $sql, $encoding)
    {
        pm_logDebug(3, $sql, "search.php->getOptionsFromDb()");
        
        // Load PEAR class
        $pearDbClass = $_SESSION['pearDbClass'];
        require_once ("$pearDbClass.php");
        
        // Query DB
        $options = array ('persistent'=>0);
		if ($pearDbClass == "DB") {
			$options['portability'] = DB_PORTABILITY_ALL;
		}
		
		// init DB class
        $db = new $pearDbClass;

        // Connect to DB       
        $dbh = $db->connect($dsn);
        if ($db->isError($dbh)) {
            PMCommon::db_logErrors($dbh);
            die();
        }
        
        // Execute query 
        $res = $dbh->query($sql);
        if ($db->isError($res)) {
            PMCommon::db_logErrors($res);
            die();
        }
        
        
        // Create output JSON
        $json = "{";
        
        $rc = 0;
        while ($row = $res->fetchRow()) {
            $sep = $rc > 0 ? "," : "";
            $k = $encoding != "UTF-8" ? iconv($encoding, "UTF-8", $row[0]) : $row[0];
            $v = $encoding != "UTF-8" ? iconv($encoding, "UTF-8", $row[1]) : $row[1];
            // avoid <, >, ...
            $v = htmlentities($v, ENT_COMPAT, 'UTF-8');
            $json .= "$sep \"$k\":" . "\"". addslashes(trim($v)) . "\"";
            $rc++;
        }
        
        $json .=  "}";
        
        //error_log($json);
        pm_logDebug(3, $json, "search.php->getOptionsFromDb()->json");
        
        return $json;
    }
    
    /**
     * Get the option values from a CVS file
     */
    protected function getOptionsFromCSV($csvfile, $separator, $encoding, $sort)
    {
        if (!is_file($csvfile)) {
            error_log("P.MAPPER ERROR: File $csvfile not existing.");
            return false;
        } 
        
        $fh = fopen($csvfile, "r");
        while (($row = fgetcsv($fh, 0, $separator)) !== FALSE) {
            $k = trim($row[0]);
            $v = $encoding != "UTF-8" ? iconv($encoding, "UTF-8", $row[1]) : $row[1];
            $ol[$k] = addslashes(trim($v));
            // avoid <, >, ...
            $ol[$k] = htmlentities($ol[$k], ENT_COMPAT, 'UTF-8');
        }
        //print_r($ol);
        fclose($fh);
        
        $json = $this->options_array2json($ol, $sort);
        //error_log($json);
        return $json;
    }
    
    /**
     * Get the option values from a dBase file
     */
    protected function getOptionsFromDbase($dbasefile, $encoding, $keyfield, $showfield, $sort)
    {
        // Load dbf extension on Windows if needed
        if (PHP_OS == "WINNT" || PHP_OS == "WIN32") {
            if (!extension_loaded('dBase')) {
                if( function_exists( "dl" ) ) {
                    dl("php_dbase.dll");
                } else {
                    error_log("P.MAPPER ERROR: This version of PHP does support the 'dl()' function. Please enable dBase extension in your php.ini");
                    return false;
                }
            }
        }
        
        // Check if dbase functions exist; if not stop
        if(!function_exists( "dbase_open" ) ) {
            error_log("P.MAPPER ERROR: This version of PHP does support dBase functions. Please use anothet configuration type");
            return false;
        }
        
        if (!is_file($dbasefile)) {
            error_log("P.MAPPER ERROR: File $dbasefile not existing.");
            return false;
        }
        
        $dbf = dbase_open($dbasefile, 0);
        if (!$dbf) error_log("P.MAPPER ERROR: dBase file $dbf_file could not be opened");
        
        $record_numbers = dbase_numrecords($dbf);
        
        for ($i = 1; $i <= $record_numbers; $i++) {
            $row = dbase_get_record_with_names($dbf, $i);
            $k = $encoding != "UTF-8" ? iconv($encoding, "UTF-8", trim($row[$keyfield])) : trim($row[$keyfield]);
            $v = $encoding != "UTF-8" ? iconv($encoding, "UTF-8", $row[$showfield]) : $row[$showfield];

            //if (strlen(trim($k)) > 0) 
            $ol[$k] = trim($v); //addslashes($v);
            // avoid <, >, ...
            $ol[$k] = htmlentities($ol[$k], ENT_COMPAT, 'UTF-8');
        }
        dbase_close($dbf);
        
        $json = $this->options_array2json($ol, $sort);
        
        //error_log($json);
        return $json;
    }
    
    
    /**
     * Get the option values from a MapServer layer
     */
    protected function getOptionsFromMS($mslayer, $encoding, $keyfield, $showfield, $sort)
    {        
        $qLayer = $this->map->getLayerByName($mslayer);
        
        $query = $qLayer->queryByAttributes($keyfield, "/()/", MS_MULTIPLE);
        if ($query == MS_SUCCESS) {
            $qLayer->open();
            $numResults = $qLayer->getNumResults();
            //error_log($numResults);
            for ($iRes=0; $iRes < $numResults; $iRes++) {
                $qRes = $qLayer->getResult($iRes);
                $qShape = PMCommon::resultGetShape($this->msVersion, $qLayer, $qRes);  // changed for compatibility with PG layers and MS >= 5.6
                $k = $encoding != "UTF-8" ? iconv($encoding, "UTF-8", $qShape->values[$keyfield]) : $qShape->values[$keyfield];
                $v = $encoding != "UTF-8" ? iconv($encoding, "UTF-8", $qShape->values[$showfield]) : $qShape->values[$showfield];
                $ol[$k] = trim($v);
                // avoid <, >, ...
                $ol[$k] = htmlentities($ol[$k], ENT_COMPAT, 'UTF-8');
                PMCommon::freeMsObj($qShape);
            }
            $json = $this->options_array2json($ol, $sort);
            return $json;
        }
        
    }
  
    
    
    protected function options_array2json($array, $sort)
    {
        $uarray = array_unique($array);
        
        if ($sort == "asc") {
            natsort($uarray);
        } elseif ($sort == "desc") {
            arsort($uarray);
        }
        
        $json = '{';
        $rc = 0;
        foreach ($uarray as $k=>$v) {
            $sep = $rc > 0 ? "," : "";
            //$json .= "$sep \"" . $k . "\":" . "\"". addslashes($v) . "\"";
            $json .= "$sep \"" . $this->escapeForJson($k) . "\":" . "\"". $this->escapeForJson($v) . "\"";
            $rc++;
        }
        $json .= '}';

        return $json;
    }
    
    protected function escapeForJson($str)
    {
        $aSearch = array('\\', '"');
        $aReplace = array('\\\\', '\"');                               
        return str_replace( $aSearch, $aReplace, $str );
    }



    protected function getDataFilePath($inpath)
    {
        $config_dir = $_SESSION['PM_CONFIG_DIR'];
        
        if ($inpath{0} == "/" || $inpath{1} == ":") {
            $retPath = $inpath;
        } else {
            if ($inpath{0} == "$") {
                if ($this->dataRoot{0} == "/" || $this->dataRoot{1} == ":") {
                    $retPath = $this->dataRoot . substr($inpath, 1);
                } else {
                    $retPath = str_replace('\\', '/', realpath($config_dir . "/" . $this->dataRoot)) . substr($inpath, 1);
                }
                
            } else {
                $retPath = str_replace('\\', '/', realpath($config_dir . "/" . $inpath));
            }
        }
        //error_log($retPath);
        pm_logDebug(3, $retPath, "Data path for XML search \nfile: " . __FILE__ . "\nfunction: " . __FUNCTION__);
        
        return $retPath;
    }
    
}



?>
