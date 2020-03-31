<?php

$jsFiles = array('../common/common.js', '../common/queryResultCallBack.js', 'selectionManagement.js');

$jsInitFunction = 'PM.Plugin.SelectionManagement.init()';

$cssFiles = array('selectionManagement.css');

$jsModifyQueryResultsFunctions = array('PM.Plugin.SelectionManagement.extendQueryLayersHeaders', 'PM.Plugin.SelectionManagement.extendQueryLayersValues');

?>