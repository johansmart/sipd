<?php

/******************************************************************************
 *
 * Purpose: increase or decrease object's size
 * Author:  Christophe Arioli, SIRAP
 *
 *****************************************************************************
 *
 * Copyright (c) 2011 SIRAP
 *
 * p.mapper is free software; you can redistribute it and/or modify
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

// includes:
require_once(dirname(__FILE__) . '/../common/groupsAndLayers.php');

$styleDefaultMinSize = 1;
$styleDefaultMinWidth = 1;

// need to recalculate ?
$recalculate = (!isset($_SESSION['mapObjModifierFirstInclude']) || ($_SESSION['mapObjModifierFirstInclude'] === true));

// recalculate --> keep config values in session
if ($recalculate) {
	// values from config:
	
	// here $_SESSION['pluginsConfig'] is not set or contains previous values
	$pluginConfig = array();
	if (isset($ini['pluginsConfig'])) {
		$iniPluginConfig = (array)$ini['pluginsConfig'];
		if (isset($iniPluginConfig['sizeUpDownObj'])) {
			$pluginConfig = $iniPluginConfig['sizeUpDownObj'];
		}
	}
	
	if (!isset($_SESSION['sizeUpDownObj'])) {
		$_SESSION['sizeUpDownObj'] = array();
	}

	$_SESSION['sizeUpDownObj']['layers'] = array();	
	// factor for each iteration
	$_SESSION['sizeUpDownObj']['factor'] = isset($pluginConfig['factor']) ? $pluginConfig['factor'] : 1.33;
	// maximum number of application of the factor
	$_SESSION['sizeUpDownObj']['maxSizeUpIterator'] = isset($pluginConfig['max']) ? abs($pluginConfig['max']) : 4;
	// minimum number of application of the factor (negative value)
	$_SESSION['sizeUpDownObj']['minSizeDownIterator'] = isset($pluginConfig['min']) ? -abs($pluginConfig['min']) : -4;
	// do increase or decrease labels size
	$_SESSION['sizeUpDownObj']['doLabels'] = isset($pluginConfig['doLabels']) && $pluginConfig['doLabels'] ? true : false;
	// labels min size
	$_SESSION['sizeUpDownObj']['configLabelMinSize'] = isset($pluginConfig['labelminsize']) ? $pluginConfig['labelminsize'] : -1;
// not recalculate --> apply size up or down:
} else {
	if (!isset($_SESSION['sizeUpDownObj']['layers'])) {
		$_SESSION['sizeUpDownObj']['layers'] = array();
	} else {
		// values from session:
		$factor = $_SESSION['sizeUpDownObj']['factor'];
		$maxSizeUpIterator = $_SESSION['sizeUpDownObj']['maxSizeUpIterator'];
		$minSizeDownIterator = $_SESSION['sizeUpDownObj']['minSizeDownIterator'];
		$doLabels = $_SESSION['sizeUpDownObj']['doLabels'];
		$configLabelMinSize = $_SESSION['sizeUpDownObj']['configLabelMinSize'];
		
		$msVersion = $_SESSION['MS_VERSION'];

		// layers array to increase or decrease size
		$layerArray = $_SESSION['sizeUpDownObj']['layers'];
		foreach ($layerArray as $layerName => $multiplicator) {
			if ($multiplicator != 0) {
				// check number of application of the factor
				if ($multiplicator > 0) {
					$multiplicator = $multiplicator > $maxSizeUpIterator ? $maxSizeUpIterator : $multiplicator;
				} else {
					$multiplicator = $multiplicator < $minSizeDownIterator ? $minSizeDownIterator : $multiplicator;
				}
				$factmulti = $multiplicator > 0 ? abs($factor * $multiplicator) : 1 / abs($factor * $multiplicator);
					
				// change size for each layer
				$layers = getLayersByGroupOrLayerName($map, $layerName);
				foreach ($layers as $layer) {
					$numClassesObj = $layer->numclasses ;
					for ($iClass = 0 ; $iClass < $numClassesObj ; $iClass++) {
						$classObj = $layer->getClass($iClass);

						$numStylesObj = $classObj->numstyles;
						for ($iStyle = 0 ; $iStyle < $numStylesObj ; $iStyle++) {
							$styleObj = $classObj->getStyle($iStyle);

							// size
							$newStyleMinSize = abs($styleObj->minsize * $factmulti);
							$sizeTmp = $newStyleMinSize < $styleDefaultMinSize ? $styleDefaultMinSize : $newStyleMinSize;
							$styleObj->set('minsize', $sizeTmp);

							$styleMinSize = $styleObj->minsize;
							$newStyleSize = abs($styleObj->size * $factmulti);
							$sizeTmp = $newStyleSize < $styleMinSize ? $styleMinSize : $newStyleSize;
							$styleObj->set('size', $sizeTmp);

							$styleObj->set('maxsize', abs($styleObj->maxsize * $factmulti));

							// width
							$newStyleMinWidth = abs($styleObj->minwidth * $factmulti);
							$sizeTmp = $newMinWidth < $styleDefaultMinWidth ? $styleDefaultMinWidth : $newMinWidth;
							$styleObj->set('minwidth', $sizeTmp);

							$styleMinWidth = $styleObj->minwidth;
							$newStyleWidth = abs($styleObj->width * $factmulti);
							$sizeTmp = $newStyleWidth < $styleMinWidth ? $styleMinWidth : $newStyleWidth;
							$styleObj->set('width', $sizeTmp);

							$styleObj->set('maxwidth', abs($styleObj->maxwidth * $factmulti));
						}
						// label
						if ($doLabels) {
							$labels = array();
							if ($msVersion >= 6.2) {
								$numlabels = $classObj->numlabels;
								for ($iLabel  = 0 ; $iLabel < $numlabels ; $iLabel++) {
									$labels[] = $classObj->getLabel($iLabel);
								}
							} else {
								$labels[] = $classObj->label;
							}
							foreach ($labels as $labelObj) {
								if (isset($labelObj) && $labelObj) {
									$newMinSizeTmp = abs($labelObj->minsize * $factmulti);
									$newSizeTmp = abs($labelObj->size * $factmulti);
									$newMaxSizeTmp = abs($labelObj->maxsize * $factmulti);
									if ($configLabelMinSize != -1) {
										$newMinSizeTmp =  $newMinSizeTmp > $configLabelMinSize ? $newMinSizeTmp : $configLabelMinSize;
										$newSizeTmp =  $newSizeTmp > $configLabelMinSize ? $newSizeTmp : $configLabelMinSize;
										$newMaxSizeTmp =  $newMaxSizeTmp > $configLabelMinSize ? $newMaxSizeTmp : $configLabelMinSize;
									}
									$labelObj->set('minsize', $newMinSizeTmp);
									$labelObj->set('size', $newSizeTmp);
									$labelObj->set('maxsize', $newMaxSizeTmp);

									// increase readability
									if ($factmulti < 1) {
										$newShadowSizeX = abs($labelObj->shadowsizex * $factmulti);
										$labelObj->set('shadowsizex', $newShadowSizeX);

										$newShadowSizeY = abs($labelObj->shadowsizey * $factmulti);
										$labelObj->set('shadowsizey', $newShadowSizeY);
									}
								}
							}
						}
					}
				}
			}
		}
	}
}
?>