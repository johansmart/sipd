/******************************************************************************
 *
 * Purpose: ThemesAndViews plugin
 * Author:  Thomas RAFFIN, SIRAP
 *
 ******************************************************************************
 *
 * Copyright (c) 2009 SIRAP
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

$.extend(PM.Plugin, 
{
	ThemesAndViews: $.extend({}, themesAndViewsCommon, {
		settings: {
			themes: {
				insertBoxType: 'user', // user,first,last
				boxContainer: '',
				initAfterTOC: '0',
				selBoxStr: '<div id=\"selThemeBox\" class=\"tavSelectBox\" />',
				keepSelected: '0'
			},
			views: {
				insertBoxType: 'user',
				boxContainer: '',
				initAfterTOC: '0',
				selBoxStr: '<div id=\"selViewBox\" class=\"tavSelectBox\" />',
				keepSelected: '0'
			},
			defaultType: 'none', // none,theme,view
			defaultCodeValue: ''
		},
		
		init: function(){
			if (typeof(PM.ini.pluginsConfig.themesandviews) != 'undefined') {
				var pluginConfig = PM.ini.pluginsConfig.themesandviews;
				if (typeof(pluginConfig.themes) != 'undefined') {
					$.extend(this.settings.themes, pluginConfig.themes);
				}
				if (typeof(pluginConfig.views) != 'undefined') {
					$.extend(this.settings.views, pluginConfig.views);
				}
				if (typeof(pluginConfig.defaultType) != 'undefined') {
					this.settings.defaultType = pluginConfig.defaultType;
				}
				if (typeof(pluginConfig.defaultCodeValue) != 'undefined') {
					this.settings.defaultCodeValue = pluginConfig.defaultCodeValue;
				}
			}
			this.beforeTocInit();
		},
		
		/**
		 * Initialisation of themes or views, but after TOC loading
		 */
		afterTocInit: function(){
			var resizeToc = false;
			if (this.settings.themes.initAfterTOC == '1') {
				this.initThemes();
				resizeToc = true;
			}
			if (this.settings.views.initAfterTOC == '1') {
				this.initViews();
				resizeToc = true;
			}
			if (resizeToc) {
			// TODO				tocResizeUpdate();
			}
		},
		
		
		/**
		 * Initialisation of themes or views, but before TOC loading
		 */
		beforeTocInit: function(){
			if (this.settings.themes.initAfterTOC != '1') {
				this.initThemes();
			}
			if (this.settings.views.initAfterTOC != '1') {
				this.initViews();
			}
		},
		
		
		/**
		 * Themes initialisation
		 */
		initThemes: function(){
			this._initThemesAndViews('theme');
		},
		
		
		/**
		 * Views initialisation
		 */
		initViews: function(){
			this._initThemesAndViews('view');
		},
		
		/**
		 * Themes and Views initialisation
		 * type = 'theme' or 'view'
		 */
		_initThemesAndViews: function(type){
			var settingsToUse = false;
			var boxId = '';
			if (type == 'theme') {
				settingsToUse = this.settings.themes;
				boxId = '#selThemeBox';
			}
			else 
				if (type == 'view') {
					settingsToUse = this.settings.views;
					boxId = '#selViewBox';
				}
			if (settingsToUse) {
				if (settingsToUse.insertBoxType != 'user') {
					var jqtavThemesBoxContainer = $(settingsToUse.boxContainer + ':first');
					if (jqtavThemesBoxContainer.size()) {
						if (!$(boxId).size()) {
							if (settingsToUse.insertBoxType == 'first') {
								jqtavThemesBoxContainer.prepend(settingsToUse.selBoxStr);
							}
							else 
								if (settingsToUse.insertBoxType == 'last') {
									jqtavThemesBoxContainer.append(settingsToUse.selBoxStr);
								}
						}
					}
				}
				this._boxInit(type);
			}
		},
		
		/**
		 * Themes box initialisation
		 */
		themesBoxInit: function(){
			this._boxInit('theme');
		},
		
		/**
		 * Views box initialisation
		 */
		viewsBoxInit: function(){
			this._boxInit('view');
		},
		
		/**
		 * Themes And Views box initialisation
		 *
		 * - AJAX request
		 * - response in #selThemeBox or #selViewBox
		 * - load specified (by code value, or the first one) default theme
		 *
		 * type = 'theme' or 'view'
		 */
		_boxInit: function(type) {
			var settingsToUse = false;
			var boxId = '';
			if (type == 'theme') {
				settingsToUse = this.settings.themes;
				boxId = '#selThemeBox';
			}
			else 
				if (type == 'view') {
					settingsToUse = this.settings.views;
					boxId = '#selViewBox';
				}
			
			if (settingsToUse) {
				if ($(boxId).size()) {
					$.ajax({
						url: PM_PLUGIN_LOCATION + '/themesandviews/x_tavBox.php?type=' + type + '&' + SID,
						dataType: "json",
						success: function(response){
							var settingsToUse = false;
							var boxId = '';
							var formName = '';
							var applyDefault = false;
							if (response.type == 'theme') {
								settingsToUse = PM.Plugin.ThemesAndViews.settings.themes;
								boxId = '#selThemeBox';
								formName = 'selThemesBoxForm';
								applyDefault = PM.Plugin.ThemesAndViews.settings.defaultType == 'theme';
							}
							else 
								if (response.type == 'view') {
									settingsToUse = PM.Plugin.ThemesAndViews.settings.views;
									boxId = '#selViewBox';
									formName = 'selViewsBoxForm';
									applyDefault = PM.Plugin.ThemesAndViews.settings.defaultType == 'view';
								}
							
							if (settingsToUse) {
								var pluginObj = PM.Plugin.ThemesAndViews;
								var currentVal = pluginObj.getSelectedThemeAndViewsBox(formName);
								
								var selStr = response.selStr;
								$(boxId).html(selStr);
								
								if (currentVal.length == 0) {
									if (applyDefault) {
										if (pluginObj.settings.defaultCodeValue.length > 0) {
											$(boxId + " select").val(pluginObj.settings.defaultCodeValue);
											$(boxId + " select").change();
											pluginObj.settings.defaultType = 'none';
										}
										else {
											var tavCodeTmp = $(boxId + " select option:eq(1)").val();
											if (typeof(tavCodeTmp) != 'undefined') {
												if (tavCodeTmp.length > 0) {
													$(boxId + " select").val(tavCodeTmp);
													$(boxId + " select").change();
													pluginObj.settings.defaultType = 'none';
												}
											}
										}
									}
								}
								else {
									$(boxId + " select").val(currentVal);
									$(boxId + " select").change();
								}
							}
						},
		                error: function (XMLHttpRequest, textStatus, errorThrown) {
		                    if (window.console) console.log(errorThrown);
		                }
					});
				}
			}
		},
		
		/**
		 * Theme box click
		 *
		 * Create a div in "mapToolArea", then call "themesboxInit" to init the themes box
		 */
		themesbox_click: function(){
			PM.Map.resetFrames();
			
			PM.Map.mode = 'themebox';
			PM.Map.maction = 'click';
			PM.Map.tool = 'themebox';
			if (PM.useCustomCursor) {
				PM.setCursor(false, false);
			}
			
			$("#mapToolArea").html('<div id="selThemeBox" class="pm-toolframe"></div>');
			this.themesBoxInit();
		},
		
		
		/**
		 * View box click
		 *
		 * Create a div in "mapToolArea", then call "viewsboxInit" to init the views box
		 */
		viewsbox_click: function(){
			PM.Map.resetFrames();
			
			PM.Map.mode = 'viewbox';
			PM.Map.maction = 'click';
			PM.Map.tool = 'viewbox';
			if (PM.useCustomCursor) {
				PM.setCursor(false, false);
			}
			
			$("#mapToolArea").html('<div id="selViewBox" class="pm-toolframe"></div>');
			this.viewsBoxInit();
		},
		
			/**
		 * Submit the selected theme
		 */
		submitSelThemeBox: function(){
			this._submitSelBox('theme');
		},
		
		/**
		 * Submit the selected view
		 */
		submitSelViewBox: function(){
			this._submitSelBox('view');
		},
		
		/**
		 * Submit the selected theme or view
		 *
		 * 1) AJAX call : update server map object (layers, transparencies, ...)
		 * 2) call tavUpdateMapAndToc to update interface
		 *
		 * type = 'theme' or 'view'
		 */
		_submitSelBox: function(type){
			var settingsToUse = false;
			var tavBoxName = '';
			if (type == 'theme') {
				settingsToUse = this.settings.themes;
				tavBoxName = 'selThemesBoxForm';
			}
			else 
				if (type == 'view') {
					settingsToUse = this.settings.views;
					tavBoxName = 'selViewsBoxForm';
				}
			if (settingsToUse) {
				var selelem = this.getSelectedThemeAndViewsBox(tavBoxName);
				if (settingsToUse.keepSelected != '1') {
					var selform = _$(tavBoxName);
					selform.selgroup.selectedIndex = -1;
				}
				if (selelem.length > 0) {
					$.ajax({
						url: PM_PLUGIN_LOCATION + '/themesandviews/x_tavApply.php?' + SID + '&type=' + type + '&selected=' + selelem,
						dataType: "json",
						success: function(response){
							PM.Plugin.ThemesAndViews.updateMapAndToc(response.transparencies, null, response.reload);
						},
		                error: function (XMLHttpRequest, textStatus, errorThrown) {
		                    if (window.console) console.log(errorThrown);
		                }
					});
				}
			}
		},
		
		/**
		 * Update interface
		 *
		 * 1) Map image
		 * 2) TOC visible layers (scale)
		 * 3) TOC layers transparency and checked state
		 *
		 * Be carrefull : this function is also called by the ThemesAndViewsAdmin plugin.
		 */
		// TODO		
		updateMapAndToc: function(transparencies, extent, reload){
			// Warning : This function can be called by and other window (TAV configurator --> opener.tavUpdateMapAndToc)
			// So the begining of the url is not what it seems...
			
			// Map update :
			/*
		 var mapurl = tavPmDirURL + PM_XAJAX_LOCATION + 'x_load.php?'+SID+'&zoom_type=';
		 */
			/*
		 var urlprefix = '';
		 if (!jQuery.browser.msie) {
		 if (document.URL.indexOf('themesandviews') >=  0) {
		 urlprefix = '../../';
		 }
		 }
		 var mapurl = urlprefix + PM_XAJAX_LOCATION + 'x_load.php?'+SID+'&zoom_type=';
		 */
			var mapurl = PM_XAJAX_LOCATION + 'x_load.php?' + SID + '&zoom_type=';
			
			
			if (extent) {
				mapurl += 'zoomextent&extent=' + extent + '&mode=map';
			}
			else {
				mapurl += 'zoompoint';
			}
			PM.Map.updateMap(mapurl);
			
			// TOC update (checked groups, scale) :
			if (reload) {
				//		pmToc_init(); // Pb : call treeInit() and so setDefGroups()...
				//		var tocurl = urlprefix + PM_XAJAX_LOCATION + 'x_toc_update.php?' + SID;
				var tocurl = PM_XAJAX_LOCATION + 'x_toc_update.php?' + SID;
				PM.Toc.tocUpdateScale(tocurl, true);
			}
			
			// TOC update (uncheck groups) :
			$("#toc").find('[id^=\'ginput_\']').attr("checked", "");
			
			// TOC update (transparencies values ans checked groups) :
			if (transparencies) {
				for (transparency in transparencies) {
					if (typeof(PM.groupTransparencies) != 'undefined') {
						PM.groupTransparencies[transparency] = transparencies[transparency];
					}
					$("#toc #ginput_" + transparency).attr("checked", "true");
				}

				PM.Toc.tocPostLoading();
			}
		}
	})
});

//$.extend(PM.Plugin.themesAndViews, themesAndViewsCommon); 