/******************************************************************************
 *
 * Purpose: Extension of transparency plugins
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

$.extend(PM.Plugin,
{
	Transparency2: 
	{
		// Array to keep names of layergroups, and the associated slider
		// (slider array is used to call the setPosition method)
		sliders: null,

		options: {
			useOpacity: false
		},


		/**
		 * re-define the function of transparency plugin
		 * It just made the same as the original one, and then call initGroupTransparencies2
		 */
		
		init: function() {
			this.sliders = null;
			if (typeof(PM.ini.pluginsConfig.transparency2) != 'undefined') {
				var newValues = PM.ini.pluginsConfig.transparency2;
				if (typeof(newValues.useOpacity) != 'undefined' && newValues.useOpacity != 'off') {
					this.options.useOpacity = true;
				}
			}
//			PM.Plugin.Transparency.initGroupTransparencies();
			this.initGroupTransparencies();
		},
		
		/**
         * Initialize transparency values of groups and create a transparency object for PMap
         */
        initGroupTransparencies: function() {
			if (typeof(PM.groupTransparencies) == 'undefined') {
	            var url = PM_PLUGIN_LOCATION + '/transparency/x_get-transparencies.php?' + SID;
	            $.ajax({
	                type: "POST",
	                url: url,
	                dataType: "json",
	                success: function(response){
// Modified by Thomas RAFFIN (SIRAP)
// to change after transparency plugin has this method implemented
//	            		PM.Plugin.Transparency.setGroupsAndTransparencies(response.transparencies);
PM.groupTransparencies = response.transparencies;
	            		PM.Plugin.Transparency2.initSliders();
	                },
	                error: function() {
	                	PM.Plugin.Transparency2.initSliders();
	                }
	            });
			} else {
				PM.Plugin.Transparency2.initSliders();
			}
		},

		/**
		 * Init the sliders in TOC (create and update position)
		 */
		
		 initSliders: function() {
			this.initSlidersArray();
		
			var sliders = PM.Plugin.Transparency2.sliders;

			// for each groups
			$('#toc .tocgrp').each(function() {
				// Be carrefull : in IE and Firefox, slit function return different arrays
				// if test is on the begining of string...
				var gnames = $(this).find('[id^=\'spxg_\']').id().split(/spxg_/);
				if (gnames.length > 0) {
					var gname = gnames[gnames.length - 1];
					if (gname.length > 0) {
						var sliderDivID = 'toc_transp2_' + gname;
						var sliderDiv = $('#' + sliderDivID);
						// Add transparency (slider, etc...) only if not ever done
						if (sliderDiv.size() == 0) {
							$(this).prepend('<div id="' + sliderDivID + '" class="transparency2Slider"></div>');
							PM.Plugin.Transparency2.createSlider(sliderDivID, gname);
						}
						// update slider position :
						sliderDiv = $('#' + sliderDivID);
						if (sliderDiv.size() > 0) {
							if (typeof(sliders[gname]) == 'object') {
								if (typeof(PM.groupTransparencies) != 'undefined') {
									var sliderPos = PM.groupTransparencies[gname]/100;
									sliderPos = PM.Plugin.Transparency2.options.useOpacity ? 1 - sliderPos : sliderPos;
									sliders[gname].slider.setPosition(sliderPos);
								}
							}
						}
					}
				}
			});
		 },
	
		/**
		 * Generate 2 arrays for keeping sliders object in order 
		 * to call the "setPosition" js function later 
		 * Remove existing divs for sliders
		 */ 

		initSlidersArray: function() {
			this.sliders = null;
			
			var sliders = {};
			$.each(PM.grouplist, function(name, grp) {
				// remove divs:
				$('#toc_transp2_' + name).remove();
				
			    sliders[name] = {
			        groupName: name,
			        setGroupTransparency: function(sliderPosition) {
			        	PM.Plugin.Transparency2.setGroupTransparency(sliderPosition, name);
			        },
			        slider: null
			    };
			});
			this.sliders = sliders;
		},
	
		/**
		 * Create slider for transparency setting
		 */
		
		createSlider: function(sliderDivID, gname) {
			if (typeof(this.sliders[gname]) == 'object') {
				this.sliders[gname].slider = new slider(
			        sliderDivID,
			        3, 40, '#666666',
					1, '#000000',
					2, '#666666',
					8, 3, '#999999', 1,
			        '', true,     
			        false, 'PM.Plugin.Transparency2.sliders[\''+gname+'\'].setGroupTransparency',
			        null
				);
			}
		},
	
	
		/**
		 * Post the Transparency value to PHP GROUP object
		 *
		 * re-write the transparency plugin function
		 */
/*
		setGroupTransparency2: function(groupname, transparency) {
		    var url = PM_PLUGIN_LOCATION + '/transparency/x_set-transparency.php?' + SID + '&transparency=' + transparency + '&groupname=' + groupname;
		    $.ajax({
		        type: "POST",
		        url: url,
		        dataType: "json",
		        success: function(response){
		            PM.groupTransparencies[groupname] = transparency;
		            if (response.reload && (PM.layerAutoRefresh == '1')) {
		                showloading();
		                PM.Map.reloadMap(false);
		            }
		        }
		    });
		},
*/
		setGroupTransparency: function(pos, groupname) {
			var transparency;
		
			// for classical transparency plugin 
			if (groupname == undefined) {
			    groupname = $('#transpdlg_groupsel option:selected').val();
			    if (typeof(groupname)=='undefined') {
			        var groupname = $('#layerSliderCont').attr('name');
			        var cmenu = 1;
			    }
			    if (groupname == '#') return false;
			    if (cmenu) $('#layerSliderContTab').remove();
		
			    if (typeof(this.sliders[groupname]) == 'object') {
					if (typeof(this.sliders[groupname].slider) != 'undefined' && this.sliders[groupname].slider != null) {
						this.sliders[groupname].slider.setPosition(pos);
					}
				}
				transparency = Math.round(pos  * 100);
			}
			// for extended transparency plugin (Transparency2)
			else {
				var sliderPos100 = Math.round(pos  * 100);
				transparency = this.options.useOpacity ? 100 - sliderPos100 : sliderPos100;
			}
		    	
		    var url = PM_PLUGIN_LOCATION + '/transparency/x_set-transparency.php?' + SID + '&transparency=' + transparency + '&groupname=' + groupname;
		    $.ajax({
		        type: "POST",
		        url: url,
		        dataType: "json",
		        success: function(response){
		    		PM.groupTransparencies[groupname] = transparency;
		            if (response.reload && (PM.layerAutoRefresh == '1')) {
		                //showloading();
		                PM.Map.reloadMap(false);
		            }
		        },
	            error: function (XMLHttpRequest, textStatus, errorThrown) {
	                if (window.console) console.log(errorThrown);
	            }
		    });
		}		
	}
});

$.extend(PM.Plugin.Transparency,
{
	setGroupTransparency: function(pos) {
	    PM.Plugin.Transparency2.setGroupTransparency(pos);
	}
});