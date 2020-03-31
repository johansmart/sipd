<?php
$old_path = getcwd();
$fileDirname = str_replace('\\', '/', realpath(dirname(__FILE__)));
chdir($fileDirname);

require_once ("dynlayercat.php");

$dyn = new DynLayerCat($map);
$dyn -> initDynLayers(true);

chdir($old_path);
?>