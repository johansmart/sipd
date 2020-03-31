<?php
// prevent XSS
if (isset($_REQUEST['_SESSION'])) exit();

require_once("../../incphp/pmsession.php");
require_once("wmsclient.php");

$req = new $_REQUEST['wmsrequest'];

class GetCapabilities
{
    public function __construct()
    {
        $err = 0;
        $errMsg = "";

        $wmsurl = $_REQUEST['wmsurl'];
        $wms = new WMSClient($wmsurl);

        if ($wms->checkError()) {
            $err = 1;
            $errMsg = $wms->returnErrorMsg();
        }

        $layers = json_encode($wms->getLayerList());
        $imgFormats = json_encode($wms->getImgFormats());
        $srsList = json_encode($wms->getSrsList());

        // return JS object literals "{}" for XMLHTTP request 
        header("Content-Type: text/plain; charset=$defCharset");
        echo "{\"err\":$err, \"errMsg\":\"$errMsg\", \"layers\":$layers, \"imgFormats\":$imgFormats, \"srsList\":$srsList}";
    }
}


class WmsLayer
{
    public function __construct()
    {
        
    
    }
}

?>