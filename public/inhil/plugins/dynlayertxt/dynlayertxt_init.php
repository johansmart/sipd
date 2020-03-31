<?php

$old_path = getcwd();
$fileDirname = str_replace('\\', '/', realpath(dirname(__FILE__)));
//error_log("fileloc: " . $fileDirname);
chdir($fileDirname);

require_once("dynlayertxt.php");

$dyn = new DynLayerTxt($map);
$dyn->initDynLayers(true);

chdir($old_path);

?>