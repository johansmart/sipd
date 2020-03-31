<?php

/******************************************************************************
 *
 * Purpose: export query results as XLS document
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
 * Export results to PDF file
 * 1 page per result group
 */
class ExportPDF extends ExportQuery
{
    
    
    /**
     * Init function
     */
    function __construct($json)
    {
        require('mc_table.php');
        parent::__construct($json);
        
        // Get Config
        $conf = (isset($_SESSION['pluginsConfig']) && isset($_SESSION['pluginsConfig']['export']) && isset($_SESSION['pluginsConfig']['export']['PDF'])) ? $_SESSION['pluginsConfig']['export'] : array('PDF' => array());
        $defaultFont = isset($conf['PDF']['defaultFont']) ? $conf['PDF']['defaultFont'] : 'FreeSans';
        $defaultFontSize = isset($conf['PDF']['defaultFontSize']) ? $conf['PDF']['defaultFontSize'] : 9;
        
        $headerFont = isset($conf['PDF']['headerFont']) ? $conf['PDF']['headerFont'] : $defaultFont;
        $headerFontSize = isset($conf['PDF']['headerFontSize']) ? $conf['PDF']['headerFontSize'] : $defaultFontSize;
        $headerFontStyle = isset($conf['PDF']['headerFontStyle']) ? $conf['PDF']['headerFontStyle'] : '';
        
        $layerFont = isset($conf['PDF']['layerFont']) ? $conf['PDF']['layerFont'] : $defaultFont;
        $layerFontSize = isset($conf['PDF']['layerFontSize']) ? $conf['PDF']['layerFontSize'] : $defaultFontSize;
        $layerFontStyle = isset($conf['PDF']['layerFontStyle']) ? $conf['PDF']['layerFontStyle'] : '';
        
        // Write to table
        $pdf=new PDF_MC_Table();
        
		// only for tcpdf version >= 3
		// - font not well defined --> errors
		// - header and footer printed = lines on top and bottom 
        $pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);

        $pdf->Open();
            
        $groups = (array)$this->jsonList[0];
        foreach ($groups as $grp) {
            $ret = $this->prepareData4PDF($grp);
            $colsPerc = $ret[0];
            $data = $ret[1];
            $header = $data['header'];
            $records = $data['records'];
            
            $pdfW = 180;
            // orientation detection
            $orientation = 'P';
            $colsum = $ret[2];
            $maxSize = max($headerFontSize, $defaultFontSize); 
            $widthTmp = $colsum * $maxSize / 4;
            if ($widthTmp > 1.5 * $pdfW) {
            	$pdfW = 270;
            	$orientation = 'L';
            }
            $pdf->AddPage($orientation);

            $cols = array();
            foreach ($colsPerc as $cp) {
                $cols[] = $cp * $pdfW;
            }
            

			// add group name:
			$pdf->SetFont($layerFont, $layerFontStyle, $layerFontSize);
			$x = $pdf->GetX();
            $y = $pdf->GetY();
			$pdf->Cell(0, 0, $grp->description);
            $pdf->SetXY($x, $y + 9);
                
            // Calculate column widths
            $pdf->SetWidths($cols); 

            // Add header
            $pdf->SetFont($headerFont, $headerFontStyle, $headerFontSize);
            $pdf->Row($header);
            
            // Add records
            $pdf->SetFont($defaultFont, '', $defaultFontSize);
            foreach ($records as $row) {
                $pdf->Row($row);
            }
        }
        
        $pdfFilePath = $this->tempFilePath .'.pdf';
        $this->tempFileLocation .= '.pdf' ;
        $pdf->Output($pdfFilePath, 'F');
    }
    
    
    function prepareData4PDF($grp)
    {
        $data = array();
        
        $headerList = $grp->header; 
        $cols = array();
        
        $withShpLink = 0;
        $headerLine = array();
        foreach ($headerList as $h) {
            // cols size depends also on header size
            if ($h != '@') {
                $cols[] = strlen($h);
                $headerLine[] = $h;
            } else {
                $withShpLink = 1;
            }
        }
        $data['header'] = $headerLine;
        // cols size priority to header size
        $hcols = $cols;
        
        // Values
        $records = array();
        $values = $grp->values; 
        foreach ($values as $vList) {
            $valLine = array();
            $vL = count($vList);
            $start = $withShpLink ? 1 : 0;
            for ($i=$start; $i<$vL; $i++) {
                $ii = $withShpLink ? $i-1 : $i;
                // Links
                $v = $vList[$i];
                $valueTmp = false;
                if (is_object($v)) {
                    if (isset($v->shplink)) {
                    }
                    
                    if (isset($v->hyperlink)) {
                    	$valueTmp = $v->hyperlink[2];
                    }
                } else {
                	$valueTmp = $v;
                }
                if ($valueTmp !== false) {
                	$len = strlen($valueTmp);
                    $cols[$ii] = max($cols[$ii], $len); 
                    $valLine[] = $valueTmp;
                }
            }
            $records[] = $valLine;
        }
        $data['records'] = $records;
        
        $csum = array_sum($cols);

        // cols size priority to header size
        $hcsum = array_sum($hcols);
        if ($csum > 1.5 * $hcsum) {
            $colsTmp = $hcols;
            $sumTmp = $hcsum;
        } else {    
            $colsTmp = $cols;
            $sumTmp = $csum;
        }

        $colsPerc = array();
        foreach ($colsTmp as $c) {
            $colsPerc[] = round($c/$sumTmp, 2);
        }

        return array($colsPerc, $data, $hcsum);
    }
    
}

?>