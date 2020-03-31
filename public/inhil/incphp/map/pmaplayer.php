<?php

class PMapLayer
{
    protected $map;
    protected $layername;
    protected $layer;
    protected $layertype;
    
   /**
    * Class constructor
    * @param object $map map object
    * @param string $layername name of layer
    * @return void
    */ 
    public function __construct($map, $layername)
    {
        $this->map = $map;
        $this->layername = $layername;
        $this->layer = $map->getLayerByName($layername);
        $this->layertype = $this->layer->type;
        
        //pm_logDebug(3, $map->getAllLayerNames(), "all layers");
    }
    
   /**
    * Return extent of layer as array
    * @param bool $inMapProjection define if extent shall be returned in map projection 
    * @return object extent with minx, miny, maxx, maxy properties 
    */
    public function getLayerExtent($inMapProjection)
    {
        // PostgIS layers
        if ($this->layer->connectiontype == 6) {
            $data = trim($this->layer->data);
            $dataList1 = preg_split("/\s/", $data);
            $dataList2 = preg_split("/using/i", $data);
            $geomFld = array_shift($dataList1);

            // use filter for layer extent
            $filter = trim($this->layer->getFilterString());
            if (!$filter) $filter = "TRUE";
            $sql = "select ST_xmin(extent) as minx, ST_ymin(extent) as miny, ST_xmax(extent) as maxx, ST_ymax(extent) as maxy  
                    from (SELECT St_Extent($geomFld) as extent " . substr($dataList2[0], strlen($geomFld)) . "WHERE $filter) as bar";

            pm_logDebug(3, $sql, "P.MAPPER-DEBUG: pmaplayer.php/getLayerExtent() - SQL for PG layer extent");
            
            // load DLL on Win if required
            if (PHP_OS == "WINNT" || PHP_OS == "WIN32") {
                if (! extension_loaded('pgsql')) {
                    if( function_exists( "dl" ) ) {
                        dl('php_pgsql.dll');
                    } else {
                        error_log("P.MAPPER ERROR: This version of PHP does support the 'dl()' function. Please enable 'php_pgsql.dll' in your php.ini");
                        return false;
                    }
                }
            }
            
            $connString = $this->layer->connection;
            if (!($connection = pg_Connect($connString))){
               error_log ("P.MAPPER: Could not connect to database");
               error_log ("P.MAPPER: PG Connection error: " . pg_last_error($connection));
               exit();
            }
            
            $qresult = pg_query ($connection, $sql);
            if (!$qresult) error_log("P.MAPPER: PG Query error for : $query" . pg_result_error($qresult));
            
            $pgE = pg_fetch_object($qresult);
            $layerExt = ms_newRectObj();
            $layerExt->setExtent($pgE->minx, $pgE->miny, $pgE->maxx, $pgE->maxy); 
                   
        } else {
            $layerExt = @$this->layer->getExtent();
            
            // Raster layers (no extent function available, so take map extent) 
            if (!$layerExt) {
                $layerExt = $this->map->extent;
            }
            
            pm_logDebug(3, $this->layer->type, "pmap layerInfo");
        }
        
        
        
        // if layer projection != map projection, reproject layer extent
        if ($inMapProjection) {
            $mapProjStr = $this->map->getProjection();
            $layerProjStr = $this->layer->getProjection();
        
            if ($mapProjStr && $layerProjStr && $mapProjStr != $layerProjStr) {
                if ($_SESSION['MS_VERSION'] < 6) {
                	$mapProjObj = ms_newprojectionobj($mapProjStr);
                	$layerProjObj = ms_newprojectionobj($layerProjStr);
                } else {
                	$mapProjObj = new projectionObj($mapProjStr);
                	$layerProjObj = new projectionObj($layerProjStr);
                }
                $layerExt->project($layerProjObj, $mapProjObj);
            } 
        }
        pm_logDebug(3, $layerExt, "pmap layerExt");
        
        return $layerExt;
    }

}


?>