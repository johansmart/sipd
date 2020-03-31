/******************************************************************************
 *
 * Purpose: Display marker at specified coordinates
 * Author: Thomas Raffin, SIRAP
 *
 ******************************************************************************
 *
 * Copyright (c) 2010 SIRAP
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
		DisplayMarker:
	    {
			markers: {},
	    	imgSrc: 'plugins/displaymarker/images/marker_rounded_green.png',
	    	imgPrefix: 'displaymarker-', 
	    	timeout: 400,
			nummax: 10,
			
	    	timer: null,
	    	
	    	/**
	    	 * Init params from config file
	    	 */
	    	init: function() {
				this.markers = {};
                if (typeof(PM.ini.pluginsConfig.displaymarker) != 'undefined') {
                	var pluginConfig = PM.ini.pluginsConfig.displaymarker;
                	if (typeof(pluginConfig.imgSrc) != 'undefined') {
    	    			this.imgSrc = pluginConfig.imgSrc;
    	    		}
    	    		if (typeof(pluginConfig.imgPrefix) != 'undefined') {
    	    			this.imgPrefix = pluginConfig.imgPrefix;
    	    		}
    	    		if (typeof(pluginConfig.timeout) != 'undefined') {
    	    			this.timeout = parseInt(pluginConfig.timeout);
    	    		}
    	    		if (typeof(pluginConfig.nummax) != 'undefined') {
    	    			this.nummax = parseInt(pluginConfig.nummax);
    	    		}
    	    	}
                PM.Map.bindOnMapRefresh(function(e){ 
                	PM.Plugin.DisplayMarker.refreshMarkers();
                });
	    	
	    	},
			
			createMarker: function(id, x, y, imgSrc, timeout, nummax) {
				if (typeof(imgSrc) == 'undefined' || imgSrc.length == 0) {
					imgSrc = this.imgSrc;
				}
				if (typeof(timeout) == 'undefined') {
					timeout = this.timeout;
				}
				if (typeof(nummax) == 'undefined') {
					nummax = this.nummax;
				}
				this.stopMarker(id);
				//var timerId = setInterval("PM.Plugin.DisplayMarker.display('" + id + "')", timeout);
				this.markers[id] = {'id': id, 'htmlId': this.imgPrefix + id, 'display': true, 'x': x, 'y': y, 'imgSrc': imgSrc, 'timeout': timeout, 'timerId': null, 'num': 0, 'nummax': nummax};
				this.startMarker(id);
			},
			
			startMarker: function(id) {
				if (typeof(this.markers[id]) != 'undefined') {
					this.stopMarker(id);
					var marker = this.markers[id];
					var pointGeo = new Point(marker.x, marker.y);
					var pointPx = PM.Draw.toPxPoint(pointGeo);
					jg.drawImage(marker.imgSrc, pointPx.x, pointPx.y, null, null, 'id="' + marker.htmlId + '"');
					jg.paint();
					var timerId = setInterval("PM.Plugin.DisplayMarker.display('" + id + "')", this.markers[id].timeout);
					this.markers[id].timerId = timerId;
				}
			},

			stopMarker : function(id) {
				if (typeof(this.markers[id]) != 'undefined') {
					var marker = this.markers[id];
					marker.display = false;
//					marker.num = 0;
					if (marker.timerId != null) {
						clearInterval(marker.timerId);
						marker.timerId = null;
						$('#' + marker.htmlId).css('display', 'none').remove();
					}
				}
			},
			
			refreshMarkers: function() {
				$.each(PM.Plugin.DisplayMarker.markers, function(id, marker) {
					var num = marker.num;
					PM.Plugin.DisplayMarker.startMarker(id);
					marker.num = num;
				});
			},
			
			display: function(id) { 
				if (typeof(PM.Plugin.DisplayMarker.markers[id]) != 'undefined') {
					var marker = PM.Plugin.DisplayMarker.markers[id];
					marker.num++;
					var elem = $('#' + marker.htmlId);
					if (elem.css('display') == 'block') {
						elem.css('display', 'none');
					} else {
						if (marker.num <= marker.nummax) {
							elem.css('display', 'block');
						} else {
							elem.css('display', 'none');
							PM.Plugin.DisplayMarker.stopMarker(id);
						}
					}
				}
			}
		}
	}
);	    	

