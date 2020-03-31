<?php

/******************************************************************************
 *
 * Purpose: parses WMS capapilities
 * Author:  Armin Burger
 *
 ******************************************************************************
 *
 * Copyright (c) 2003-2009 Armin Burger
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
require_once("../../incphp/common.php");

class WMSClient
{
    public function __construct($wmsurl)
    {
        $this->wmsVersion = "1.1.1";
        $this->capabilities = $this->getServerCapabilities($wmsurl);
        $this->wmsError = false;
        $this->wmsErrorMsg = "";
    }
    
    protected function getServerCapabilities($wmsurl, $raw=false)
    {
        $capUrl = $wmsurl . "request=GetCapabilities&service=WMS&version=" . $this->wmsVersion;
        error_log ($capUrl);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $capUrl);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $capabilitiesXml = curl_exec($ch);
        if (curl_errno($ch)) {
           $this->wmsError = true;
           $this->wmsErrorMsg .= "Error when connecting to server. Error message: " . curl_error($ch);
           curl_close($ch);
           return false;
        }
        curl_close($ch);
        
        pm_logDebug(3, $capabilitiesXml, "Capabilities for :\n $capUrl");
                
        // return false if no response from server
        if (strlen($capabilitiesXml) < 1) return false;
        
        if ($raw) {
            return $capabilitiesXml;
        } else {
            return simplexml_load_string($capabilitiesXml);
        }
    }
    
    
    public function returnCapabilities()
    {
        return $this->capabilities;
    }
    
    public function checkError()
    {
        return $this->wmsError;
    }
    
    public function returnErrorMsg()
    {
        return $this->wmsErrorMsg;
    }
    
    public function getLayerList()
    {
        //print_r( $this->capabilities);
        //$layerList = $this->capabilities->xpath('//Capability/Layer/Layer');
        if (!$this->capabilities) return '0';
        $layerInfo = $this->capabilities->Capability->Layer;
        
        $layerList = $layerInfo->Layer;
        if (!is_array((array)$layerList) || count($layerList) < 1) {
            $this->wmsError = true;
            $this->wmsErrorMsg .= "Error when connecting to server. Error message: " . curl_error($ch);
        }
        $returnList = array();
        foreach ($layerList as  $layer) {
            $returnList[] = $this->getLayer($layer);
        }
        return $returnList;
    }
    
    
    protected function getLayer($layer)
    {
        $nestedLayers  = $layer->xpath('Layer');
        $rl['name']     = (string)$layer->Name;
        $rl['title']    = trim((string)addslashes($layer->Title));
        //$rl['abstract'] = trim((string)$layer->Abstract);
        $rl['styles']   = trim((string)$layer->Style);
        
        if (count($nestedLayers) > 0) { 
            $nlList = array();
            foreach ($nestedLayers as $nlay) {
               $nlList[] = $this->getLayer($nlay);
            }
        }
        $rl['layer'] = $nlList;
        
        return $rl;
    }
    
    
    public function getSrsList()
    {
        if (!$this->capabilities) return '0';
        include_once("epsg_list.inc");
        $capSrsStr = $this->capabilities->Capability->Layer->SRS;
        
        $capSrsList = preg_split("/\s+/", $capSrsStr[0]);
        pm_logDebug(3, $capSrsList, "SRS List");
        
        return $capSrsList;
        
        foreach ($capSrsList as $s) {
            /*if (array_key_exists((string)$s, $srsList)) {
                $srsList[(string)$s] = $epsgL[substr((string)$s, 5)];
            } else {
                $srsList[(string)$s] = "EPSG:" . (string)$s;
            }*/
        }
        return $srsList;
    }
    
    
    public function getImgFormats()
    {
        if (!$this->capabilities) return '0';
        $cap_wms_formats = $this->capabilities->Capability->Request->GetMap->Format;
        foreach ($cap_wms_formats as $f) {
            if (preg_match('/image/', $f)) {
                $wmsFormats[] = "$f";
            }
        }
        return $wmsFormats;
    }
    
    
}

?>