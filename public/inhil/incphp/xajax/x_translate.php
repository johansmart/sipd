<?php
require_once("../pmsession.php");
require_once("../globals.php");
require_once("../common.php");

$translateStr = $_REQUEST['translate'];
$translateList = explode(',', $translateStr);

$ret = "[";
foreach ($translateList as $ts) {
    $ret .= "'" . _p($ts) . "',";
}
$ret = substr($ret, 0, -1);
$ret .= "]";

//error_log($ret);

header("Content-Type: text/plain; charset=$defCharset");

// return JS object literals "{}" for XMLHTTP request 
echo "{\"translation\":\"$ret\"}";
?>