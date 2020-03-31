<?php
include_once("transparency.inc");

$grouplist = $_SESSION["grouplist"];

$transparencies = "{";
foreach ($grouplist as $grp) {
    $groupname = $grp->getGroupName();
    $glayerList = $grp->getLayers();
    $glayer = $glayerList[0];
    $transparency = 100 - $glayer->getOpacity();
    $transparencies .= "\"$groupname\":$transparency,";
}
$transparencies = substr($transparencies, 0, -1) . "}"; 
//error_log($transparencies);

header("Content-Type: text/plain");
echo "{\"transparencies\":$transparencies}";
?>