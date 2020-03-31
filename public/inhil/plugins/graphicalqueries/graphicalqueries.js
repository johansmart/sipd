/******************************************************************************
 *
 * Purpose: Graphical queries
 * Author:  Vincent Mathis, SIRAP
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

var pluginGraphicalQueries = $.extend({}, drawingBase,
{
	selectType: '',
	circle: {draw:{defaultThickness:0}},
	selectMethode: '',
	useMenuDlg: false,

	pluginNameSrv: 'graphicalqueries', // pluginName for init function
	pluginNameClt: 'GraphicalQueries', // pluginName for init function
	
	
	init: function() {
		this.init_base(); // init parameters of common drawing class
		this.tableId = "graphicalQueriesTable";
		
		if (typeof(PM.ini.pluginsConfig.graphicalqueries) != 'undefined') {
			if (typeof(PM.ini.pluginsConfig.graphicalqueries.useMenuDlg) != 'undefined') {
				if (PM.ini.pluginsConfig.graphicalqueries.useMenuDlg == 1) {
					this.useMenuDlg = true;
				}
			}
		}
		
		this._after_init();
	},
	
	// add buffer search button
	_after_init: function() {
		// pmQueryContainer_MSG
		var strToAdd = 'PM.Plugin.GraphicalQueries.addButtonSelect("object")';
		if ($.inArray(strToAdd, PM.Custom.queryResultAddList) != 1) {
			$.merge(PM.Custom.queryResultAddList, [strToAdd]);
		}
	},
	
	// Remove activ Tool
	stopSelectObject: function() {
		PM.Map.resetFrames();
		// return to the first tool
		PM.setTbTDButton('zoomin');
		PM.Map.domouseclick('zoomin');
	},
	
	// Add form for selected option
	addToolForm: function(container){
		// Add container div
		if ($("#GraphicalQueries_Id").length == 0) {
			var text ='<div id="GraphicalQueries_Id" class="graphicalQueries">\n </div>';
			container.append(text);
		}
		
		// Add tool's option
		if ($("#GraphicalQueries_Buffer").length == 0) {
			var text = '<input id="GraphicalQueries_AllLayer" type="checkbox" value="0" size="6">'+_p('GraphicalQueries_AllLayer_text')+'</input>\n';
			text += '<br/>'+_p('GraphicalQueries_Buffer_text')+'<input id="GraphicalQueries_Buffer" type="text" value="0" size="6"> </input>\n';
			
			if (this.selectType != "line") {
				text += '<br/><input id="GraphicalQueries_BufferInt" type="checkbox" value="0" size="6">'+_p('GraphicalQueries_BufferInt_text')+'</input>\n';
			}
			
			if (this.selectType == "object") {
				PM.setTbTDButton();
				text += '<br/><br/><input type="submit" value="' + _p('Apply') +'" onclick="PM.Plugin.' + this.pluginNameClt + '.select()"></input>\n';
				text += ' <input type="submit" value="' + _p('Cancel') + '" onclick="PM.Plugin.' + this.pluginNameClt + '.stopSelectObject()"></input>\n';
			}
			
			$("#GraphicalQueries_Id").append(text);
			
			if (typeof(PM.Plugin.SelectionManagement) != 'undefined') {
				$("#GraphicalQueries_Id").append('<br/>');
				PM.Plugin.SelectionManagement.addSelectionOperator('#GraphicalQueries_Id', 'Plugin.GraphicalQueries', false);
			}
		}
	},
	
	// Generic click to a Button of this plugin
	click: function(type) {
		this.setType(type);
		var txt_base = 'graphicalqueries';
		var txt = txt_base + '_' + type;

		var help = '';
		var helpTmp1 = txt + '_help';
		var helpTmp2 = _p(helpTmp1);
		if (helpTmp1 != helpTmp2) {
			help = helpTmp2;
		}

		if (typeof(cursor) == 'undefined') {
			cursor = 'pointer';
		}
		
		PM.Map.resetFrames();
		
		PM.Map.mode = txt_base;
		PM.Map.maction = txt_base;
		PM.Map.tool = type;
	    
	    if (PM.useCustomCursor) {
	        PM.setCursor(false, 'crosshair');
	    }
		
		if (help) {
			PM.Map.showHelpMessage(_p(help));
		}
		
		//if (this.selectType != 'delSelect' && this.selectType != 'reloadSelect'){
		if (this.selectType){
			this.addToolForm($("#mapToolArea"));
		
			var selurl = PM_XAJAX_LOCATION + 'x_select.php?'+SID;
			PM.Map.updateSelectTool(selurl);
			if (this.selectMethode) {
				this.setSelection(this.selectMethode);				
			} else {
				this.setSelection('new');
			}
		}
		
		if (type == "circle"){
			this.createCircleDimensionsInput();
		}	
	},
	
	// First click of the map
	start: function(imgxy) {
		var pixccoords = imgxy.split('+');
		var pixX = pixccoords[0];
		var pixY = pixccoords[1];

		pixX = parseInt(pixX);
		pixY = parseInt(pixY);

		if (this.selectType == 'point') {
			// draw a cross
			this.initColors();
			var size = 5;
			var pointP1 = new Point(pixX-size,pixY);
			var pointP2 = new Point(pixX+size,pixY);
			var pointP3 = new Point(pixX,pixY-size);
			var pointP4 = new Point(pixX,pixY+size);
			PM.Draw.drawLineSegment(jg,new Line(pointP1, pointP2));
			PM.Draw.drawLineSegment(jg,new Line(pointP3, pointP4));
			// double click
			this.drawSymbols(pixX, pixY, true);
		} else {
			this.drawSymbols(pixX, pixY, false);
		}
	},
	
	// Before send the tool type
	beforeSetType: function(type) {
		var newtype = type;
		if (type == "object") {
			newtype = "point";
		}
		if (type == "rectangle") {
			newtype = "polygon";
		}
		
		this.selectType = type;
		
		return newtype;
	},
	
	// after double click -> End of the exection type
	afterDblClick: function() {
		this.select();
	},
	
	// any text to insert: re-write funtion of drawing plugin
	insertTxt: function() {
		this.select();
	},
	
	setSelection: function(type) {
		//var typeSelection = type;
		this.selectMethode = type;
		if (typeof(PM.Plugin.SelectionManagement) != 'undefined') {
			PM.Plugin.SelectionManagement.setSelectionOperator('GraphicalQueries', this.selectMethode);
		}
	},
	
	
	getSelectLayer: function() {
		var ret = '';
		// Layer
		if (!$("#GraphicalQueries_AllLayer").attr("checked")) {
			ret = "&groups="+PM.Query.getSelectLayer(); //"groups=XXX" // selected layer
		}
		return ret;
	},
	
	// Send to the server
	select: function(){
	
		var urlreq = PM_PLUGIN_LOCATION + "/graphicalqueries/x_graphicalqueries.php";
		var params = SID;
		var select_type = this.selectType;
		params += "&operation=select_type&select_type="+select_type;

		params += this.getSelectLayer();

		// selectMethode
		//--------------
		var selectMethode = this.selectMethode;
		params += "&selectMethode=" + selectMethode;
		
		var bufferValue = $("#GraphicalQueries_Buffer").attr("value");
		if (bufferValue != 0 && typeof(bufferValue) != 'undefined'){
			if($("#GraphicalQueries_BufferInt").attr("checked") ){
				params += "&select_buffer=-" + bufferValue;
			}else{
				params += "&select_buffer=" + bufferValue;
			}
		}

		if (select_type == "polygon" || select_type == "rectangle") {
			params += "&select_poly=" + this.polyline;
		} 
		else if (select_type == "line") {
			params += "&select_line=" + this.polyline;
		}
		if (select_type == "point") {
			var pointPx = new Point(PM.ZoomBox.upX, PM.ZoomBox.upY);
			var pointGeo = PM.Draw.toGeoPoint(pointPx);
			
			params += "&select_pointX=" + pointGeo.x;
			params += "&select_pointY=" + pointGeo.y;
		} 
		else if (select_type == "circle") {
			nPoints = this.polyline.getPointsNumber();
			if (nPoints == 1) {
				var centerPointGeo = this.polyline.getPoint(0);
//				var borderPointGeo = this.polyline.getPoint(1);
				
				var radiusGeo = this.circle.draw.defaultThickness / 2;
						
				params += "&select_radius=" + radiusGeo;
				params += "&select_point=" + centerPointGeo;
			}
		} else if(select_type == "object") {
			this.stopSelectObject();
		}
	
		$.ajax({
			url: urlreq,
			dataType: "json",
			type: "POST",
			data: params,
			callingPluginName: this.pluginNameClt,
			success: function(response) {
				// Select the objects
				PM.Query.writeQResult(response.queryResult, PM.infoWin);	
				
				// empty selection
				if (response.queryResult == 0) {
					PM.Map.clearInfo();
				}
			},
			error: function (XMLHttpRequest, textStatus, errorThrown) {
				if (window.console) {
					console.log(errorThrown);
				}
            },
			complete: function() {
				var pluginObject = getPMPluginObjFromString(this.callingPluginName);
				if (pluginObject) {
					pluginObject.resetDrawing();
				}
			}
		});
	}, //End function select
	
	//Add the button to start select by object -> To add at the end of the select box
	addButtonSelect: function(type) {
		var text ='<input type="submit" value="' + _p('graphicalqueries_object') + '" onclick="PM.Plugin.' + this.pluginNameClt + '.click(\''+type+'\')"></input>';
		
		return text;	
	}
});

$.extend(PM.Plugin, {GraphicalQueries: pluginGraphicalQueries});

$.extend(PM.Map,
{
	// Start point
	firstX : 0,
	firstY : 0,
	
	/**
	 * click on the tool button 
	 */
	graphicalqueries_polygon_click: function() {
		PM.Plugin.GraphicalQueries.click('polygon');
	},
	
	graphicalqueries_polyline_click: function() {
		PM.Plugin.GraphicalQueries.click('line');
	},

	graphicalqueries_circle_click: function() {
		PM.Plugin.GraphicalQueries.click('circle');
	},
	
	graphicalqueries_point_click: function() {
		PM.Plugin.GraphicalQueries.click('point');
	},
	
	graphicalqueries_object_click: function() {
		PM.Plugin.GraphicalQueries.click('object');
	},
	
	graphicalqueries_rectangle_click: function() {
		PM.Plugin.GraphicalQueries.click('rectangle');
	},
	
	/**
	 * AfterUpdateSelLayer event
	 */
	graphicalqueries_afterUpdateSelLayers: function() {
		if (PM.Plugin.GraphicalQueries.selectType) {
			var selurl = PM_XAJAX_LOCATION + 'x_select.php?'+ SID + '&activegroup=' + PM.Query.getSelectLayer();
			PM.Map.updateSelectTool(selurl);
		}
	},
	 
	/**
	 * AfterUpdateMap event
	 */
	graphicalqueries_afterUpdateMap: function() {
		// To not display the select box when using del or refresh function. 
		if (PM.Plugin.GraphicalQueries.selectType){
			var selurl = PM_XAJAX_LOCATION + 'x_select.php?'+ SID + '&activegroup=' + PM.Query.getSelectLayer();
		    PM.Map.updateSelectTool(selurl);
		}
	 },
	 
	/**
      * SIMPLE CLICK event in main map
	  */   
	graphicalqueries_start: function(imgxy) {
		 if (PM.Map.tool == "rectangle" ) {
			 if (!PM.Plugin.GraphicalQueries.polyline.points[0]) {
				 // memorize starting point
				 PM.Plugin.GraphicalQueries.polyline.reset();
				 var tmpPoint = imgxy.split("+");		 
				 var pointPx = new Point(tmpPoint[0], tmpPoint[1]);
				 var pointGeo = PM.Draw.toGeoPoint(pointPx);
				 
				 PM.Plugin.GraphicalQueries.polyline.addPoint(pointGeo);
			 }
		 } else {
			 PM.Plugin.GraphicalQueries.start(imgxy);
		 }
	},

	/**
	 * MOUSE MOVE event
	 */
	graphicalqueries_mmove: function(e, moveX, moveY) {
		if(PM.Map.tool == "rectangle" && PM.Plugin.GraphicalQueries.polyline.points[0]){
			var pointGeo = PM.Plugin.GraphicalQueries.polyline.points[0];
			var pointPx = PM.Draw.toPxPoint(pointGeo);
			
			var x = Math.min(moveX, pointPx.x);
			var y = Math.min(moveY, pointPx.y);
			var larg = Math.abs(moveX - pointPx.x);
			var long = Math.abs(moveY - pointPx.y);
			
			jg.clear();
			jg.drawRect(x, y, larg, long);
			jg.paint();
			
		}else{
			PM.Plugin.GraphicalQueries.redrawSegmentTmp(moveX, moveY);			
		}
	},
	
	/**
	  * DOUBLE CLICK event
	  * end drawing
	  */
	graphicalqueries_mdblclick: function(imgxy) {
		if(PM.Map.tool == "rectangle" && PM.Plugin.GraphicalQueries.polyline.points[0]){
			//Point 1
			var pointGeo1 = PM.Plugin.GraphicalQueries.polyline.points[0];
			
			//Point 2 
			//var pointPx2 = new Point(imgxy.clientX, imgxy.clientY);
			var pointPx2 = new Point(PM.ZoomBox.upX, PM.ZoomBox.upY);
			var pointGeo2 = PM.Draw.toGeoPoint(pointPx2);

			var tmpPoint;
			
			tmpPoint = new Point(pointGeo2.x, pointGeo1.y);
			PM.Plugin.GraphicalQueries.polyline.addPoint(tmpPoint);
			tmpPoint = new Point(pointGeo2.x, pointGeo2.y);
			PM.Plugin.GraphicalQueries.polyline.addPoint(tmpPoint);
			tmpPoint = new Point(pointGeo1.x, pointGeo2.y);			
			PM.Plugin.GraphicalQueries.polyline.addPoint(tmpPoint);
			tmpPoint = new Point(pointGeo1.x, pointGeo1.y);
			PM.Plugin.GraphicalQueries.polyline.addPoint(tmpPoint);
		}
		PM.Plugin.GraphicalQueries.drawSymbols(PM.ZoomBox.upX, PM.ZoomBox.upY, true);
	},
	
	/**
	 * Delete last point when the user press key "DEL"
	 */
	graphicalqueries_delKeyPress: function() {
		PM.Plugin.GraphicalQueries.delLastPoint();
	},

	/**
	 * Remove all measure settings (called when users press key "ESC")
	 */
	graphicalqueries_EscKeyPress: function() {
		PM.Plugin.GraphicalQueries.resetDrawing();
	},
	
	/**
	 * quit function
	 */
	graphicalqueries_Quit: function() {
		this.graphicalqueries_EscKeyPress();
	}
	
});