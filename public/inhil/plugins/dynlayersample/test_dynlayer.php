<?php

require_once("dynlayer.php");

require_once("benchmark/Timer.php");
$timer = new Benchmark_Timer(0);
#$timer->start();

require_once('../../incphp/pmsession.php');

$categories['cat_admin']  = array("countries", "cities10000eu", "settlements");
$categories['cat_nature'] = array("rivers", "corine");
$categories['cat_raster'] = array("dem", "jpl_wms_global_mosaic"); 
$_SESSION['categories'] = $categories;


/**
 * INITIALIZE MAP
 */
$PM_MAP_FILE = "D:/webdoc/eclipse/pmapper_trunk/config/default/pmapper_demo.map";
$map = ms_newMapObj($PM_MAP_FILE);

//print_r($map);

$timer->start();

$jsonFile = "dynlayer_def.txt";
$dynLayers = preg_replace(array("/\s{2,}/", "/\n/"), array(" ", ""), file_get_contents($jsonFile));
$_SESSION['dynLayers'] = json_decode($dynLayers, true);


$dyn = new DynLayer($map, json_encode($_SESSION['dynLayers']));
$dyn->createDynLayers();

//$map->getLayerByName("cities_1")->set("status", MS_ON);
$map->getLayerByName("cities_2")->set("status", MS_ON);
$map->getLayerByName("countries")->set("status", MS_ON);



//print_r($map->getAllLayerNames());
$map->selectOutputFormat("png");
$map_img = $map->draw();
$map_url = $map_img->saveWebImage();
require_once('../../incphp/common.php');
PMCommon::freeMsObj($map_img);

#$timer->stop();
#$timer->display();

$map->save("D:/webdoc/tmp/dynlayer.map");


$dynJson = json_decode($dynLayers);
$lay1 = $dynJson->newlayers->layerlist->cities_1;
unset($dynJson->newlayers->layerlist->cities_1); // = null;
$lay1->name = "pippo";
$dynJson->newlayers->layerlist->cities_1 = $lay1;
#print_r($dynJson->newlayers->layerlist->cities_2);
//print_r($dynJson->newlayers->layerlist);


//unset($_SESSION['dynLayers']['cities_2']);
//$_SESSION['dynLayers']['cities_2'] = null;

//print_r(json_decode(json_encode($_SESSION['dynLayers'])));


?>