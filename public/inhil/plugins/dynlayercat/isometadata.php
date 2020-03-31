<?php
/******************************************************************************
 *
 * Purpose: Class to easily parse and extract information from 
 * ISO19139 or ISO19110 metadata records.
 * Author:  François Prunayre
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
class ISOMetadata {
    private $_xml;
    
    /*
     * @param $xml_as_string String representation of the XML document to parse.
     * The metadata record does not need to be the root element. For example a 
     * CSW response could be used to extract metadata records from.
     * 
     * TODO : Support multiple records
     */
    function __construct($config) {
        if (isset($config['url'])) {
            $this -> _xml = $this ->load_url($config['url']);
        } else {
            $this -> _xml = $this->string_as_xml($config['xml']);
        }
    }
    
    /**
     * Check a metadata is an ISO19139 or ISO19110 record
     */
    public function isIso() {
        // Check an ISO metadata is available
        $this -> _xml -> registerXPathNamespace('gfc', 'http://www.isotc211.org/2005/gfc');
        $this -> _xml -> registerXPathNamespace('gmd', 'http://www.isotc211.org/2005/gmd');
        $root = $this -> _xml -> xpath('//gmd:MD_Metadata|//gfc:FC_FeatureCatalogue');
        return !empty($root);
    }
    
    /**
     * Parse the document as XML and create an array of elements to be extracted.
     * 
     * @param $elements Hash of elements to extract. key => value where key 
     * is the array key to set in the results and value could be an XPath or
     * a nested array to parse recursively.
     */
    public function parse($elements) {
        $info = array();
        foreach ($elements as $key => $value) {
            //error_log($key . "=>" . $value);
            // Recursively call parse if $value is an array
            if (is_array($value)) {
                $info[$key] = $this -> parse($value);
            } else {
                $var = ($this -> xpath_query($value));
                if ($var) {
                    $info[$key] = $var;
                } else {
                    $info[$key] = NULL;
                }
            }
        }
        return $info;
    }

    /**
     * Convert a string to a simple_xml Object.
     * 
     * @return A simple xml object with all namespace registered by default.
     */
    public function string_as_xml($xml_as_string, $register_ns=true) {
        $sxml = @simplexml_load_string($xml_as_string);
        if ($sxml === false) {
            return;
        }
        
        if ($register_ns) {
            $this -> register_ns($sxml);
        }
        return $sxml;
    }
    /**
     * Load an XML file from URL and create a simple XML object.
     * 
     * @return A simple xml object with all namespace registered by default.
     */
    public function load_url($url, $register_ns=true) {
        $sxml = new SimpleXMLElement($url, NULL, true);
        if ($sxml === false) {
            return;
        }
        if ($register_ns) {
            $this -> register_ns($sxml);
        }
        return $sxml;
    }
    
    /**
     * Register all XML document namespace.
     */
    public function register_ns($sxml) {
        //Registers all namespaces used (required to make XPath queries with the simplexml library)
        $namespaces = $sxml -> getNamespaces(true);
        foreach ($namespaces as $prefix => $ns) {
            $sxml -> registerXPathNamespace($prefix, $ns);
        }
    }
    /**
     * Populate an array with column and codelist informatino from 
     * an ISO19110 records.
     */
    public function parse_featureCatalogue() {
        $feature_cat_specs = array();
        $attributes = $this -> _xml -> xpath('//gfc:FC_FeatureAttribute');
        foreach ($attributes as $att) {
            $att_code = $this -> xpath_query('gfc:memberName/gco:LocalName', $att);
            $att_def = $this -> xpath_query('gfc:definition/gco:CharacterString', $att);
            $values = $att -> xpath('gfc:listedValue/gfc:FC_ListedValue');
            $lv = array();
            foreach ($values as $value) {
                $value_code = $this -> xpath_query('gfc:code/gco:CharacterString', $value);
                $value_label = $this -> xpath_query('gfc:label/gco:CharacterString', $value);
                $value_def = $this -> xpath_query('gfc:definition/gco:CharacterString', $value);
                $lv[$value_code] = array('label' => $value_label, 'def' => $value_def);
            }
            $a = array('code' => $att_code, 'def' => $att_def, 'values' => $lv);
            $feature_cat_specs[$att_code] = $a;
        }
        error_log(print_r($feature_cat_specs, true));
        return $feature_cat_specs;
    }

    //XPath query function
    private function xpath_query($xpath_expr, $context=NULL) {
        /* $sxml: SimpleXML object
         $uuid_req: UUID received in the GET request
         $xpath_expr: XPath expression to run against the SimpleXML object
         */
        if ($context !== NULL) {
            $result = $context -> xpath($xpath_expr);
        } else {
            $result = $this -> _xml -> xpath($xpath_expr);
        
        }
        if (empty($result) || strlen($result[0]) == 0) {
            error_log("XPath query error: " . $xpath_expr);
            return false;
        } else {
            settype($result[0], 'string');
            return $result[0];
        }
    }
    
    
}
?>