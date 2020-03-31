<?php

/******************************************************************************
 *
 * Purpose: Initialize application settings and write settings to PHP session
 *          Create legend icons if the map file has been modified
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


class Init_map
{
    // Class variables
    protected $map;
    protected $mapFile;
    protected $ini;
    protected $gLanguage;
    protected $jsReference;
    protected $cssReference;
    protected $jsInitFunctions;
    
    
    public function __construct($map, $mapFile, $ini, $gLanguage)
    {
        $this->map = $map;
        $this->mapFile = $mapFile;
        $this->ini = $ini;
        $this->gLanguage = $gLanguage;
    }
    
   /**
    * Initialize all parameters calling all local functions
    */
    public function initAllParameters()
    {
        $this->_initExtParams();
        $this->sectionToSession($this->ini['pmapper']);
        $this->sectionToSession($this->ini['query']);
        $this->sectionToSession($this->ini['ui']);
        $this->sectionToSession($this->ini['locale']);
        $this->sectionToSession($this->ini['download']);
        $this->sectionToSession($this->ini['php']);
        $this->_initConfig();
        $this->_initMap();
        $this->_initPrint();
        $this->_initImageFormats();

        $this->_initLegend();
        $this->_initDataPath();
        $this->jsReference = $this->initJSReference();
        $this->jsCustomReference = $this->initJSCustomReference();
        $this->cssReference = $this->initCSSReference(); 
        $this->jsInitFunctions = $this->_initPlugins();
        
        //pm_logDebug(3, $this->ini, "INI hash");
    }
    
    /**
     * Add single ini parameter to PHP session
     */
    private function iniToSession($k, $v)
    {
        $_SESSION[$k] = $v;
    }

    /**
     * Add full ini section to PHP session
     */
    private function sectionToSession($section)
    {
        if (is_array($section)) {
            foreach ($section as $k=>$v) {
                $this->iniToSession($k, $v);
            }
        }
    }
    
    
    /**
     * Get external parameters via URL (eg from links)
     */    
    private function _initExtParams()
    {
        if (isset($_REQUEST['me'])) {
            $ext = explode(',', $_REQUEST['me']);
            $_SESSION['zoom_extparams'] = $ext;
        }
        
        if (isset($_REQUEST['up'])) {
            //$_SESSION['ul'] = $_REQUEST['ul'];
            $pointList = explode("@@@", $_REQUEST['up']);
            foreach($pointList as $p) {
                $upnt = explode("@@", $p);
                $url_points[] = $upnt;
            }
            $_SESSION['url_points'] = $url_points;
        }
        
        if (isset($this->ini['map']['mapZoomToExtent'])) {
            $zExt = $this->ini['map']['mapZoomToExtent'];
            $_SESSION['zoom_extparams'] = $zExt['bounds'];
            
            if ((bool)$zExt['maxExtent']) {
                $_SESSION['mapMaxExt'] = array (
                    'minx'=>$zExt['bounds'][0],
                    'miny'=>$zExt['bounds'][1],
                    'maxx'=>$zExt['bounds'][2],
                    'maxy'=>$zExt['bounds'][3]
                );
            }
        }
        
        $_SESSION['geo_scale'] = 0;
        $_SESSION['historyBack'] = array();
        $_SESSION['historyFwd']  = array();    
    }
    
    /**
     * Initilaize 'map' section from config
     */
    private function _initMap()
    {
        $iniMap = $this->ini['map'];
        
        $this->iniToSession("layerAutoRefresh", $iniMap['layerAutoRefresh']);
        $this->iniToSession("sliderMax", $iniMap['sliderMax']);
        $this->iniToSession("sliderMin", $iniMap['sliderMin']);

        //*** Categories
        $categories = array();
        $categoriesClosed = array();
        
		// categories defined
		if (isset($iniMap['categories']) && isset($iniMap['categories']['category'])) {
			$iniCat = $iniMap['categories']['category'];
			if (array_key_exists('name', $iniCat)) {
				$descr = $iniCat['description'] ? $iniCat['description'] : $iniCat['name'];
				$categories[$iniCat['name']] = array("description"=>$descr, "groups"=>(array)$iniCat['group']);
			} else {
				foreach ($iniCat as $cat) {
					$descr = array_key_exists('description', $cat) ? $cat['description'] : $cat['name'];
					$categories[$cat['name']] = array("description"=>$descr, "groups"=>(array)$cat['group']);
                    // "closed = false" --> not closed!
                    if (array_key_exists('closed', $cat) && $cat['closed'] === "true") $categoriesClosed[] = $cat['name'];
				}
            }
        }

        $_SESSION['categories'] = $categories;
        $_SESSION['categoriesClosed'] = $categoriesClosed;
        
        //*** AllGroups
        $mapGrps = $this->map->getAllGroupNames();
        $mapLays = $this->map->getAllLayerNames();
        $GrpLay  = array_merge($mapGrps, $mapLays);
        if (isset($iniMap["allGroups"])) {
			$allGroups = (array)$iniMap['allGroups']['group'];
        } else {
            $allGroups = $this->map->getAllGroupNames();
            foreach ($mapLays as $ml) {
                if (!$this->map->getLayerByName($ml)->group) {
                    $allGroups[] = $ml ;
                }
            }
        }
        $_SESSION['allGroups'] = $allGroups;
        $_SESSION['allGroups0'] = $allGroups;
        
        
        //*** default groups, visible at start
        // without definition, ALL groups will be set visible   
        
        // Check if layers are set externally via URL
        if (isset($_REQUEST['dg'])) {
            $defGroupsGET = explode(',', $_REQUEST['dg']);
            $defGroups = array();
            foreach ($defGroupsGET as $gG) {
                if (in_array($gG, $allGroups)) {
                    $defGroups[] = $gG;
                }
            }
            // if no valid layers supplied, take first from ini
            if (count($defGroups) < 1) $defGroups = array($allGroups[0]); 
        
        // Else take them from config settings 
        } elseif (isset($iniMap['defGroups'])) {
            // only groups available in defGroups and categories
            $defGroups = array();

            // defGroups defined in config settings
            $defGroupsConfig = isset($iniMap['defGroups']['group']) ? (array)$iniMap['defGroups']['group'] : array();

            // keep only those available in categories
            foreach ($defGroupsConfig as $g) {
                if (!in_array($g, $GrpLay )) {
                   pm_logDebug(0, "P.MAPPER-ERROR: Layer/Group '$g' not existing. Check 'config_{$_SESSION['config']}.xml' file definition for section 'map.defGroups'.");
                } else {
                	$defGroups[] = $g;
                }
            }
        // Else take all
        } else {
            $defGroups = array(); //$allGroups;
        }
        $_SESSION['defGroups'] = $defGroups;
        
        
        //** autoidentifygropus: layer where to apply auto_indentify() function ***/
        if (isset($iniMap['autoIdentifyGroups'])) {
            $autoIdentifyGroups = (array)$iniMap['autoIdentifyGroups']['group'];
            // Check for errors
            foreach ($autoIdentifyGroups as $g) {
               if (!in_array($g, $GrpLay) ) {
                   pm_logDebug(1, "P.MAPPER-ERROR: Layer/Group '$g' not existing. Check 'config_{$_SESSION['config']}.xml' file definition for section 'map.autoIdentifyGroups'.");
               }
            }
            $_SESSION['autoIdentifyGroups'] = $autoIdentifyGroups;
        }
        
        /*** LAYERS DISABLING EACH OTHER ***/
        if (isset($iniMap['mutualDisableList'])) {
            $mutualDisableList = (array)$iniMap['mutualDisableList']['group']; 
            foreach ($mutualDisableList as $mg) {
                if (!in_array($mg, $allGroups )) {
                    pm_logDebug(1, "P.MAPPER-ERROR: Layer/Group '$mg' not existing. Check 'config_{$_SESSION['config']}.xml' file definition for section 'map.mutualDisableList'.");
                    $mutualDisableList = array(); 
                }
            }
        } else {
            $mutualDisableList = array(); 
        }
        $_SESSION['mutualDisableList'] = $mutualDisableList; 
        
        
        /*** LAYERS CAUSING MAP TO SWITCH TO ALTERNATIVE IMAGE FORMAT ***/
        if (isset($iniMap['altImgFormatLayers'])) {
            $altImgFormatLayers = (array)$iniMap['altImgFormatLayers']['layer']; 
            foreach ($altImgFormatLayers as $mg) {
                if (! @$mapLayer = $this->map->getLayerByName($mg)) {
                    pm_logDebug(0, "P.MAPPER-ERROR: Layer/Group '$mg' not existing. Check 'config_{$_SESSION['config']}.xml' file definition for section 'map.altImgFormatLayers'.", 0);
                }
            }
        } else {
            $altImgFormatLayers = 0; 
        }
        $_SESSION['altImgFormatLayers'] = $altImgFormatLayers;
                
        
        /*** Specify GROUP objects ***/
        require_once(PM_INCPHP . "/initgroups.php");
        $iG = new Init_groups($this->map, $allGroups, $this->gLanguage, $this->ini); 
        $iG->createGroupList();    
        
    }

    /**
     * Check INI settings for errors
     */    
    private function _initConfig()
    {

        /*** Test if resolution tag is set ***/
        if ($this->map->resolution != "96") {
            pm_logDebug(1, "P.MAPPER-ERROR: RESOLUTION tag not set to 96. This value is needed for proper function of PDF print.");
        }

        /*** LAYERS ***/        
        // Test for groups with blanks
        $gList = $this->map->getAllGroupNames();
        foreach ($gList as $gr) {
            if (preg_match('/\s/', $gr)) {
                pm_logDebug(0, "P.MAPPER-ERROR: Group '$gr' defined in the map file has blanks in its name. This is not possible for the p.mapper application. Remove blanks or substitute with e.g. '_'.");
            }
        }
        // Test for layers with blanks
        $gList = $this->map->getAllLayerNames();
        foreach ($gList as $ly) {
            if (preg_match('/\s/', $ly)) {
                pm_logDebug (0,"P.MAPPER-ERROR: Layer '$ly' defined in the map file has blanks in its name. This is not possible for the p.mapper application. Remove blanks or substitute with e.g. '_'.");
            }
        }
   
        
        /*** USER AGENT (Browser type) ***/
        $ua = $_SERVER["HTTP_USER_AGENT"]; 
        if (preg_match("/gecko/i", $ua)) {
            $_SESSION['userAgent'] = "mozilla";
        } elseif (preg_match("/opera/i", $ua)) {
            $_SESSION['userAgent'] = "opera";
        } elseif (preg_match("/MSIE/i", $ua)) {
            $_SESSION['userAgent'] = "ie";
        }        
        
        
        $_SESSION['web_imagepath'] = str_replace('\\', '/', $this->map->web->imagepath);
        $_SESSION['web_imageurl']  = str_replace('\\', '/', $this->map->web->imageurl);
        
        $ms_Version = ms_GetVersion();
        $msvL = explode('.', substr($ms_Version, strpos($ms_Version, "version") + 8, 5));
        $_SESSION['MS_VERSION'] = (float)("{$msvL[0]}.{$msvL[1]}{$msvL[2]}");
    
        // MS >= 6
        if ($_SESSION['MS_VERSION'] >= 6) {
        	if ($this->map->defresolution != "96") {
        		pm_logDebug(1, "P.MAPPER-ERROR: DEFRESOLUTION tag not set to 96. This value is needed for proper function of PDF print.");
        	}
        }
    }
    
    /**
     * Initilaize 'print' section from config
     */
    private function _initPrint()
    {  
        $this->iniToSession("pdfres", $this->ini['print']['pdfres']);
        $this->iniToSession("pdfversion", $this->ini['print']['pdfversion']);
    }
    
    /**
     * Image formats for map and print
     */
    private function _initImageFormats()
    {
        $iniMap = $this->ini['map'];
        $imgFormat = $iniMap['imgFormat'];
        $_SESSION['imgFormatExt'] = $this->getImgFormatExt($imgFormat);
        $_SESSION['imgFormat'] = $imgFormat;
        
        $altImgFormat = $iniMap['altImgFormat'];
        $_SESSION['altImgFormatExt'] = $this->getImgFormatExt($altImgFormat);
        $_SESSION['altImgFormat'] = $altImgFormat;
        
        $iniPrint = $this->ini['print'];
        $printImgFormat = isset($iniPrint['printImgFormat']) ? $iniPrint['printImgFormat'] : $imgFormat;
        $_SESSION["printImgFormatExt"] = $this->getImgFormatExt($printImgFormat);
        $_SESSION["printImgFormat"] = $printImgFormat;
        
        $printAltImgFormat = isset($iniPrint['altPrintImgFormat']) ? $iniPrint['altPrintImgFormat'] : $altImgFormat;
        $_SESSION["printAltImgFormatExt"] = $this->getImgFormatExt($printAltImgFormat);
        $_SESSION["printAltImgFormat"] = $printAltImgFormat;
    }
    
    /**
     * Return image file extension for selected image format
     */
    private function getImgFormatExt($imgFormat)
    {
        $this->map->selectOutputFormat($imgFormat);
        $selectedFormat = $this->map->outputformat;
        return $selectedFormat->extension;
    }
    

    /**
     * Settings for legend/TOC
     */
    private function _initLegend()
    {                
        /*** WRITES LEGEND ICONS ***/
        
        // Check if images have to be created
        // => if map file is newer than last written log file
        $writeNew = 0;
        
        $pwd = str_replace('\\', '/', getcwd());
        $legPath = "$pwd/images/legend/";
        $imgLogFile = $legPath.'createimg.log';
        
        if (!is_file($imgLogFile)) {
            $writeNew = 1;
        } else {
            $mapfile_mtime = filemtime($this->mapFile);
            $imglogfile_mtime = filemtime($imgLogFile);
            if ($mapfile_mtime > $imglogfile_mtime) {
                $writeNew = 1;
            }
        }
        
        //$writeNew = 1;
        // If necessary re-create legend icons
        if ($writeNew == 1) {
            $this->createLegendList();
        }
    }
    
    /**
     * Create all legend icons and group/layer 
     */ 
    public function createLegendList()
    {
        
        $legPath = $_SESSION['PM_BASE_DIR'] . "/images/legend/";
        $imgLogFile = $legPath.'createimg.log';
        
        $this->map->selectOutputFormat($_SESSION["imgFormat"]);
        $allLayers = $this->map->getAllLayerNames();        

        // Define background image for legend icons
        //$icoBGLayer = ms_newLayerObj($this->map);
        //$icoBGLayer->set("type", 2);
        // Add class
        /*$bgClass = ms_newClassObj($icoBGLayer);
        $bgClStyle = ms_newStyleObj($bgClass);
        $bgClStyle->color->setRGB(255, 255, 255);
        $bgClStyle->outlinecolor->setRGB(180, 180, 180);*/
        //return false;
        foreach ($allLayers as $layName) {
            $qLayer = $this->map->getLayerByName($layName);
            
            // All layers but RASTER or ANNOTATION layers        
            $numclasses = $qLayer->numclasses;
            if ($numclasses > 0) {
                $clno = 0;
                for ($cl=0; $cl < $numclasses; $cl++) {
                    $class = $qLayer->getClass($cl);
                    if (!$class->keyimage) {
                        $clname = ($numclasses < 2 ? "" : $class->name);
                        $clStyle = ms_newStyleObj($class);
                        
                        // Set outline for line themes to background color
                        if ($qLayer->type == 1) {
                           #$clStyle->setcolor("outlinecolor", 180, 180, 180);
                           #$clStyle->outlinecolor->setRGB(180, 180, 180);
                        }
                        // set outline to main color if no outline defined (workaround for a bug in the icon creation)
                        if ($qLayer->type == 2) {
                            if ($clStyle->outlinecolor->red == -1 || $clStyle->outlinecolor->green == -1 || $clStyle->outlinecolor->blue == -1) {
                               #$clStyle->setcolor("outlinecolor", $clStyle->color);
                               $clStyle->outlinecolor->setRGB($clStyle->color->red, $clStyle->color->green, $clStyle->color->blue);
                            }
                        }

						// bad legend icon (due to symbolscaledenom, unit or attribute size/width binding)
//						if ($qLayer->type == MS_LAYER_POINT) {
							// find max size for all styles
							$maxSymbolSize = 0;
							$maxSymbolWidth = 0;
							$numStyles = $class->numstyles;
							for ($iStyle = 0 ; $iStyle < $numStyles ; $iStyle++) {
								$style = $class->getStyle($iStyle);
								if ($style->size > $maxSymbolSize) {
									$maxSymbolSize = $style->size;
								}
								if ($style->width > $maxSymbolWidth) {
									$maxSymbolWidth = $style->width;
								}
							}
							
							// reduce all symbol size and set minsize and maxsize to the same value than size
							for ($iStyle = 0 ; $iStyle < $numStyles ; $iStyle++) {
								$style = $class->getStyle($iStyle);
								
								// Size:

								// attribute binding:
								$changeSize = false;
								$newSize = 0;
								if ($style->size == -1) {
									// line widthout size --> MS use symbol defined size, but it's not the good way to do...
									if ($qLayer->type == MS_LAYER_LINE) {
										$newSize = 1;
										$changeSize = true;
									} else if ($qLayer->type == MS_LAYER_POLYGON) {
										$newSize = $_SESSION["icoH"] / 0.8 / 4;
										$changeSize = true;
									} else {
//										$newSize = $_SESSION["icoH"] / 0.8;
										$changeSize = false;
									}
								// reduce size
								} else if ($maxSymbolSize > $_SESSION["icoH"]) {
									$newSize = $style->size * $_SESSION["icoH"] / $maxSymbolSize;
									$changeSize = true;
								}
								if ($changeSize) {
									$style->set('size', $newSize);
								}
								
								// set minsize and maxsize to size
								$style->set('minsize', $style->size);
								$style->set('maxsize', $style->size);

								// Width:

								// attribute binding:
								$changeWidth = false;
								$newWidth = 0;
								// bad width for MS > 6
								if ($_SESSION['MS_VERSION'] >= 6) {
									if ($qLayer->type == MS_LAYER_LINE) {
										$maxLineWidth = 4; // max width in pixel for legend
										$widthLimitFactor = $_SESSION["icoH"] / $maxLineWidth;
										if ($maxSymbolWidth > $widthLimitFactor) {
											$maxSymbolWidth = $maxSymbolWidth * $widthLimitFactor;
										}
									}
								}
								if ($style->width == -1) {
//									$newWidth = $_SESSION["icoH"];
									$changeWidth = false;
									if ($qLayer->type == MS_LAYER_POLYGON) {
										$newWidth = $_SESSION["icoH"] / $maxSymbolWidth;
										$changeWidth = true;
									}
								// reduce width
								} else if ($maxSymbolWidth > $_SESSION["icoH"]) {
									$newWidth = $style->width * $_SESSION["icoH"] / $maxSymbolWidth;
									$changeWidth = true;
								}
								if ($changeWidth) {
									$style->set('width', $newWidth);
								}
								
								// set minwidth and maxwidth to width
								$style->set('minwidth', $style->width);
								$style->set('maxwidth', $style->width);
							}

							// set size units in pixel :
							$qLayer->set('sizeunits', MS_PIXELS);
//						}

                        $icoImg = $class->createLegendIcon($_SESSION["icoW"], $_SESSION["icoH"]);  // needed for ms 3.7
                        $imgFile = $legPath.$layName.'_i'.$clno . '.'.$_SESSION["imgFormatExt"];
                        //error_log($imgFile);
                        
                        $icoUrl = $icoImg->saveImage($imgFile);
                        PMCommon::freeMsObj($icoImg);
                    // class->keyimage --> generate icon too
                    } else if (isset($_SESSION['legendKeyimageRewrite']) && $_SESSION['legendKeyimageRewrite'] == '1') {
                    	$imgFile = $legPath.$layName.'_i'.$clno . '.'.$_SESSION['imgFormatExt'];
                    	$keyimage = dirname($_SESSION['PM_MAP_FILE']) . '/' . $class->keyimage;

                    	if (file_exists($keyimage)) {
                    		if (extension_loaded('gd')) {
                    			// existing image:
                    			$imageInfo = getimagesize($keyimage);
      							$imageType = $imageInfo[2];
      							$imageWidth =  $imageInfo[0];
      							$imageHeight =  $imageInfo[1];
								if ($imageType == IMAGETYPE_JPEG) {
									$image = imagecreatefromjpeg($keyimage);
								} elseif($imageType == IMAGETYPE_GIF) {
									$image = imagecreatefromgif($keyimage);
								} elseif($imageType == IMAGETYPE_PNG) {
									$image = imagecreatefrompng($keyimage);
								}
                    			
								// new size:
								$wRedFactor = $imageWidth <= $_SESSION['icoW'] ? 1 : $_SESSION['icoW'] / $imageWidth;
								$hRedFactor = $imageHeight <= $_SESSION['icoH'] ? 1 : $_SESSION['icoH'] / $imageHeight;
								$redFactor = min(max($wRedFactor, $hRedFactor), 1);
                    			$newWidth = $imageWidth * $redFactor;
                    			$newHeight = $imageHeight * $redFactor;

                    			//new image:
                    			$newImage = imagecreatetruecolor($newWidth, $newHeight);
      							imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $imageWidth, $imageHeight);
      							$newFormat = strtolower($_SESSION['imgFormatExt']);
      							switch($newFormat) {
      								case 'png':
      									imagepng($newImage, $imgFile);
      									break;
      								case 'jpg':
      								case 'jpeg':
      									imagejpeg($newImage, $imgFile);
      									break;
      								case 'gif':
      									imagegif($newImage, $imgFile);
      									break;
      								default:
      									break;
      							}
                    		}
                    	}
                    }
                    if ($class->name) $clno++;
                }
            } else {
                //error_log("connType: " . $qLayer->connectiontype);
                if ($qLayer->connectiontype == 7) {
                    //$this->getWmsLegend($qLayer, $layName, $legPath);
                }
            }
        }
        
        date_default_timezone_set($_SESSION['defaultTimeZone']); // Required for PHP 5.3
        $today = getdate();
        $datestr =  $today['hours'].':'.$today['minutes'].':'.$today['seconds'].'; '.$today['mday'].'/'.$today['month'].'/'.$today['year'];
    
        $logStr = "Created legend icons newly on:  $datestr";
        $imgLogFileFH = fopen ($imgLogFile, 'w+');
        fwrite($imgLogFileFH, $logStr);
        fclose($imgLogFileFH);    
        
    }
    
    
    protected function getWmsLegend($qLayer, $layName, $legPath)
    {
        require_once(PM_INCPHP . "/map/wmsclient.php");
        $getWmsLegend = (bool)$qLayer->getMetaData("wms_show_legend");
        //error_log("getWmsLegend: $getWmsLegend");
        if ($getWmsLegend) {
            $onlineResource = $qLayer->connection;
            error_log($onlineResource);
            $wmsLayerName = $qLayer->getMetaData("wms_name");
            $wmsLayerList = explode(",", $wmsLayerName);
            
            // get URL for legend image
            $wmsClient = new WMSClient($onlineResource);
            $legendUrlList = $wmsClient->getLayerLegendParams($wmsLayerList[0]);
            if ($legendUrlList) {
                $formatList = explode("/", $legendUrlList['format']);
                $imgFile = $legPath.$layName.'_i.'. $formatList[1];
                
                // download image via URL
                $legendImgUrl = $legendUrlList['legendImgUrl'];
                if (strlen($legendImgUrl) > 0) {
                    $ch = curl_init($legendImgUrl);
                    //error_log("imgUrl: " . $legendUrlList['legendImgUrl']);
                    $fp = fopen($imgFile, "w");
                    curl_setopt($ch, CURLOPT_FILE, $fp);
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_setopt($ch, CURLOPT_BINARYTRANSFER, True);
                    curl_exec($ch);
                    curl_close($ch);
                    fclose($fp);
                }
            }
        }
    }
    
    
    
    /**
     * Get absolute data path from map file
     */ 
    function _initDataPath()
    {
        $shapepath = trim($this->map->shapepath);
        
        // absolute path in map file
        if (isset($shapepath{0}) && $shapepath{0} == '/' || isset($shapepath{1}) && $shapepath{1} == ':') {
            $_SESSION['datapath'] = str_replace('\\', '/', $shapepath);
        
        // relative path in map file, get absolute as combination of shapepath and map file location
        } else {
            $_SESSION['datapath'] = str_replace('\\', '/', realpath(dirname($_SESSION['PM_MAP_FILE']) . "/" . $shapepath) );
        }
    }
    
    
    /**
     * FUNCTION TO RETURN URL FOR MAPFRAME
     * used for starting application with pre-defined extent
     * extent read from shape features
     */
    function getMapInitURL($map, $zoomLayer, $zoomQuery) 
    {
        $qLayer = $this->map->getLayerByName($zoomLayer);
        
        // Query parameters
        $queryList = preg_split('/@/', $zoomQuery);
        $queryField = $queryList[0];
        $queryFieldType = $queryList[1];
        //$queryValue = "/^" . $queryList[2] ."$/";
        $queryValue = $queryList[2];
        $highlFeature = $queryList[3];
        $setMaxExtent = $queryList[4];
        
        // Modify filter for PostGIS & Oracle layers
        if ($qLayer->connectiontype == 6 || $qLayer->connectiontype == 8) {
            $q = $queryFieldType == 1 ? "'" : "";
            $queryValue = "$queryField = $q$queryValue$q";
            //error_log($queryValue);
        }
        
        // Query layer
        @$qLayer->queryByAttributes($queryField, $queryValue, MS_MULTIPLE);

        $numResults = $qLayer->getNumResults();
        $qLayer->open();
        
        // Return query results (SINGLE FEATURE): shape index and feature extent
        /*
        $qRes = $qLayer->getResult(0);
        $qShape = PMCommon::resultGetShape($_SESSION['MS_VERSION'], $qLayer, $qRes);
        $qShpIdx = $qShape->index;
        $qShpBounds = $qShape->bounds;
        */
        
        // Check if layer has different projection than map
        // if yes, re-project extent from layer to map projection 
        $mapProjStr = $this->map->getProjection();
        $qLayerProjStr = $qLayer->getProjection();
        if ($mapProjStr && $qLayerProjStr && $mapProjStr != $qLayerProjStr) {
        	if ($_SESSION['MS_VERSION'] < 6) {
        		$mapProjObj = ms_newprojectionobj($mapProjStr);
        		$qLayerProjObj = ms_newprojectionobj($qLayerProjStr);
        	} else {
        		$mapProjObj = new projectionObj($mapProjStr);
        		$qLayerProjObj = new projectionObj($qLayerProjStr);
        	}
            //$qShpBounds->project($this->qLayerProjObj, $this->mapProjObj);
            $reprojectShape = 1;
        }
        
        // initial max/min values
        $mExtMinx = 999999999;
        $mExtMiny = 999999999;
        $mExtMaxx = -999999999;
        $mExtMaxy = -999999999;
        
        // ABP: Store all shape indexes
        $qShpIdxArray = array();
        
        // Return query results: shape index and feature extent
        for ($iRes=0; $iRes < $numResults; $iRes++) {
            $qRes = $qLayer->getResult($iRes);
            $qShape = PMCommon::resultGetShape($_SESSION['MS_VERSION'], $qLayer, $qRes);
            $qShpIdx = $qShape->index;
            $qShpIdxArray[] = $qShpIdx;
            $qShpBounds = $qShape->bounds;
            if ($reprojectShape) {
                $qShpBounds->project($qLayerProjObj, $mapProjObj);
            }

            $shpMinx = $qShpBounds->minx;
            $shpMiny = $qShpBounds->miny;
            $shpMaxx = $qShpBounds->maxx;
            $shpMaxy = $qShpBounds->maxy;
            
            // Get max/min values of ALL features
            $mExtMinx = min($mExtMinx, $shpMinx);
            $mExtMiny = min($mExtMiny, $shpMiny);
            $mExtMaxx = max($mExtMaxx, $shpMaxx);
            $mExtMaxy = max($mExtMaxy, $shpMaxy);
        }
        
        // Apply buffer (in units of features)
        if ($qLayer->type == 0) {
            $buffer = $_SESSION["pointBuffer"];
        } else {
            $buffer = $_SESSION["shapeQueryBuffer"] * ((($mExtMaxx - $mExtMinx) + ($mExtMaxy - $mExtMiny)) / 2);
        }
        $mExtMinx -= $buffer;
        $mExtMiny -= $buffer;
        $mExtMaxx += $buffer;
        $mExtMaxy += $buffer;
        
        $roundFact = ($map->units != 5 ? 0 : 6); 
        $shpMinx = round($mExtMinx, $roundFact);
        $shpMiny = round($mExtMiny, $roundFact);
        $shpMaxx = round($mExtMaxx, $roundFact);
        $shpMaxy = round($mExtMaxy, $roundFact);

        // no object found --> extent = mapExtent
		if (!$qShpIdxArray) {
			$mapExtent = $this->map->extent;
			$shpMinx = $mapExtent->minx;
			$shpMiny = $mapExtent->miny;
			$shpMaxx = $mapExtent->maxx;
			$shpMaxy = $mapExtent->maxy;
			$highlFeature = false;
			$setMaxExtent = false;
		}
                        
        $ext = array ($shpMinx, $shpMiny, $shpMaxx, $shpMaxy);
        $_SESSION['zoom_extparams'] = $ext;
        
        // Set Max Extent for map
        if ($setMaxExtent) {
            $mapMaxExt['minx'] = $shpMinx;
            $mapMaxExt['miny'] = $shpMiny;
            $mapMaxExt['maxx'] = $shpMaxx;
            $mapMaxExt['maxy'] = $shpMaxy;
            
            $_SESSION['mapMaxExt'] = $mapMaxExt;
        }

        // Add highlight feature if defined in URL parameters
        if ($highlFeature) { 
            $resultlayers[$zoomLayer] = $qShpIdxArray;
            $_SESSION["resultlayers"] = $resultlayers;
        }
        
        // Return URL
        $searchString = "&mode=map&zoom_type=zoomextent&extent=" . $shpMinx ."+". $shpMiny ."+". $shpMaxx ."+". $shpMaxy . ($highlFeature ? "&resultlayer=$zoomLayer+$qShpIdx" : ""); 
        $mapInitURL = "map.phtml?$searchString";
        
        return $mapInitURL;
    }
    
    
    /**
     * Calculate max scale for slider max settings (JS variable s1)
     * works only for units dd or meters
     */
    public function returnMaxScale($map, $mapheight)
    {
        $initExtent = $this->map->extent;
        $y_dgeo = $initExtent->maxy - $initExtent->miny;
        $scrRes = $this->map->resolution;
        $this->mapUnits = $this->map->units;
        
        $y_dgeo_m = ($this->mapUnits == 5 ? $y_dgeo * 111120 : $y_dgeo);
        $maxScale = ($y_dgeo_m / $mapheight) / (0.0254 / $scrRes);
        
        return round($maxScale);
    }
    
    public function returnXYGeoDimensions()
    {
        //$initExtent = $this->map->extent;
        if (isset($_SESSION['mapMaxExt'])) {
            $me = $_SESSION['mapMaxExt'];
            $initExtent = ms_newrectObj();
            $initExtent->setExtent($me["minx"],$me["miny"],$me["maxx"],$me["maxy"]);
        } else {
            $initExtent = $this->map->extent;
        }
        
        $dgeo['x'] = $initExtent->maxx - $initExtent->minx;
        $dgeo['y'] = $initExtent->maxy - $initExtent->miny;
        $dgeo['c'] = $this->map->units == 5 ? 111120 : 1;

        return $dgeo;
    }

    /**
     * Get JS file references
     */
    protected function initJSReference()
    {
        $jsReference = "";
                 
        //- from jQuery dir
        $jQueryDir = PM_JAVASCRIPT_REALPATH ."/jquery";
        if (file_exists($jQueryDir)) {
            $jqueryFiles = PMCommon::scandirByExt($jQueryDir, "js");
            sort($jqueryFiles);
            foreach ($jqueryFiles as $jqf) {
                $jsReference .= " <script type=\"text/javascript\" src=\"". PM_JAVASCRIPT ."/jquery/$jqf\"></script>\n";
            }
        }
        
        //- from main JS dir
        $jsFiles = PMCommon::scandirByExt(PM_JAVASCRIPT_REALPATH, "js");
        sort($jsFiles);
        foreach ($jsFiles as $jsf) {
            $jsReference .= " <script type=\"text/javascript\" src=\"". PM_JAVASCRIPT ."/$jsf\"></script>\n";
        }
        
        //- from plugins
        /*
        $plugin_jsFileList = $_SESSION['plugin_jsFileList'];
        if (count($plugin_jsFileList) > 0) {
            foreach ($plugin_jsFileList as $pf) {
                $jsReference .= " <script type=\"text/javascript\" src=\"$pf\"></script>\n";
            }
        }
        */
        
        //- from JS OPTIONAL dir
        if ($js_optional = $_SESSION['PM_JAVASCRIPT_OPTIONAL']) {
            foreach ($js_optional as $dir) {
                $jsFiles = PMCommon::scandirByExt(PM_JAVASCRIPT_REALPATH . "/$dir", "js");
                sort($jsFiles);
                foreach ($jsFiles as $jsf) {
                    $jsReference .= " <script type=\"text/javascript\" src=\"". PM_JAVASCRIPT ."/$dir/$jsf\"></script>\n";
                }
            }
        }

        
        return $jsReference;
    }
    
    
    
    /**
     * Get JS custom file references from application config directory
     */
    protected function initJSCustomReference()
    {
        $jsReference = "";
                 

        $dirNames = array();
        //- from config/common (custom) dir
        $dirNames[] = PM_CONFIG_LOCATION_COMMON;
        //- from config dir
        $dirNames[] = PM_CONFIG_LOCATION;
        foreach($dirNames as $dirName) {
        	$dirTmp = PM_BASECONFIG_DIR . "/$dirName";
        	if (file_exists($dirTmp)) {
        		$customJSFiles = PMCommon::scandirByExt($dirTmp, 'js');
        		$customJSFiles = array_unique($customJSFiles);
        		if (count($customJSFiles) > 0) {
        			foreach ($customJSFiles as $cf) {
        				$jsReference .= " <script type=\"text/javascript\" src=\"config/$dirName/$cf\"></script>\n";
        			}
        		}
        	}
        }
        
        return $jsReference;
    }
    
    
    
    
    /**
     * Get CSS file references
     */
    private function initCSSReference()
    {
        $cssReference = "";
		$regExpForUrlPath = '/(url\(["\']?)([^\/\)][^\)]*["\']?\))/';
        $plugin_cssFileList = $_SESSION['plugin_cssFileList'];
        if (count($plugin_cssFileList) > 0) {
			$mergeCSS = isset($_SESSION['pm_merge_css_plugins_files']) && $_SESSION['pm_merge_css_plugins_files'] == 1;
			if ($mergeCSS) {
				$cssReference .= "<style type=\"text/css\">\n";
			}
            foreach ($plugin_cssFileList as $pf) {
				if (!$mergeCSS) {
                	$cssReference .= " <link rel=\"stylesheet\" href=\"$pf\" type=\"text/css\" />\n";
				} else {
	            	$tmpFile = $_SESSION['PM_BASE_DIR'] . "/$pf";
					if (file_exists($tmpFile) && is_file($tmpFile)) {
						ob_start();
						include $tmpFile;
						$cssReferenceTmp = ob_get_contents();
						// change path for images:
						$path = dirname($pf) . '/';
						$cssReferenceTmp = preg_replace($regExpForUrlPath, "$1$path$2", $cssReferenceTmp);
						$cssReference .= $cssReferenceTmp;
						$cssReference .= "\n";
						ob_end_clean();
					}
				}
            }
			if ($mergeCSS) {
				$cssReference .= "</style>\n";
			}
        }

        $dirNames = array();
        //- from config/common (custom) dir
        $dirNames[] = PM_CONFIG_LOCATION_COMMON;
        //- from config dir
        $dirNames[] = PM_CONFIG_LOCATION;
        $dirNames = array_unique($dirNames);
        
        $mergeCSS = isset($_SESSION['pm_merge_css_config_files']) && $_SESSION['pm_merge_css_config_files'] == 1;
        $cssReferenceFiles = array();
        foreach ($dirNames as $dirName) {
        	$dirTmp = PM_BASECONFIG_DIR . "/$dirName";
        	if (file_exists($dirTmp)) {
        		$cssFiles = PMCommon::scandirByExt($dirTmp, 'css');
        		$cssFiles = array_unique($cssFiles);
        		foreach ($cssFiles as $cf) {
        			$cssReferenceFiles[] = "config/$dirName/$cf";
        		}
        	}
        }
		$cssReferenceFiles = array_unique($cssReferenceFiles);
		
		if ($mergeCSS && count($cssReferenceFiles) > 0) {
			$cssReference .= "<style type=\"text/css\">\n";
		}
		foreach ($cssReferenceFiles as $cssReferenceFile) {
			if ($mergeCSS) {			
				$tmpFile = $_SESSION['PM_BASE_DIR'] . "/$cssReferenceFile";
				if (file_exists($tmpFile) && is_file($tmpFile)) {
					ob_start();
					include $tmpFile;
					$cssReferenceTmp = ob_get_contents();
					// change path for images:
					$path = dirname($cssReferenceFile) . '/';
					$cssReferenceTmp = preg_replace($regExpForUrlPath, "$1$path$2", $cssReferenceTmp);
					$cssReference .= $cssReferenceTmp;
					$cssReference .= "\n";
					ob_end_clean();
				}
			} else {
				$cssReference .= " <link rel=\"stylesheet\" href=\"$cssReferenceFile\" type=\"text/css\" />\n";
			}
		}
		if ($mergeCSS && count($cssReferenceFiles) > 0) {
			$cssReference .= "</style>\n";
		}
		
        return $cssReference;
    }
    
    /**
     * Initialize all plugins
     */
    private function _initPlugins()
    {
        // Modified by Thomas RAFFIN (SIRAP)
        // only load configuration for activated plugins
        $_SESSION['pluginsConfig'] = array();
    	$plugins = (array)$this->ini['pmapper']['plugins']; 
        ##pm_logDebug(3, $plugins, "Plugin array in initmap.php");
        if (array_key_exists('pluginsConfig', $this->ini)) {
            $iniPluginsConfig = (array)$this->ini['pluginsConfig'];
            foreach ($plugins as $p) {
                if (array_key_exists($p, $iniPluginsConfig)) {
                    $_SESSION['pluginsConfig'][$p] = $iniPluginsConfig[$p];
                }
            }
        }

        $plugin_jsInitList = $_SESSION['plugin_jsInitList'];
        $jsInitFunctions = "";
        if (count($plugin_jsInitList) > 0) {
            foreach ($plugin_jsInitList as $jsI) {
                $jsInitFunctions .= "$jsI;\n";
            }
        }
        return $jsInitFunctions;
    }
    
    public function returnJSConfigReference()
    {
        $jsConfRef  = "<script type=\"text/javascript\" src=\"" . PM_INCPHP_LOCATION . "/js/js_session.php?" . SID . "\"></script>\n";
      
        if (file_exists(PM_BASECONFIG_DIR . "/" . PM_CONFIG_LOCATION_COMMON . "/js_config.php")) {
            $jsConfRef .= " <script type=\"text/javascript\" src=\"config/" . PM_CONFIG_LOCATION_COMMON . "/js_config.php?" . SID . "\"></script>\n";
        }
        if (file_exists(PM_BASECONFIG_DIR . "/" . PM_JS_CONFIG)) {
            $jsConfRef .= " <script type=\"text/javascript\" src=\"config/" . PM_JS_CONFIG . "?" . SID . "\"></script>\n";
        }
        
        return $jsConfRef;
    }
    
    public function returnJSReference()
    {
        return $this->jsReference;
    }
    
    public function returnJSCustomReference()
    {
        return $this->jsCustomReference;
    }
    
    public function returnCSSReference()
    {
        return $this->cssReference;
    }
    
    public function returnjsInitFunctions()
    {
        return $this->jsInitFunctions;
    }
}

?>