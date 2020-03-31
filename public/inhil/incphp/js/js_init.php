<script type="text/javascript">

 var SID = '<?php echo SID ?>';
 var PM_XAJAX_LOCATION  = 'incphp/xajax/';
 var PM_INCPHP_LOCATION = 'incphp';
 var PM_PLUGIN_LOCATION = 'plugins';
     
 PM.mapW = <?php echo $mapW ?>;
 PM.mapH = <?php echo $mapH ?>;
 PM.refW = <?php echo $refW ?>;
 PM.refH = <?php echo $refH ?>; 
 //PM.sid = '<?php echo session_id() ?>';
 //PM.sname = '<?php echo session_name() ?>';
 PM.gLanguage = '<?php echo $gLanguage ?>';
 PM.config = '<?php echo trim($config) ?>';
 PM.tocStyle = '<?php echo $_SESSION["tocStyle"] ?>';
 PM.legendStyle = '<?php echo $_SESSION["legendStyle"] ?>';
 PM.infoWin = '<?php echo $_SESSION["infoWin"] ?>';
 PM.s1 = <?php echo $maxScale ?>;
 PM.s2 = <?php echo $minScale ?>;
 PM.dgeo_x = <?php echo $dgeo['x'] ?>;
 PM.dgeo_y = <?php echo $dgeo['y'] ?>;
 PM.dgeo_c = <?php echo $dgeo['c'] ?>;
 PM.layerAutoRefresh = <?php echo ($_SESSION['layerAutoRefresh']) ?>;
 PM.tbThm = '<?php echo $toolbarTheme ?>';
 
 PM.pluginTocInit = [<?php if (count($plugin_jsTocInitList) > 0) echo ("'" . implode("','", $plugin_jsTocInitList) . "'"); ?>];
 <?php
    // only load configuration for activated plugins
    $ini['pluginsConfig'] = $_SESSION['pluginsConfig'];
    // add allGroups if not defined in XML config
    if (!$ini['map']['allGroups']) $ini['map']['allGroups']['group'] = $_SESSION['allGroups'];
 ?>
 
 PM.ini = <?php echo PMCommon::parseJSON($ini, false) ?> ;
 
 <?php echo PMCommon::writeJSArrays() ?>
 
 $.merge(PM.categoriesClosed, <?php echo ("['" . implode("','", $_SESSION['categoriesClosed']) . "']") ?>);

// Query layers: modify query results in js
PM.modifyQueryResultsFunctions = [<?php if (count($plugin_jsModifyQueryResultsFunctions) > 0) echo ("'" . implode("','", $plugin_jsModifyQueryResultsFunctions) . "'"); ?>];

</script>
