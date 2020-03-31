<?php
/****************************************************
 some annotations in the correct locale have to be 
 available directly from JavaScript
 They are produced as associated array 'localeList'
 creating the entries via PHP

*****************************************************/
include_once("group.php");
require_once('../pmsession.php');


function returnImgSrc()
{
    $grouplist = $_SESSION["grouplist"];
    $allGroups = $_SESSION["allGroups"];
    $imgFormat = $_SESSION["imgFormat"];
    $scaleLayers = $_SESSION["scaleLayers"];

    $legPath =  "images/legend/";
     
    foreach ($grouplist as $grp){
                           
        $glayerList = $grp->getLayers();
        foreach ($glayerList as $glayer) {
            //$legLayer = $map->getLayer($glayer->getLayerIdx());
            $legLayerType = $glayer->getLayerType();
            $skipLegend = $glayer->getSkipLegend();
            $numClasses = count($glayer->getClasses());
            
            if (($legLayerType < 3 || $legIconPath || $numClasses > 1) && $skipLegend < 2) {
                $legLayerName = $glayer->getLayerName();
                
                if ($legLayerType < 3 || $numClasses > 1) { 
                    $classes = $glayer->getClasses();
                } else {
                    $classes = array($grpDescription);
                }
                
                $clno = 0;
                foreach ($classes as $cl) {
                    if ($legLayerType < 3 || $numClasses > 0) { 
                        $icoUrlList[] = $legPath.$legLayerName.'_i'.$clno.'.'.$imgFormat;
                    }
                    $clno++;
                }
            }
        }
    }
    
    $treeList = array("base.gif", "cd.gif", "empty.gif","folder.gif","folderopen.gif","globe.gif","imgfolder.gif", "join.gif","joinbottom.gif","layers.gif",   "layers0.gif","line.gif","minus.gif","minusbottom.gif","musicfolder.gif","nolines_minus.gif","nolines_plus.gif","page.gif","plus.gif","plusbottom.gif","question.gif","trash.gif");    
    //printDebug($icoUrlList);
    foreach ($treeList as $timg) {
        $icoUrlList[] = "images/tree/$timg";
    }
    
    
    return $icoUrlList;
}




?>

//<SCRIPT LANGUAGE="Javascript">

function preloadImages() {
    var imgUrlList = new Array();
<?php 
    $icoUrlList = returnImgSrc();
    $i=0;  
    foreach ($icoUrlList as $u) {
        $js .= "imgUrlList[$i] = '$u;'\n";
        $i++;
    }
    //$js .= "imgUrlList[$i++] = '$u;'\n";
    //error_log($js);
    echo $js;
    echo ("var listCnt = 3;");
?>
    
    var preload_image_object = new Image();
    for(var i=0; i<=listCnt; i++) {
        //alert(imgUrlList[i]);
        preload_image_object.src = imgUrlList[i];
        //if (!preload_image_object.complete) preload_image_object.src = imgUrlList[i];
    }
}



//</SCRIPT>

