<?php

/******************************************************************************
 *
 * Purpose: PDF printing functions
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


require_once(dirname(__FILE__) . "/tcpdf.php");
require_once(dirname(__FILE__) . "/print.php");


class Pdf extends TCPDF
{
	// existing variables in pmapper:
	protected $margin;
	protected $topLineY;
    protected $botLineY;
    protected $xminM;
	protected $yminM;
	protected $xmaxM;
	protected $ymaxM;
	protected $pdfSettings;
	protected $prefmap;

	// new members (concerns legend)
	protected $xLegStart;
	protected $xLegStop;
	protected $yLegStart;
	protected $yLegStop;
	protected $legendPosition; // "B" for bottom or "R" for right
	protected $legendNumColumns; // 1, 2, ...
	protected $legendCurrentColumn;
	protected $legendCurrentPageNumColumns;
	protected $xColumnSize;
	protected $printLegendContour;
	protected $xLegend;
	protected $yLegend;
	protected $x0;
	protected $x0NewPage;
	protected $y0;
	protected $y0NewPage;
	protected $mcellW;
	protected $mcellWNewPage;
	protected $numcls;
	protected $clscnt;
	protected $yadd;

	
	protected $printUrlList; // for printmap creation in derivated classes


    function __construct($map, $printScale, $orientation, $units, $format, $pdfSettings, $prefmap=true)
    {
        parent::__construct($orientation,$units,$format,true);
        
		// only for tcpdf version >= 3
		// - font not well defined --> errors
		// - header and footer printed = lines on top and bottom 
		$this->setPrintHeader(false);
		$this->setPrintFooter(false);

        $mapW = $pdfSettings['width']; 
        $mapH = $pdfSettings['height'];
        $this->pdfSettings = $pdfSettings;
        $this->prefmap = $prefmap;
        

		// this function init all position parameters, legend, ...
		$this->initDimensions($mapW, $mapH);

        // printmap creation done in derivated classes :
        $this->createPrintMap($map, $mapW, $mapH, $printScale, 'pdf', 144, false, true, $pdfSettings);

        // avoid bad title
        $title = $pdfSettings['printtitle'] ? $pdfSettings['printtitle'] : $pdfSettings['pdftitle'];
        $this->initPDF($pdfSettings['author'], $title, $pdfSettings['defFont'], $pdfSettings['defFontSize']);

        $this->printPDF($map, $mapW, $mapH, $this->printUrlList, $printScale);

        // scale is now printed in printPDF because of bgcolor and framesLines
        //$this->printScale(_p("Scale"), $printScale);

		// printLegend function to call depends on the type of legend man want
        //$this->printLegendPDF($map, $printScale, 30, 500);
		switch ($this->legendPosition) {
			case "none": // no legend
			case "imgms": // image generated with MapServer
				break;
			case "custom": // a custom legend will be inserted outside this class (so do the same as bottom legend)
				$this->printLegendPDFCustom($map, $printScale);
				break;
			case "imgbr": // a fixed image instead of dyn legend
				$this->Image($this->pdfSettings["img"], $this->xLegStart, $this->yLegStart, $this->xmaxM - $this->xLegStart - 1, $this->ymaxM - $this->yLegStart - 1);
				break;
			case "R": // legend on the right
			case "B": // bottom
			case "BC": // bottom
			default:  // default = bottom
				$this->printLegendPDF($map, $printScale);
				break;
        }
    }
    
    
    function initPDF($author, $title, $defFontType, $defFontSize)
    {
        $this->SetFont($defFontType, "B", $defFontSize);
        $this->setAuthor($author);
        $this->setTitle($title);
        $this->Open();
        $this->SetLineWidth(1.5);
        $this->AddPage();
        $this->defaultFontType = $defFontType;
        $this->defaultFontSize = $defFontSize;
        $this->setPDFVersion($_SESSION['pdfversion']);
    }
    
    
    // FONTS 
    function resetDefaultFont()
    {
        $this->SetFont($this->defaultFontType, "", $this->defaultFontSize); 
        $rgb = preg_split("/[\s,]/", $this->pdfSettings['defFontColor']); 
        $this->SetTextColor($rgb[0], $rgb[1], $rgb[2]);
    }
    
    
    
    /*
     * PRINT FUNCTIONS
     ***********************/
    
    // MAIN PDF PAGE PTINTING
    function printPDF($map, $pdfMapW, $pdfMapH, $printUrlList, $prScale)
    {
        $printmapUrl  = $printUrlList[0];
        $printrefUrl  = $printUrlList[1];
        $printsbarUrl = $printUrlList[2];
    
        // Dimensions: A4 Portrait
// Now done in InitDimensions
//        $pageWidth = 595;
//        $pageHeight = 842;
    
        // Reduction factor, calculated from PDF resolution (72 dpi)
        // reolution from map file and factor for increased map size for PDF output
        //$redFactor = 72/(($map->resolution) / $_SESSION["pdfres"]);
        $redFactor = 72 / 96; 
    
        $imgWidth = $pdfMapW * $redFactor;
        $imgHeight = $pdfMapH * $redFactor;
    
        // Margin lines around page

// The equivalent is done in InitDimensions function
//        $this->margin = round(($pageWidth - ($pdfMapW * $redFactor)) / 2);
//        $this->xminM = $this->margin;                                     //   ____________
//        $this->yminM = $this->margin;                                     //  |             |topLineDelta
//        $this->xmaxM = $pageWidth - $this->margin;                        //  |------------  topLineY
//        $this->ymaxM = $pageHeight - $this->margin;                       //  |   IMG
                                                                          //  |
//        $this->topLineDelta = $this->pdfSettings['top_height'];           //  |------------  botLineY
//        $this->topLineY = $this->yminM + $this->topLineDelta;             //  |   LEG
//        $this->botLineY = $this->topLineY + $imgHeight;                   //  |------------
    
    
        // Draw Map Image
        $web = $map->web;
        $basePath = $web->imagepath;
        $mapImgBaseName = basename($printmapUrl);
        $mapImgFullName = $basePath . $mapImgBaseName;
        $this->Image($mapImgFullName, $this->xminM, $this->topLineY , $imgWidth, $imgHeight);
    
        //Draw Reference Image
        if ($this->prefmap) {
            $refImgBaseName = basename($printrefUrl);
            $refImgFullName = $basePath . $refImgBaseName;
            $refmap = $map->reference;
            $this->refmapwidth = ($refmap->width) * $redFactor;
            $this->refmapheight = ($refmap->height) * $redFactor;
			// refmap bg color
			if (array_key_exists('refmap_bgcolor', $this->pdfSettings)) {
				$rgb = preg_split("/[\s,]/", $this->pdfSettings['refmap_bgcolor']);
				if (count($rgb) == 3 && $rgb[0] != -1 && $rgb[1] != -1 && $rgb[2] != -1) {
					$this->SetFillColor($rgb[0], $rgb[1], $rgb[2]);
					$this->Rect($this->xminM, $this->topLineY, $this->refmapwidth, $this->refmapheight , "F");
				}
			}
            $this->Image($refImgFullName, $this->xminM, $this->topLineY, $this->refmapwidth, $this->refmapheight);
        }
        
        //Draw Scalebar Image
        $sbarImgBaseName = basename($printsbarUrl);
        $sbarImgFullName = $basePath . $sbarImgBaseName;
        $sbar = $map->scalebar;
        
        // MS < 6
        if ($_SESSION['MS_VERSION'] < 6) {
	        $sbarwidth = $sbar->width * $redFactor / $_SESSION['pdfres'];
	        $sbarheight = $sbar->height * $redFactor / $_SESSION['pdfres'];
        } else {
//        	$redFactor2 = 72 / ($map->resolution / $_SESSION['pdfres']);
        	$redFactor2 = $redFactor / $_SESSION['pdfres'];
	        $sbarwidth = $sbar->width * $redFactor2;
	        $sbarheight = $sbar->height * $redFactor2;
        }
        $this->Image($sbarImgFullName, $this->xminM, $this->botLineY - 20, $sbarwidth, $sbarheight + 15);
        
        // scale is now printed in here because of bgcolor and framesLines
        $this->printScale(_p("Scale"), $prScale);

        // Print title bar with logo
        $this->printTitle($this->pdfSettings['printtitle']);
        
        // Print frame lines (margins, inner frames)
        $this->printFrameLines(1);
        
        $this->redFactor = $redFactor;
    }
    
    
    // PRINT OUTER AND INNER FRAME LINES AROUND IMAGE AND LEGEND
    function printFrameLines($firstPage)
    {
        $this->printMargins();
    
        // Inner frame lines
        $this->SetLineWidth(1);
        $this->Line($this->xminM, $this->topLineY, $this->xmaxM, $this->topLineY);
        
        if ($firstPage) { 
            // Bottom line for map image
            // By introducing new legend output type, 
            // the border of legend should be drawned differently
            //$this->Line($this->xminM, $this->botLineY, $this->xmaxM, $this->botLineY);
			if ($this->printLegendContour) {
				$this->Line($this->xLegStart, $this->yLegStart, $this->xLegStop, $this->yLegStart);
				$this->Line($this->xLegStop, $this->yLegStart, $this->xLegStop, $this->yLegStop);
				$this->Line($this->xLegStop, $this->yLegStop, $this->xLegStart, $this->yLegStop);
				$this->Line($this->xLegStart, $this->yLegStop, $this->xLegStart, $this->yLegStart);
			}
        
            // Frame around ref map
            if ($this->prefmap) {
                $this->Line($this->xminM, $this->topLineY + $this->refmapheight, $this->xminM + $this->refmapwidth, $this->topLineY + $this->refmapheight);
                $this->Line($this->xminM + $this->refmapwidth, $this->topLineY + $this->refmapheight, $this->xminM + $this->refmapwidth, $this->topLineY);
            }
        }
    }
    
    // OUTER (MARGIN) LINES
    function printMargins()
    {
        // Outer margin
        $this->SetLineWidth(1.5);
        $this->Line($this->xminM, $this->yminM, $this->xminM, $this->ymaxM);
        $this->Line($this->xminM, $this->ymaxM, $this->xmaxM, $this->ymaxM);
        $this->Line($this->xmaxM, $this->ymaxM, $this->xmaxM, $this->yminM);
        $this->Line($this->xmaxM, $this->yminM, $this->xminM, $this->yminM);
    }
    
    
    // TITLE IN TITLE BAR
    function printTitle($prTitle)
    {
        // Draw background in image color
        $rgb = preg_split("/[\s,]/", $this->pdfSettings['top_bgcolor']); 
        $this->SetFillColor($rgb[0], $rgb[1], $rgb[2]);
        $this->Rect($this->xminM, $this->yminM, $this->xmaxM - $this->xminM, $this->topLineDelta , "F");
        
        // Print logo image
        
		// logo path and fit logo height and upgrade the image quality
		if ($this->pdfSettings['top_logo']) {
			$logo = $this->pdfSettings['top_logo'];
			if ($logo{0} != "/" && $logo{1} != ":") {
				$logo = $_SESSION['PM_BASE_DIR'] . "/" . $logo;
			}
			if (file_exists($logo)) {
				$this->Image($logo, $this->xminM, $this->yminM, 0, $this->pdfSettings['top_height']);
			}
		}

		$prTitle = stripcslashes($prTitle);

        // additionnal text : before / after title:
		$titleTop = $this->yminM;
		$titleBottom = $this->topLineDelta;

		if (array_key_exists('additionnalTitle', $this->pdfSettings)
		&& array_key_exists('before', $this->pdfSettings['additionnalTitle'])
		&& isset($this->pdfSettings['additionnalTitle']->before->text)
		&& strlen(trim($this->pdfSettings['additionnalTitle']->before->text))) {
			$text = $this->pdfSettings['additionnalTitle']->before->text;
			$textFont = isset($this->pdfSettings['additionnalTitle']->before) ? $this->pdfSettings['additionnalTitle']->before->textFont : $this->defaultFontType;
			$textSize = isset($this->pdfSettings['additionnalTitle']->before) ? $this->pdfSettings['additionnalTitle']->before->textSize : $this->defaultFontSize + 5;
			$textColor = isset($this->pdfSettings['additionnalTitle']->before) ? $this->pdfSettings['additionnalTitle']->before->textColor : $this->pdfSettings['top_color'];
            $textColor = preg_split("/[\s,]/", $textColor); 
			$textBgColor = isset($this->pdfSettings['additionnalTitle']->before) ? $this->pdfSettings['additionnalTitle']->before->textBgColor : $this->pdfSettings['top_bgcolor'];
			$textBgColor = preg_split("/[\s,]/", $textBgColor); 

            $this->SetFont($textFont, 'B', $textSize);
            $this->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
        	$this->SetFillColor($textBgColor[0], $textBgColor[1], $textBgColor[2]);
        	$size = $this->GetStringWidth($text) + 0.7 * $textSize;
        	if ($size > $this->xmaxM) {
        		$size = $this->xmaxM;
        	}
            $this->SetXY($this->xmaxM - $size, $this->yminM + 1);
        	$this->Cell($size, 0, $text, 0, 0, '', 1);
            
			$titleTop = $this->yminM + $textSize;
		}
		
		if (array_key_exists('additionnalTitle', $this->pdfSettings)
		&& array_key_exists('after', $this->pdfSettings['additionnalTitle'])
		&& isset($this->pdfSettings['additionnalTitle']->after->text)
		&& strlen(trim($this->pdfSettings['additionnalTitle']->after->text))) {
			$text = $this->pdfSettings['additionnalTitle']->after->text;
			$textFont = isset($this->pdfSettings['additionnalTitle']->after) ? $this->pdfSettings['additionnalTitle']->after->textFont : $this->defaultFontType;
			$textSize = isset($this->pdfSettings['additionnalTitle']->after) ? $this->pdfSettings['additionnalTitle']->after->textSize : $this->defaultFontSize + 5;
			$textColor = isset($this->pdfSettings['additionnalTitle']->after) ? $this->pdfSettings['additionnalTitle']->after->textColor : $this->pdfSettings['top_color'];
            $textColor = preg_split("/[\s,]/", $textColor); 
			$textBgColor = isset($this->pdfSettings['additionnalTitle']->after) ? $this->pdfSettings['additionnalTitle']->after->textBgColor : $this->pdfSettings['top_bgcolor'];
			$textBgColor = preg_split("/[\s,]/", $textBgColor); 

            $this->SetFont($textFont, 'B', $textSize);
            $this->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
        	$this->SetFillColor($textBgColor[0], $textBgColor[1], $textBgColor[2]);
        	$size = $this->GetStringWidth($text) + 0.7 * $textSize;
        	if ($size > $this->xmaxM) {
        		$size = $this->xmaxM;
        	}

            $this->SetXY($this->xmaxM - $size, $this->yminM + $this->topLineDelta - $textSize - 2);
        	$this->Cell($size, 0, $text, 0, 0, '', 1);

			$titleBottom = $this->topLineDelta - 2 * $textSize;
		}
        $this->SetFillColor($rgb[0], $rgb[1], $rgb[2]);

        if (strlen($prTitle) > 0) {
            // Print title
            $trgb = preg_split("/[\s,]/", $this->pdfSettings['top_color']); 
            $this->SetTextColor($trgb[0], $trgb[1], $trgb[2]);
			$titleFontSize = $this->defaultFontSize + 5;
            $this->SetFont($this->defaultFontType, "B", $this->defaultFontSize + 5);
            $titleXOffs = isset($this->pdfSettings['title_xoffs']) && ((string)$this->pdfSettings['title_xoffs']).length > 0 ? (int)$this->pdfSettings['title_xoffs'] : 120;
			if (isset($this->pdfSettings['title_yoffs']) && ((string)$this->pdfSettings['title_yoffs']).length > 0) {
				$titleYOffs = isset($this->pdfSettings['title_yoffs']) ? (int)$this->pdfSettings['title_yoffs'] : (0.5 * $this->topLineDelta);
				$titleY = $this->yminM + $titleYOffs;
			} else {
				$titleY = $titleTop + 0.5 * ($titleBottom - $titleFontSize);
			}
			$this->SetXY($this->xminM + $titleXOffs, $titleY);
            $this->Cell(0, 0, $prTitle);
        }
    }
    
    // SCALE ABOVE SCALEBAR
	// now consider bg color
    function printScale($prString, $prScale)
    {
        $prString = $prString;
        $scaleStr = " $prString 1: $prScale";
		
        if (array_key_exists('scaletxt_bgcolor', $this->pdfSettings)) {
        	$rgb = preg_split("/[\s,]/", $this->pdfSettings['scaletxt_bgcolor']);
        } else {
        	$rgb = array('255', '255', '255');
        }
        $this->SetFillColor($rgb[0], $rgb[1], $rgb[2]);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont($this->defaultFontType, "B", $this->defaultFontSize);
		
        $this->SetXY($this->xminM, $this->botLineY - 32);
        $this->Cell($this->GetStringWidth($scaleStr) + 0.7 * $this->defaultFontSize, 0, $scaleStr, 0, 0, '', 1);
    }
    
    
    // 2-COLUMN LEGEND
    function printLegendPDF($map, $scale)
    {
        $defGroups = $_SESSION["defGroups"];
        $icoW      = $_SESSION["icoW"] * $this->redFactor;  // Width in pixels
        $icoH      = $_SESSION["icoH"] * $this->redFactor;  // Height in pixels
        $imgExt    = $_SESSION["imgFormatExt"];
        
        $layerView = new LayerView($map, true, true, $scale);
        $categoryList = $layerView->getCategoryList();
        //pm_logDebug(3, $categoryList, "writeGroups()");
        
        $yCatSpB = round($this->defaultFontSize * 0.6);
        $yCatGrp = round($this->defaultFontSize * 2.3);
        $yGrpGrp = round($this->defaultFontSize * 2.1);
        $yGrpCls = round($this->defaultFontSize * 1.6);
        $yClsCls = round($this->defaultFontSize * 1.8);
        $yClsGrp = round($this->defaultFontSize * 2.2);
        $yClsAdd = round($this->defaultFontSize * 1.0);
        
        $yMCellH = round($this->defaultFontSize * 0.7);
        
        $xToLeft = round($this->defaultFontSize * 0.3);
        $xIcoTxt = round($this->defaultFontSize * 0.6);
        
        $yPageBreakThresh = 42; //round(($this->defaultFontSize * 4) + 4);
        
        $this->resetDefaultFont();

        // Due to possibility of having legend on the right,
        // few parameters could now be different on the 1st page, and on the following ones
        // Moreover, most of them are now class members
        $this->xColumnSize = (($this->xmaxM - $this->xminM) + (2 * $this->margin)) / $this->legendNumColumns + 5;
//        $this->mcellWNewPage = (($this->xmaxM - $this->xminM) / 2) - $icoW - 38;
        $this->mcellWNewPage = (($this->xmaxM - $this->xminM) / $this->legendNumColumns) - $icoW - 38;
        $this->x0 = $this->xLegStart + 10;
        $this->x0NewPage = $this->xminM + 10;
        $this->xLegend = $this->x0;
        $this->y0 = $this->yLegStart + 10;
        $this->y0NewPage = $this->yminM + 35 + 10;
        $this->yLegend = $this->y0;
        if ($this->legendPosition == "R") {
        	$this->mcellW = ($this->xmaxM - $this->xLegStart) - $icoW - 38;
        } else {
        	$this->mcellW = $this->mcellWNewPage;
        }

        
        foreach ($categoryList as $cat) {
            $catDescr = $cat['description'];
            $groupList = $cat['groups'];
            
            // check if category has one or more group with one or more class
            $catWithClass = false;
            if (count($groupList) > 0) {
            	foreach ($groupList as $grp){
            		$classList = $grp['classList'];
            		if (count($classList)) {
            			$catWithClass = true;
            			break;
            		}
            	}
            }
            
            if ($catWithClass) {
            	if ($this->pdfSettings['printCategory']) {
            		 
            		$this->yLegend += $yCatSpB;
            		// grouping things to do when adding page for legend:
            		$mcellH = $this->defaultFontSize + 2;
            		$mcellWcat = $this->mcellW + 35;
            		$this->SetFont($this->defaultFontType, "BU", $mcellH);
            		$height = 1;
            		if ($this->GetStringWidth($catDescr) >= $mcellWcat) {   // test if string is wider than cell box
            			$height = ceil($this->GetStringWidth($catDescr)/$mcellWcat);
            		}
            		$this->legendAddPageIfNeeded($yCatGrp * $height);

            		$this->SetXY($this->xLegend - $xToLeft, $this->yLegend);
            		if ($this->GetStringWidth($catDescr) >= $mcellWcat) {   // test if string is wider than cell box
            			$this->MultiCell($mcellWcat, $mcellH, $catDescr, 0, "L", 0);
            		} else {
            			$this->Cell(0, 0, $catDescr);
            		}
            		$this->yLegend += $yCatGrp * $height;
            	}
            
                foreach ($groupList as $grp){  
                    $grpName = $grp['name'];
                    $grpDescr = $grp['description'];
                    $classList = $grp['classList'];
                    $this->numcls = count($classList);
                    
                    // Only 1 class for all Layers -> 1 Symbol for Group
                    if ($this->numcls == 1) {
                        $cls = $classList[0];
                        $clsName = $cls['name'];
                        $iconUrl = $cls['iconUrl'];

						// grouping things to do when adding page for legend:                        
                        $this->legendAddPageIfNeeded($yGrpGrp);
                        
                        // Output PDF
                        $this->Image($iconUrl, $this->xLegend, $this->yLegend, $icoW, $icoH);

						// outline rectangle for icons
                        $this->Rect($this->xLegend - 1, $this->yLegend - 1, $icoW + 1, $icoH + 1, "D");

                        $this->SetXY($this->xLegend + $icoW + $xIcoTxt, $this->yLegend);
                        $this->SetFont($this->defaultFontType, "B", $this->defaultFontSize);
                        
						// line height and interline depend on $mcellH
                        $mcellH = $this->defaultFontSize;
                        $mcellWgrp = $this->mcellW + $icoW;
    					if ($this->GetStringWidth($grpDescr) >= $mcellWgrp) {   // test if string is wider than cell box
    						$this->yadd = ceil($this->GetStringWidth($grpDescr)/$mcellWgrp) + 0.5;
        	                $this->yLegend += $yMCellH * $this->yadd;
                            $this->MultiCell($mcellWgrp, $mcellH, $grpDescr, 0, "L", 0);
    					} else {
    						$this->Cell(0, 0, $grpDescr);
    					}
        
                        $this->yLegend += $yGrpGrp;   // y-difference between GROUPS
        
                    // More than 2 classes for Group  -> symbol for *every* class
                    } elseif ($this->numcls > 1) {
						// grouping things to do when adding page for legend:
                    	$this->legendAddPageIfNeeded($yGrpCls);

                        $this->SetXY($this->xLegend - $xToLeft, $this->yLegend);
                        $this->SetFont($this->defaultFontType, "B", $this->defaultFontSize);
                        
						// too long lines
                        $mcellWgrp = $this->mcellW + $icoW;
    					if ($this->GetStringWidth($grpDescr) >= $mcellWgrp) {   // test if string is wider than cell box
    						$this->yadd = ceil($this->GetStringWidth($grpDescr)/$mcellWgrp) + 0.5;
        	                $this->yLegend += $yMCellH * $this->yadd;
                            $this->MultiCell($mcellWgrp, $mcellH, $grpDescr, 0, "L", 0);
    					} else {
    						$this->Cell(0, 0, $grpDescr);
    					}

                        $this->yLegend += $yGrpCls;  // y-difference between GROUP NAME and first class element
        
                        $allc = 0;
                        $this->clscnt = 0;
        
                        #if ($clscnt < $numcls) {

						// too long lines
                        $this->yadd = 0;

                        foreach ($classList as $cls) {
                            $clsName = $cls['name'];
                            $iconUrl = $cls['iconUrl'];
                            $clno = 0;
                            
                            // What to do if CLASS string is too large for cell box
                            if ($this->GetStringWidth($clsName) >= $this->mcellW) {   // test if string is wider than cell box
								// line height and interline depend on $mcellH
                                $ydiff = -1;
								// Modifyed by Walter Lorenzetti
								// Modified by Thomas RAFFIN (SIRAP)
								// rows number fo multicell is bigger than 2

                                // row number cell
                                $this->yadd = max($this->yadd, ceil($this->GetStringWidth($clsName)/$this->mcellW));
                            } else {
								// line height and interline depend on $mcellH
                                $ydiff = 0;
                            }

							// grouping things to do when adding page for legend:
                            $this->legendAddPageIfNeeded($yClsCls + $yMCellH * $this->yadd);
                            // Output PDF
                            $this->Image($iconUrl, $this->xLegend, $this->yLegend, $icoW, $icoH);

							// outline rectangle for icons
                        	$this->Rect($this->xLegend - 1, $this->yLegend - 1, $icoW + 1, $icoH + 1, "D");

                            $this->SetFont($this->defaultFontType, "", $this->defaultFontSize);
                            $this->SetXY($this->xLegend + $icoW + $xIcoTxt, $this->yLegend + $ydiff);
                            $this->MultiCell($this->mcellW, $mcellH, $clsName, 0, "L", 0);
        

	                        // after printing RIGHT column or 
	                        // new group when number of printed classes equals total class number
	                        // last class of a group or only one column in the output PDF (legend on the right for instance)
	                        if ( $this->legendPosition == 'BC' ||
	                        	( ($this->clscnt % $this->legendNumColumns == $this->legendNumColumns - 1) ||
	                        		($this->clscnt == ($this->numcls - 1)) || ($this->legendCurrentColumn == $this->legendCurrentPageNumColumns) ) ) {   
	                            if ($this->clscnt != ($this->numcls - 1)) {
	                            	$this->yLegend += $yClsCls; 
	                            }
	                            if ($this->legendPosition != 'BC') {
	                        		$this->xLegend = $this->x0;
	                            }
	                        	if ($this->yadd) {
	                        		$this->yLegend += $yMCellH * $this->yadd; // yadd : offset due to multiple lines for 1 description
	                        	}
	                            $this->yadd = 0;
	                        // after printing LEFT column
	                        } else {
	                            $this->xLegend += $this->xColumnSize;     // Continue in same group, add only new class item
	                        }

	                        // Begin new group when number of printed classes equals total class number
	                        if ($this->clscnt == ($this->numcls - 1)) {
	                        	$this->yLegend += $yClsGrp;
	                        }
                            
                            $allc++;
                            $this->clscnt++;
        
                            // if Y too big add new PDF page and reset Y to beginning of document
                        }
                    }
                }
            }
        }
    }


    /*
     * This function will init all apameters concerning dimensions :
     *  - width and height
     *  - vertical and horizontal margin,
     *  - x/y min/max
     *  - topLineDelta
     *  - topLineY, botLineY
     *  - x/y legend start/stop
     *  - printLegendContour for later call (or not) of printLegend function...
     */
	protected function initDimensions($mapW, $mapH) {
        $this->legendPosition = isset($this->pdfSettings['legendposition']) ? $this->pdfSettings['legendposition'] : "B";
        $this->legendNumColumns = 2;
        if (isset($this->pdfSettings['legendNumColumns'])) {
        	$legendNumColumnsTmp = $this->pdfSettings['legendNumColumns'];
        	if ($legendNumColumnsTmp > 0) {
        		$this->legendNumColumns = $legendNumColumnsTmp;
        	}
        }
        $this->legendCurrentColumn = 1;
        $this->legendCurrentPageNumColumns = ($this->legendPosition == 'R') ? 1 : $this->legendNumColumns;
        $pageWidth = ceil($this->w);
        $pageHeight = ceil($this->h);
		$redFactor = 72 / 96;

		// margin :
		$marginH = round(($pageWidth - ($mapW * $redFactor)) / 2);
		$marginV = round(($pageHeight - ($mapH * $redFactor) - $this->pdfSettings['top_height']) / 2);
		switch ($this->legendPosition) {
			case "R": // legend on the right
				$this->margin = $marginH = $marginV;
				break;
			case "none": // no legend
			case "imgms": // image generated with MapServer
			case "imgbr": // a fixed image instead of dyn legend
				$this->margin = min($marginH, $marginV);
				break;
			case "custom": // a custom legend will be inserted outside this class (so do the same as bottom legend)
			case "B": // bottom
			default:  // default = bottom
				$this->margin = $marginV = $marginH;
				break;
        }
        $this->xminM = $marginH;               
        $this->yminM = $marginV;               
        $this->xmaxM = $pageWidth - $marginH;  
        $this->ymaxM = $pageHeight - $marginV; 
        $this->topLineDelta = $this->pdfSettings['top_height'];
        $this->topLineY = $this->yminM + $this->topLineDelta;  
        $this->botLineY = $this->topLineY + $mapH * $redFactor;        
        $this->SetAutoPageBreak(false, 0);

		// legend start / stop / contour :
		$this->printLegendContour = true;
		// margin :
		switch ($this->legendPosition) {
			case "R": // legend on the right
				$this->xLegStart = $this->xminM + ($mapW * $redFactor);
				$this->yLegStart = $this->topLineY; 
				$this->xLegStop = $this->xmaxM;
				$this->yLegStop = $this->botLineY; 
				break;
			case "none": // no legend
			case "imgms": // image generated with MapServer
				$this->printLegendContour = false;
				break;
			case "imgbr": // a fixed image instead of dyn legend
				$this->xLegStart = $this->xmaxM - $this->pdfSettings["imgwidth"] * $redFactor - 1;
				$this->yLegStart = $this->ymaxM - $this->pdfSettings["imgheight"] * $redFactor - 1;
				$this->xLegStop = $this->xmaxM;
				$this->yLegStop = $this->botLineY; 
				break;
			case "custom": // a custom legend will be inserted outside this class (so do the same as bottom legend)
			case "B": // bottom
			default:  // default = bottom
	        	$this->xLegStart = $this->xminM;
	        	$this->yLegStart = $this->botLineY; 
				$this->xLegStop = $this->xmaxM;
				$this->yLegStop = $this->botLineY; 
	        	break;
        }
	}
	
    /*
     * this function is here to be re-written in different derivate classes
     */
	protected function printLegendPDFCustom($map, $printScale) {
	}

	/*
	 * grouping things to do when adding page for legend
	 */
	protected function legendAddPageIfNeeded($heightToWrite) {
		if ($this->yLegend > ($this->ymaxM - $heightToWrite)) {
			// columns, but not the last one
			if ($this->legendPosition == 'BC' && $this->legendCurrentColumn < $this->legendCurrentPageNumColumns) {
				$this->legendCurrentColumn++;
				$this->yLegend = $this->y0;
				$this->xLegend += $this->xColumnSize;
				$this->yadd = 0;
				$this->legendAddPageIfNeeded($heightToWrite);
			// 	not last column :
			} else {
				$this->AddPage($this->DefOrientation);
				$this->printTitle('');
				$this->printFrameLines(0);
				$this->resetDefaultFont();
				$this->mcellW = $this->mcellWNewPage;
				$this->x0 = $this->x0NewPage;
				$this->xLegend = $this->x0;
				$this->y0 = $this->y0NewPage;
				$this->yLegend = $this->y0;
				$this->numcls -= $this->clscnt;
				$this->clscnt = 0;
				$this->yadd = 0;
				$this->legendCurrentPageNumColumns = $this->legendNumColumns;
				$this->legendCurrentColumn = 1;
			}
		}
	}
	
	// printmap creation in derivated classes
	protected function createPrintMap($map, $mapW, $mapH, $printScale, $printType, $imgDPI, $imgFormat, $prefmap, $printSettings) {
        $printMap = new PRINTMAP($map, $mapW, $mapH, $printScale, $printType, $imgDPI, $imgFormat, $prefmap, $printSettings);
        $this->printUrlList = $printMap->returnImgUrlList();
	}

}  // END CLASS


?>