<?php
/******************************************************************************
 *
 * Purpose: suggest (auto-complete) functions for search 
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
 
class Suggest
{
    protected $search;
    protected $sjson;
    protected $defCharset;
	protected $resdelimiter;
	protected $map;
    protected $msVersion;

    public function __construct($map, $search, $searchitem, $fldname, $request)
    {
        $this->search = $search;
        $this->resdelimiter = "\n";

		$this->map = $map;
        
        $suggestList = $_SESSION['suggestList'];
        $searchParams = $suggestList[$searchitem][$fldname];
        $searchType = $searchParams['type'];

		// many dependfields
        $dependflds = explode(',', $searchParams['dependfld']);
        $dependFields = array();
        foreach ($dependflds as $fieldName) {
        	$nameTmp = "dependfldval_$fieldName";
        	if (isset($request[$nameTmp])) {
        		$dependFields[$fieldName] = trim($request[$nameTmp]);
        	}
        }
        unset($searchParams['dependfld']);
        unset($searchParams['dependfldval']);
        $searchParams['dependFields'] = $dependFields;        
        
        $this->defCharset = $_SESSION['defCharset'];
        $this->msVersion = $_SESSION['MS_VERSION'];
        
        //pm_logDebug(3, $request, "request");
        pm_logDebug(3, $searchParams, "Parameters for attribute search file: suggest.php function: __construct");
        
        switch ($searchType) {
            case "db":
                $this->sjson = $this->dbSuggestMatch($searchParams);
                break;
            
            case "txt":
                $this->sjson = $this->txtSuggestMatch($searchParams);
                break;
                
            case "dbase":
                $this->sjson = $this->dbaseSuggestMatch($searchParams);
                break;
                
            case "ms":
                $this->sjson = $this->msSuggestMatch($searchParams);
                break;
        }
    }

    /**
     * Return JSON string from suggest query
     *
     * @return string
     */
    public function returnJson()
    {
        return $this->sjson;
    }
    
    /**
     * Convert input array into a (sorted or not) JSON string
     *
     * @return string
     */
    protected function suggest_array2json($array_in, $sort="asc")
    {
        
        $array = array_unique($array_in);
	
        if ($sort == "asc") {
            sort($array);
        } elseif ($sort == "desc") {
            rsort($array);
        }
        
        $ret = implode($this->resdelimiter, $array);
        
        return $ret;
    }
    

    /**
     * Get Suggest records from a database
     *
     * @return string
     */
    private function dbSuggestMatch($searchParams)
    {
        $dsn       = $searchParams['dsn'];
        $encoding  = $searchParams['encoding'];

		// many dependfields
        //$search    = ($encoding != "UTF-8" ? iconv("UTF-8", $encoding, $this->search) : $this->search);
        $search = $this->search;
        $dependFields = isset($searchParams['dependFields']) && $searchParams['dependFields'] ? $searchParams['dependFields'] : array();
        $sql = str_replace('[search]', $search, $searchParams['sql']);
        foreach ($dependFields as $fieldName => $fieldValue) {
       		$sql = str_replace(array('[dependfldval]', "[dependfldval_$fieldName]"), trim($fieldValue), $sql);
        }
        
        // Perform requests in case of dependency defined
        if ($dependFields) {
        	$sql = str_replace(array("{", "}"), "", $sql);
        } else {
        	// remove {...} part if exists in SQL string
        	$sql = preg_replace("/(\{)([\w\d\s\='%_\(\)\*~])+(\})/", "", $sql);
        }
        pm_logDebug(3, $sql); 
        
        // Load PEAR class
        $pearDbClass = $_SESSION['pearDbClass'];    
        require_once ("$pearDbClass.php");
        
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
        
		$ret = '';
        if ($res->numRows() > 0) {
            $rc = 0;
            while ($row = $res->fetchRow()) {
                //$ret .= $row[0] . "\n";
                $sep = $rc > 0 ? $this->resdelimiter : "";
                $suggest = ($encoding != "UTF-8" ? iconv($encoding, "UTF-8", $row[0]) : $row[0]) ;
                //$ret .= "$sep" . addslashes(str_replace("\n", "", $suggest)) ;
                $ret .= "$sep" . str_replace("\n", "", $suggest) ;

                $rc++;
            }     
        } else {
			$ret = 0;
        }
	
		return $ret;
    }
    
    /**
     * Get Suggest records from a text file
     *
     * @return string
     */
    private function txtSuggestMatch($searchParams)
    {
        $txtfile   = $searchParams['txtfile'];   
        $separator = $searchParams['separator']; 
        $encoding  = $searchParams['encoding'];
        $sort      = $searchParams['sort'];
        $regexleft = $searchParams['regexleft'];
        $startleft = $searchParams['startleft'];

        if ($encoding != $this->defCharset) {
            $search = iconv($this->defCharset, $encoding, $this->search);
        } else {
            $search = $this->search;
        }
        
        $left = $startleft ? "^" : "";
        
        $infile = file($txtfile);
        foreach ($infile as $r) {
            $pattern = "$left$regexleft($search)";
            if (preg_match("/$pattern/i", $r)) {
            //if (preg_match("/$left$this->search/i", $r)) {
                //$resArray[] = trim(addslashes($r));
                $resArray[] = trim($r);
            }
        }
        
        if (count($resArray) > 0) {
            $ret = $this->suggest_array2json($resArray, $sort);
            //error_log($ret);
            return $ret;
        } else {
            return 0;
        }
    }
    
    
    /**
     * Get Suggest records from a dBase file 
     *
     * @return string
     */
    private function dbaseSuggestMatch($searchParams)
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
        
        $dbasefile   = $searchParams['dbasefile'];   
        $encoding    = $searchParams['encoding'];
        $searchfield = $searchParams['searchfield'];
        $sort        = $searchParams['sort'];
        $regexleft   = $searchParams['regexleft'];
        $startleft   = $searchParams['startleft'];
        // many dependfields
        $dependFields = isset($searchParams['dependFields']) && $searchParams['dependFields'] ? $searchParams['dependFields'] : array();
        
        pm_logDebug(3, $searchParams, "suggest.php->dBase"); 
        
        if ($encoding != $this->defCharset) {
            $search = iconv($this->defCharset, $encoding, stripslashes($this->search));
            //$search = $this->search;
            //$search = stripslashes($this->search);
        } else {
            //$search = utf8_encode($this->search);
            //$search = $this->search;
            $search = stripslashes($this->search);
        }
        
        $left = $startleft ? "^" : "";
        
        $dbf = dbase_open($dbasefile, 0);
        if (!$dbf) error_log("ERROR: dBase file $dbf_file not found");
        
        $record_numbers = dbase_numrecords($dbf);
        
        
        for ($i = 1; $i <= $record_numbers; $i++) {
            $row = dbase_get_record_with_names($dbf, $i);
            $fldValue = $row[$searchfield];
            
            // Check for dependval and berak out of inner loop and continues 
			// many dependfields
            $keepThisRow = true;
            if ($dependFields) {
            	foreach ($dependFields as $fieldName => $fieldValue) {
            		$depFldName = "dependfldval_$fieldName";
            		$depFldValue = $searchParams[$depFldName];
            		$depFldValue = trim($depFldValue);
            		//error_log("$depFldValue\n");
            		// only in case dependval ist not the * from the select box
            		if ($depFldValue != '') {
            			if ($depFldValue != trim($row[$depFldName])) {
            				$keepThisRow = false;
            				break;
            			}
            		}
            	}
            }
            if (!$keepThisRow) {
            	continue;
            }
            
            $pattern = "$left$regexleft($search)";
            //error_log($pattern);
            if (preg_match("/$pattern/i", $fldValue)) {
                if ($encoding != $this->defCharset) {
                    $fldValue = iconv($encoding, $this->defCharset, $fldValue);
                }
                //error_log($fldValue);
                //$resArray[] = trim(addslashes($fldValue));
                $resArray[] = trim($fldValue);
                $rc++;
            }
        }
        dbase_close($dbf);
        
        if (count($resArray) > 0) {
            $ret = $this->suggest_array2json($resArray, $sort);
            return $ret;
        } else {
            return 0;
        }
    }
    
    /**
     * Get Suggest records from a MS layer using generic MapScript attribute query 
     *
     * @return string
     */
    private function msSuggestMatch($searchParams)
    {
        $msVersion = $_SESSION['msVersion']; 
        if (!extension_loaded('MapScript')) {
            dl("php_mapscript$msVersion." . PHP_SHLIB_SUFFIX);
        }
        
        $map = $this->map;
        
        pm_logDebug(3, $searchParams, "suggest.php->msSuggestMatch()");
        
        $mslayer     = $searchParams['mslayer'];   
        $searchfield = $searchParams['searchfield'];
        $sort        = $searchParams['sort'];
        $encoding    = $searchParams['encoding'];
        $regexleft   = $searchParams['regexleft'];
        $startleft   = $searchParams['startleft'];
		// many dependfields
        $dependFields = isset($searchParams['dependFields']) && $searchParams['dependFields'] ? $searchParams['dependFields'] : array();
        
        $left = $startleft ? "^" : "";
        
        $qLayer = $map->getLayerByName($mslayer);
        $qs = "";
    
        // Create search string
		// many dependfields

        $qsDependFieldsParts = array();
        if ($dependFields) {
        	foreach ($dependFields as $fieldName => $fieldValue) {
        		$depFldName = "dependfldval_$fieldName";
        		$depFldValue = $searchParams[$depFldName];
        		$depFldValue = trim($depFldValue);
        		
        		if ($depFldValue != '') {
        			$qsDependFieldsParts[] = "( \"[$fieldName]\" =~ /$depFldValue/ )";
        		}
            }
        }
        if ($qsDependFieldsParts) {
        	$qs .= ' (' . implode(' AND ', $qsDependFieldsParts) . ') AND ';
        }
            
        if (trim($searchParams['fieldtype']) == "s") {
            if ($encoding != $this->defCharset) {
                $this->search = iconv($this->defCharset, $encoding, $this->search);
            }
            $searchVal = get_magic_quotes_gpc() ? stripslashes($this->search) : $this->search;
            if ($this->msVersion >= 6) {
                $qs .=  "( \"[$searchfield]\" ~* \"$regexleft$left$searchVal\" )" ;
            } else {
                $val = preg_replace ("/\w/ie", "'('. strtoupper('$0') . '|' . strtolower('$0') .')'", $searchVal);
                $qs .=  "( \"[$searchfield]\" =~ /$regexleft$left$val/ )" ;
            }
        } else {
            $qs .=  "([$searchfield] = $this->search)" ;
        }
        
        if ($layFilter = $qLayer->getFilterString()) {
            $querystring = "( ($layFilter) AND ($qs) )"; 
        } else {
            $querystring = "( $qs )"; 
        }
        pm_logDebug(3, $querystring, "MS suggest query string, suggest.php/msSuggestMatch()");

        // Query layer
        $query = @$qLayer->queryByAttributes($searchfield, $querystring, MS_MULTIPLE);
        if ($query == MS_SUCCESS) {
            $qLayer->open();
            $numResults = $qLayer->getNumResults();
            
            // Return query results
            for ($iRes=0; $iRes < $numResults; $iRes++) {
                $qRes = $qLayer->getResult($iRes);
                $qShape = PMCommon::resultGetShape($this->msVersion, $qLayer, $qRes);  // changed for compatibility with PG layers and MS >= 5.6
                $fldValue = $qShape->values[$searchfield];
                if ($encoding != $this->defCharset) {
                    $fldValue = iconv($encoding, $this->defCharset, $fldValue);
                }
                $resArray[] = trim($fldValue);
                PMCommon::freeMsObj($qShape);
            }
            $ret = $this->suggest_array2json($resArray, $sort);
            return $ret;
            
        } else {
            return 0;
        }
    }
    
    

}


?>