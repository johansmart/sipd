/******************************************************************************
 *
 * Purpose: Drawing points, polylines, polygons 
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

var drawingPlugin = $.extend({}, drawingBase, 
{
	//Dialog options
	dlgOptions: {width:465, height:250, left:50, top:100, resizeable:true, newsize:true, container:'pmDrawingContainer', name:'Drawing'},
	dlgType: 'dynwin',
	default_color: '#FF0000', //default drawing color
	default_outlineColor: '#00FF00', //default outline color
	//shape draw and label default properties
	point: {draw:{defaultSymbol:'circle', defaultThickness:10}, label:{defaultFont:'FreeSans', defaultTextSize:10}},
	line: {draw:{defaultSymbol:'circle', defaultThickness:10}, label:{defaultFont:'FreeSans', defaultTextSize:10}},
	polygon: {draw:{defaultSymbol:'square', defaultThickness:10}, label:{defaultFont:'FreeSans', defaultTextSize:10}},
	circle: {draw:{defaultSymbol:'drawing-circle', defaultThickness:10}, label:{defaultFont:'FreeSans', defaultTextSize:10}},
	rectangle: {draw:{defaultSymbol:'square', defaultThickness:10}, label:{defaultFont:'FreeSans', defaultTextSize:10}},
	annotation: {draw:{defaultSymbol:'', defaultThickness:null}, label:{defaultFont:'FreeSans', defaultTextSize:10}},
	
	//layers definition
	layer_def_point: '{"type":"tplMapFile", "tplname": "drawPoint", "layername": "drawingPoint", "category": "cat_drawing"}',
	layer_def_annotation: '{"type":"tplMapFile", "tplname": "drawAnnotation", "layername": "drawingAnnotation", "category": "cat_drawing"}',
	layer_def_line: '{"type":"tplMapFile", "tplname": "drawLine", "layername": "drawingLine", "category": "cat_drawing"}',
	layer_def_polygon: '{"type":"tplMapFile", "tplname": "drawPolygon", "layername": "drawingPolygon", "category": "cat_drawing"}',
	layer_def_circle: '{"type":"tplMapFile", "tplname": "drawCircle", "layername": "drawingCircle", "category": "cat_drawing"}',
	layer_def_rectangle: '{"type":"tplMapFile", "tplname": "drawRectangle", "layername": "drawingRectangle", "category": "cat_drawing"}',
	
	pointButtonId: "tb_drawPoint", //point button name
	lineButtonId: "tb_drawLine", //line button name
	polygonButtonId: "tb_drawPolygon", //polygon button name
	circleButtonId : "tb_drawCircle", //circle button name	
	rectangleButtonId: "tb_drawRectangle", //rectangle button name
	annotationButtonId: "tb_drawAnnotation", //annotation button name
	
	current_properties: [], //array of shape properties

	pluginNameSrv: 'drawing', // pluginName for init function
	pluginNameClt: 'Drawing', // pluginName for init function
	
	helpMsg: 'drawing_help',

	init: function() {
		this.init_base(); //init parameters of common drawing class

		this.selectColorId = this.pluginNameSrv + "color"; //color input element's name for the plugin drawing
		this.selectOutlineColorId = this.pluginNameSrv + "outlineColor"; //outline color input element's name for the plugin drawing
		this.tableContentId = this.pluginNameSrv + "TableContent"; //HTML table content name for the plugin drawing
		this.emptyButtonId = this.pluginNameSrv + "Empty"; //empty button name for the plugin drawing
		this.tableId = this.pluginNameSrv + "Table"; //HTML table header name for the plugin drawing

		
		// load config settings from config_XXXXX.xml file
		if (typeof(PM.ini.pluginsConfig[this.pluginNameSrv]) != 'undefined') {
			var pluginsConfig = PM.ini.pluginsConfig[this.pluginNameSrv];
			if (typeof(pluginsConfig.dlgType) != "undefined") {
				this.dlgType = pluginsConfig.dlgType;
			}
			if (typeof(pluginsConfig.default_color) != "undefined") {
				this.default_color = pluginsConfig.default_color;
				this.color = this.default_color;
			}
			if (typeof(pluginsConfig.default_outlineColor) != "undefined") {
				this.default_outLineColor = pluginsConfig.default_outlineColor;
				this.outLineColor = this.default_outLineColor;
			}
			if (typeof(pluginsConfig.point) != "undefined") {
				this.point = pluginsConfig.point;
			}
			if (typeof(pluginsConfig.line) != "undefined") {
				this.line = pluginsConfig.line;
			}
			if (typeof(pluginsConfig.polygon) != "undefined") {
				this.polygon = pluginsConfig.polygon;
			}
			if (typeof(pluginsConfig.circle) != "undefined") {
				this.circle = pluginsConfig.circle;
			}
			if (typeof(pluginsConfig.rectangle) != "undefined") {
				this.rectangle = pluginsConfig.rectangle;
			}
			if (typeof(pluginsConfig.annotation) != "undefined") {
				this.annotation = pluginsConfig.annotation;
			}
		}
	},
	
	//apply css class to the selected choice in the menu
	beforeSetType: function(type) {
		this.drawTypeObj = type;
		if (!$.isEmptyObject(this.point)) {
			$('#' + this.pointButtonId).removeClass('drawing_point_select').addClass('drawing_point');
		}
		if (!$.isEmptyObject(this.line)) {
			$('#' + this.lineButtonId).removeClass('drawing_line_select').addClass('drawing_line');
		}
		if (!$.isEmptyObject(this.polygon)) {
			$('#' + this.polygonButtonId).removeClass('drawing_polygon_select').addClass('drawing_polygon');
		}
		if (!$.isEmptyObject(this.circle)) {
			$('#' + this.circleButtonId).removeClass('drawing_circle_select').addClass('drawing_circle');
		}
		if (!$.isEmptyObject(this.rectangle)) {
			$('#' + this.rectangleButtonId).removeClass('drawing_rectangle_select').addClass('drawing_rectangle');
		}
		if (!$.isEmptyObject(this.annotation)) {
			$('#' + this.annotationButtonId).removeClass('drawing_annotation_select').addClass('drawing_annotation');
		}

		switch(this.drawTypeObj){
			case 'line':
				if (!$.isEmptyObject(this.line)) {
					$('#' + this.lineButtonId).removeClass('drawing_line').addClass('drawing_line_select');
				}
				break;
			case 'polygon':
				if (!$.isEmptyObject(this.polygon)) {
					$('#' + this.polygonButtonId).removeClass('drawing_polygon').addClass('drawing_polygon_select');
				}
				break;
			case 'point':
				if (!$.isEmptyObject(this.point)) {
					$('#' + this.pointButtonId).removeClass('drawing_point').addClass('drawing_point_select');
				}
				break;
			case 'circle':
				if (!$.isEmptyObject(this.circle)) {
					$('#' + this.circleButtonId).removeClass('drawing_circle').addClass('drawing_circle_select');
				}
				this.createCircleDimensionsInput();
				break;	
			case 'rectangle':
				if (!$.isEmptyObject(this.rectangle)) {
					$('#' + this.rectangleButtonId).removeClass('drawing_rectangle').addClass('drawing_rectangle_select');
				}
				break;		
			case 'annotation':
				if (!$.isEmptyObject(this.annotation)) {
					$('#' + this.annotationButtonId).removeClass('drawing_annotation').addClass('drawing_annotation_select');
				}
				break;		

			default :
				break;
		}
		return this.drawTypeObj;
	},
	
	/**
	 * Create box dialog when the user click on the drawing plugin in toolbar
	 */
	openDlg: function() {
		$('#helpMessage').html(_p('drawing_help')).show();
		
		if ($('#' + this.tableId).length == 0) {
			// Create dynamic window
			PM.Dlg.createDnRDlg(this.dlgOptions, _p('Drawing'), false, 'PM.Plugin.' + this.pluginNameClt + '.onTop');
			
    		// window contents 
    	    //header table
    	    var htmltable = '<table id="' + this.tableId + '" class="drawingTable" border="0" cellspacing="3" cellpadding="0">';

    		if (!$.isEmptyObject(this.point)) {
	    	    htmltable += '<tr><th id="' + this.pointButtonId + '" class="drawing_type drawing_point" alt="' + _p('Point') + '" title="' + _p('Point') + '" onclick="javascript:PM.Plugin.' + this.pluginNameClt + '.setType(\'point\')" ></th>';
    		}
			if (!$.isEmptyObject(this.line)) {
	    	    htmltable += '<th id="' + this.lineButtonId + '" class="drawing_type drawing_line" alt="' + _p('Line') + '" title="' + _p('Line') + '" onclick="javascript:PM.Plugin.' + this.pluginNameClt + '.setType(\'line\')" ></th>';
    		}
			if (!$.isEmptyObject(this.polygon)) {
	    	    htmltable += '<th id="' + this.polygonButtonId + '" class="drawing_type drawing_polygon" alt="' + _p('Polygon') + '" title="' + _p('Polygon') + '" onclick="javascript:PM.Plugin.' + this.pluginNameClt + '.setType(\'polygon\')" ></th>';
    		}
			if (!$.isEmptyObject(this.circle)) {
				htmltable += '<th id="' + this.circleButtonId + '" class="drawing_type drawing_circle" alt="' + _p('Circle') + '" title="' + _p('Circle') + '" onclick="javascript:PM.Plugin.' + this.pluginNameClt + '.setType(\'circle\')" ></th>';
    		}
			if (!$.isEmptyObject(this.rectangle)) {
				htmltable += '<th id="' + this.rectangleButtonId + '" class="drawing_type drawing_rectangle" alt="' + _p('Rectangle') + '" title="' + _p('Rectangle') + '" onclick="javascript:PM.Plugin.' + this.pluginNameClt + '.setType(\'rectangle\')" ></th>';
    		}
			if (!$.isEmptyObject(this.annotation)) {
				htmltable += '<th id="' + this.annotationButtonId + '" class="drawing_type drawing_annotation" alt="' + _p('Annotation') + '" title="' + _p('Annotation') + '" onclick="javascript:PM.Plugin.' + this.pluginNameClt + '.setType(\'annotation\')" ></th>';
    		}
			htmltable += '<th>' + _p('Default colors') + ' : <br /><input type="text" id="' + this.selectColorId + '" name="' + this.selectColorId + '" value="'+ this.color +'" /> ';
    	    htmltable += '<input type="text" id="' + this.selectOutlineColorId + '" name="' + this.selectOutlineColorId + '" value="'+ this.outLineColor +'" />';
    	    htmltable += '</tr></table>';
    	    
    	    //content table
    	    var htmltableContent = '<table id="' + this.tableContentId + '" class="drawingTableContent" border="0" cellspacing="1" cellpadding="0">';
    	    htmltableContent += '<tr><th>' + _p('Index') + '</th><th>' + _p('Type') + '</th><th>' + _p('Comment') + '</th><th>' + _p('Color') + '</th><th>' + _p('Outline') + '</th>';
    	    htmltableContent += '<th>'+ _p('Delete') + '</th>';
    	    htmltableContent += '</tr></table>';
    	    htmltableContent += '<div id="' + this.emptyButtonId + '"><input type="button" value="'+ _p('Empty') + '" onClick="javascript:PM.Plugin.' + this.pluginNameClt + '.clearObjectsTab()"></input></div>';
    	    
    	    $('#' + this.dlgOptions.container + '_MSG').html(htmltable + htmltableContent);
    	    
    	    $('#' + this.selectColorId).SevenColorPicker();
    	    $('#' + this.selectColorId).bind('change', {"pluginNameClt": this.pluginNameClt}, function(e) {
    	    	var pluginObject = getPMPluginObjFromString(e.data.pluginNameClt);
    	    	if (pluginObject) {
    	    		pluginObject.setColor();
    	    	}
    	    });
    	    $('#' + this.selectOutlineColorId).SevenColorPicker();
    	    $('#' + this.selectOutlineColorId).bind('change', {"pluginNameClt": this.pluginNameClt}, function(e) {
    	    	var pluginObject = getPMPluginObjFromString(e.data.pluginNameClt);
    	    	if (pluginObject) {
    	    		pluginObject.setColor();
    	    	}
    	    });
    	    
    	    $('#' + this.emptyButtonId).hide();

    		this.updateTab();
    		
    		if (!this.drawTypeObj) {
    			$('#' + this.tableId + ' .drawing_type:eq(0)').first().click();
    		}
		} else {
			this.redrawPoly();
		}

		$('#' + this.dlgOptions.container).show().trigger('click');

	    this.setType(this.drawTypeObj);
	},
	
	customOnTopFunction: function() {
	    this.setType(this.drawTypeObj);
	},
	
	//function called when the user end drawing
	afterDblClick: function(drawNbObjet) {
		//get the last point of the poly
		var lastPointPolyGeo = this.polyline.getPoint(this.polyline.getPointsNumber()-1);
		var lastPointPolyPx = PM.Draw.toPxPoint(lastPointPolyGeo);
		// insert annotation
		var txt = "";
		if (this.drawTypeObj != "circle") {
			txt = this.insertTxt(lastPointPolyPx); 
		}
		var properties = this.initObjProperties(txt);
		var data = this.addObject(this.drawTypeObj, '[' + this.polyline.toString(',', '],[') + ']', properties);
		this.sendLayerToServer(this.drawTypeObj, data);
		
		this.addObjToTab(drawNbObjet, this.drawTypeObj);
	},

	//display empty button
	afterDrawSymbols: function(clickX, clickY, dblClick) {
		if ((clickX < PM.mapW) && (clickY < PM.mapH)) {   // Don't go outside map
			if (this.tabObjects.length > 0) {
				$('#' + this.emptyButtonId).show();
			}
		}
	},
	
	/** Get layer definition
	 * parameters: type (type of object : point,line ...)  
	 * @return: ret (a json string)
	 */
	getLayerDef: function(type) {
		var ret = false;
		
		switch (type) {
		
			case 'annotation':
				ret = this.layer_def_annotation;
				break;
			
			case 'point':
				ret = this.layer_def_point;
				break;
						
			case 'line':
				ret = this.layer_def_line;
				break;
			
			case 'polygon':
				ret = this.layer_def_polygon;
				break;
			
			case 'circle':
				ret = this.layer_def_circle;
				break;	
			
			case 'rectangle':
				ret = this.layer_def_rectangle;
				break;	
				
			default : 
				break;
		}	
		return ret;
	},
	
	
	/** function used to add an object to the HTML table.
	 * parameters: drawNbObjet(object's index), type(object's type), properties(object's properties)  
	 * @return: void
	 */
	addObjToTab_extend: function(drawNbObjet, type) {

		var txt = this.current_properties["comment"];
		var color = this.current_properties["colorHex"];
		var outLineColor = this.current_properties["outLineHex"];
		
		var upperType = upperWord(type); 
		
		var htmlstr = "<tr><td>" + drawNbObjet + "</td><td class='drawing_" + type + "' alt='" + _p(upperType) + "' title='" + _p(upperType) + "'></td><td>"  + txt + "</td><td><input type='text' class='drawColor' value='" + color + "' /></td><td>";
		if (type != "line" && type != "circle" && type != "annotation") {
			htmlstr += "<input type='text' class='drawOutlineColor' value='" + outLineColor + "' />";
		}
		htmlstr += "</td><td><a href='javascript:PM.Plugin." + this.pluginNameClt + ".deleteObj(" + (drawNbObjet) + ")'><img alt='" + _p('Delete') + "' title='" + _p('Delete') + "' width='20' height='20' src='" + PM_PLUGIN_LOCATION + "/drawing_base/images/delete.gif'/></a></td>";
		htmlstr += "</tr>";
		$("#" + this.tableContentId).append(htmlstr);
/*
		$("#" + this.tableContentId + " tr:not(:first):eq(" + drawNbObjet + ") >> .drawColor").SevenColorPicker();
*/
		var thisPlugin = this;
		// be careful: because of js compression bad algorithm, do not use space followed by point in string
		$("#" + this.tableContentId + " tr:not(:first):eq(" + drawNbObjet + ")" + " " + ".drawColor").each(function() {
			thisPlugin.showElemColor(this);
		});
		$("#" + this.tableContentId + " tr:not(:first):eq(" + drawNbObjet + ")" + " " + ".drawOutlineColor").each(function() {
			thisPlugin.showElemColor(this);
		});
		drawNbObjet++;
	},
	
	//initialize array of object's properties
	initCurrentProperties: function(txt) {
		this.current_properties = ["index", "comment", "symbolThickness", "color", "outLineColor", "colorHex", "outLineHex", 
			"textSize", "fontFamily", "symbolShape"];

		this.current_properties["index"] = this.tabObjects.length;
		this.current_properties["comment"] = txt;
		this.current_properties["symbolThickness"] = this[this.drawTypeObj].draw.defaultThickness;
		this.current_properties["color"] = convertHexToRGB(this.color);
		
		this.current_properties["outLineColor"] = convertHexToRGB(this.outLineColor);
		this.current_properties["colorHex"] = this.color;
		this.current_properties["outLineHex"] = this.outLineColor;
		this.current_properties["textSize"] = this[this.drawTypeObj].label.defaultTextSize;
		this.current_properties["fontFamily"] = this[this.drawTypeObj].label.defaultFont;	
		this.current_properties["symbolShape"] = this[this.drawTypeObj].draw.defaultSymbol;
		
		return this.current_properties;
	},
	
	sendLayers: function(type, data) {
		this.sendLayerToServer(type, data);
	},
	
	/**
	 * Layers to remove for the plugin drawing 
	 */
	getLayersToRemove: function(data) {
		
		var drawLayer = '';
		
		var drawLayerAnno = this.createLayersString(this.layer_def_annotation, data);
		var drawLayerPoint = this.createLayersString(this.layer_def_point, data);
		var drawLayerLine = this.createLayersString(this.layer_def_line, data);
		var drawLayerPolygon = this.createLayersString(this.layer_def_polygon, data);
		var drawLayerCircle = this.createLayersString(this.layer_def_circle, data);
		var drawLayerRectangle = this.createLayersString(this.layer_def_rectangle, data);
		
		drawLayer = '[' + drawLayerAnno + ',' + drawLayerPoint + ',' + drawLayerLine + ',' + drawLayerPolygon + ',' + drawLayerCircle + ',' + drawLayerRectangle + ']';		
		return drawLayer;
	},
	
	/** 
	 * Update html table 
	 * after an action like deleting an object, close and re-open box dialog, clear all objects ...
	 */ 
	updateTab_extend: function() {
		
		for (var iObj = 0 ; iObj < this.tabObjects.length ; iObj++) {
			var typeObj = this.tabObjects[iObj]["type"];
			var properties = this.tabObjects[iObj]["properties"];
			this.current_properties = properties;
			this.addObjToTab(iObj, typeObj);
		}
		this.redrawPoly();
		
		$('#' + this.downloadLinkId + ' a').attr('href','#').parent().hide();
		
		if (this.tabObjects.length == 0) {
			$('#' + this.emptyButtonId).hide();
		} else {
			$('#' + this.emptyButtonId).show();
		}
	},
	
	/**
	 * function used to close box dialog
	 * @return: void
	 */
	drawCloseWindow: function() {
		$('#' + this.dlgPropertiesOptions.container).hide();
	}

});

$.extend(PM.Plugin, {Drawing: drawingPlugin});
			
$.extend(PM.Map,
{
    
	/**
     * custom sample script for extending tool functions
     * called from map.js/domouseclick()
     * must be named '*_click()'
     */
	drawing_click: function() {
		PM.Map.mode = PM.Plugin.Drawing.pluginNameSrv;
        PM.Map.maction = PM.Plugin.Drawing.pluginNameSrv;
        PM.Map.tool = PM.Plugin.Drawing.pluginNameSrv;
        
        // define the cursor
        if (PM.useCustomCursor) {
            PM.setCursor(false, 'crosshair');
        }
        PM.Plugin.Drawing.openDlg();
	},
	
	/**
     * SIMPLE CLICK event in main map
     * start drawing point, line, polygon ...
     * called from map.js/zoombox_apply()
     * must be named '*_start(imgxy)'
     */
	drawing_start: function(imgxy) {
		var pixccoords = imgxy.split('+');
	    var pixX = pixccoords[0];
		var pixY = pixccoords[1];
		
		PM.Plugin.Drawing.drawSymbols(pixX, pixY, false);
	},

	/**
     * MOUSE MOVE event
     */
	drawing_mmove: function(e, moveX, moveY) {
		PM.Plugin.Drawing.redrawSegmentTmp(moveX, moveY);
	},
	
	/**
      * DOUBLE CLICK event
      * end drawing
      */
	drawing_mdblclick: function() {
		PM.Plugin.Drawing.drawSymbols(PM.ZoomBox.upX, PM.ZoomBox.upY, true);
	},
	
	/**
	 * Delete last point when the user press key "DEL"
	 */
	drawing_delKeyPress: function() {
		PM.Plugin.Drawing.delLastPoint();
	},
	
	/**
	 * Remove all measure settings (called when users press key "ESC")
	 */
	drawing_EscKeyPress: function() {
		PM.Plugin.Drawing.resetDrawing();
	},
	
	/**
	 * Quit function
	 */
	drawing_Quit: function() {
		this.drawing_EscKeyPress();
	}		     
});