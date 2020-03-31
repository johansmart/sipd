/******************************************************************************
 *
 * Purpose: Locate XY plugin
 * Author:  Mouanis LAHLOU, IAV Hassan II
 *
 ******************************************************************************
 *
 * Copyright (c) 2012
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
	locateXY: 
	{
		dlgOptions: {width:300, height:225, left:250, top:250, resizeable:true, newsize:true, container:'pmLocateXYContainer', name:'locateXY_win'},
		dlgType: 'dynwin',
		marginX: 500,
		marginY: 500,
		mapPrjDef: "EPSG:4326",
		mapPrj: null,
		digitize: false,
		useMarker: true,
		markerTimeout: 400,
		markerNumMax: 3,

		/**
		 * load config settings from config_XXXXX.xml file 
		 */
		init: function() {
			if (typeof(Proj4js) == "undefined") {
				alert(_p('locateXY_ErrorProj'));
			}
			if (typeof(PM.ini.pluginsConfig['locateXY']) != 'undefined') {
				var pluginsConfig = PM.ini.pluginsConfig.locateXY;
				if (typeof(pluginsConfig.dlgType) != 'undefined') {
					this.dlgType = pluginsConfig.dlgType;
				}
				if (typeof(pluginsConfig.dlgOptions) != 'undefined') {
					$.extend(this.dlgOptions, pluginsConfig.dlgOptions);
                    this.dlgOptions.width = parseInt(this.dlgOptions.width);
                    this.dlgOptions.height = parseInt(this.dlgOptions.height);
                    this.dlgOptions.left = parseInt(this.dlgOptions.left);
                    this.dlgOptions.top = parseInt(this.dlgOptions.top);
				}
				if (typeof(pluginsConfig.marginX) != 'undefined' && !isNaN(pluginsConfig.marginX)) {
					this.marginX = parseFloat(pluginsConfig.marginX);
				}
				if (typeof(pluginsConfig.marginY) != 'undefined' && !isNaN(pluginsConfig.marginY)) {
					this.marginY = parseFloat(pluginsConfig.marginY);
				}
				if (typeof(pluginsConfig.mapPrjDef) != 'undefined') {
					this.mapPrjDef = pluginsConfig.mapPrjDef;
				}
				if (typeof(pluginsConfig.digitize) != 'undefined') {
					this.digitize = pluginsConfig.digitize == 1 ? true : false;
				}
				if (typeof(pluginsConfig.useMarker) != 'undefined') {
					this.useMarker = pluginsConfig.useMarker == 1 ? true : false;
				}
				if (typeof(pluginsConfig.markerTimeout) != 'undefined') {
					this.markerTimeout = parseInt(pluginsConfig.markerTimeout);
				}
				if (typeof(pluginsConfig.markerNumMax) != 'undefined') {
					this.markerNumMax = parseInt(pluginsConfig.markerNumMax);
				}
			}
			this.mapPrj = new Proj4js.Proj(this.mapPrjDef);
		},
		
		openDlg: function() {
			var url = PM_PLUGIN_LOCATION + '/locateXY/locateXY.phtml';
			var params = SID;

			openAjaxQueryIn(this.dlgType, this.dlgOptions, this.dlgOptions.name, url, params);
		},
		
		cancel: function() {
			$('#' + this.dlgOptions.container + ' .jqmClose').click();
		},
		
		load: function() {
			var x = $('#locateXY-xcoord').val();
			var y = $('#locateXY-ycoord').val();
			if (isNaN(x) || isNaN(y)) {
				alert(_p('locateXY_NaN'));
			} else if (!this.mapPrj) {
				alert(_p('locateXY_ErrorProj'));				
			} else {
				x = parseFloat(x);
				y = parseFloat(y);
				var p = new Proj4js.Point(x,y);
				var inproj = $('#locateXY-inproj').val();
				var source = new Proj4js.Proj(inproj);
				var Ptext = $('#locateXY-Ptext').val();
				//PM.ZoomBox.transformCoordinates(source,dest,p); Not working
				p = Proj4js.transform(source, this.mapPrj, p);
				var x1 = p.x - this.marginX;
				var x2 = p.x + this.marginX;
				var y1 = p.y - this.marginY;
				var y2 = p.y + this.marginY;
				var extent=x1+"+"+y1+"+"+x2+"+"+y2;
				this.zoominGeo(extent);
				if (this.digitize) {
					var poiText = '[' + "X:" + Math.round(p.x*100)/100 + "," + " Y:" + Math.round(p.y*100)/100;
					if (Ptext.length) {
						poiText += "," + ' Name: ' + Ptext;
					}
					poiText += "]";
					var digitizeurl = PM_XAJAX_LOCATION + 'x_poi.php?' + SID + '&up=' + p.x + '@@' + p.y + '@@' + poiText;
					PM.Map.addPOI(digitizeurl);
					this.cancel();
				}
				if (this.useMarker && typeof(PM.Plugin.DisplayMarker) != 'undefined') {
					PM.Plugin.DisplayMarker.createMarker('locateXY', p.x, p.y, '', this.markerTimeout, (this.markerNumMax + 1)*2);
				}
			}
		},

		/**
		 * Zoom to rectangle
		 */
		zoominGeo: function(extent) {
			var mapurl = PM_XAJAX_LOCATION + 'x_load.php?'+SID+'&mode=map&zoom_type=zoomextent&extent='+extent;
//			alert(mapurl);
			PM.Map.updateMap(mapurl);
		},
		
		/**
		 * Write X,Y or Lat/Long texts
		 */
		changeProj: function() {
			$('#locateXY-Xtext').text(this.getTextX());
			$('#locateXY-Ytext').text(this.getTextY());
		},
		
		getTextX: function() {
			var ret = this.useAlternateText() ? 'locateXY_Xtext2' : 'locateXY_Xtext1';
			return _p(ret);
		},
		
		getTextY: function() {
			var ret = this.useAlternateText() ? 'locateXY_Ytext2' : 'locateXY_Ytext1';
			return _p(ret);
		},
		
		useAlternateText: function() {
			var ret = false;
			var inproj = $('#locateXY-inproj').val();
			if (inproj.search(/epsg:4326$/i) != -1) {
				ret = true;
			}

			return ret;
		}
	}
});