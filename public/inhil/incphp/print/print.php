<?php

/******************************************************************************
 *
 * Purpose: general printing functions
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


class PrintMap extends PMap
{ 
    var $mapW;  
    var $mapH;  
    var $scale; 
    var $groups;
    var $imgUrlList;
    var $existsXYLayer;

    
   /**
    * Class constructor
    */
    function __construct($map, $mapW, $mapH, $scale, $printType, $imgDPI, $imgFormat=false, $prefmap=true, $printSettings=array())
    {
        parent::__construct($map);
        $this->mapW   = $mapW;
        $this->mapH   = $mapH;
        $this->scale  = $scale;
        $this->groups = $_SESSION["groups"];
        
        // Check for custom layers
        $this->pmap_addCustomLayers();
        
        // Set active groups/layers
        PMCommon::setGroups($map, $this->groups, $scale, 1);
        
        // Check and if necessary add result layers to map
        $this->pmap_checkResultLayers();
        
        // Check for XY Layers (event layers)
        $this->existsXYLayer = ($_SESSION["existsXYLayer"] ? 1 : 0);
        
        // Set width and height
        $this->map->set("width", $this->mapW);
        $this->map->set("height", $this->mapH);
    
        // ZOOM TO PRE-DEFINED SCALE OR MAP EXTENT FROM SESSIONID
        $this->zoom2scale();
    
        // if needed, add legend to map
        $this->applyLegendObjToMap($map, $printSettings);
        
        // to allow to redefine images drawing
        // All the previous code in now contains in drawImages member function
        $this->drawImages($printType, $imgDPI, $imgFormat, $prefmap);
    
    }
    
   /**
    * Return List of image URL's (map, refmap, sbar)
    */
    function returnImgUrlList()
    {
        return $this->imgUrlList;
    }
    
    
   /**
    * Return legend HTML string
    */
    function returnLegStr()
    {
        return $this->writePrintLegendHTML();
    }
    
    
    
    /**
     * Increase label size for PDF print and download
     */
    function increaseLabels($factor)
    {
    	$msVersion = $_SESSION['MS_VERSION'];

    	if ($msVersion < 6) {
    		$numLayers = $this->map->numlayers;
	        
	        for ($iLayer = 0 ; $iLayer < $numLayers ; $iLayer++) {
	            $layer = $this->map->getLayer($iLayer);
	
	            // increase annotation layer too
//	            if ($layer->labelitem) {
	                $numClasses = $layer->numclasses;
	                for ($iClass = 0 ; $iClass < $numClasses; $iClass++) {
	                    $class = $layer->getClass($iClass);
	                    $labels = array();
	                    if ($msVersion >= 6.2) {
	                    	$numlabels = $class->numlabels;
	                    	for ($iLabel  = 0 ; $iLabel < $numlabels ; $iLabel++) {
	                    		$labels[] = $class->getLabel($iLabel);
	                    	}
	                    } else {
	                    	$labels[] = $class->label;
	                    }
	                    foreach ($labels as $label) {
	                    	if ($label) {
	                    		if ($label->type == 0) {

	                    			// mpascript doesn't detect attribute binding for label !!
	                    			// check layers names for exceptions...
	                    			if ($layer->name == 'drawPolygon'
	                    					|| $layer->name == 'drawLine'
	                    					|| $layer->name == 'drawPoint'
	                    					|| $layer->name == 'drawCircle'
	                    					|| $layer->name == 'drawRectangle'
	                    					|| $layer->name == 'sirapDrawingPolygon'
	                    					|| $layer->name == 'sirapDrawingLine'
	                    					|| $layer->name == 'sirapDrawingPoint'
	                    					|| $layer->name == 'sirapDrawingCircle'
	                    					|| $layer->name == 'sirapDrawingRectangle') {
	                    				$label->setbinding(MS_LABEL_BINDING_SIZE, 'textSize');
	                    				$labelSize0 = 10;
	                    				$label->set('size', $labelSize0 * $factor);
	                    				$label->set('minsize', $labelSize0);
	                    				$label->set('maxsize', $labelSize0);
	                    			}
	                    			
	                    			// north arrow etc...
	                    			if ($layer->transform === 1) {
	                    				$labelSize0 = $label->size;
	                    				$label->set('size', $labelSize0 * $factor);
	                    				// increase label min and max size in PDF
//	                            		$sizeTmp = $label->minsize ? $label->minsize :
	                    				$label->set('minsize', $label->minsize * $factor);
	                    				$label->set('maxsize', $label->maxsize * $factor);
	                    			}
	                    		}
	                    	}
	                    }
	                }
//				}
	        }
		}
    }
    
    
    /**
     * ZOOM MAP TO SPECIFIED SCALE
     */
    function zoom2scale()
    {
        $GEOEXT = $_SESSION["GEOEXT"];
        $geoext0 = ms_newrectObj();
        $geoext0->setExtent($GEOEXT["minx"],$GEOEXT["miny"],$GEOEXT["maxx"],$GEOEXT["maxy"]);
    
        // PREPARE MAP IMG 
        $x_pix = $this->mapW/2;
        $y_pix = $this->mapH/2;
    
        $xy_pix = ms_newPointObj();
        $xy_pix->setXY($x_pix, $y_pix);
    
        $this->map->zoomscale($this->scale, $xy_pix, $this->mapW, $this->mapH, $geoext0);
        PMCommon::freeMsObj($xy_pix);
    }



    /**
     * Draw Scale Bar
     */
    function createScaleBar($printType, $imgDPI)
    {
        $this->pmap_setImgFormat(true); 
        $scalebar = $this->map->scalebar;
        $sbarlabel = $scalebar->label;
        if ($_SESSION['MS_VERSION'] < 6) {
        	$scalebar->set("transparent", MS_OFF);
        }
    
        if ($printType == 'dl' && $imgDPI >= 200) {
            $sbarlabel->set('size', MS_GIANT);
            $scalebar->set('width', $this->map->width * 0.3);
            $scalebar->set('height', $this->map->height * 0.011);
        } else if ($printType == 'pdf') {
        	$pdfres = $_SESSION['pdfres'];
        	$size0 = $sbarlabel->size;
            $sbarlabel->set('size', $size0 * $pdfres);
/*
        	if ($minsize0 = $sbarlabel->minsize) {
            	$sbarlabel->set('minsize', $minsize0 * $pdfres);
        	}
        	if ($maxsize0 = $sbarlabel->maxsize) {
            	$sbarlabel->set('maxsize', $maxsize0 * $pdfres);
        	}
*/
            $scalebar->set('width', $scalebar->width * $pdfres);
            $scalebar->set('height', $scalebar->height * $pdfres);        	
        }
        
        $sbarlabel->color->setRGB(0, 0, 0);
        $sbarlabel->outlinecolor->setRGB(255, 255, 255);
    
        // adapt unit between meters and kilometers:
        $coeffForMeters = $this->map->resolution / 0.0254;
		$bigValue = $scalebar->width * 0.8 / $coeffForMeters * $this->map->scaledenom; // 80% of the scalebar width
        $smallValue = $bigValue / $scalebar->intervals;
        if ( ($scalebar->units == MS_KILOMETERS) || ($scalebar->units == MS_METERS) ) {
        	if ( ($smallValue < 400) || ($bigValue < 1000) ) { 
        		$scalebar->set("units", MS_METERS);
        	} else {
        		$scalebar->set("units", MS_KILOMETERS);
        	}
        }
        	
        $sbarImg = $this->map->drawScaleBar();
    
        return $sbarImg;
    }
    
    
    
    
    
    //===================================================================================//
    //                            LEGEND                                                 //
    //===================================================================================//
    
    /**
     * CREATES HTML LEGEND FOR PRINT OUTPUT
     */
    function writePrintLegendHTML()
    {
        $layerView = new LayerView($this->map, true, true, $this->scale);
        $grouplist = $layerView->getGroupList();
        $icoW      = $_SESSION["icoW"];  // Width in pixels
        $icoH      = $_SESSION["icoH"];  // Height in pixels
    
        $html = "<table class=\"print_legendtable\"> \n";
    
        foreach ($grouplist as $grp){  
            // Only 1 class for Layer -> 1 Symbol for Group
            $classList = $grp['classList'];
            $numcls = count($classList);
            if ($numcls == 1) {
                $iconUrl = $classList[0]['iconUrl'];

                $html .= "<tr>";
                $html .= "<th><img src=\"$iconUrl\" width=\"$icoW\" height=\"$icoH\" alt=\"ico\" /></th>";
                $html .= "<th style=\"width:100%\" colspan=\"3\">" . $grp['description'] . "</th>";
                $html .= "</tr> \n";

            // More than 2 classes for Group  -> symbol for *every* class
            } elseif ($numcls > 1) {
                $html .= "\n  <tr><th colspan=\"4\">" . $grp['description'] . "</th></tr> \n";

                $clscnt = 0;
                foreach ($classList as $cls) {
                    $clsName = $cls['name'];
                    $iconUrl = $cls['iconUrl'];                
                    
                    $html .= $clscnt % 2 ? "" : "<tr>" ; 

                    $html .= "<td style=\"width:$icoW\"><img src=\"$iconUrl\" width=\"$icoW\" height=\"$icoH\" alt=\"ico\" /> </td>";  
                    $html .= "<td>$clsName</td> ";

                    if ($clscnt % 2) {   // after printing RIGHT column
                        $html .= "  </tr> \n";
                    } else {           // after printing LEFT column
                        if ($clscnt == ($numcls - 1)) {    // Begin new group when number of printed classes equals total class number
                            $html .= "<td></td></tr> \n";
                        } else {
                                 // Continue in same group, add only new class item
                        }
                    }
                    $clscnt++;
                }
            }
        }
        
        $html .= "</table> \n";
        
        return $html;
    }


    /*
     * Allow to redefine images drawing
     * 
     * This part of code could now be re-written in derivated classes,
     * and if needed call with parent::drawImages for instance...
     */
    protected function drawImages($printType, $imgDPI, $imgFormat, $prefmap) {
        
        // CREATE MAP IMAGE AND PASTE SCALEBAR AND REFERENCE MAP
        switch ($printType) {
            // HTML OUTPUT
            case "html":
				// allow to redefine images drawing
                $this->map->set("width", $this->mapW);
                $this->map->set("height", $this->mapH);
//                $this->map->set("resolution", 96);

                $images = $this->drawScaleBarAndRefMap($printType, $imgDPI);
                $sbarImg = $images['sbarImg'];
                $refImg = $images['refImg'];
                
                $this->pmap_setImgFormat(true);
                $mapImg = $this->map->draw();
                
                // CHECK iF THERE'S AN XY-LAYER AND THEN DRAW IT
                if ($this->existsXYLayer) {
                    $this->pmap_drawXYLayer($mapImg); 
                }
                
                //$mapImg->pasteImage($sbarImg, 0, 3, $this->mapH-25);
                //if ($prefmap) $mapImg->pasteImage($refImg, -1);
                //$this->imgUrlList[] = $mapImg->saveWebImage();
                $this->imgUrlList[] = PMCommon::mapSaveWebImage($this->map, $mapImg);
                $this->imgUrlList[] = PMCommon::mapSaveWebImage($this->map, $refImg, true);
                $this->imgUrlList[] = PMCommon::mapSaveWebImage($this->map, $sbarImg);
                PMCommon::freeMsObj($mapImg);
                
                break;
        
            // PDF OUTPUT
            case "pdf":
                // Increase size and resolution for better print quality (factor set in config.ini -> pdfres)
                // Note: resolution has to be increased, too, to keep scale dependency of layers
                $pdfres = $_SESSION["pdfres"];
                
                $this->map->set("width", $this->mapW * $pdfres);
                $this->map->set("height", $this->mapH * $pdfres);
                $this->map->set("resolution", 96 * $pdfres);
                
                // MS < 6
                // Increase Label size according to magnificion for PDF output
               	$this->increaseLabels($pdfres);
                
                $images = $this->drawScaleBarAndRefMap($printType, $imgDPI, $pdfres);
                $sbarImg = $images['sbarImg'];
                $refImg = $images['refImg'];
                
                $this->pmap_setImgFormat(true);
                $mapImgHR = $this->map->draw();
                
                // CHECK iF THERE'S AN XY-LAYER AND THEN DRAW IT
                if ($this->existsXYLayer) {
                    $this->pmap_drawXYLayer($mapImgHR); 
                }
                
                /*$this->imgUrlList[] = $mapImgHR->saveWebImage();
                $this->imgUrlList[] = $refImg->saveWebImage();
                $this->imgUrlList[] = $sbarImg->saveWebImage();*/
                
                $this->imgUrlList[] = PMCommon::mapSaveWebImage($this->map, $mapImgHR);
                $this->imgUrlList[] = PMCommon::mapSaveWebImage($this->map, $refImg, true);
                //$this->imgUrlList[] = $refImg->saveWebImage();
                $this->imgUrlList[] = PMCommon::mapSaveWebImage($this->map, $sbarImg);
    
                PMCommon::freeMsObj($mapImgHR);
                break;
        
            // DOWNLOAD HIGH RESOLUTION IMAGE
            case "dl":                
                // Increase Label size according to DPI
               	$factor = round($imgDPI / 96);
               	$this->increaseLabels($factor);
                
                $images = $this->drawScaleBarAndRefMap($printType, $imgDPI);
                $sbarImg = $images['sbarImg'];
                $refImg = $images['refImg'];
                
                //$this->map->selectOutputFormat("jpeg");
                if ($imgFormat) {
                    $this->map->selectOutputFormat($imgFormat);  
                } else {
                    $this->pmap_setImgFormat(true);
                }
                $mapImgHR = $this->map->draw();
                
                // CHECK iF THERE'S AN XY-LAYER AND THEN DRAW IT
                if ($this->existsXYLayer) {
                    $this->pmap_drawXYLayer($mapImgHR); 
                }
                
                // GeoTIFF output
                if ($imgFormat) {
                    $tmpFileName = str_replace('\\', '/', $this->map->web->imagepath) . substr(SID, 10) . ".tif";
                    $mapImgHR->saveImage($tmpFileName, $this->map);
                    $this->imgUrlList[] = $tmpFileName;
                
                // JPG or PNG output
                } else {
                    $this->imgUrlList[] = PMCommon::mapSaveWebImage($this->map, $mapImgHR);
                    $legImg = $this->map->drawLegend();
                    $this->imgUrlList[] = PMCommon::mapSaveWebImage($this->map, $legImg);
                    PMCommon::freeMsObj($legImg);
                }
                
                PMCommon::freeMsObj($mapImgHR);
                break;
        }
    
        PMCommon::freeMsObj($refImg);
        PMCommon::freeMsObj($sbarImg);
	}
	
	protected function drawScaleBarAndRefMap($printType, $imgDPI, $resFactor = 1) {
		$images = array();

        // DEFINE SCALEBAR/REFERENCE-MAP IMG
        $this->pmap_setImgFormat(true);
        $sbarImg = $this->createScaleBar($printType, $imgDPI);
        //$this->pmap_setImgFormat(true);
		$this->prepareRefMap($printType, $resFactor);
		$refImg = $this->map->drawReferenceMap();
		
    	$images['sbarImg'] = $sbarImg;
    	$images['refImg'] = $refImg;
    	
    	return $images;
	}
	
	protected function prepareRefMap($printType, $resFactor = 1) {
/*
		if (array_key_exists('ms_auto_refmap', $_SESSION)
		&& array_key_exists('minx', $_SESSION['ms_auto_refmap'])
		&& array_key_exists('miny', $_SESSION['ms_auto_refmap'])
		&& array_key_exists('maxx', $_SESSION['ms_auto_refmap'])
		&& array_key_exists('maxy', $_SESSION['ms_auto_refmap'])
		&& array_key_exists('width', $_SESSION['ms_auto_refmap'])
		&& array_key_exists('height', $_SESSION['ms_auto_refmap'])) {
			$minx = $_SESSION['ms_auto_refmap']['minx'];
			$miny = $_SESSION['ms_auto_refmap']['miny'];
			$maxx = $_SESSION['ms_auto_refmap']['maxx'];
			$maxy = $_SESSION['ms_auto_refmap']['maxy'];
			$width = $_SESSION['ms_auto_refmap']['width'] * $resFactor;
			$height = $_SESSION['ms_auto_refmap']['height'] * $resFactor;

			$autoRefMapFile = dirname(__FILE__) . '/../../plugins/ms_auto_refmap/autoRefMap.inc.php';
			if (file_exists($autoRefMapFile)) {
				require_once($autoRefMapFile);
				$autoRefMap = new AutoRefMap($this->map);
				$autoRefMap->drawRefMap($minx, $miny, $maxx, $maxy, $width, $height, true);
				$autoRefMap->applyToGlobalMap();
			}
			
			
		}
*/
	}

	/**
	*
    * Legend generated by MapServer (embed in image)
    * 
    */
	private function applyLegendObjToMap($map, $printSettings) {
		if ($printSettings) {
			if (isset($printSettings['legendposition']) && $printSettings['legendposition'] == 'imgms') {
				$msLegPosition = isset($printSettings['msLegPosition']) && $printSettings['msLegPosition'] ? $printSettings['msLegPosition'] : 'lr';
				if (strpos('MS_', $msLegPosition) !== 0) {
					$msLegPosition = 'MS_' . strtoupper($msLegPosition);
				}
				$msLegPosition = constant($msLegPosition);
				$msImgColor = isset($printSettings['msImgColor']) && $printSettings['msImgColor'] ? $printSettings['msImgColor'] : '255 255 255';
				$msTxtColor = isset($printSettings['msTxtColor']) && $printSettings['msTxtColor'] ? $printSettings['msTxtColor'] : '0 0 0';
				$msTxtOutlineColor = isset($printSettings['msTxtOutlineColor']) && $printSettings['msTxtOutlineColor'] ? $printSettings['msTxtOutlineColor'] : '-1 -1 -1';
				$msTxtSize = isset($printSettings['msTxtSize']) && $printSettings['msTxtSize'] ? $printSettings['msTxtSize'] : 18;
				$msIconH = isset($printSettings['msIconH']) && $printSettings['msIconH'] ? $printSettings['msIconH'] : 18;
				$msIconW = isset($printSettings['msIconW']) && $printSettings['msIconW'] ? $printSettings['msIconW'] : 12;
				try {
					if ($msLegPosition !== NULL) {
						$map->legend->set('position', $msLegPosition);
					}
					$msLegPosition = $map->legend->position;
					$spacingLeft = ' ';
					$spacingRight = '';
					switch ($msLegPosition) {
						case MS_UR:
						case MS_LR:
							$spacingRight = '  ';
							break;
						case MS_UL:
						case MS_LL:
						case MS_UC:
						case MS_LC:
						default:
							break;
					}
					$colorTmp = preg_split('/[\s,]/', $msImgColor);
					if ($colorTmp) {
						$map->legend->imagecolor->setRGB($colorTmp[0], $colorTmp[1], $colorTmp[2]);
					}
					$colorTmp = preg_split('/[\s,]/', '0 0 0'); 
					if ($colorTmp) {
						$map->legend->outlinecolor->setRGB($colorTmp[0], $colorTmp[1], $colorTmp[2]);
					}
					$map->legend->set('keysizex', $msIconW);
					$map->legend->set('keysizey', $msIconH);
					$colorTmp = preg_split('/[\s,]/', $msTxtColor);
					if ($colorTmp) {
						$map->legend->label->color->setRGB($colorTmp[0], $colorTmp[1], $colorTmp[2]);
					}
					$colorTmp = preg_split('/[\s,]/', $msTxtOutlineColor);
					if ($colorTmp) {
						$map->legend->label->outlinecolor->setRGB($colorTmp[0], $colorTmp[1], $colorTmp[2]);
					}
					$map->legend->label->set('size', $msTxtSize);
					$map->legend->status = MS_EMBED;
					
					// change names of classes with "_p" function
					$numLayers = $map->numlayers;
					for ($iLayer = 0 ; $iLayer < $numLayers ; $iLayer++) {
						$layer = $map->getLayer($iLayer);
						$numClasses = $layer->numclasses;
						for ($iClass = 0 ; $iClass < $numClasses ; $iClass++) {
							$class = $layer->getClass($iClass);
							$classDesc = $class->name;
							if ($classDesc) {
								$classDesc = $spacingLeft . _p($classDesc) . $spacingRight;
								$class->set('name', $classDesc);
							}
						}
					}
				} catch (Exception $e) {
					pm_logDebug(0, 'Print MS legend error: ' . $e->getMessage());
				}
			}
		}
	}
	
} // END CLASS PRINTMAP

?>
