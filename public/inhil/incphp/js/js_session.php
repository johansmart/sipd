<?php
header("Content-type: text/javascript");
require_once("../group.php");
require_once("../pmsession.php");

$gLanguage = $_SESSION["gLanguage"];
require_once "../common.php";
require_once "../locale/language_" . $gLanguage . ".php";

// Print list with locales for access via JS
foreach ($_sl as $k=>$v) {
    print "PM.Locales.list['$k'] = '" . str_replace('\\\\r\\\\n', '\\r\\n', addslashes($v)) . "';\n";
}

// Write compressed JS functions from plugins
$plugin_jsFileList = $_SESSION['plugin_jsFileList'];    
$debugLevel = $_SESSION['debugLevel'];
if (count($plugin_jsFileList) > 0) {
    foreach ($plugin_jsFileList as $jsLoc) {
        $jsFile = $_SESSION['PM_BASE_DIR'] . "/$jsLoc";
        print PMCommon::compressJavaScriptFile($jsFile, $debugLevel);
    }
}

print ("PM.grouplist = " . PMCommon::parseJSON($_SESSION['grouplist'], false) . ";");



?>