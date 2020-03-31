<?php
/******************************************************************************
*
* Purpose: simple extention of PDO
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
require_once("x_common.php");

// put roles type in a js array
$q = "SELECT * FROM pmauth_roles";
$roles = $db->eQuery($q);

$mapselectNames = json_encode('');
if (isset($_SESSION['PM_PLUGIN_PMAUTH_MAPSELECT_NAME']))
    $mapselectNames = json_encode($_SESSION['PM_PLUGIN_PMAUTH_MAPSELECT_NAME']);

header('Content-type:application/json');
// for retriesve php var for js var
echo '{"vars":{"idRole":'.$a->id_role.',"userconfigs":'.json_encode($a->configs['cfgs']).', "roles":'.json_encode($roles).',"configs":'.json_encode($_SESSION['PM_PLUGIN_PMAUTH_CONFIGS']).',"configs_noauth":'.json_encode($_SESSION['PM_PLUGIN_PMAUTH_CONFIGS_NOAUTH']).',"mapselectNames":'.$mapselectNames.'}}';
