<?php
include_once("transparency.inc");

$groupname = $_REQUEST['groupname'];
$transparency = $_REQUEST['transparency'];
$opacity = 100 - $transparency;

$grouplist = $_SESSION["grouplist"];
$groups = $_SESSION["groups"];

// Check if layer is visible and map shall be reloaded
$reload = in_array($groupname, $groups) ? 1 : 0;

// Set opacity to GROUP object
$grp = $grouplist["$groupname"];
$glayerList = $grp->getLayers();
foreach ($glayerList as $glayer) {
    $glayer->setOpacity($opacity); 
}

header("Content-Type: text/plain");
echo "{\"reload\":$reload}";
?>