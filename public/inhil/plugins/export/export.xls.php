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
 * Export results to Excel 5 spreadsheet
 * requires PEAR modules OLE and Spreadsheet_Excel_Writer
 */
class ExportXLS extends ExportQuery
{
    
    /**
     * Init function
     */
    function __construct($json)
    {
        parent::__construct($json);
        
        if (!require_once 'Spreadsheet/Excel/Writer.php') {
             pm_logDebug(0, "Missing PEAR packages Spreadsheet_Excel_Writer (and maybe OLE). See plugin Readme.txt for details.");   
        }

        $this->tempFilePath .= '.xls';
        $this->tempFileLocation .= '.xls';
        
        $this->workbook = new Spreadsheet_Excel_Writer($this->tempFilePath);
        
        // For UTF encoding
        $this->workbook->setVersion(8);
        
        $format_bold = $this->workbook->addFormat();
        $format_bold->setBold();
        $format_title = $this->workbook->addFormat();
        $format_title->setBold();
        $format_title->setColor('blue');
        
        $worksheet = $this->workbook->addWorksheet();
        $worksheet->setInputEncoding('UTF-8'); 
        
        $groups = (array)$this->jsonList[0];
        $r = 0;
        foreach ($groups as $grp) {
            $worksheet->write($r, 0, $grp->description, $format_title);
            $r++;
            
            $headerList = $grp->header; 
            $hL = count($headerList);
            $col = 0;
            for ($hi=0; $hi < $hL; $hi++) {
                $headline = $headerList[$hi];
                if ($headline == '@') {
                    //$col--;
                } else {
                    $worksheet->write($r, $col, $headline, $format_bold);
                    $col++;
                }
            }
            $r++;
            
            $values = $grp->values; 
            
            foreach ($values as $vList) {
                $vcol = 0;
                foreach ($vList as $v) {
                    // Links
                    if (is_object($v)) {
                        if (isset($v->shplink)) {
                        
                        }
                        
                        if (isset($v->hyperlink)) {
                            $worksheet->write($r, $vcol, utf8_decode($v->hyperlink[2]));           
                            $vcol++;
                        }
                    } else {
                        if (is_string($v)) {
                    		$worksheet->writeString($r, $vcol, $v);
                    	} else {
                        	$worksheet->write($r, $vcol, $v);
                    	} 
                        $vcol++;
                    }
                }
                $r++;
            }
            
            $r++;
        }
        
        $this->workbook->close();
    }
    
    function sendFile()
    {
        // Send to client
        $this->workbook->send($this->tempFilePath);

    }
   
}

?>