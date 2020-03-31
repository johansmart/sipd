<?php
/******************************************************************************
 *
 * Purpose: initialization and creation of dynamic layers based on 
 * a layer description contained in a metadata record extracted from a CSW catalogue.
 * 
 * The metadata record must contains :
 *  * layer title
 *  * layer location : Supported formats are location to a vector or raster file,
 * PostGIS DB connection information.
 *  * layer extent
 *  * layer projection system
 * 
 * Optionnaly the metadata record could be linked to a feature catalogue further 
 * describing the column definition and list of values. In that case, the ISO19110
 * feature catalogue is registered in the FEATURE_CATALOGUE_URL properties 
 * (later used by the Query module to extract and display columns and codelist
 * information).
 * 
 * Author:
 *  * Armin Burger
 *  * Francois Prunayre
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

require_once (str_replace('\\', '/', realpath(dirname(__FILE__) . "/../../incphp/map/dynlayer.php")));
require_once (str_replace('\\', '/', realpath(dirname(__FILE__) . "/csw.php")));
require_once (str_replace('\\', '/', realpath(dirname(__FILE__) . "/isometadata.php")));

class DynLayerCat extends DynLayer {
    public $map;

    private $data_path_prefix = "";
    // List of protocols - default to GeoNetwork values
    private $protocols = array('FILE:GEO', 'FILE:RASTER', 'DB:POSTGIS');
    private $proj_authority = array('EPSG');
    
    public function __construct($map) {
        require_once "dynlayercat_config.php";
        $this -> connections = $connections;
        $this -> layerSrcType = "CAT";
        $this -> map = $map;
        if (isset($_REQUEST['uuid'])) {
            $this -> uuid = $_REQUEST['uuid'];
        }
        $this -> dataSpecs = isset($_SESSION['catalogueDataSpecs']) ? $_SESSION['catalogueDataSpecs'] : false;
        $this -> config = $_SESSION['pluginsConfig']['dynlayercat'];
        
        if (isset($_REQUEST['csw_url'])) {
            $this -> config['csw_url'] = $_REQUEST['csw_url'];
        } elseif (!isset($this -> config['csw_url'])) {
            error_log($_SERVER['REQUEST_URI'] . " Empty or missing csw_url as input parameter to the script");
            die();
        }
        
        if (isset($this -> config['data_path_prefix'])) {
            $this -> data_path_prefix = $this -> config['data_path_prefix'];
        }
        
        // Overrides the default protocol list
        if (isset($this -> config['protocols'])) {
            $this -> protocols = explode(',', $this -> config['protocols']);
        }
        
        // Overrides the default protocol list
        if (isset($this -> config['proj_authority'])) {
            $this -> proj_authority = explode(',', $this -> config['proj_authority']);
        }
    }

    /**
     * Add prefix and suffix to an array value
     */
    private function decorate(&$value, $key, $config = array('prefix' => '', 'suffix' => '')) {
        $value = $config['prefix'] . $value . $config['suffix'];
    }

    protected function getLayerList() {
        $layerList = array();
        
        // Avoid duplicate
        $uuidList =  array_unique(explode(",", $this -> uuid));
	// Skip if no UUID provided        
	if($uuidList = 0) return;

        foreach ($uuidList as $uuid) {
            $catalogue = new CSWCatalogue($this -> config['csw_url']);
            $response = $catalogue -> getRecordById($uuid);
            
            // Add prefix and suffix to protocols and proj authority to build XPath
            $list_of_protocols = $this -> protocols;
            array_walk($list_of_protocols, array($this, 'decorate'), array('prefix' => 'gco:CharacterString="', 'suffix' => '"'));
            //error_log(print_r($list_of_protocols, true));
            $list_of_projauthority = $this -> proj_authority;
            array_walk($list_of_projauthority, array($this, 'decorate'), array('prefix' => 'contains(translate(gco:CharacterString,"abcedfeghijklmnopqrstuvwxyz","ABCEDFGHIJKLMNOPQRSTUVWXYZ"),"', 'suffix' => '")'));
            
            // List of elements to extract from the record
            $elements = array(
                'uuid' => '//gmd:MD_Metadata/gmd:fileIdentifier/gco:CharacterString',
                'title' => '//gmd:identificationInfo[1]/*/gmd:citation/*/gmd:title/gco:CharacterString',
                'dataset_id' => '//gmd:identificationInfo[1]/*/gmd:citation/*/gmd:identifier/*/gmd:code/gco:CharacterString',
                // Protocol set in plugin config
                'path' => '//gmd:distributionInfo/*/gmd:transferOptions/*/gmd:onLine/*/gmd:protocol[' . implode(' or ', $list_of_protocols) . ']/../gmd:linkage/gmd:URL',
                'bbox' => array(
                    'westLon' => '//gmd:identificationInfo[1]/*/gmd:extent/*/gmd:geographicElement/*/gmd:westBoundLongitude/gco:Decimal',
                    'eastLon' => '//gmd:identificationInfo[1]/*/gmd:extent/*/gmd:geographicElement/*/gmd:eastBoundLongitude/gco:Decimal',
                    'northLat' => '//gmd:identificationInfo[1]/*/gmd:extent/*/gmd:geographicElement/*/gmd:northBoundLatitude/gco:Decimal',
                    'southLat' => '//gmd:identificationInfo[1]/*/gmd:extent/*/gmd:geographicElement/*/gmd:southBoundLatitude/gco:Decimal'
                    ),
                'epsg' => '//gmd:referenceSystemInfo/*/gmd:referenceSystemIdentifier/*/gmd:code[' . implode(' or ', $list_of_projauthority) . ']/gco:CharacterString',
                'spatial_type' => '//gmd:identificationInfo[1]/*/gmd:spatialRepresentationType/*/@codeListValue',
                'feature_catalogue' => '//gmd:contentInfo/*/gmd:featureCatalogueCitation/@uuidref'
            );
            $im = new ISOMetadata(array('xml'=>$response));
            // Do not add the current requested metadata if it's not an ISO record
            if (!$im -> isIso()) {
                error_log("Do not add the current requested metadata if it's not an ISO record. UUID: $uuid");
                continue;
            }
            $dataSpecs = $im->parse($elements);
            
            // Extract EPSG code and use default if not found
            // urn:ogc:def:crs:EPSG:7.1:4326
            // urn:ogc:def:crs:EEA:1.0:777100
            preg_match("/urn:ogc:def:crs:(EPSG|EEA):.*:([0-9]*)/", $dataSpecs['epsg'], $matches);
            
            $dataSpecs['epsg'] = strtolower($matches[1]) . ":" . $matches[2];
            $dataSpecs['srscode'] = $matches[2];
            if (empty($dataSpecs['epsg'])) {
                $dataSpecs['epsg'] = $this->config['default_proj'];
            }
            if (empty($dataSpecs)) {
                error_log("Unable to retrieve data specification from metadata catalogue for layer with UUID: $uuid.");
                continue;
            }
            
            
            // Add a GetRecordById URL to retrieve the feature catalogue
            if ($dataSpecs['feature_catalogue']) {
                $dataSpecs['feature_catalogue_url'] = $catalogue -> getUrl() . "?SERVICE=CSW&VERSION=2.0.2&REQUEST=GetRecordById&outputSchema=http://www.isotc211.org/2005/gmd&elementSetName=full&ID=" . $dataSpecs['feature_catalogue'];
            }
            
            $layerData = $dataSpecs['path'];
            $layerName = $dataSpecs['uuid'];
            $layerSpatialType = $dataSpecs['spatial_type'];
            $le = $this -> getMapExtent($dataSpecs);
            $dataSpecs['mapext'] = $le;
            $this -> map -> setExtent($le['minx'], $le['miny'], $le['maxx'], $le['maxy']);

            $connParams = $this -> getLayerConnectionParams($layerData);
            $dataSpecs['connParams'] = $connParams;

            $this -> dataSpecs["$uuid"] = $dataSpecs;

            $layerString = $this -> getLayerDefinition($connParams, $layerSpatialType);
            //$_SESSION['catalogueDataSpecs'] = $dataSpecs;
            if ($layerString != null) {
                $layerList[$layerName] = array("layerDefinition" => $layerString);
            }
        }
        // Store in session for current object use or for later use
        // from globals.php
        $_SESSION['catalogueDataSpecs'] = $this -> dataSpecs;
        return $layerList;
    }

    protected function postprocessDynLayers() {
        $_SESSION['defGroups'] = array_merge($_SESSION['defGroups'], $this -> layerNames);
    }

    /**
     * Create dynamic layers based on its definition from the metadata record
     */
    protected function createDynLayer($layerName, $layerString) {
        //error_log("createDynLayer: " . $layerName . "-" . $layerString);
        $newLayer = ms_newLayerObj($this -> map);
        $newLayer -> updateFromString($layerString);
        
        $dataSpecs = $_SESSION['catalogueDataSpecs'][$layerName];
        
        $connectionType = $dataSpecs['connParams']['type'];

        if ($connectionType == "generic") {
            $newLayer -> set("data", $this -> data_path_prefix . $dataSpecs['path']);
        } else {
            $connTypeList['POSTGIS'] = MS_POSTGIS;
            $connTypeList['OGR'] = MS_OGR;
            $connTypeList['WMS'] = MS_WMS;
            $connTypeList['ORACLESPATIAL'] = MS_ORACLESPATIAL;

            $newLayer -> setConnectiontype($connTypeList[$connectionType]);
            $newLayer -> set("connection", $dataSpecs['connParams']['connection']);

            if ($connectionType == "POSTGIS") {
                $geom = $dataSpecs['connParams']['geom'];
                $table = $dataSpecs['connParams']['schema'] . "." . $dataSpecs['connParams']['layer'];
                $uniqueFid = $dataSpecs['connParams']['uniqueFid'];
                $epsg = $dataSpecs['srscode'];
                $newLayer -> set("data", "$geom FROM $table USING UNIQUE $uniqueFid USING SRID=$epsg");
            } else {
                $newLayer -> set("data", $dataSpecs['path']);
            }
        }

        $newLayer -> set("name", $dataSpecs['uuid']);
        $newLayer -> setMetaData("DESCRIPTION", $dataSpecs['title']);
        $newLayer -> setMetaData("UUID", $dataSpecs['uuid']);
        if (isset($dataSpecs['feature_catalogue_url'])) {
            $newLayer -> setMetaData("FEATURE_CATALOGUE_URL", $dataSpecs['feature_catalogue_url']);
        }
        $newLayer -> setMetaData("CATEGORY", $this -> config['layer_category']);

        // Move layer to configured index
        $newLayerCurrentIdx = $newLayer -> index;
        $newLayerTargetIdx = $this -> config['layeridx'];
        $newDrawingOrder = $this -> getNewDrawingOrder($this -> map -> getLayersDrawingOrder(), $newLayerCurrentIdx, $newLayerTargetIdx);
        $this -> map -> setLayersDrawingOrder($newDrawingOrder);

        // Set projections and extent
        $projStr = "init=" . $dataSpecs['epsg'];
        $newLayer -> setProjection($projStr);
        $this -> map -> setProjection($projStr, 1);
	$_SESSION['DYNLAYERCAT_PROJ'] = $projStr; 
        $le = $dataSpecs['mapext'];
        $this -> map -> setExtent($le['minx'], $le['miny'], $le['maxx'], $le['maxy']);
        
        if (!empty($this -> config['mapfile_dir'])) {
            $this -> map -> save($this -> config['mapfile_dir'] . $layerName . '.map');
        }
    }

    

    protected function getNewDrawingOrder($a, $ci, $ti) {
        $alen = count($a);
        $na = array();
        for ($i = 0; $i < $alen; $i++) {
            if ($i < $ti) {
                $na[] = $i;
            } elseif ($i == $ti) {
                $na[] = $ci;
            } elseif ($i > $ti) {
                $na[] = $i - 1;
            }
        }
        return $na;
    }

    protected function getLayerConnectionParams($layerFilename) {
        $connParams = array();

        if (strpos($layerFilename, ":")) {
            $ogrDBConn = true;
            $connList = explode("/", $layerFilename);
            $conn = $this -> connections[$connList[0]];
            $layerList = explode(".", $connList[1]);
            $schema = $layerList[0];
            $layerName = $layerList[1];
            $uniqueFid = $this -> getLayerUniqueFid($schema, $layerName, $conn['connection']);
            $geomFld = $this -> getGeomFld($schema, $layerName, $conn['connection']);

            $connParams['connection'] = $conn['connection'];
            $connParams['schema'] = $schema;
            $connParams['type'] = $conn['type'];
            $connParams['layer'] = $layerName;
            $connParams['geom'] = $geomFld;
            $connParams['uniqueFid'] = $uniqueFid;
        } else {
            $ogrConnectionStr = $layerFilename;
            $connParams['type'] = "generic";
            $connParams['layer'] = $layerFilename;
        }
        return $connParams;
    }

    protected function getLayerUniqueFid($schema, $layerName, $dsn) {
        $sql = " SELECT
                    pg_attribute.attname as unique_id,
                    format_type(pg_attribute.atttypid, pg_attribute.atttypmod) 
                 FROM 
                    pg_index, pg_class, pg_attribute 
                 WHERE
                    pg_class.oid = (SELECT oid FROM pg_class WHERE relname = '$layerName') AND
                    pg_class.relnamespace = (SELECT oid FROM pg_namespace WHERE nspname = '$schema') AND
                        indrelid = pg_class.oid AND
                        pg_attribute.attrelid = pg_class.oid AND
                        pg_attribute.attnum = any(pg_index.indkey)
                    AND indisprimary
                ";
        //error_log($sql);
        //print("toto".$dsn);
        $conn = pg_pconnect($dsn);
        $result = pg_query($conn, $sql);
        $row = pg_fetch_assoc($result);

        return $row['unique_id'];

    }

    protected function getGeomFld($schema, $layerName, $dsn) {
        $sql = " SELECT
                    f_geometry_column AS geom
                 FROM 
                    geometry_columns 
                 WHERE
                    f_table_name = '$layerName' 
                    AND f_table_schema = '$schema'
                ";
        //error_log($sql);
        $conn = pg_pconnect($dsn);
        $result = pg_query($conn, $sql);
        $row = pg_fetch_assoc($result);
        return $row['geom'];
    }

    /**
     * Return layer definition string
     * reads either *.msl file with same base name as data file
     *   or default polygon/line/point.lyr file from plugin dir
     * @return string $layerString
     */
    protected function getLayerDefinition($connParams, $layerSpatialType) {
        $ogrDBConn = false;
        if ($connParams['type'] == "generic") {
            $ogrConnectionStr = $this -> data_path_prefix . $connParams['layer'];
            //error_log($ogrConnectionStr);
            // $layerDefFile = dirname(__FILE__) . "/" . $connParams['layer'] . ".msl";   ## swap comment if layer definition file in plugin dir
            $layerDefFile = $ogrConnectionStr . ".msl";
        } else {
            $ogrConnectionStr = "PG: " . $connParams['connection'];
            $layerDefFile = dirname(__FILE__) . "/layerdefinition/" . $connParams['schema'] . "." . $connParams['layer'] . ".msl";
            //$layerDefFile = dirname(__FILE__) . "/layerdefinition/" . $connParams['layer'] . ".msl";
        }

        if ($layerSpatialType == "vector") {
            if (!file_exists($ogrConnectionStr) && $connParams['type'] == "generic") {
                error_log($ogrConnectionStr . " does not exist.");
                return null;
            }

            // Register all drivers
            OGRRegisterAll();

            // Open data source
            $hSFDriver = NULL;
            $hDatasource = OGROpen($ogrConnectionStr, 0, $hSFDriver);

            if (!$hDatasource) {
                error_log("Unable to open %s\n" . $ogrConnectionStr);
                return 0;
            }

            if ($connParams['type'] == "generic") {
                $hLayer = OGR_DS_GetLayer($hDatasource, 0);
            } else {
                $hLayer = OGR_DS_GetLayerByName($hDatasource, $connParams['schema'] . "." . $connParams['layer']);
            }

            /* Dump info about this layer */
            $hLayerDefn = OGR_L_GetLayerDefn($hLayer);
            $hFeature = OGR_L_GetNextFeature($hLayer);
            if (OGR_F_GetGeometryRef($hFeature) != NULL) {
                $hGeom = OGR_F_GetGeometryRef($hFeature);
                //$geomType = OGR_G_GetGeometryType($hGeom);
                $geomNameOGR = OGR_G_GetGeometryName($hGeom);
            }
            /* Close data source */
            OGR_DS_Destroy($hDatasource);

            /*$geomList[1] = "point";
             $geomList[2] = "line";
             $geomList[3] = "polygon";
             $geomList[6] = "polygon";*/

            $geomList["POINT"] = "point";
            $geomList["MULTIPOINT"] = "point";
            $geomList["LINESTRING"] = "line";
            $geomList["MULTILINESTRING"] = "line";
            $geomList["POLYGON"] = "polygon";
            $geomList["MULTIPOLYGON"] = "polygon";

            //            $geomName = $geomList[$geomType];
            $geomName = $geomList[$geomNameOGR];
        } else {
            $geomName = "raster";
        }

        if (is_file($layerDefFile)) {
            //if (is_file($layerDefFile = $layerFilename . ".msl")) {
            $layerString = file_get_contents($layerDefFile);
        } else {
            $layerPath = dirname(__FILE__) . "/layerdefinition/$geomName.lyr";
            $layerString = file_get_contents($layerPath);
        }
        //error_log("layerDefFile: $layerDefFile");

        return $layerString;
    }

    /**
     * Return map extent in projected coordinates
     * @return array $mePrj
     */
    protected function getMapExtent($dataSpecs) {
        $extBuffer = isset($_SESSION['pluginsConfig']['dynlayercat']['mapExtentBuffer']) ? $_SESSION['pluginsConfig']['dynlayercat']['mapExtentBuffer'] : 0;
        $layerProjStr = "init=" . $dataSpecs['epsg'];
        $layerPrj = ms_newprojectionobj($layerProjStr);
        $latlonPrj = ms_newprojectionobj("init=epsg:4326");
        //$le = $dataSpecs['bbox'];
        $le = array();
        foreach ($dataSpecs['bbox'] as $k => $v) {
            $buffer = $v < 0 ? $extBuffer * -1 : $extBuffer;
            $le[$k] = $v + $buffer;
        }

        $mapExt = ms_newRectObj();
        $mapExt -> setExtent($le['westLon'], $le['southLat'], $le['eastLon'], $le['northLat']);
        $mapExt -> project($latlonPrj, $layerPrj);
        //error_log($mapExtLatLon->minx . ", " . $mapExtLatLon->miny . ", " . $mapExtLatLon->maxx . ", " . $mapExtLatLon->maxy  );

        $mePrj = array("minx" => $mapExt -> minx, "miny" => $mapExt -> miny, "maxx" => $mapExt -> maxx, "maxy" => $mapExt -> maxy);
        return $mePrj;
    }

    function printDebug($dbgstr0, $headerstr = false) {
        ob_start();
        print_r($dbgstr0);
        $dbgstr = ob_get_contents();
        ob_end_clean();

        $errlog_dir = str_replace('\\', '/', dirname(ini_get("error_log")));
        if (file_exists($errlog_dir)) {
            $outMapFN = $errlog_dir . "/pm_debug.log";

            date_default_timezone_set($_SESSION['defaultTimeZone']);
            // Required for PHP 5.3
            $header = "\n[" . date("d-M-Y H:i:s") . "] P.MAPPER debug info \n";
            if ($headerstr)
                $header .= "$headerstr\n";
            $fpOut = fopen($outMapFN, "a+");
            if (!$fpOut) {
                error_log("Cannot create debug log file $fpOut. Check permissions.");
                return false;
            }
            fwrite($fpOut, "$header $dbgstr");
            fclose($fpOut);
        } else {
            error_log("Incorrect setting for 'error_log' in 'php.ini'. Set to a valid file name.");
        }
    }

}
?>
