<?php
/******************************************************************************
 *
 * Purpose: Map Utilities
 * Author:  Thomas Raffin, SIRAP
 *
 ******************************************************************************
 *
 * Copyright (c) 2009 SIRAP
 *
 * This is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version. See the COPYING file.
 *
 * This software is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with p.mapper; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 ******************************************************************************/

//require_once(dirname(__FILE__) . '/easyMDB2.inc.php');

class PluginsMapUtils
{

	public static function calculateExtent($map, $groups, $applyMapExtentIfNull = false, $addBuffer = false) {
		$mapExtent = ms_newrectObj();
		
		$layers = getLayersByGroupOrLayerName($map, $groups);
		foreach ($layers as $layer) {
			$layerExtent = false;

			if ($layer) {
				if ($layer->connectiontype == MS_POSTGIS 
//				|| $layer->connectiontype == MS_MYGIS 
				|| $layer->connectiontype == MS_ORACLESPATIAL) {
					//get dns string containing : type of database, user name, password, host and database.
					$dsn = PluginsMapUtils::getDSNConnection($layer);
					if ($dsn) {
						// code from mapserver mailing list (by Armin Burger):
						// get table and filter :
						$data = PluginsMapUtils::getQueryParamsFromDataString($layer, false, false);
						$mapLayerFilter = $data['mapLayerFilter'];
						$fromTable = $data['fromTable'];
						$geomFld = $data['geomFld'];
						
						$sql = 'SELECT ST_xmin(extent) AS minx, ST_ymin(extent) AS miny, ST_xmax(extent) AS maxx, ST_ymax(extent) AS maxy ';  
						$sql .= "FROM (SELECT St_Extent($geomFld) AS extent FROM $fromTable ";
						$sql .= ($mapLayerFilter ? "WHERE $mapLayerFilter" : '') . ' ';
						$sql .= ') AS bar';
						$sql .= ' WHERE extent IS NOT NULL';
pm_logDebug(4, "calculateExtent - sql:$sql");
					    // DB:
						require_once(dirname(__FILE__) . '/easyMDB2.inc.php');
						$edb = new Easy_MDB2;
						$edb->setDSN($dsn);
						$edb->start();
						
					 	$qresultE = $edb->selectByQuery($sql, '');
					 	
					 	$resValues = $qresultE['values'][0];
					 	if ($resValues) {
					 		$resIsValid = true;
					 		foreach ($resValues as $val) {
					 			if (!isset($val) || $val == 0 || $val == -1) {
					 				$resIsValid = false;
					 				break;
					 			}
					 		}
					 		if ($resIsValid) {
					 			$layerExtent = ms_newRectObj();
					 			$layerExtent->setExtent($resValues['minx'], $resValues['miny'], $resValues['maxx'], $resValues['maxy']);
					 		}
					 	}
	
					}
				} else if ($layer->type != MS_LAYER_RASTER && ($layer->connectiontype == MS_SHAPEFILE || $layer->connectiontype == MS_TILED_SHAPEFILE || $layer->connectiontype == MS_OGR)) { // SHP layer, OGR layer
					$layerExtent = @$layer->getExtent();
	        	}
				
				if ($layerExtent) {
					// change projection
					$mapProjStr = $map->getProjection();
					$layerProjStr = $layer->getProjection();
					if ($mapProjStr && $layerProjStr && $mapProjStr != $layerProjStr) {
						if ($_SESSION['MS_VERSION'] < 6) {
							$mapProjObj = ms_newprojectionobj($mapProjStr);
							$layerProjObj = ms_newprojectionobj($layerProjStr);
						} else {
							$mapProjObj = new projectionObj($mapProjStr);
							$layerProjObj = new projectionObj($layerProjStr);
						}
						$layerExtent->project($layerProjObj, $mapProjObj);
					}
					// add buffer around freshly calculated extent
					if ($addBuffer) {
						$minx = $layerExtent->minx;
			        	$miny = $layerExtent->miny;
			        	$maxx = $layerExtent->maxx;
			        	$maxy = $layerExtent->maxy;
			        	
			        	
						$pointBuffer = isset($_SESSION['pointBuffer']) ? $_SESSION['pointBuffer'] : 50;
						$shapeQueryBuffer = isset($_SESSION['shapeQueryBuffer']) ? $_SESSION['shapeQueryBuffer'] : 0.01;

						$buf = 0;
						if ($layer->type == MS_LAYER_POINT || $layer->type == MS_LAYER_ANNOTATION) {
							$buf = $pointBuffer;        // set buffer depending on dimensions of your coordinate system
						} else if (isset($shapeQueryBuffer) && $shapeQueryBuffer > 0) {
							$buf = $shapeQueryBuffer * ((($maxx - $minx) + ($maxy - $miny)) / 2);
						}
						if ($buf > 0) {
							$minx -= $buf;
							$miny -= $buf;
							$maxx += $buf;
							$maxy += $buf;
							$layerExtent->setExtent($minx, $miny, $maxx, $maxy);
						}
					}

					if ( ($mapExtent->minx == -1) && ($mapExtent->miny == -1) && ($mapExtent->maxx == -1) && ($mapExtent->maxy == -1) ) {
			        	$minx = $layerExtent->minx;
			        	$miny = $layerExtent->miny;
			        	$maxx = $layerExtent->maxx;
			        	$maxy = $layerExtent->maxy;
			       } else {
			        	$minx = ($layerExtent->minx < $mapExtent->minx) ? $layerExtent->minx : $mapExtent->minx;
			        	$miny = ($layerExtent->miny < $mapExtent->miny) ? $layerExtent->miny : $mapExtent->miny;
			        	$maxx = ($layerExtent->maxx > $mapExtent->maxx) ? $layerExtent->maxx : $mapExtent->maxx;
			        	$maxy = ($layerExtent->maxy > $mapExtent->maxy) ? $layerExtent->maxy: $mapExtent->maxy;
			       }
			       $mapExtent->setExtent($minx, $miny, $maxx, $maxy);
				}
        	}
		}
		if ($mapExtent->minx == -1 || $mapExtent->miny == -1 || $mapExtent->maxx == -1 || $mapExtent->maxy == -1) {
			$mapExtent = $map->extent;
			if (!$applyMapExtentIfNull) {
				$mapExtent->setExtent(0, 0, 1, 1);
			}
		}
		return $mapExtent;
	}
	
	
	public static function calculateSize($map, $extent, $maxHeight, $maxWidth, $minHeight, $minWidth, $stepHeight, $stepWidth) {
		$height = $maxHeight;
		$width = $maxWidth;

		// 1st pass: height

		// calculate new size (height):
		$map->setSize($width, $height);
		$height = $map->height; 
		if ($stepHeight > 0) {
			do {
				// next size:
				$height -= $stepHeight;
	
				// new map:
				$map->setExtent($extent->minx, $extent->miny, $extent->maxx, $extent->maxy);
				$map->setSize($width, $height);
			} while (PluginsMapUtils::isRectObjStrictlyIncludeIn($extent, $map->extent, false, true) && ($height > $minHeight));
			// previous value:
			$height += $stepHeight;
			$height = min($height, $maxHeight);
		}

		// 2d pass: width 

		// calculate new size (width):
		$map->setSize($width, $height);
		$width = $map->width; 
		if ($stepWidth > 0) {
			do {			
				// next size:
				$width -= $stepWidth;
	
				// new map:
				$map->setExtent($extent->minx, $extent->miny, $extent->maxx, $extent->maxy);
				$map->setSize($width, $height);
			} while (PluginsMapUtils::isRectObjStrictlyIncludeIn($extent, $map->extent, true, false) && ($width > $minWidth));
			// previous value:
			$width += $stepWidth;
			$width = min($width, $maxWidth);
		}
			
		return array('height' => $height, 'width' => $width);	
	}
	
	// return true only if 1st is strictly included in 2d :
	public static function isRectObjStrictlyIncludeIn($rectObj1, $rectObj2, $doX, $doY) {
		$ret = false;
		
		if ($doX) {
			$xres = false;
			if ($rectObj1->minx > $rectObj2->minx) {
				if ($rectObj1->maxx < $rectObj2->maxx) {
					$xres = true;
				}
			}
		} else {
			$xres = true;
		}

		if ($doY) {
			$yres = false;
			if ($rectObj1->miny > $rectObj2->miny) {			
				if ($rectObj1->maxy < $rectObj2->maxy) {
					$yres = true;
				}
			}
		} else {
			$yres = true;
		}
	
		$ret = $xres && $yres;
		
		return $ret;
	}
	

	/**
	 * if not $PDOType return dsn string connection in this format : "dbtype://user_name:password@host:port/database"; 
	 * if $PDOType return array with user, password and dsn in this format : "dbtype:dbname=database host=host port=port";
	 */
	public static function getDSNConnection($msLayer, $PDOType = false) {
		$dsn = false;
		$user = '';
		$password = '';
		$host = '';
		$port = '';
		$dbname = '';
		$dbtype = '';
		
		$connectionType = $msLayer->connectiontype;
		
		switch($connectionType) {
			case MS_POSTGIS:
				//CONNECTION "user=clientssirap password=clientssirap dbname=ClientsSirap host=localhost port=5432"
				$connString = $msLayer->connection;
				$listParamsConn = preg_split("/\s/", $connString);
				$tabParams = Array();
				
				foreach ($listParamsConn as $param) {
					$tmp = explode("=", $param);
					$tabParams[$tmp[0]] = $tmp[1];
				}
				
				$user = $tabParams['user'];
				$password = $tabParams['password'];
				$dbname = $tabParams['dbname'];
				$host = isset($tabParams['host']) ? $tabParams['host'] : 'localhost';
				$port = isset($tabParams['port']) ? $tabParams['port'] : '5432';
				$dbtype = 'pgsql';
				
				if ($PDOType === true) {
					$dsn = array();
					$dsn['user'] = $user;
					$dsn['password'] = $password;
					$dsn['dsn'] = "$dbtype:dbname=$dbname host=$host port=$port";
				} else {
					$dsn = "$dbtype://$user:$password@$host";
					if ($port){
						$dsn .= ":$port";
					}
					$dsn .= "/$dbname";
				}
				break;
/*			
//			case MS_MYGIS:
			case MS_OGR:
				//CONNECTION "MySQL:test,user=root,password=mysql,port=3306"
				$connString = $msLayer->connection;
				$tabParams1 = explode(':', $connString);
				if ($tabParams1 && isset($tabParams1[1])) {
					$host = $tabParams1[0];
					if (strcasecmp($host, 'MySQL') == 0) {
						$tabParams2 = explode(',', $tabParams1[1]);
						if ($tabParams2 && isset($tabParams2[2])) {
							foreach ($tabParams2 as $param) {
								$tabParam = explode('=', $param);
								if ($tabParam) {
									if (isset($tabParam[1])) {
										if (strcasecmp($tabParam[1], 'user') == 0) {
											$user = $tabParam[1];
										} else if (strcasecmp($tabParam[1], 'password') == 0) {
											$password = $tabParam[1];
										} else if (strcasecmp($tabParam[1], 'port') == 0) {
											$port = $tabParam[1];
										}
									} else {
										$dbname = $tabParam[0];
									}
								}
							}
						}
						if ($dbname && $user) {
							//$dbtype = 'mysql';	//MySQL <= 4.0
							$dbtype = 'mysqli '; //MySQL >= 4.1

							$dsn = "$dbtype://$user:$password@$host";
							if ($port){
								$dsn .= ":$port";
							}
							$dsn .= "/$dbname";
						}
					}
				}				
				break;
*/
			case MS_ORACLESPATIAL:	
				//CONNECTION : "usr/pwd@db"
				$connString = $msLayer->connection;
				$listParamsConn = preg_split('/\//', $connString);
				
				$passAndBd = preg_split('/@/', $test[1]);
				
				$user = $listParamsConn[0];
				$password = $passAndBd[0];
				$dbname = $passAndBd[1];
				$dbtype = 'oci8'; //Oracle 7/8/9
				$host = 'localhost'; //par défaut
				$port = false; // TODO 
				
				
				if ($PDOType === true) {
					$dsn = array();
					$dsn['user'] = $user;
					$dsn['password'] = $password;
					$dsn['dsn'] = "$dbtype:dbname=$dbname host=$host";
					if ($port) {
						$dsn['dsn'] .= " port=$port";
					}
				} else {
					$dsn = "$dbtype://$user:$password@$host";
					if ($port){
						$dsn .= ":$port";
					}				
					$dsn .= "/$dbname";
				}
				
				break;
				
				/*
				 * case :
				 * MS_INLINE, MS_SHAPEFILE, MS_TILED_SHAPEFILE
				 * MS_SDE, MS_OGR, MS_WMS, MS_WFS, MS_GRATICULE, MS_RASTER  
				 */
				
			default:
				break;
		}
		return $dsn;
	}	
	
	
	/**
	 * return an associative array :
	 * - geomFld: geometry field
	 * - fromTable: table name
	 * - mapLayerFilter: filter defined for this layer
	 * - gidFld: PG unique field
	 * - db_table: "from talbe_name"
	 * - srid: "using srid=" (-1 if not specified)
	 */
	public static function getQueryParamsFromDataString($layer, $doDataSubstitution, $strictTableName = false) {
		
		$data = '';
		// data substitution:
		if ($doDataSubstitution) {
			$data = $layer->getMetaData("PM_RESULT_DATASUBSTITION");
			$data = trim($data);
		} 
		if (!$data) {	
			$data = trim($layer->data);
		}
		$dataList1 = preg_split("/\s/", $data); 
		$dataList2 = preg_split("/using/i", $data); 

		$geomFld = array_shift($dataList1);

		$tmp = preg_split("/using unique/i", $data);
		$tmp = trim($tmp[1]);
		$tmp = preg_split("/\s/", $tmp);
		$gidFld = $tmp[0];
		
		$srid = "-1";
		$tmp = preg_split("/using srid=/i", $data);
		if (count($tmp) == 2) {
			$tmp = trim($tmp[1]);
			$tmp = preg_split("/\s/", $tmp);
			$srid = $tmp[0];
		}
		
		$db_table = $dataList1[1];

		$fromTableTmp = trim(substr($dataList2[0], strlen($geomFld))); //string witch contains "from table_name"
		$fromTableTmp = substr($fromTableTmp, strpos($fromTableTmp, ' '));
		
		// Avoid alias and subqueries:
		if ($strictTableName) {
			$fromTableTmp = preg_split("/ as /i", $fromTableTmp);
			$fromTableTmp = $fromTableTmp[0];
		} 
		$fromTable = trim($fromTableTmp);
		
		$mapLayerFilter = $layer->getFilterString();
		
		$queryParams = Array();
		$queryParams['geomFld'] = $geomFld;
		$queryParams['fromTable'] = $fromTable;
		$queryParams['mapLayerFilter'] = $mapLayerFilter;
		$queryParams['gidFld'] = $gidFld;
		$queryParams['db_table'] = $db_table;
		$queryParams['srid'] = $srid;

		return $queryParams;
	}
	
	/**
	 * 
	 * Decode object fields values
	 * 
	 * @param ms MapObj $map mapfile
	 * @param string $layerName Layer's name
	 * @param string $valIn value to decode
	 * @param string $charset string output charset (false -> $_SESSION['defCharset'])
	 * 
	 * Attention : ne pas utiliser cette fonction si les données viennent de MDB2 (ou PDO ?)
	 */
	public static function decodeLayerVal($map, $layerName, $valIn, $charset = false) {
		$valOut = $valIn;
		
		if (!$charset) {
			$charset = $_SESSION['defCharset'];
		}
		
		if ($layer = $map->getLayerByName($layerName)) {
			
			// use layer encoding
			if ($layerEncoding = trim($layer->getMetaData('LAYER_ENCODING'))) {
				if ($layerEncoding != $charset) {
	            	$valOut = iconv($layerEncoding, $charset, $valIn);
				}
			
			// use same encoding as mapfile
	        } else {
	        	$valOut = PluginsMapUtils::decodeMapFileVal($map, $valIn);
	        }
		}
		
		return $valOut;
	}
	

	/**
	 * 
	 * Decode Mapfile values (= text in mapfile like "DESCRIPTION")
	 * 
	 * @param ms MapObj $map mapfile
	 * @param string $valIn value to decode
	 * @param string $charset string output charset (false -> $_SESSION['defCharset'])
	 * 
	 * Attention : ne pas utiliser cette fonction si les données viennent de MDB2 (ou PDO ?)
	 */
	public static function decodeMapFileVal($map, $valIn, $charset = false) {
		$valOut = $valIn;
		
		if (!$charset) {
			$charset = $_SESSION['defCharset'];
		}
		
        $map2unicode = $_SESSION['map2unicode'];
		if ($map2unicode) {
			$mapfile_encoding = trim($map->getMetaData('MAPFILE_ENCODING'));
			if (!$mapfile_encoding) {
				$mapfile_encoding = 'ISO-8859-1';
			}
			if ($mapfile_encoding != $charset) {
            	$valOut = iconv($mapfile_encoding, $charset, $valIn);
			}
		}   
		
		return $valOut;
	}
	
	/**
     * check for mapfile encoding and decode strings accordingly
     * @param string $inString value in UTF8
     * @param string $outString string output charset
     */

	public static function utf8ToMapCharset($map, $inString) {
        $map2unicode = $_SESSION['map2unicode'];
		if ($map2unicode) {
	    	$mapfile_encoding = trim($map->getMetaData('MAPFILE_ENCODING'));
			if ($mapfile_encoding) {
	           if ($mapfile_encoding != 'UTF-8') {
					$outString = iconv('UTF-8', $mapfile_encoding, $inString);
	            } else {
		        	$outString = $inString;
	            }
			} else {
	        	$outString = utf8_decode($inString);
			}
        } else {
            $outString = $inString;
        }
		
		return $outString;
    }
}


?>