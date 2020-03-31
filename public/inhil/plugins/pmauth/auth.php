<?php
/******************************************************************************
*
* Purpose: Authentication script checkin
* Author:  Walter Lorenzetti, gis3w, lorenzetti@gis3w.it
*
******************************************************************************
*
* Copyright (c) 2008-2010 gis3w
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version. See the COPYING file.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with p.mapper; if not, write to the Free Software
* Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
******************************************************************************/

require_once('incphp/db.php');
require_once('incphp/auth.php');


// get the congif_*.xml file that contain pmauth plugin
// scanning dir
$dir = scandir($_SESSION['PM_BASECONFIG_DIR']);
$_SESSION['PM_PLUGIN_PMAUTH_CONFIGS'] = $_SESSION['PM_PLUGIN_PMAUTH_CONFIGS_NOAUTH'] = array();
foreach($dir as $fd){
  if (substr($fd,0,7) == 'config_' &&  substr($fd,-4) == '.xml'){
    $cfg = substr($fd,7,-4);
    $iniXmlFile = $_SESSION['PM_BASECONFIG_DIR']. "/".$fd;
        if (file_exists($iniXmlFile)) {
            $iniXml = simplexml_load_file($iniXmlFile, NULL, LIBXML_NOENT);
            $pls = get_object_vars($iniXml->ini->pmapper);
            if(in_array('pmauth', $pls['plugins'])){
              $_SESSION['PM_PLUGIN_PMAUTH_CONFIGS'][] = $cfg;
            }else{
              $_SESSION['PM_PLUGIN_PMAUTH_CONFIGS_NOAUTH'][]=$cfg;
            }
            // add for mapselect plugin
            if(in_array('mapselect', $pls['plugins'])){
            	if(isset($iniXml->ini->pluginsConfig) && isset($iniXml->ini->pluginsConfig->mapselect)){
    		  $opts = get_object_vars($iniXml->ini->pluginsConfig->mapselect);
            	  $_SESSION['PM_PLUGIN_PMAUTH_MAPSELECT_NAME'][$cfg] = $opts['nameoption'];
            	}
            }
            
        }
  }
}


$db = new Db();
// start user/password authentication
$a = Auth::getInstance($db);
$_SESSION['pmLogoSrc'] = '../../images/logos/logo-black.png';
$a->formTarget = $_SERVER['REQUEST_URI'];
$a->authView = $_SESSION['PM_PLUGIN_LOCATION'].'/pmauth/auth.phtml';

if($a->getAuth()){
  // redirect to default user config
  if($a->firstLogin && $_SESSION['config'] !=  $a->configs['def']) {header('Location: ?config='. $a->configs['def']); exit();};
  unset($_SESSION['pmLogoSrc']);
  // start config pass: check if user can access to this profile
  if($a->id_role != 0 && !in_array($_SESSION['config'], $a->configs['cfgs'])){
    $a->logout();
  }
}

