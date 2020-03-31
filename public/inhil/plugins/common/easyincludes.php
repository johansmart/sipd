<?php

/******************************************************************************
 *
 * Purpose: Function for easy includes in php scripts
 * Author:  Thomas Raffin, SIRAP
 *
 ******************************************************************************
 *
 * Copyright (c) 2007 SIRAP
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

/**
 * Get jQuery files include string (<script ...>)
 *
 * @param string $pmDir: p.mapper main directory URL
 * To get it, use getURLReqDir function
 * @param array $filesname: name of the file contained in the javascript/jquery directory
 * @return js include string
 * If no $filesname specified, only the jquery file is return
 * else the jqueryfile and the others specified...
 * For instance : "blockUI" will return the string for "jquery-1.1.3.1.pack.js" and "jquery.blockUI.js"
 * @see getURLReqDir
 */
function getJQueryFiles($pmDir, $filesname = null) {
	$jqueryFiles = PMCommon::scandirByExt($_SESSION['PM_INCPHP'] . '/../' . $_SESSION['PM_JAVASCRIPT'] . '/jquery', 'js');
	$jsFiles = '';
	sort($jqueryFiles);

	if (strrpos($_SESSION['PM_INCPHP_LOCATION'], $pmDir) !== false
	&& strrpos($_SESSION['PM_INCPHP_LOCATION'], $pmDir) == (strlen($pmDir) - strlen($_SESSION['PM_INCPHP_LOCATION']))) {
		$relPath = '../';
	} else {
		$relPath  = '';
	}
	$urlJS = $pmDir . $relPath . $_SESSION['PM_JAVASCRIPT'];

	foreach ($jqueryFiles as $jqf) {
		if ( (strpos($jqf, 'jquery-') === 0) || (strpos($jqf, 'jquery_') === 0) ) {
			$jsFiles .= "<script type=\"text/javascript\" src=\"$urlJS/jquery/$jqf\"></script>\r\n";
		}
		if ($filesname) {
			foreach($filesname as $filename) {
				if (strpos($jqf, 'jquery.' . $filename) === 0) {
					$jsFiles .= "<script type=\"text/javascript\" src=\"$urlJS/jquery/$jqf\"></script>\r\n";
				}
			}
		}
	}
	return $jsFiles;
}

/**
 * Get jQuery files include string (<script ...>)
 *
 * @param string $pmDir: p.mapper main directory URL
 * To get it, use getURLReqDir function
 * @return js include string
 * @see getURLReqDir
 */
function getJSFiles($pmDir) {
	$jsFiles = '';
	
	if (strrpos($_SESSION['PM_INCPHP_LOCATION'], $pmDir) == (strlen($pmDir) - strlen($_SESSION['PM_INCPHP_LOCATION']))) {
		$relPath = '../../';
	} else {
		$relPath  = '';
	}
	$urlJS = $pmDir . $relPath . $_SESSION['PM_JAVASCRIPT'];

	$scanFiles = PMCommon::scandirByExt($_SESSION['PM_INCPHP'] . '/../' . $_SESSION['PM_JAVASCRIPT'] . '/jquery', 'js');
	sort($scanFiles);
	foreach ($scanFiles as $scanFile) {
		$jsFiles .= "<script type=\"text/javascript\" src=\"$urlJS/jquery/$scanFile\"></script>\n";
	}

	$scanFiles = PMCommon::scandirByExt($_SESSION['PM_INCPHP'] . '/../' . $_SESSION['PM_JAVASCRIPT'], 'js');
	sort($scanFiles);
	foreach ($scanFiles as $scanFile) {
		$jsFiles .= "<script type=\"text/javascript\" src=\"$urlJS/$scanFile\"></script>\n";
	}

	return $jsFiles;
}


/**
 * Get the requested URL
 */
function getURLReq($doQuickClean = false) {
	$url = '';
	
	if (isset($_ENV) && isset($_ENV['REQUEST_URI']) && $_ENV['REQUEST_URI']) {
		$url = $_ENV['REQUEST_URI'];
	} else {
		$url = $_SERVER['REQUEST_URI'];
	}
	
	$pos = strpos($url, '@');
	if ($pos !== false) {
		$url = substr($url, 0, $pos);
	}
	
	if ($doQuickClean) {
		$pos = strpos($url, '?');
		if ($pos !== false) {
			$url = substr($url, 0, $pos);
		}
	}
	
	return $url;
}


/**
 * Get the requested URL directory (after quick cleaning)
 */
function getURLReqDir() {
	$urlReqDir = getURLReq(true);
	
	$pos = strrpos($urlReqDir, '/');
	if ($pos !== false) {
		$urlReqDir = substr($urlReqDir, 0, $pos + 1);
	}
	
	return $urlReqDir;
}

/**
 * Get the pmapper directory URL (containing map.phtml for isntance)
 *
 * @param string $pluginDirName: name of the plugin directory.
 * @return current p.mapper main url
 */
function getPMDir($pluginDirName) {
	$urlReqDir = getURLReqDir();
	$pmDir = substr($urlReqDir, 0, strpos($urlReqDir, $_SESSION['PM_PLUGIN_LOCATION'] . '/' . $pluginDirName .'/'));
	return $pmDir;
}

/**
 * Get the CSS include string for HTML files generation (<link ...>)
 *
 * This function is used to easy get all the "<link>" elements for CSS
 * used in the current configuration. So, if called in a plugin, even in
 * new opening pages you can have the same appearence than in the map.phtml.
 *
 * @param string $pmDir: p.mapper main directory URL
 * @return string containing "<link ..."
 */
function getCSSReference($pmDir) {
	$ret = '';

    $dirNames = array();

    //- from config/common (custom) dir
    $dirNames[] = PM_CONFIG_LOCATION_COMMON;

    //- from config dir
    $dirNames[] = PM_CONFIG_LOCATION;

    foreach($dirNames as $dirName) {
        if (file_exists(PM_BASECONFIG_DIR . '/' . $dirName)) {
            $cssFiles = PMCommon::scandirByExt(PM_BASECONFIG_DIR . '/' . $dirName, 'css');
            $cssFiles = array_unique($cssFiles);
            if (count($cssFiles) > 0) {
                foreach ($cssFiles as $cf) {
                    $ret .= " <link rel=\"stylesheet\" href=\"config/$dirName/$cf\" type=\"text/css\" />\n";
                }
            }
        }
    }

	return $ret;
}


/**
 * get real path
 *
 * - replace '\' with '/'
 * - cacluate path :
 *   - if absolute : pathToDecode
 *   - if relative : defaultBasePath + '/' + pathToDecode
 *
 * @param object $pathToDecode
 * @param object $defaultBasePath [optional] if not specified, pmapper base directory will be used
 * @return real path
 */
function getRealPath($pathToDecode, $defaultBasePath = false) {
	$path = '';
	$pathTmp = trim($pathToDecode);
    // try as absolute path
    if ($pathTmp{0} == '/' || $pathTmp{1} == ':') {
        $path = str_replace('\\', '/', $pathTmp);
    // else as relative to $PM_CONFIG_DIR
    } else {
    	if (!$defaultBasePath) {
    		$defaultBasePath = $_SESSION['PM_BASE_DIR'];
    	}
    	$defaultBasePath = trim($defaultBasePath);
		$defaultBasePath = str_replace('\\', '/', $defaultBasePath);
        $path = str_replace('\\', '/', realpath("$defaultBasePath/$pathTmp"));  
    }
	return $path;
}

?>