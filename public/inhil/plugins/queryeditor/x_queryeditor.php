<?php

/******************************************************************************
 *
 * Purpose: AJAX server part of the query editor plugin for p.mapper
 * Author:  Thomas Raffin, SIRAP
 *
 ******************************************************************************
 *
 * Copyright (c) 2007 SIRAP
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
// prevent XSS
if (isset($_REQUEST['_SESSION'])) exit();

require_once('../../incphp/group.php');

require_once('../../incphp/pmsession.php');

if (!isset($_SESSION['queryeditor_activated']) || !$_SESSION['queryeditor_activated']) {
	exit();
}

require_once($_SESSION['PM_INCPHP'] . '/common.php');
require_once($_SESSION['PM_INCPHP'] . '/globals.php');
require_once($_SESSION['PM_INCPHP'] . '/query/squery.php');
require_once($_SESSION['PM_INCPHP'] . '/query/search.php');
require_once(dirname(__FILE__) . '/../common/groupsAndLayers.php');
require_once(dirname(__FILE__) . '/../common/selectTools.inc.php');
require_once($_SESSION['PM_PLUGIN_REALPATH'] . '/common/pluginsMapUtils.inc.php');
require_once($_SESSION['PM_PLUGIN_REALPATH'] . '/common/easyMDB2.inc.php');

require_once(dirname(__FILE__) . '/queryeditor.php');

$operation = $_REQUEST['operation'];

header("Content-type: text/plain; charset=$defCharset");

// Old selection
$jsonPMResult = isset($_SESSION['JSON_Results']) ? $_SESSION['JSON_Results'] : false;
$selectMethode = isset($_REQUEST['selectMethode']) ? $_REQUEST['selectMethode'] : '';

$continue = false;
$layerName = $_REQUEST['layername'];
$groups = queryeditorGetGroups($map);
if (count($groups) >= 0) {
	if (array_key_exists($layerName, $groups)) {
		$continue = true;
	}
}

if ($continue) {
    // Request = ask for fields list
    if ($operation == 'getattributes') {
    	$attributes = getAttributsRealAndReadNames($layerName);

    	$attributesRealNames = $attributes['valuesToUse'];
    	$attributesReadNames = $attributes['valuesToShow'];

		$result = array();
		$numAttributes = min(count($attributesRealNames), count($attributesReadNames));
    	for ($iAttribute = 0 ; $iAttribute < $numAttributes ; $iAttribute++) {
    		$attribute = array();
    		$attribute['field'] = $attributesRealNames[$iAttribute];
    		$attribute['header'] = $attributesReadNames[$iAttribute];
    		$result[] = $attribute;
    	}
    	echo "{\"attributes\":" . json_encode($result) . "}";
    
	// Request = ask for field type
    } else if ($operation == 'getattributetype') {
    	$attributeType = '';
    	$attributeName = $_REQUEST['attributename'];
		
    	if ($attributeName && $layerName) {
    		$msLayers = getLayersByGroupOrLayerName($map, $layerName);
	    	if ($msLayers && count($msLayers) > 0) {
	    		$msLayer = $msLayers[0];
	    		if ($msLayer &&
	    		($msLayer->connectiontype == MS_POSTGIS
//				|| $msLayer->connectiontype == MS_MYGIS
	    		|| $msLayer->connectiontype == MS_ORACLESPATIAL)) {
	    			// get dns string containing : type of database, user name, password, host and database :
	    			$dsn = PluginsMapUtils::getDSNConnection($msLayer);
	    			if ($dsn) {
	    				// data substitution :
	    				$data = PluginsMapUtils::getQueryParamsFromDataString($msLayer, false, true);
	    					
	    				// DB :
	    				$edb = new Easy_MDB2;
	    				$edb->setDSN($dsn);
	    				$edb->start();
	
	    				// Query :
	    				$db_table = $data['db_table'];
	    				$mdb2FieldType = $edb->getFieldType($db_table, $attributeName);

	    				if (!$mdb2FieldType) {
	    					$data = PluginsMapUtils::getQueryParamsFromDataString($msLayer, true, true);
	    					$db_table = $data['db_table'];
	    					$mdb2FieldType = $edb->getFieldType($db_table, $attributeName);
	    				}

	    				switch ($mdb2FieldType) {
	    					case 'integer':
	    					case 'decimal':
	    					case 'float':
	    					case 'boolean':
	    						$attributeType = 'N';
	    						break;
	
	    					case 'text':
	    					case 'date':
	    					case 'time':
	    					case 'timestamp':
	    					case 'blob':
	    					case 'clob':
	    						$attributeType = 'S';
	    						break;
	
	    					default:
	    						break;
	    				}
	
	    				// Close connection :
	    				$edb->end();
	    			}
	    		}
	    	}
    	}

   		echo "{\"attributeType\":\"$attributeType\"}";
    	 
	// Request = execute query
    } else if ($operation == 'query') {
    
    	$_REQUEST['externalSearchDefinition'] = true;
    	$_REQUEST['mode'] = 'search';
    
    	
    	$mapLayers = getLayersByGroupOrLayerName($map, $layerName);
    
    	if ($mapLayers && $mapLayers[0]) {
    		$queryResult = '';
    		// Query received from the editor without modification:
    		$originalQuery = $_REQUEST['query'];
    		
    		foreach ($mapLayers as $mapLayer) {
    			$_REQUEST['layerName'] = $mapLayer->name;
        		$layerType = $mapLayer->connectiontype;
        		// Query to execute:
        		$modifiedQuery = '';
        		// Query with the real fields names instead of headers:
        		$modifiedQueryWithRealNames = $originalQuery;
        		$attributes = getAttributsRealAndReadNames($layerName);
        		$attributesRealNames = $attributes['valuesToUse'];
        		$attributesReadNames = $attributes['valuesToShow'];

        		$numAttributes = min(count($attributesRealNames), count($attributesReadNames));
        		for ($iAttribute = 0 ; $iAttribute < $numAttributes ; $iAttribute++) {
        			$valueToShow = addslashes($attributesReadNames[$iAttribute]);
        			$valueToUse = $attributesRealNames[$iAttribute];
        			$modifiedQueryWithRealNames = str_replace("[$valueToShow]", "[$valueToUse]", $modifiedQueryWithRealNames);
        			$valueToShow = $attributesReadNames[$iAttribute];
        			$modifiedQueryWithRealNames = str_replace("[$valueToShow]", "[$valueToUse]", $modifiedQueryWithRealNames);
        		}
        		// First field :
        		if (preg_match("/\[([^\]]*)\]/", $modifiedQueryWithRealNames, $attributes)) {
        			$firstFld = $attributes[1];
        		} else {
        			$firstFld = '';
        		}
        		// end of lines :
        		$modifiedQueryWithoutEOL = str_replace("\n", ' ', $modifiedQueryWithRealNames);
        		
        		// change encoding in query string
        		// Thanks to Siki Zoltan
        		$layerEncoding = $mapLayer->getMetaData('LAYER_ENCODING');
        		if ($layerEncoding && $layerEncoding != $_SESSION['defCharset']) {
					$modifiedQueryWithoutEOL = iconv($defCharset, $layerEncoding, $modifiedQueryWithoutEOL);
        		}
        		
        		// backslashes before quotes:
                if (get_magic_quotes_gpc()) {
        			$modifiedQueryWithoutEOL = stripslashes($modifiedQueryWithoutEOL);
        		}
            
        		// SHP :
        		if ($layerType == 1) {
        			$_REQUEST['layerType'] = 'shape';
        			$_REQUEST['fldName'] = $firstFld;
        			// simple quotes
//        			$modifiedQueryWithoutEOL = str_replace("\\'", "'", $modifiedQueryWithoutEOL);
        			// strings and numbers:
					// Some part corrected by Sylvain Arabeyre
					$modifiedQueryTmp = preg_replace("/([^\[]*)\[([^\]]*)\]\s*([^\s]*)\s*'([^']*[^\\\])'/", "$1 \"[$2]\" $3 '^$4\$'", $modifiedQueryWithoutEOL);
					// Corrected by Sylvain Arabeyre
					$modifiedQueryTmp = str_replace(" ILIKE ", " ~* ", $modifiedQueryTmp);
        			$modifiedQueryTmp = str_replace(" LIKE ", " ~ ", $modifiedQueryTmp);
        			$modifiedQueryTmp = str_replace("'^%", "'", $modifiedQueryTmp);
        			$modifiedQueryTmp = str_replace("%$'", "'", $modifiedQueryTmp);
	          		$modifiedQueryTmp = str_replace("%", ".*", $modifiedQueryTmp);
        			$modifiedQueryTmp = str_replace("<>", "!=", $modifiedQueryTmp);
        			if ($modifiedQueryTmp) {
        				$modifiedQuery = "(($modifiedQueryTmp))";
        			}

					// filter:
/*
					$mapLayerFilter = $mapLayer->getFilterString();
					if ($mapLayerFilter) {
						$mapLayerFilterItem =  $mapLayer->filteritem;
					if ($mapLayerFilter[0] =='/') {
						$operator = '=~';
					} else {
						$operator = '=';
					}
						$modifiedQuery = "((\"[$mapLayerFilterItem]\" $operator $mapLayerFilter) AND ($modifiedQuery) )";
					}
*/

        		// PostGIS :
        		} else if ($layerType == 6) {
        			$_REQUEST['layerType'] = 'postgis';
        			$_REQUEST['firstFld'] = $firstFld;
        			// strings only:
//        			$modifiedQueryTmp = preg_replace("/([^\[]*)\[([^\]]*)\]\s*([^\s]*)\s*'(.*[^\\\])'/", "$1 $2 $3 /'$4'/", $modifiedQueryWithoutEOL);
//        			$modifiedQueryTmp = preg_replace("/([^\[]*)\[([^\]]*)\]\s*([^\s]*)\s*'([^']*[^\\\])'/", "$1 $2 $3 /'$4'/", $modifiedQueryWithoutEOL);
        			$modifiedQueryTmp = preg_replace("/([^\[]*)\[([^\]]*)\]\s*([^\s]*)\s*'([^']*[^\\\])'/", "$1 $2 $3 '$4'", $modifiedQueryWithoutEOL);

        			// simple quotes
        			$modifiedQueryTmp = str_replace("\\'", "''", $modifiedQueryTmp);
        			// numbers only:
        			$modifiedQueryTmp = preg_replace("/\[([^\]]*)\]/", "$1", $modifiedQueryTmp);
/*
        			$modifiedQueryTmp = str_replace(" LIKE ", " ~ ", $modifiedQueryTmp);
	          		$modifiedQueryTmp = str_replace(" ILIKE ", " ~* ", $modifiedQueryTmp);
//        			$modifiedQueryTmp = str_replace(" LIKE ", " ~* ", $modifiedQueryTmp);
        			$modifiedQueryTmp = str_replace("/(?<!\\)'%", "'", $modifiedQueryTmp);
        			$modifiedQueryTmp = str_replace("%(?<!\\)'/", "'", $modifiedQueryTmp);
        			$modifiedQueryTmp = str_replace("/(?<!\\)'", "'^", $modifiedQueryTmp);
        			$modifiedQueryTmp = str_replace("(?<!\\)'/", "$'", $modifiedQueryTmp);
//        			$modifiedQueryTmp = str_replace("%", ".*", $modifiedQueryTmp);
*/
        			if ($modifiedQueryTmp) {
        				$modifiedQuery = $modifiedQueryTmp;
        			}

					// filter:
        			$mapLayerFilter = $mapLayer->getFilterString();
        			if ($mapLayerFilter) {
        				$modifiedQuery = "($mapLayerFilter) AND ($modifiedQuery)";
        			}
        		}
        		
        		// Execute query :
        		if ($modifiedQuery) {
					$_REQUEST["qStr"] = $modifiedQuery;
        			$mapQuery = new Query($map);
        			$mapQuery->q_processQuery();
					$resTmp = $mapQuery->q_returnQueryResult();
					if ($resTmp) {
						if ($queryResult) {
							$queryResult = SelectTools::add($queryResult, $resTmp);
						} else {
							$queryResult = $resTmp;
						}
					}
        			//$numResultsTotal = $mapQuery->q_returnNumResultsTotal();
        		}
    		}

    		$queryResult = SelectTools::mixSelection($selectMethode, $jsonPMResult, $queryResult);

    		if (!$queryResult) {
    			$queryResult = 0;
				unset($_SESSION['JSON_Results']);
				unset($_SESSION['resultlayers']);
    		} else {
    			// update selection
    			$_SESSION['JSON_Results'] = $queryResult;
    			// highlight results
    			SelectTools::updateHighlightJson($queryResult);
    		}
    		echo "{\"mode\":\"$mode\", \"queryResult\":$queryResult}";
	   } else {
	       die(_p('No results!'));
        }
    } else {
        die(_p('Query editor plugin not activated'));
    }
} else {
	die(_p('Query editor plugin not activated'));
	exit();
}

//exit();
?>