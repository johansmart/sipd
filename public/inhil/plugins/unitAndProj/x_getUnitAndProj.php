<?php

/******************************************************************************
 *
 * Purpose: get unit and projection use in mapfile
 * Author:  Christophe Arioli, SIRAP
 *
 *****************************************************************************
 *
 * Copyright (c) 2011 SIRAP
 *
 * This is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version. See the COPYING file.
 *
 * p.mapper is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with p.mapper; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 ******************************************************************************/

// prevent XSS
if (isset($_REQUEST['PM_INCPHP'])) exit();

require_once('../../incphp/pmsession.php');

require_once('../../incphp/common.php');
require_once('../../incphp/globals.php');

$projIn = isset($_REQUEST['proj']) ? $_REQUEST['proj'] : false;

$units = -1;
$projInfo = '';

if (!isset($_SESSION['pluginsConfig']['unitAndProj'])) {
    pm_logDebug(0, "P.MAPPER-ERROR: Configuration under '<pluginsConfig><unitAndProj>' is missing. Check your config.xml file ({$_SESSION['PM_BASECONFIG_DIR']}/config_{$_SESSION['config']}.xml).");
} else {
	// get map projection description : init=EPSG:27562
	if (isset($_SESSION['pluginsConfig']['unitAndProj']['projections']['prj'])) {
		$prjList = $_SESSION['pluginsConfig']['unitAndProj']['projections']['prj'];
		if ($prjList) {
			if ($projIn === false) {
				$mapProjection = $map->getProjection();
				$mapProjection = preg_replace('/^\+/', '', $mapProjection);
				// get map units
				if (strcasecmp('init=epsg:4326', $mapProjection) !== 0) {
					$units = $map->units;
				}
			} else {
				$mapProjection = "init=$projIn";
			}
			foreach ($prjList as $p) {
				if (isset($p['definition']) && strcasecmp($p['definition'], $mapProjection) == 0) {
					$projInfo = isset($p['name']) ? $p['name'] : $p['definition'];
					break;
				}
			}
		}
	}
}

$returnJson = json_encode(array('units' => $units, 'projInfo' => $projInfo));

header("Content-Type: text/plain; charset=$defCharset");
echo $returnJson;

?>