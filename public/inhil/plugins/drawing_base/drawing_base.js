/******************************************************************************
 *
 * Purpose: Common drawing class
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

/**********************************************************************************
  USES THE JAVASCRIPT LIBRARIES JSGRAPHICS FROM WALTER ZORN
  SEE FILE /JAVASCRIPT/WZ_JSGRAPHICS.JS FOR DETAILS OF COPYRIGHT
 **********************************************************************************/
/**
 * 1. init_base() : first function, called in derivated classes, initialize parameters and configuration settings.
 * 2. setType() : fix the type of draw, called when the user click on shape image's in box dialog.
 * 3. drawSymbols() : draw the chosen shape.
 * 4. insertTxt() : when the user end drawing, he can insert annotation on the shape.
 * 5. initObjProperties() : init object properties witch will be send to the server.
 * 6. addObject() : add object to array. 
 * 7. generateJson() : generate json string of the shape.
 * 8. sendLayerToServer() : send data to the server, the layer of the shape will be added to pmapper by the PM.Map.ClientDynamicLayers.xxxLayers() function
 * 9. addObjToTab() : add object to the HTML table.
 */


var drawingBase = {
// members:
	drawTypeObj: null, //shape to be drawn : point, line, polygon ...
	tabObjects: null, //will contain an array of shapes
	drawNbObj: 0, //drawing counter
	polyline: new Polygon(), //instantiates a new array of Point objects (see the constructor in javascript/src/pm.geometry.js for more details)
	
// drawing parameters:
	color: null, //color of the shape
	outLineColor: null,	//outline color of the shape
	fontFamily: "arial", //default font-family, -size and -style (used to insert text annotation)
	fontSize: "15px",
	fontStyle: Font.ITALIC_BOLD,
	default_color: '#FF0000', //default drawing color
	default_outlineColor: '#0000FF', //default outline color
	
// members that could/should be re-written in derivated classes:
	selectColorId: '', //color input element's name
	selectOutlineColorId: '', //outline color input element's name
	tableContentId: '', //HTML table content name
	emptyButtonId: '', // empty button name (used to clear all objects drawn)
	tableId: '', //HTML table header name
	
// functions that could/should be re-written in derivated classes:
	init: null, // --> Has to call "this.init_base();" !!!
	beforeSetType: null, //what to do before fixing the type of draw
	afterSetType: null,
	afterDblClick: null, //what to do after double click event
	afterDrawSymbols: null, //what to do at end drawing
	getLayerDef: null, //return a json string with the layer definition
	initCurrentProperties: null, //initialize an array of object's properties
	sendLayers: null, //Send layers to the server
	updateTab_extend: null, //Update html table after an action like deleting an object, close and re-open box dialog, clear all objects ... 
	getLayersToRemove: null, //Get layers to remove when user want to clear all draw by clicking on "emptyButtonId" 
	afterOpenDlg: null,
	addObjToTab_extend: null, //add an object to the html table
	afterRedrawPoly: null,

	pluginNameSrv: '', // pluginName for init function
	pluginNameClt: '', // pluginName for init function
	
	helpMsg: '',

	onTopAvoidRecursiveCalls: false,
	customOnTopFunction: null,
	
	//called by init() function in derivated classes : initialize parameters	
	init_base: function() {
		this.tabObjects = [];
		this.color = this.default_color;
		this.outLineColor = this.default_outlineColor;

		// redraw while refreshing map, zoom in, zoom out ...
		PM.Map.bindOnMapRefresh({"pluginNameSrv": this.pluginNameSrv, "pluginNameClt": this.pluginNameClt}, function(e) {
			if (PM.Map.maction == e.data.pluginNameSrv) {
    	    	var pluginObject = getPMPluginObjFromString(e.data.pluginNameClt);
    	    	if (pluginObject) {
    	    		pluginObject.redrawPoly();
    	    	}
			}
		});
	},

	//set the type of draw : point, line, polygon
	setType: function(type) {
		var newType = type;

		if (this.beforeSetType) {
			newType = this.beforeSetType(type);
		}

		this.drawTypeObj = newType ? newType : type;
		this.resetDrawing();
		
		if (this.afterSetType) {
			this.afterSetType();
		}
	},

	onTop: function() {
		if (!this.onTopAvoidRecursiveCalls) {
			this.onTopAvoidRecursiveCalls = true;

			if (this.pluginNameSrv) {
		        // change tool --> execute quit function
	        	if (typeof(PM.Map.mode) != 'undefined' && PM.Map.mode != this.pluginNameSrv) {
		            var fct = PM.Map.mode + '_Quit';
		            if ($.isFunction(PM.Map[fct])) {
		                eval('PM.Map.' + fct + '()');
		            }
	        	}
	        	
				// for ontop event: simulate click button
				PM.setTbTDButton(this.pluginNameSrv);
	
				PM.Map.mode = this.pluginNameSrv;
		        PM.Map.maction = this.pluginNameSrv;
		        PM.Map.tool = this.pluginNameSrv;
		        
		        // define the cursor
		        if (PM.useCustomCursor) {
		            PM.setCursor(false, 'crosshair');
		        }
			}

			$('#helpMessage').html(_p(this.helpMsg)).show();

			if ($.isFunction(this.customOnTopFunction)) {
				this.customOnTopFunction();
			}

			this.redrawPoly();
		}
		this.onTopAvoidRecursiveCalls = false;
	},

	/**
	 * Redraw the segment between polyline's or polygon's last point and mouse click in dotted.
	 * parameters: currX,currY in pixels
	 */
	redrawSegmentTmp: function(currX, currY) {
		if (typeof(this.polyline) != 'undefined' && !this.polyline.isClosed()) {
			//this.color = $('#' + this.selectColorId).val() ? $('#' + this.selectColorId).val() : this.default_color;
			
			var nbPoints = this.polyline.getPointsNumber();
			if (nbPoints > 0) {
				var lastPointGeo = this.polyline.getPoint(nbPoints - 1);
				var lastPointPx = PM.Draw.toPxPoint(lastPointGeo);
				
				var mousePoint = new Point(currX, currY); //current mouse X and Y coordinates.
				jg_tmp.clear();
				jg_tmp.setColor(this.outLineColor != null ? this.outLineColor : this.color);		//Specifies the color of the drawing "pen"
				jg_tmp.setStroke(Stroke.DOTTED);	//constant Stroke.DOTTED -> to draw dotted lines.
				if (this.drawTypeObj == 'polygon') {
					jg_tmp.setStroke(2); //Specifies the thickness of the drawing "pen"
					PM.Draw.drawLineSegment(jg_tmp, new Line(lastPointPx, mousePoint));
					jg_tmp.setStroke(1);
					jg_tmp.setStroke(Stroke.DOTTED);
					var firstPointGeo = this.polyline.getPoint(0); //get polyline first point
					var firstPointPx = PM.Draw.toPxPoint(firstPointGeo); //convert to pixels
					PM.Draw.drawLineSegment(jg_tmp, new Line(firstPointPx, mousePoint));
				} else if (this.drawTypeObj == 'line') {
					PM.Draw.drawLineSegment(jg_tmp, new Line(lastPointPx, mousePoint));
				} else if (this.drawTypeObj == 'circle') {
					//draw a cross at circle center	
					var crossSize = 3;
					jg_tmp.drawLine(lastPointPx.x - crossSize, lastPointPx.y - crossSize, lastPointPx.x + crossSize, lastPointPx.y + crossSize);
					jg_tmp.drawLine(lastPointPx.x - crossSize, lastPointPx.y + crossSize, lastPointPx.x + crossSize, lastPointPx.y - crossSize);
					
					this.drawCircle(jg_tmp, lastPointPx, mousePoint);
					
					// calculate radius, circumference, area while moving
					var lastPointPxGeo = PM.Draw.toGeoPoint(lastPointPx);
					var mousePointGeo = PM.Draw.toGeoPoint(mousePoint);			

					var radius = this.calculateCircleRadius(lastPointPxGeo, mousePointGeo);			
					radius = this.roundMeasures(radius, 4);
					
					var circumference = this.calculateCircleCircumference(radius);
					circumference = this.roundMeasures(circumference, 4);
					
					var area = this.calculateCircleArea(radius);
					area = this.roundMeasures(area, 6);
					
					$('#circleRadius').val(radius);
					$('#circleCircumference').val(circumference);				
					$('#circleArea').val(area);	
					
				} else if (this.drawTypeObj == 'rectangle') {
					var x = Math.min(mousePoint.x, lastPointPx.x);
					var y = Math.min(mousePoint.y, lastPointPx.y);
					var larg = Math.abs(mousePoint.x - lastPointPx.x);
					var lon = Math.abs(mousePoint.y - lastPointPx.y);
					
					jg_tmp.drawRect(x, y, larg, lon);
					jg_tmp.paint();
				}	
			}
		}
	},
	
	/**
	 * Main function, draws symbol points between 2 mouseclicks
	 * parameters:  clickX, clickY: coords in pixels; dblClick: true / false 
	 * @return void
	 */
	drawSymbols: function(clickX, clickY, dblClick) {
//		if ((this.drawTypeObj==null) || (this.drawTypeObj=="undefined") || ($('#' + this.tableId).length == 0) || ($('#' + this.tableId).css('display') == 'none')){
		if ((this.drawTypeObj==null) || (this.drawTypeObj=="undefined")){
			return;
		}
		
		if ((clickX < PM.mapW) && (clickY < PM.mapH)) {   // Don't go outside map
			
			var drawNbObjet = this.tabObjects.length;
			var pointPx = new Point(clickX,clickY); // Create a Point object(px coordinates)
			var pointGeo = PM.Draw.toGeoPoint(pointPx); // Return a Point object with geo coordinates	
		
			this.initColors(); // fix user's color, outlineColor choice
			
			if (!dblClick) { // SINGLE CLICK

				switch(this.drawTypeObj) {
				
					case 'annotation':
					case 'point':
						if (this.drawTypeObj == 'point') {
							PM.Draw.drawLineSegment(jg,new Line(pointPx, pointPx));	
						}
						var txt = this.insertTxt(pointPx); // insert annotation
						var properties = this.initObjProperties(txt);
						var data = this.addObject(this.drawTypeObj, '[' + pointGeo.toString(',') + ']', properties);
						this.sendLayerToServer(this.drawTypeObj, data);
						
						this.addObjToTab(drawNbObjet, this.drawTypeObj);
						
						break;
						
					case 'line':
					case 'polygon':	
						var nPoints = this.polyline.getPointsNumber();
						
						// First point for start click
			        	if (nPoints < 1) {
			        		this.polyline.addPoint(pointGeo);
			        	} else {
			        		var lastpointGeoPoly = this.polyline.getPoint(this.polyline.getPointsNumber()-1);
			        		if (!pointGeo.equals(lastpointGeoPoly)) {
			        			this.polyline.addPoint(pointGeo);
				        		// USE wz_jsgraphics.js TO DRAW LINE. lastSegment is of Line type                 
				      			var lastSegment = this.polyline.getLastSide();
				      			var sidesNumber = this.polyline.getSidesNumber();                              		
				      			
				      			var lastPointPoly = this.polyline.getPoint(this.polyline.getPointsNumber()-1);
				      			var penultimatePointPoly = this.polyline.getPoint(this.polyline.getPointsNumber()-2);
				      			
				      			var lastPointPolyPx = PM.Draw.toPxPoint(lastPointPoly);	
				      			var penultimatePointPolyPx = PM.Draw.toPxPoint(penultimatePointPoly);
				      			
				      			var lastSegmentPx = new Line(penultimatePointPolyPx , lastPointPolyPx);
				      			
				      			// check for the overlapping of the new side.
				      			// it will never overlap with the previous side  	    	  
				      			if (this.drawTypeObj == 'polygon') {
					      			if (sidesNumber > 2) {      		    
					      				for (var s = 1 ; s < (sidesNumber-1); s++) {                 
					      					var intersectionPoint = this.polyline.getSide(s).intersection(lastSegment);
					      					if (intersectionPoint != null) {                  
					      						alert(_p('digitize_over'));
					      						this.polyline.delPoint(this.polyline.getPointsNumber()-1);
					      						return;                  
					      					}                
					      				}
					      			}
				      			}
				      			PM.Draw.drawLineSegment(jg,lastSegmentPx);
				      		}
			      		} 
						break;
					
					case 'circle':
					case 'rectangle':
						var nPoints = this.polyline.getPointsNumber();
						// many simple click --> remove the first if nb > 2:
						if (nPoints > 2) {
							// keep the last one
							this.polyline.delPoint(0);
						}
						nPoints = this.polyline.getPointsNumber();

						// First point for start click
			        	if (nPoints < 1) {
			        		this.polyline.addPoint(pointGeo);
			        	} else {
							// detect if we are in the second single click of a double click:
			        		var lastpointGeoPoly = this.polyline.getPoint(this.polyline.getPointsNumber()-1);
							// --> not second single click of a double click
			        		if (!pointGeo.equals(lastpointGeoPoly)) {
			        			this.polyline.addPoint(pointGeo);
			        		}	
			        	}
						break;	
										
					default:
						break;
				}
				
			} else { //DOUBLE CLICK 
				
				switch(this.drawTypeObj) {
				
				case 'line':
				case 'polygon':
								
					var nPoints = this.polyline.getPointsNumber();
					if (nPoints <= 1) {
						this.polyline.delPoint(0);
					}
					
					nPoints = this.polyline.getPointsNumber();
					if (nPoints > 0) {
						if (this.drawTypeObj=='polygon') {
							this.polyline.close(); // Closing the polyline to have a polygon  	 
				  	    	
				            // fix the last side
				            var lastSegment = this.polyline.getLastSide();	   
				  	    	var sidesNumber = this.polyline.getSidesNumber();
				  	    	
				  	    	var lastPointPoly = this.polyline.getPoint(this.polyline.getPointsNumber()-1);
			      			var penultimatePointPoly = this.polyline.getPoint(this.polyline.getPointsNumber()-2);
			      			
			      			var lastPointPolyPx = PM.Draw.toPxPoint(lastPointPoly);	
			      			var penultimatePointPolyPx = PM.Draw.toPxPoint(penultimatePointPoly);
			      			
			      			var lastSegmentPx = new Line(penultimatePointPolyPx , lastPointPolyPx);
			
				  	    	// check for the overlapping of the closing side
				  	    	// it will never overlap with the first and the last side
			      			if (this.drawTypeObj == 'polygon') {
				      			for (var s = 2 ; s < (sidesNumber-1); s++) {                 
					                var intersectionPoint = this.polyline.getSide(s).intersection(lastSegment);
					                if (intersectionPoint != null) {                  
					                    alert(_p('digitize_over'));
					                    this.polyline.delPoint(this.polyline.getPointsNumber()-1);
					                    return false;                  
					                }                
					            }
			      			}
				  	    		    	  	    	            		
				  	    	if (lastSegment != null) {
				  	    		PM.Draw.drawLineSegment(jg,lastSegmentPx); //draw polygon last segment
				  	    	}
						}	
			  	    } else {
						alert(_p('dblclick_error')); // you can't double click to start a new polygon
					}	
		            break;
		        
				case 'circle':
					var nPoints = this.polyline.getPointsNumber();
					if (nPoints <= 1) {
						this.polyline.delPoint(0);
					}
					nPoints = this.polyline.getPointsNumber();
					if (nPoints >= 2) {
						
						var centerPointGeo = this.polyline.getPoint(0);
						var borderPointGeo = this.polyline.getPoint(1);
						
						var centerPointPx = PM.Draw.toPxPoint(centerPointGeo);	
						var borderPointPx = PM.Draw.toPxPoint(borderPointGeo);
						
						jg_tmp.clear();
						// draw circle
						this.drawCircle(jg, centerPointPx, borderPointPx);

						// calculate circle diameter with geo coordinaters for the server
						var radiusGeo = this.calculateCircleRadius(centerPointGeo, borderPointGeo);
						var diameterGeo = 2 * radiusGeo;
						if (typeof(this[this.drawTypeObj]) != 'undefined') {
							this[this.drawTypeObj].draw.defaultThickness = diameterGeo;
						}
						this.polyline.delPoint(1); //keep only the first point witch is the circle center.

					} else {
						alert(_p('dblclick_error'));
					}
					break;
				
				case 'rectangle':	
					var nPoints = this.polyline.getPointsNumber();
					if (nPoints <= 1) {
						this.polyline.delPoint(0);
					}
					nPoints = this.polyline.getPointsNumber();
					if (nPoints >= 2) {
						
						var point1Geo = this.polyline.getPoint(0);
						var point2Geo = this.polyline.getPoint(1);
						
						var point3Geo = new Point(point2Geo.x, point1Geo.y);
						var point4Geo = new Point(point1Geo.x, point2Geo.y);
						
						this.polyline.reset();
						this.polyline.addPoint(point1Geo);
						this.polyline.addPoint(point3Geo);
						this.polyline.addPoint(point2Geo);
						this.polyline.addPoint(point4Geo);
						this.polyline.close();
						
					} else {
						alert(_p('dblclick_error'));
						//return false;
					}
					break;
					
				default : 
					break;	
				}
				if (this.afterDblClick) {
	  	    		this.afterDblClick(drawNbObjet);
				}
				this.polyline.reset(); // remove all points from the polygon      
			}
		}
		if (this.afterDrawSymbols) {
			this.afterDrawSymbols(clickX, clickY, dblClick);
		}
	},
	
	//add the object to the html table
	addObjToTab: function(drawNbObjet, type) {
		if (this.addObjToTab_extend) {
			this.addObjToTab_extend(drawNbObjet, type);
		}
	},
	
	//initialize color, outline color to use for drawing
	initColors: function() {
//		this.color = $('#' + this.selectColorId).val() ? $('#' + this.selectColorId).val() : '#FF0000';
//		this.outLineColor = $('#' + this.selectOutlineColorId).val() ? $('#' + this.selectOutlineColorId).val() : '#00FF00';
		jg.setColor(this.outLineColor != null ? this.outLineColor : this.color); //Specifies the color of the drawing "pen"
		jg.setStroke(2); //Specifies the thickness of the drawing "pen" 
	},
	
	setColor: function() {
		var container = null;
		var color = this.default_color;
		
		container = $('#' + this.selectColorId);
		color = this.default_color;
		if (container.length) {
			color = container.val();
		}
		this.color = color;

		if (this.selectOutlineColorId != '') {
			container = $('#' + this.selectOutlineColorId);
			color = this.default_outlineColor;
			if (container.length) {
				color = container.val();
			}
			this.outLineColor = color;
		}
	},
	
	//insert text annotation on the shape
	insertTxt: function(point) {
		jg.setFont(this.fontFamily,this.fontSize,this.fontStyle); //set font-family, -size and -style values
		
		var insertTxt = prompt(_p('Add comment:'), ''); //user can enter his text
		if (insertTxt == null) {
			insertTxt = '';
		}
		jg.drawString(insertTxt, point.x, point.y); //Writes text to the location specified by X and Y
		jg.paint(); //Must be envoked explicitly to draw the internally-generated graphics into the html page.
		
		return insertTxt;
	},
	
	// show color graphically
	showElemColor: function(elem) {
		$(elem).hide().parent().css('background-color', $(elem).val());
	},
	
	/** 
	 * Send data to the server
	 * @param: type, data 
	 * @return: void
	 */
	sendLayerToServer: function(type, data) {
		if (this.getLayerDef) {
			var testDefPlugin = this.getLayerDef(type);
			
			var drawLayer = '[' + this.createLayersString(testDefPlugin, data) + ']';
			
			//to know if the layer will be added or removed, we search the key word "geometry" in the string str
			//removed layers no have geometry
			var str = drawLayer;
			var subStr = "geometry";
			var position = str.indexOf(subStr);
			
			if (position != -1) 
				PM.Map.ClientDynamicLayers.addOrReplaceLayers(drawLayer); //add dynamic layer to pmapper
			else 
				PM.Map.ClientDynamicLayers.removeLayers(drawLayer); //remove dynamic layer to pmapper

//			PM.Map.reloadMap(true);
		}
	},
	
	
	/** 
	 * initialize object's properties
	 * @param: txt(text grabbed by user) 
	 * @return: properties(array of properties)
	 */
	initObjProperties: function(txt) {
		var pluginProperties = [];

		if (this.initCurrentProperties) {
			pluginProperties = this.initCurrentProperties(txt);
		}
		
		return pluginProperties;
	},
	
	/** 
	 * Delete a specified object
	 * @param drawNbObjet: index of the object 
	 * @return void
	 */
	deleteObj: function(index) {
		var type = this.tabObjects[index]["type"];
		this.tabObjects.splice(index, 1);
		var data = this.generateJson(type);
		
		if (this.sendLayers) {
			this.sendLayers(type, data);
		}
		this.updateTab();
	},
	
	/** 
	 * Updates the table
	 * @return void
	 */
	updateTab: function() {
		this.clearTab();
		if (this.updateTab_extend) {
			this.updateTab_extend();
		}	
	},
	
	/**
	 * remove HTML table content
	 */
	clearTab: function() {
		$("#" + this.tableContentId + " tr:not(:first)").remove();
	},

	/** 
	 * Redraw polylines and polygons
	 * @return void
	 */
	redrawPoly: function() {
		// remove lines
		jg.clear();
		jg_tmp.clear();
		
		if (typeof(this.polyline) == 'undefined')
			return;
		// redraw all polylines or polygons.
		var tabPointsPoly = this.polyline.getPoints();	
					
		for (var iPoint = 0; iPoint < tabPointsPoly.length - 1; iPoint++) {
					
			var point1Geo =  tabPointsPoly[iPoint];
			var point2Geo =  tabPointsPoly[iPoint +1];
						
			var point1Px = PM.Draw.toPxPoint(point1Geo);
			var point2Px =  PM.Draw.toPxPoint(point2Geo);
				
			PM.Draw.drawLineSegment(jg,new Line(point1Px, point2Px));
		}

		if (this.afterRedrawPoly && $.isFunction(this.afterRedrawPoly)) {
			this.afterRedrawPoly();
		}
	},
	
	
	/**
	 * create the string to be sent to the server by concatenating layer's defintion and Data.
	 */
	createLayersString: function(def, data) {
		var ret = '{"def": ' + def + ', "datatype": "GeoJson", "data": ' + data + '}';
		return ret;
	},
	
	/** 
	 * Add an object in Array
	 * @param: type, coordinates, properties
	 * @return: result of generateJson() function call's.
	 */
	addObject: function(type, coordinates, properties) {
		var obj = []; 
		obj = ["type", "properties", "coordinates"];
		obj["type"] = type; 
		obj["coordinates"] = coordinates; 
		obj["properties"] = properties; 
		
		this.tabObjects.push(obj);
		
		return this.generateJson(type);
	},
	
	
	/** 
	 * Generate Json of the object
	 * @param type: object's type (point, line, polygon etc ...)
	 * @return out : Json string.
	 */
	generateJson: function(type) {
		var geojsonstr = '';
		for (var i=0; i < this.tabObjects.length; i++) {
			if (this.tabObjects[i]["type"] == type) {
				geojsonstr += this.objGeoToJson(this.tabObjects[i]) + ',';
			}
		}
		// remove the last comma
		if (geojsonstr.length > 0) {
			geojsonstr = geojsonstr.substring(0, geojsonstr.length -1);
		}
		
		var out = '{"type": "FeatureCollection", "features": [' + geojsonstr + ']}';
		return out;
	},
	
	/** 
	 * Delete all objects of the table
	 * @return void
	 */
	clearObjectsTab: function() {
		var data = '{"type": "FeatureCollection", "features": []}';
		$('#' + this.emptyButtonId).hide();
		
		var drawLayer = this.getLayersToRemove(data);
		PM.Map.ClientDynamicLayers.removeLayers(drawLayer);
		
		this.tabObjects.length = 0;
		this.updateTab();
	},
	
	/**
	 * Delete last point, function called when the user press key "DEL"
	 */
	delLastPoint: function() {
		var nPoints = this.polyline.getPointsNumber();
	    
		if (nPoints > 0) {
	    	this.polyline.delPoint(nPoints - 1);
	    	this.redrawPoly(); //Reload drawing after deleting a point 
		}
	},

	/**
	 * Clear all draw, function called when the user press key "ESC"
	 */
	resetDrawing: function() {
	    // remove lines
		this.polyline.reset();
		jg.clear();    
	    jg_tmp.clear();
	},
	
	
	/** 
	 * Check if the string "data" contains the substring "subStr"
	 * parameters: data
	 * @return: void
	 */
	checkGeometry: function(data) {
		
		var str = data;
		var subStr = "geometry";
		var position = str.indexOf(subStr);
		
		if (position != -1)
			str = data;
		else 
			str = '';
		
		return str;
	},
	
	/** 
	 * Generate Json string of the object drawn, 
	 * doc of Json fomat available at : http://www.json.org/
	 */
	objGeoToJson: function(obj) {
		var returnJsonData ="";
		var type = "";
		var coordinates = "[]";
		var properties = "{}";
		var typeOK = true;
		var coordinatesTmp = obj["coordinates"];

		switch (obj["type"]) {
			case 'line':
				type = "LineString";
				break;
			case 'point':
			case 'circle': 
			case 'annotation':	
				type = "Point";
				// remove "[" and "]" around the coordinates
				coordinatesTmp = obj["coordinates"].substring(1, obj["coordinates"].length - 1);
				break;
			case 'polygon':
			case 'rectangle':
				type = "Polygon";
				coordinatesTmp = '[' + obj["coordinates"] + ']';
				break;	
			default:
				typeOK = false;
				break;
		}
		if (typeOK) {
			coordinates = coordinatesTmp;
			properties = "";
			var propertyTitle;
			var propertyValue;
			var quotes;
			for(var i = 0; i < obj["properties"].length; i++){
				propertyTitle = obj["properties"][i];
				propertyValue = obj["properties"][propertyTitle];
				
				// escape '"' character, "&" --> "%26":
				if (propertyTitle == "comment") {
					var regExp = /"/g;
					propertyValue = propertyValue.replace(regExp, '\\"');
					regExp = /&/g;
					propertyValue = propertyValue.replace(regExp, '%26');
				}

				if (typeof(obj["properties"][propertyTitle]) == 'number'){
					quotes = '';
				} else{
					quotes = '"';
				}
				
				properties += '"' + propertyTitle + '": ' + quotes + propertyValue + quotes + ',';
	        }
			properties = properties.substring(0, properties.length -1);	
		}

		returnJsonData = '{ "type": "Feature",';
		returnJsonData += '"geometry": {';
		returnJsonData += '"type": "' + type + '",';
		
		returnJsonData += '"coordinates": [';
		returnJsonData += coordinates; 
		returnJsonData += ']';
		returnJsonData += '},';
		
		returnJsonData += '"properties": {';
		returnJsonData += properties;
		returnJsonData += '}';  
	    returnJsonData += '}';
			
		return returnJsonData;
	},
	
	/**
	 * function used to draw a circle.
	 * parameters: jg, point1, point2 in pixels.
	 * @return: void
	 */
	drawCircle: function (jg, centerPointPx, borderPointPx) {
		var radius = this.calculateCircleRadius(centerPointPx, borderPointPx);

		var x1 = centerPointPx.x - radius;
		var y1 = centerPointPx.y - radius;
		
		var diameter = radius * 2; 
		jg.drawEllipse(x1, y1, diameter, diameter);
		jg.paint();
	},
	
	calculateCircleRadius: function(centerPoint, borderPoint) {
		var radius = 0;
		
		var circleWidth = Math.abs(borderPoint.x - centerPoint.x);
		var circleHeight = Math.abs(borderPoint.y - centerPoint.y);

		radius = Math.sqrt((Math.pow(circleWidth, 2)) + (Math.pow(circleHeight, 2)));
		
	    var cntCircleRadius = Math.round(radius).toString().length;
	    numSize = Math.max(0, (4 - cntCircleRadius));
	    circumference = PM.roundN(radius, numSize); 
		
		return radius;
	},
	
	calculateCircleRadius: function(centerPoint, borderPoint) {
		var radius = 0;
		
		var circleWidth = Math.abs(borderPoint.x - centerPoint.x);
		var circleHeight = Math.abs(borderPoint.y - centerPoint.y);

		radius = Math.sqrt((Math.pow(circleWidth, 2)) + (Math.pow(circleHeight, 2)));

		return radius;
	},
	
	calculateCircleCircumference: function(radius) {
		return Math.PI * 2 * radius;
	},
	
	calculateCircleArea: function(radius) {
		return Math.PI * Math.pow(radius, 2);
	},
	
	roundMeasures: function (measure, radix) {
	    var cntMeasure = Math.round(measure).toString().length;
	    var numSize = Math.max(0, (radix - cntMeasure));
	    measure = PM.roundN(measure, numSize); 			

		return measure;
	},
	
	/**
     * Create the circle input elements
     */
	createCircleDimensionsInput: function() {
        var mStr =  '<form id="circleDimensionsForm"><div class="pm-measure-form"><table class="pm-toolframe"><tr>';
        mStr += '<td NOWRAP>' + _p('Circle') + ' :</td>';
        mStr += '<td NOWRAP>' + _p('Radius') + PM.measureUnits.distance + '</td>';
        mStr += '<td><input type=text size=7 id="circleRadius"></td>';
        mStr += '<td NOWRAP>' + _p('Circumference') + PM.measureUnits.distance + '</td>';
        mStr += '<td><input type=text size=7 id="circleCircumference"></td>';
        mStr += '<td NOWRAP>' + _p('Area') + PM.measureUnits.area + '</td>';
        mStr += '<td><input type=text size=7 id="circleArea"></td>';
        mStr += '</tr></table></form>';
        
        $('#circleDimensionsForm').remove();
        $('#mapToolArea').append(mStr).show();
	}
	
};