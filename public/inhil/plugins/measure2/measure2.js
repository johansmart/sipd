/******************************************************************************
 *
 * Purpose: Measure lines lengths and polygons areas
 * Author:  Jaouad Bennasser, SIRAP
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

var measure2Plugin = $.extend({}, drawingBase, 
{
	//Dialog options
	dlgOptions: {width:320, height:250, left:80, top:125, resizeable:true, newsize:true, container:'pmMeasure2Container', name:'Measure2'},
	dlgType: 'dynwin',
	createMeasureInput: true, //create the measure input elements
	displayTemporaryArea: true,

	//layers definition
	test_def_distance: '{"type":"tplMapFile", "tplname": "measureDist", "layername": "measureDist", "category": "cat_measure"}',
	test_def_area: '{"type":"tplMapFile", "tplname": "measureArea", "layername": "measureArea", "category": "cat_measure"}',
	
	measureType: null, //type of measure : distance, area
	current_properties: [], //array of measurement properties

	distanceButtonId: "tb_measureDist", //line button name
	areaButtonId: "tb_measureArea", //polygon button name
	
	default_outlineColor: null,

	pluginNameSrv: 'measure2', // pluginName for init function
	pluginNameClt: 'Measure2', // pluginName for init function
	
	init: function() {
		this.init_base(); //init parameters of common drawing class  
		
		this.selectColorId = this.pluginNameSrv + "Color"; //color input element's name for the plugin measure2
		this.tableContentId = this.pluginNameSrv + "TableContent"; //outline color input element's name for the plugin measure2
		this.emptyButtonId = this.pluginNameSrv + "Empty"; //empty button name for the plugin measure2
		this.tableId = this.pluginNameSrv + "Table"; //HTML table header name for the plugin drawing
		
		//load config settings from config_XXXXX.xml file 
		if (typeof(PM.ini.pluginsConfig[this.pluginNameSrv]) != 'undefined') {
			var pluginsConfig = PM.ini.pluginsConfig[this.pluginNameSrv];
			if (typeof(pluginsConfig.dlgType) != 'undefined') {
				this.dlgType = pluginsConfig.dlgType;
			}
			if (typeof(pluginsConfig.default_color) != 'undefined') {
				this.default_color = pluginsConfig.default_color;
				this.color = this.default_color;
			}
			if (typeof(pluginsConfig.createMeasureInput) != 'undefined') {
				if (pluginsConfig.createMeasureInput == false
				|| pluginsConfig.createMeasureInput == 'false'
				|| pluginsConfig.createMeasureInput == 0) {
					this.createMeasureInput = false;
				}
			}
			if (typeof(pluginsConfig.displayTemporaryArea) != 'undefined') {
				if (pluginsConfig.displayTemporaryArea == false
				|| pluginsConfig.displayTemporaryArea == 'false'
				|| pluginsConfig.displayTemporaryArea == 0) {
					this.displayTemporaryArea = false;
				}
			}
		}
	},
	
	//Create box dialog when the user click on the measure2 plugin in toolbar
	openDlg: function() {
		
		if ($('#' + this.tableId).length == 0) {
    		PM.Dlg.createDnRDlg(this.dlgOptions, _p('cat_measure'), false, 'PM.Plugin.' + this.pluginNameClt + '.onTop');
			
    		// window contents 
    		var htmltable = '<table id="' + this.tableId + '" class="drawingTable" border="0" cellspacing="0" cellpadding="0">';
    		//distance and area images, color picker
    		htmltable += '<tr><th id="' + this.distanceButtonId + '" class="measure_type measure_distance" alt="' + _p('Distance') + '" title="' + _p('Distance') + '" onclick="javascript:PM.Plugin.Measure2.setType(\'distance\')" ></th>';
    		htmltable += '<th id="' + this.areaButtonId + '" class="measure_type measure_area" alt="' + _p('Area') + '" title="' + _p('Area') + '" onclick="javascript:PM.Plugin.Measure2.setType(\'area\')" ></th>';
    	    htmltable += '<th>' + _p('Color') + ' : <input type="text" id="' + this.selectColorId + '" name="' + this.selectColorId + '" value="' + this.color + '" /> ';
    	    htmltable += '</tr></table>';
    	    
    	    //table content
    	    var htmltableContent = '<table id="' + this.tableContentId + '" class="drawingTableContent" border="0" cellspacing="1" cellpadding="0">';
    	    htmltableContent += '<tr><th>' + _p('Number') + '</th><th>' + _p('Type') + '</th><th>' + _p('Measure') + '</th><th>' + _p('Color') + '</th><th>' + _p('Delete') + '</th></tr>';
    	    htmltableContent += '</table>';
    			
    	    $('#' + this.dlgOptions.container + '_MSG').html(htmltable + htmltableContent);
    	    $('#' + this.dlgOptions.container + '_MSG').append("<input type='button' id='" + this.emptyButtonId + "' value='" + _p('Empty') + "' onClick='javascript:PM.Plugin.Measure2.clearObjectsTab()'></input>");
    		
    		$('#' + this.selectColorId).SevenColorPicker();
	   	    $('#' + this.selectColorId).bind('change', function() {PM.Plugin.Measure2.setColor()});

    		this.updateTab();

    		if (!this.drawTypeObj) {
    			$('#' + this.tableId + ' .measure_type:eq(0)').first().click();
    		}
		} else {
			this.redrawPoly();
		}

		$('#' + this.dlgOptions.container).show().trigger('click');

		this.setType(this.measureType); //set type of measure : distance, area
	    
	    if (this.createMeasureInput) {
	    	PM.UI.createMeasureInput(); //create the measure input elements
	    }
	},

	
	customOnTopFunction: function() {
		this.setType(this.measureType); //set type of measure : distance, area

	    if (this.createMeasureInput) {
	    	PM.UI.createMeasureInput(); //create the measure input elements
	    	if (this.measureType == 'area' && this.displayTemporaryArea) {
	    		$("#mSegTxt").html(_p('Area') + PM.measureUnits.area);
	    	}
	    }
	},
	
	//apply css class to the selected choice in the menu
	beforeSetType: function(type) {
		this.measureType = type;
		var newTypeObj = this.measureType;
		
		switch(this.measureType) {
			case 'distance': 
				$('#' + this.distanceButtonId).addClass('measure_distance_select');
				$('#' + this.areaButtonId).removeClass('measure_area_select').addClass('measure_area');
				newTypeObj = 'line';
				break;
			case 'area': 
				$('#' + this.areaButtonId).addClass('measure_area_select');
				$('#' + this.distanceButtonId).removeClass('measure_distance_select').addClass('measure_distance');
				newTypeObj = 'polygon';
				break;
			default :
				$('#' + this.areaButtonId).removeClass('measure_area_select').addClass('measure_area');
				$('#' + this.distanceButtonId).removeClass('measure_distance_select').addClass('measure_distance');
				break;
		}
		
		return newTypeObj;
	},
	
	
	/**
	 * Calculate Area or distance when user double click
	 * @param nbMeasure
	 * @return void
	 */
	afterDblClick: function(nbMeasure) {
		var unit;
		var measure;
		var txt;
		
		if (this.drawTypeObj == "line") {
			unit = PM.measureUnits.distance;
			measure = this.calculLengthPoly(this.polyline);
		} else if (this.drawTypeObj == "polygon"){
			unit = PM.measureUnits.area;
			measure = this.calculAreaPoly(this.polyline);
			
			var length = this.calculLengthPoly(this.polyline);
			if (this.polyline.getPointsNumber() == 3) {
				length = length /2;
			}
			var area = this.calculAreaPoly(this.polyline);
			
			// Change input text box to 'Area'
			$('#measureFormSum').val(length);
			$("#mSegTxt").html(_p('Area') + PM.measureUnits.area); 
		    $('#measureFormSeg').val(area);
		}
		
		unit = this.removeHooks(unit);
		txt = measure + ' ' + unit; 
		
		var properties = this.initObjProperties(txt);
		var data = this.addObject(this.drawTypeObj, '[' + this.polyline.toString(',', '],[') + ']', properties);
		
		if (this.drawTypeObj == "line") {
			this.sendLayerToServer('distance', data);
		} else if (this.drawTypeObj == "polygon"){
			this.sendLayerToServer('area', data);
		}
		
		this.addObjToTab(nbMeasure, this.measureType);
		$('#' + this.emptyButtonId).show();
	},
	
	
	/**
	 * Plugin measure2 properties 
	 */
	initCurrentProperties: function(txt) {
		this.current_properties = ["comment", "color", "measureType"];
		this.current_properties["comment"] = txt;
		this.current_properties["color"] = this.color;
		this.current_properties["measureType"] = this.measureType;
		
		return this.current_properties;
	},
	
	/** Used to add a measure to the HTML table.
	 * @param: nbpoly: number of polygon or polyline ; type: line, polygon 
	 * @return: void
	 */
	addObjToTab_extend: function(nbpoly, type) {
		var measure = this.current_properties["comment"];
		var color = this.current_properties["color"];
		var measureType = this.current_properties["measureType"];
		var upperMeasureType = upperWord(measureType);
		$("#" + this.tableContentId).append("<tr><td>" + (nbpoly + 1) + "</td><td class='measure_" + measureType + "' alt='" + _p(upperMeasureType) + "' title='" + _p(upperMeasureType) + "'></td><td>" + measure + "</td><td><input type='text' class='measure2Color' value='" + color + "' /></td><td><a href='javascript:PM.Plugin.Measure2.deleteObj(" + (nbpoly) + ")'><img alt='delete' title='" + _p('Delete') + "' width='20' height='20' src='" + PM_PLUGIN_LOCATION + "/drawing_base/images/delete.gif'/></a></td></tr>");
		var thisPlugin = this;
		// be careful: because of js compression bad algorithm, do not use space followed by point in string
		$("#" + this.tableContentId + " tr:not(:first):eq(" + (nbpoly) + ")" + " " + ".measure2Color").each(function() {
			thisPlugin.showElemColor(this);
		});
	},
	
	
	/** 
	 * Calculate polyline length
	 * @param: polyGEO Polyline object passed to the handler
	 * @return: perimGEO polyline length 
	 */
	calculLengthPoly: function(polyGEO) {
	   
		var perimGEO  = polyGEO.getPerimeter() / PM.measureUnits.factor ;
		var cntPerLen = Math.round(perimGEO).toString().length;
	    var numSize = Math.max(0, (4 - cntPerLen));
	    perimGEO = PM.roundN(perimGEO, numSize); 
	    
		return perimGEO;
	},
	
	/** 
	 * Calculate last segment length's
	 * @param poly: Polygon object passed to the handler
	 * @return: void 
	 */
	calculLastSegLength: function(poly) {
		
		var segLength = poly.getSideLength(poly.getSidesNumber()) / PM.measureUnits.factor ;
	    var cntSegLen = Math.round(segLength).toString().length;
	    numSize = Math.max(0, (4 - cntSegLen));
	    segLength = PM.roundN(segLength, numSize); 
	    $('#measureFormSeg').val(segLength);
	},
	
	
	/** 
	 * Calculate polygon area
	 * @param polyGEO: Polygon object passed to the handler
	 * @return areaGEO: polygon area 
	 */
	calculAreaPoly: function(polyGEO) {
		
		var perimGEO = polyGEO.getPerimeter() / PM.measureUnits.factor;
		var cntPerLen = Math.round(perimGEO).toString().length;
	    numSize = Math.max(0, (4 - cntPerLen));
		
	    var areaGEO = polyGEO.getArea() / (PM.measureUnits.factor * PM.measureUnits.factor);
		areaGEO = PM.roundN(areaGEO, numSize-1);
		areaGEO = Math.abs(areaGEO); // absolute value
		
		return areaGEO;
	},
	
	/** 
	 * remove hooks from units measurement (distance, area)
	 * @param unit: in this format [m]
	 * @return measureUnit: new unit in this format m 
	 */
	removeHooks: function(unit) {

		var measureUnit = unit.split('['); //remove "[ ]"
		measureUnit = measureUnit[1].split(']');
		measureUnit = measureUnit[0];

		return measureUnit; 
	},
	
	/**
	 * measure length between a point and mouse cursor
	 * @param pluginPolyline
	 * @param mousePoint
	 * @return void
	 */
	calculateLengthTmp: function(currX, currY) {
		if (typeof(PM.Plugin.Measure2.polyline) != 'undefined') {
			$('#mapToolArea').show();
			
			if (this.polyline.getPointsNumber() > 0) {
				var mousePoint = new Point(currX,currY);
				var mousePointGeo = PM.Draw.toGeoPoint(mousePoint);
				this.polyline.addPoint(mousePointGeo);
				
				var lTmp = this.calculLengthPoly(this.polyline);
				$('#measureFormSum').val(lTmp);
				
				if (this.measureType == 'area' && this.displayTemporaryArea) {
					var area = 0;
					if (this.polyline.getPointsNumber() >= 3) {
						this.polyline.addPoint(this.polyline.points[0]);
						area = this.calculAreaPoly(this.polyline);
						this.polyline.delPoint(this.polyline.getPointsNumber()-1);
					}
					$("#mSegTxt").html(_p('Area') + PM.measureUnits.area); 
					$('#measureFormSeg').val(area);
				} else {
					this.calculLastSegLength(this.polyline); // last segment length
				}
			
				this.polyline.delPoint(this.polyline.getPointsNumber()-1);
			}
		}
	},
	
	/** Get layer definition
	 * parameters: type (type of measure: distance, area)  
	 * @return: ret (a json string)
	 */
	getLayerDef: function(type) {
		var ret = false;
		
		switch (type) {
			case 'distance':
				ret = this.test_def_distance;
				break;
			case 'area':
				ret = this.test_def_area;
				break;
			default: 
				break;
		}	
		return ret;
	},
	
	sendLayers: function(type, data) {
		if (type == 'line') {
			this.sendLayerToServer('distance', data);
		} else if (type == 'polygon'){
			this.sendLayerToServer('area', data);
		}
	},
	
	/**
	 * Function called by clearObjectsTab() in drawing.js to get layers to remove 
	 */
	getLayersToRemove: function(data) {
		var drawLayer = '';
		var drawLayerMeasureDist = this.createLayersString(this.test_def_distance, data);
		var drawLayerMeasureArea = this.createLayersString(this.test_def_area, data);
		
		drawLayer = '[' + drawLayerMeasureDist + ',' + drawLayerMeasureArea + ']';
		return drawLayer;
	},
	
	/**
	 * Updates html table 
	 */
	updateTab_extend: function() {
		for (var iPoly = 0 ; iPoly < this.tabObjects.length ; iPoly++) {
			var type = this.tabObjects[iPoly]["type"];
			var properties = this.tabObjects[iPoly]["properties"];
			this.current_properties = properties;
			this.addObjToTab(iPoly, type);
			
		}
		this.redrawPoly();
		
		if (this.tabObjects.length == 0) {
			$('#' + this.emptyButtonId).hide();
		} else {
			$('#' + this.emptyButtonId).show();
		}
	},
	
	/**
	 * Reset form fields
	 */
	reloadData: function() {
	    if (this.polyline.getSidesNumber() == 0) {
	        // Reset form fields 
	        if ($('#measureForm').length > 0) {
	            $('#measureFormSum').val('');
	            $('#measureFormSeg').val('');
	        	$("#mSegTxt").html(_p('Segment') + PM.measureUnits.distance); 
	        }  
	    }
	}
	
});

$.extend(PM.Plugin, {Measure2: measure2Plugin});	    	


$.extend(PM.Map,
{
	/**
	* called from map.js/domouseclick()
	*/
	measure2_click: function() {
		PM.Map.mode = PM.Plugin.Measure2.pluginNameSrv;
		PM.Map.maction = PM.Plugin.Measure2.pluginNameSrv;
		PM.Map.tool = PM.Plugin.Measure2.pluginNameSrv;
		
		// define the cursor
		if (PM.useCustomCursor) {
			PM.setCursor(false, 'crosshair');
		}
		PM.Plugin.Measure2.openDlg();
	},

	/**
	 * SIMPLE CLICK event in main map
	 * start drawing polyline
	 * called from map.js/zoombox_apply()
	 */
	measure2_start: function(imgxy) {
		var pixccoords = imgxy.split('+');
		var pixX = pixccoords[0];
		var pixY = pixccoords[1];
		
		PM.Plugin.Measure2.drawSymbols(pixX, pixY, false);
		$("#mSegTxt").html(_p('Segment') + PM.measureUnits.distance);
	},

	/**
	 * MOUSE MOVE event
	 */
	measure2_mmove: function(e, moveX, moveY) {
		PM.Plugin.Measure2.redrawSegmentTmp(moveX, moveY);
		PM.Plugin.Measure2.calculateLengthTmp(moveX, moveY);
	},

	/**
	 * DOUBLE CLICK event
	 * end measure, calculate polyline length
	 */
	measure2_mdblclick: function() {
		PM.Plugin.Measure2.drawSymbols(PM.ZoomBox.upX, PM.ZoomBox.upY, true);
	},

	/**
	 * Delete last point when the user press key "DEL"
	 */
	measure2_delKeyPress: function() {
		PM.Plugin.Measure2.delLastPoint();
		PM.Plugin.Measure2.reloadData();
	},

	/**
	 * Clear all measures when the user press key "ESC"
	 */
	measure2_EscKeyPress: function() {
		PM.Plugin.Measure2.resetDrawing();
		PM.Plugin.Measure2.reloadData();
	},

	/**
	 * Clear all measures when the user press key "ESC"
	 */
	measure2_Quit: function() {
		PM.Plugin.Measure2.resetDrawing();
	}
});
