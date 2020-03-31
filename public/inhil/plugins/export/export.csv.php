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
 * Export results to CSV files
 * 1 file per result group
 */
class ExportCSV extends ExportQuery
{
    
    /**
     * Init function
     */
    function __construct($json)
    {
        parent::__construct($json);
        
        // Delimiter and quote characters
        $del = ",";
        $enc = '"';

        $groups = (array)$this->jsonList[0];
        $fileList = array();
        
        // Create directory
        @mkdir($this->tempFilePath, 0700);
        
        foreach ($groups as $grp) {
            $outputFile = $this->tempFilePath ."\\" .$grp->name . '.csv';
            $fileList[] = $outputFile;
            $fp = fopen($outputFile, 'w');
            
            // Header
            $headerList = $grp->header; 
            $csv_header = array();
            foreach ($headerList as $h) {
                if ($h == '@') {
                    //$col--;
                } else {
                    $csv_header[] = $h;
                }
            }
            $this->fwritecsv($fp, $csv_header, $del, $enc);

            // Values
            $values = $grp->values; 
            $csv_val = '';
            foreach ($values as $vList) {
                $csv_row = array();
                foreach ($vList as $v) {
                    // Links
                    if (is_object($v)) {
                        if (isset($v->shplink)) {
                        
                        }
                        
                        if (isset($v->hyperlink)) {
                            $csv_row[] = $v->hyperlink[2]; //str_replace($enc, "@@@@", $hyperlink[3]); //           
                        }
                    } else {
                        $csv_row[] = $v; //str_replace($enc, "@@@@", $v); 
                                                 
                    }
                }
                $this->fwritecsv($fp, $csv_row, $del, $enc);
            }
            
            fclose($fp);
            unset($fp);
        }
        
        // Write all csv files to zip
        $this->tempFileLocation .= '.zip' ;
		$zipFilePath = "{$this->tempFilePath}.zip";
        
        PMCommon::packFilesZip($zipFilePath, $fileList, true, true);
        
        // remove directory
        rmdir($this->tempFilePath);

    }
    
    /**
     * Write string to CSV file pointer
     */
    function fwritecsv($filePointer, $dataArray, $delimiter, $enclosure)
    {
        // Write a line to a file
        // $filePointer = the file resource to write to
        // $dataArray = the data to write out
        // $delimeter = the field separator

        // Build the string
        $string = '';

        // No leading delimiter
        $writeDelimiter = false;
        foreach($dataArray as $dataElement) {
            // Replaces a double quote with two double quotes
            $dataElement=str_replace("\"", "\"\"", $dataElement);

            // Adds a delimiter before each field (except the first)
            if ($writeDelimiter) $string .= $delimiter;

            // Encloses each field with $enclosure and adds it to the string
            $string .= "$enclosure$dataElement$enclosure";

            // Delimiters are used every time except the first.
            $writeDelimiter = true;
        } // end foreach($dataArray as $dataElement)

        // Append new line
        $string .= "\n";

        // Write the string to the file
        fwrite($filePointer,$string);
    }
   
}

?>