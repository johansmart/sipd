<?php

/******************************************************************************
 *
 * Purpose: export query results
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

/**
 * Class for export of query results to standard formats like XLS, CSV, PDF
 */

class ExportQuery
{
    /** Result array from JSON result string */ 
    var $jsonList;
    
    /** temporary file absolute system path */
    var $tempFilePath;
    
    /** temporary file web location  */
    var $tempFileLocation;
    
    // export: more readable file name (depends on date and time)
    /** current time to include in name */
    var $currentTimeForFileName;
    
    
    /**
     * Init function, parse JSON result string 
     */
    function __construct($json)
    {
        // Remove problematic characters for JSON parsing
        $json = str_replace(array('\\"'), array("''"), $json);
        $this->jsonList = PMCommon::parseJSON($json); 
        //pm_logDebug(3, $this->jsonList);
        
        $dateAndTime = date('Y-m-d_H-i-s');
        $this->currentTimeForFileName = $dateAndTime;
        
        $this->tempFilePath = $_SESSION['web_imagepath'] . $this->currentTimeForFileName . '_' . session_id();
        $this->tempFileLocation = $_SESSION['web_imageurl'] . $this->currentTimeForFileName . '_' . session_id();
    }
    
    
    function getTempFilePath()
    {
        return $this->tempFilePath;
    }

    function getTempFileLocation()
    {
        return $this->tempFileLocation;
    }
     
}

?>