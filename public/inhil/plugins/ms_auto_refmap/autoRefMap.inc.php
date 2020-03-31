<?php

/******************************************************************************
 *
 * Purpose: Reference map auto calculate
 * Author:  Thomas Raffin, SIRAP
 *
 ******************************************************************************
 *
 * Copyright (c) 2011 SIRAP
 *
 * This is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version. See the COPYING file.
 *
 * This software is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with p.mapper; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 ******************************************************************************/

// includes:
require_once(dirname(__FILE__) . '/../common/groupsAndLayers.php');
require_once(dirname(__FILE__) . '/../common/pluginsMapUtils.inc.php');

class AutoRefMap
{
	// config values:
	private $pluginConfig;
	private $outputFormat;
	private $altFormat;
	private $groupNames;
	private $autoExtent;
	private $autoExtentGroups;
	private $marginPercent;
	private $marginFixValue;
	private $autoSize;
	private $decreaseHeight;
	private $decreaseWidth;
	private $stepSize;
	
	// members for map:
	private $globalMap;
	private $internalMap;
	private $refMapExtent;
	
	// results members:
	private $refMapWidth;
	private $refMapHeight;
	private $refMapMinX;
	private $refMapMinY;
	private $refMapMaxX;
	private $refMapMaxY;
	private $refMapImage;
	private $refMapURL;
	
	// 
	private $ms_Version;
	
	public function __construct($map, $pluginConfig = null) {
		$this->pluginConfig = array();
		if (!$pluginConfig && array_key_exists('ms_auto_refmap', $_SESSION['pluginsConfig'])) {
			$pluginConfig = $_SESSION['pluginsConfig']['ms_auto_refmap'];
		}
		$this->pluginConfig = $pluginConfig;
		$this->loadConfigValues();
		
		$this->globalMap = $map;
		
		if (isset($_SESSION['MS_VERSION'])) {
			$this->msVersion = $_SESSION['MS_VERSION'];
		} else {
			$ms_Version = ms_GetVersion();
			$msvL = explode('.', substr($ms_Version, strpos($ms_Version, "version") + 8, 5));
			$this->msVersion = (float)("{$msvL[0]}.{$msvL[1]}{$msvL[2]}");
		}
		$this->internalMap = $this->msVersion < 6 ? $map->clone() : clone $map;
		// bug in MS 6 ?
		if ($_SESSION['MS_VERSION'] >= 6) {
			$this->internalMap->set('defresolution', $map->defresolution);
		}
		
		$this->initMapValues();
	}
	
	/**
	 * Load config values from XML file
	 */
	private function loadConfigValues() {	
		// config values:
		$this->outputFormat = isset($this->pluginConfig['format']) ? $this->pluginConfig['format'] : 'png_vg';
		$this->altOutputFormat = isset($this->pluginConfig['altFormat']) ? $this->pluginConfig['altFormat'] : $this->outputFormat;
		$this->groupNames = (isset($this->pluginConfig['groups']) && isset($this->pluginConfig['groups']['group'])) ? $this->pluginConfig['groups']['group'] : array('vg_ilot', 'vg_cours_d_eau_obj', 'vg_commune');
		$this->autoExtent = (isset($this->pluginConfig['autoExtent']) && $this->pluginConfig['autoExtent']) ? true : false;
		$this->autoExtentGroups = (isset($this->pluginConfig['autoExtentGroups']) && $this->pluginConfig['autoExtentGroups']['group']) ? $this->pluginConfig['autoExtentGroups']['group'] : array('vg_commune');
		$this->marginPercent = isset($this->pluginConfig['marginPercent']) ? $this->pluginConfig['marginPercent'] : 0;
		$this->marginFixValue = isset($this->pluginConfig['marginFixValue']) ? $this->pluginConfig['marginFixValue'] : 0;
		$this->autoSize = (isset($this->pluginConfig['autoSize']) && $this->pluginConfig['autoSize']) ? true : false;
		$this->decreaseHeight = (isset($this->pluginConfig['decreaseHeight']) && $this->pluginConfig['decreaseHeight']) ? true : false;
		$this->decreaseWidth = (isset($this->pluginConfig['decreaseWidth']) && (bool)$this->pluginConfig['decreaseWidth']) ? true : false;
		$this->stepSize = isset($this->pluginConfig['stepSize']) ? $this->pluginConfig['stepSize'] : 5;
	}
	/**
	 * Init map variables
	 */
	private function initMapValues() {
		$refMap = $this->internalMap->reference;
		$this->refMapWidth = $refMap->width;
		$this->refMapHeight = $refMap->height;
		$this->refMapExtent = $refMap->extent;
		$this->refMapMinX = $this->refMapExtent->minx;
		$this->refMapMinY = $this->refMapExtent->miny;
		$this->refMapMaxX = $this->refMapExtent->maxx;
		$this->refMapMaxY = $this->refMapExtent->maxy;
		$this->refMapImage = $refMap->image;
		
		if (array_key_exists('ms_auto_refmap', $_SESSION)
		&& array_key_exists('width', $_SESSION['ms_auto_refmap'])
		&& array_key_exists('height', $_SESSION['ms_auto_refmap'])
		&& array_key_exists('minx', $_SESSION['ms_auto_refmap'])
		&& array_key_exists('miny', $_SESSION['ms_auto_refmap'])
		&& array_key_exists('maxx', $_SESSION['ms_auto_refmap'])
		&& array_key_exists('maxy', $_SESSION['ms_auto_refmap'])
		&& array_key_exists('image', $_SESSION['ms_auto_refmap'])) {
			$this->refMapWidth = $_SESSION['ms_auto_refmap']['width'];
			$this->refMapHeight = $_SESSION['ms_auto_refmap']['height'];
			$this->refMapMinX = $_SESSION['ms_auto_refmap']['minx'];
			$this->refMapMinY = $_SESSION['ms_auto_refmap']['miny'];
			$this->refMapMaxX = $_SESSION['ms_auto_refmap']['maxx'];
			$this->refMapMaxY = $_SESSION['ms_auto_refmap']['maxy'];
			$this->refMapImage = $_SESSION['ms_auto_refmap']['image'];
		}
	}
	
	private function activateLayers() {
		// unactivate all layers:
		$numLayers = $this->internalMap->numlayers;
		for ($iLayer = 0 ; $iLayer < $numLayers ; $iLayer++) {
			$layer = $this->internalMap->getLayer($iLayer);
			$layer->set('status', MS_OFF);
		}
		
		// activate layers:
		$layers = getLayersByGroupOrLayerName($this->internalMap, $this->groupNames);
		foreach($layers as $layer) {
			$layer->set('status', MS_ON);
		}
	}
	
	/**
	 * Calculte map extent
	 */
	private function calculateExtent() {
		$mapExtent = $this->refMapExtent;//$this->internalMap->extent;
		pm_logDebug(4, $this->refMapExtent, 'refMapExtent 1:');
		if ($this->autoExtent) {
			$mapExtentAuto = PluginsMapUtils::calculateExtent($this->internalMap, $this->autoExtentGroups, false);
			if ($mapExtentAuto->minx != -1 && $mapExtentAuto->miny != -1 && $mapExtentAuto->maxx != -1 && $mapExtentAuto->maxy != -1) {
				$marginX = 0;
				$marginY = 0;
				if ($this->marginPercent != 0) {
					$marginX = ($mapExtentAuto->maxx - $mapExtentAuto->minx) * $this->marginPercent / 100;
					$marginY = ($mapExtentAuto->maxy - $mapExtentAuto->miny) * $this->marginPercent / 100;
				} else if ($this->marginFixValue != 0) {
					$marginX = $this->marginFixValue / 2;
					$marginY = $this->marginFixValue / 2;
				}
				$mapExtent->setExtent($mapExtentAuto->minx - $marginX, $mapExtentAuto->miny - $marginY, $mapExtentAuto->maxx + $marginX, $mapExtentAuto->maxy + $marginY);
			}
		}
		$this->refMapExtent = $mapExtent;
		pm_logDebug(4, $this->refMapExtent, 'refMapExtent 2:');
//		$this->internalMap->setExtent($mapExtent->minx, $mapExtent->miny, $mapExtent->maxx, $mapExtent->maxy);
	}
	
	/**
	 * Calculte map size
	 */
	private function calculateSize() {
		$width = $this->refMapWidth;//$this->internalMap->width;
		$height = $this->refMapHeight;//$this->internalMap->height;
		if ($this->autoSize && ($this->decreaseHeight || $this->decreaseWidth)) {
			$minHeight = $this->refMapHeight / 3;
			$minWidth = $this->refMapWidth / 3;
			$stepHeight = $this->decreaseHeight ? $this->stepSize : 0;
			$stepWidth = $this->decreaseWidth ? $this->stepSize : 0;
			$size = PluginsMapUtils::calculateSize($this->internalMap, $this->refMapExtent, $this->refMapHeight, $this->refMapWidth, $minHeight, $minWidth, $stepHeight, $stepWidth);
			$width = $size['width'];
			$height = $size['height'];
		}
pm_logDebug(4, $this->refMapExtent, 'refMapExtent 3:');
pm_logDebug(4, "width=$width;height=$height");
	
		$this->internalMap->setSize($width, $height);
		$this->internalMap->setExtent($this->refMapExtent->minx, $this->refMapExtent->miny, $this->refMapExtent->maxx, $this->refMapExtent->maxy);
		$this->refMapExtent = $this->internalMap->extent;
	
		$this->refMapWidth = $width;
		$this->refMapHeight = $height;
		$this->refMapMinX = $this->refMapExtent->minx;
		$this->refMapMinY = $this->refMapExtent->miny;
		$this->refMapMaxX = $this->refMapExtent->maxx;
		$this->refMapMaxY = $this->refMapExtent->maxy;
	}
	
	/**
	 * draw refmap image
	 */
	public function drawRefMap($minx, $miny, $maxx, $maxy, $width, $height, $useAlternatFormatForMS6) {
		$this->activateLayers();
		
		// do not use RGBA in MS 6 if refmap will be used in mapObj::drawReferenceMap (transparency not supported)
		if ($useAlternatFormatForMS6 && $this->msVersion >= 6) {
			$this->internalMap->selectOutputFormat($this->altOutputFormat);
		} else {
			$this->internalMap->selectOutputFormat($this->outputFormat);
		}
		$this->internalMap->setSize($width, $height);
		$this->internalMap->setExtent($minx, $miny, $maxx, $maxy);
	
		$refMapImg = $this->internalMap->draw();
		
		$this->refMapURL = PMCommon::mapSaveWebImage($this->internalMap, $refMapImg);
		PMCommon::freeMsObj($refMapImg);
		$this->refMapImage = str_replace($this->internalMap->web->imageurl, $this->internalMap->web->imagepath, $this->refMapURL);
	}
	
	/**
	 * Do the job...
	 */
	public function doIt() {
		$this->calculateExtent();
		$this->calculateSize();
		$this->drawRefMap($this->refMapMinX, $this->refMapMinY, $this->refMapMaxX, $this->refMapMaxY, $this->refMapWidth, $this->refMapHeight, false);
		$this->saveValuesInSession();
	}
	
	/**
	 * Save values in session:
	 *  - width, height
	 *  - minx, miy, maxx, maxy
	 *  - image path
	 */
	private function saveValuesInSession() {
		$_SESSION['ms_auto_refmap'] = array();
		
		// save calculated values:
		$_SESSION['ms_auto_refmap']['width'] = $this->refMapWidth;
		$_SESSION['ms_auto_refmap']['height'] = $this->refMapHeight;
		$_SESSION['ms_auto_refmap']['minx'] = $this->refMapMinX;
		$_SESSION['ms_auto_refmap']['miny'] = $this->refMapMinY;
		$_SESSION['ms_auto_refmap']['maxx'] = $this->refMapMaxX;
		$_SESSION['ms_auto_refmap']['maxy'] = $this->refMapMaxY;
		$_SESSION['ms_auto_refmap']['image'] = $this->refMapImage;
		
		// for adaptative_refmap
		$_SESSION['ms_auto_refmap_init'] = true;
		$_SESSION['ms_auto_refmap']['adaptativeRefMap']['initialImage'] = $this->refMapImage;
		$_SESSION['ms_auto_refmap']['adaptativeRefMap']['initialImageURL'] = $this->refMapURL;
		$_SESSION['ms_auto_refmap']['adaptativeRefMap']['initialScaleDenom'] = $this->internalMap->scaledenom;
		$_SESSION['ms_auto_refmap']['adaptativeRefMap']['initialMinX'] = $this->refMapMinX;
		$_SESSION['ms_auto_refmap']['adaptativeRefMap']['initialMinY'] = $this->refMapMinY;
		$_SESSION['ms_auto_refmap']['adaptativeRefMap']['initialMaxX'] = $this->refMapMaxX;
		$_SESSION['ms_auto_refmap']['adaptativeRefMap']['initialMaxY'] = $this->refMapMaxY;
		$_SESSION['ms_auto_refmap']['adaptativeRefMap']['initialCenterX'] = $this->refMapMinX + ($this->refMapMaxX-$this->refMapMinX)/2;
		$_SESSION['ms_auto_refmap']['adaptativeRefMap']['initialCenterY'] = $this->refMapMinY + ($this->refMapMaxY-$this->refMapMinY)/2;
	}
	
	public function applyToGlobalMap() {
		$this->globalMap->reference->set('width', $this->refMapWidth);
		$this->globalMap->reference->set('height', $this->refMapHeight);
		$this->globalMap->reference->extent->setExtent($this->refMapMinX, $this->refMapMinY, $this->refMapMaxX, $this->refMapMaxY);
		$this->globalMap->reference->set('image', $this->refMapImage);
		
		// set previously calculated width, height and extent:
		$this->globalMap->set('width', $this->refMapWidth);
		$this->globalMap->set('height', $this->refMapHeight);
		$this->globalMap->extent->setExtent($this->refMapMinX, $this->refMapMinY, $this->refMapMaxX, $this->refMapMaxY);
	}
}

?>