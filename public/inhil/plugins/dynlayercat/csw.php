<?php
/******************************************************************************
 *
 * Purpose: PHP CSW client connector (requires a CSW catalogue providing 
 * metadata in ISO19115/19139 and ISO19110).
 *
 * Support GeoNetwork authentification mechanism
 *
 * Author: Pierre Lagarde, François Prunayre
 *
 ******************************************************************************
 *
 * Copyright (c) 2011-2012 European Environment Agency (EEA)
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
require_once "HTTP/Request.php";

/**
 * A CSW client.
 *
 * Configuration:
 *  * csw_url: URL of the CSW entry point.
 *
 */
class CSWCatalogue {
    private $_cswAddress;
    private $_authentAddress;
    private $_cswLogin;
    private $_cswPassword;
    private $_bAuthent;
    private $_sessionID;

    private $_response;

    /**
     *
     * @param String $cswAddress address of the CSW URL
     * @param String $cswLogin login of the user to CSW-T
     * @param String $cswPassword  password of the user to CSW-T
     * @param String $authentAddress address of the login/logout address
     */
    function __construct($cswAddress, $cswLogin = null, $cswPassword = null, $authentAddress = null) {
        if (isset($_REQUEST['csw_url'])) {
            $this -> _cswAddress = $_REQUEST['csw_url'];
        } elseif (isset($cswAddress)) {
            $this -> _cswAddress = $cswAddress;
        } else {
            error_log($_SERVER['REQUEST_URI'] . " Empty or missing CSW URL set by _REQUEST or parameter");
            die();
        }
        //error_log("CSW URL: " . $this->_cswAddress);
        $this -> _bAuthent = false;
        if (isset($cswLogin)) {
            $this -> _cswLogin = $cswLogin;
            $this -> _cswPassword = $cswPassword;
            $this -> _authentAddress = $authentAddress;
            $this -> _bAuthent = true;
        }
    }

    /**
     *
     * @return bool Request success / error
     */
    private function _callHTTPCSW($request) {

        
        try {
            $request -> sendRequest();
            if (200 == $request -> getResponseCode()) {
                $this -> _response = $request -> getResponseBody();
                /* TODO : Add cookie for auth support
                 * $cookies = $request -> getCookies();
                foreach ($cookies as $cook) {
                    if ($cook['name'] == 'JSESSIONID')
                        $this -> _sessionID = $cook['value'];
                }*/
                return true;
            } else {
                $this -> _response = $request -> getResponseCode() . ' ' . $request -> getResponseBody();
                error_log($_SERVER['REQUEST_URI'] . " Error while retrieving the metadata from the CSW server: " + $this -> _response);
                return false;
            }
        } catch (HTTP_Request_Exception $e) {
            $this -> _response = 'Error: ' . $e -> getMessage();
            error_log($_SERVER['REQUEST_URI'] . " Error while retrieving the metadata from the CSW server: " + $this -> _response);
            return false;
        }
    }

    /**
     *
     * @return bool authentication success or error
     */
    private function _authentication($request) {
        //only available for Geonetwork based authentification
        //start by logout
        if ($this -> _bAuthent) {
            $req = new HTTP_Request($this -> _authentAddress . '/xml.user.logout', HTTP_Request::METHOD_POST);

            if ($this -> _callHTTPCSW($req)) {
                //success so next step
                //start to login
                $req = new HTTP_Request($this -> _authentAddress . '/xml.user.login');
                $req -> setMethod(HTTP_Request::METHOD_POST) -> setHeader("'Content-type': 'application/x-www-form-urlencoded', 'Accept': 'text/plain'") -> addPostParameter('username', $this -> _cswLogin) -> addPostParameter('password', $this -> _cswPassword);
                if ($this -> _callHTTPCSW($req)) {
                    $request -> addCookie('JSESSIONID', $this -> _sessionID);
                    return true;
                }
            }
            return false;
        }
        return true;
    }

    /**
     * retrieve a specific metadata with UUID in GeoNetwork / Geosource
     * @param String $id of the metadata
     * @return XML content (could be an empty response)
     */
    public function getRecordById($id) {
        $getRecodByIDRequest = new HTTP_Request($this -> _cswAddress);
        $getRecodByIDRequest -> addHeader("Content-Type", "text/xml");
        $getRecodByIDRequest -> setMethod(HTTP_REQUEST_METHOD_POST);
        $request = $this -> buildGetRecordById($id);
        $getRecodByIDRequest -> addRawPostData($request, true);
        
        //authentication if needed
        if (!$this -> _authentication($getRecodByIDRequest))
            throw new Exception($this -> _response, "001");
        if ($this -> _callHTTPCSW($getRecodByIDRequest)) {
            $getRecodByIDRequest = null;
            return $this -> _response;
        } else {
            $getRecodByIDRequest = null;
            throw new Exception($this -> _response, "002");
        }
    }

    public function getUrl() {
        return $this -> _cswAddress;
    }
    
    /**
     * Build a get record by id request using Filter Encoding
     */
    private function buildGetRecordById($uuid, $outputSchema='http://www.isotc211.org/2005/gmd', $elementSetName='full') {
        return '<?xml version="1.0"?>
        <csw:GetRecordById xmlns:csw="http://www.opengis.net/cat/csw/2.0.2" service="CSW" version="2.0.2"
          outputSchema="' . $outputSchema . '">
            <csw:Id>' . $uuid . '</csw:Id>
            <csw:ElementSetName>' . $elementSetName . '</csw:ElementSetName>
        </csw:GetRecordById>';
    }
}
?>
