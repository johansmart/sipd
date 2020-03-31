
/******************************************************************************
 *
 * Purpose: drawing functions (measurements, digitizing)
 *          uses the geometry.js library
 * Authors: Armin Burger, Federico Nieri
 *
 ******************************************************************************
 *
 * Copyright (c) 2003-2006 Armin Burger
 *
 * This file is part of p.mapper.
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
 
 
/**********************************************************************************
  USES THE JAVASCRIPT LIBRARIES JSGRAPHICS FROM WALTER ZORN
  SEE FILE /JAVASCRIPT/WZ_JSGRAPHICS.JS FOR DETAILS OF COPYRIGHT
 **********************************************************************************/  

$.extend(PM.Draw,
{
    numSize: null,
    polyline: new Polygon(),
    geoPolyline: new Polygon(),
        
    /** 
     * Return a Point object with geo coordinate instead of px coordinate
     * @param pxPoint: Point object with px coordinate
     */
    toGeoPoint: function(pxPoint){
        var x_geo = PM.minx_geo + (((pxPoint.x + 1)/PM.mapW)  * PM.xdelta_geo);
        var y_geo = PM.maxy_geo - (((pxPoint.y + 1)/PM.mapH) * PM.ydelta_geo);
        return new Point(x_geo,y_geo);
    },

    /** 
     * Return a Polygon object with geo coordinate instead of px coordinate
     * @param pxPolygon: Polygon object with px coordinate
     */
    toGeoPolygon: function(pxPolygon){
        var pxPoints = pxPolygon.getPoints();
        var geoPolygon = new Polygon();
        for(var i = 0; i < pxPoints.length; i++){
            geoPolygon.addPoint(this.toGeoPoint(pxPoints[i]));
        }
        return geoPolygon;
    },

    toPxPolygon: function(geoPolygon){
        var geoPoints = geoPolygon.getPoints();
        var pxPolygon = new Polygon();
        for(var i = 0; i < geoPoints.length; i++){
            pxPolygon.addPoint(this.toPxPoint(geoPoints[i]));
        }
        return pxPolygon;
    },

    toPxPoint: function(geoPoint){
      var x_px = ((geoPoint.x - PM.minx_geo) / PM.xdelta_geo) * PM.mapW - 1;
      var y_px = ((PM.maxy_geo - geoPoint.y) / PM.ydelta_geo) * PM.mapH - 1;	
        return new Point(x_px,y_px);
    },

    /**
     * Return a geography measure unit instead of px
     * @param pxLength: length in px
     */
    toGeoLength: function(pxLength){
        return (pxLength/PM.mapW) * PM.xdelta_geo;
    },

    /**
     * Main function, draws symbol points between mouseclicks
     * @return void
     */
    measureDrawSymbols: function(e, clickX, clickY, dblClick) {
        // Polyline points number before to add the current click point
        if(this.polyline.isClosed()){
          this.polyline.reset();
        }
        
        var nPoints = this.polyline.getPointsNumber();   
        var clickPoint = new Point(clickX, clickY); 
        // Reset everything when last measure ended with double click
        if (nPoints == 0) this.resetMeasure();        
        // Don't go outside map
        if ((clickX < PM.mapW) && (clickY < PM.mapH)) { 
            
            // SINGLE CLICK
            if (dblClick != 1) { 
                
                this.polyline.addPoint(new Point(clickX,clickY));
                            
                // First point for start click
                if (nPoints < 1) {

                    this.drawLineSegment(jg,new Line(clickPoint, clickPoint));         			

                // Fill distance between clicks with symbol points
                }else{
                
                  // USE wz_jsgraphics.js TO DRAW LINE. lastSegment is of Line type                 
                    var lastSegment = this.polyline.getLastSide();
                    var sidesNumber = this.polyline.getSidesNumber();                              		
                    
                      // check for the overlapping of the new side.
                    // it will never overlap with the previous side  	    	  
                    if (sidesNumber > 2){      		    
                        for (var s = 1 ; s < (sidesNumber-1); s++){                 
                            var intersectionPoint = this.polyline.getSide(s).intersection(lastSegment);
                            if (intersectionPoint != null){                  
                                alert(_p('digitize_over'));
                                this.polyline.delPoint(this.polyline.getPointsNumber()-1);
                                return;                  
                            }                
                        }
                    }
                                                                                                        
                    this.drawLineSegment(jg,lastSegment);
                    // calls the handler of the side (segment) digitation and pass it the polyline in px coords
                    this.onDigitizedSide(this.polyline);
                }      	        	        	        	                                  
                                
            // DOUBLE CLICK => CALCULATE AREA
            } else if (dblClick) {
                                                                    
                // Removes the last duplicated point because of the last 2 single click	    	
                this.polyline.delPoint(this.polyline.getPointsNumber()-1);
                                        
                // Closing the polyline to have a polygon  	    	
                this.polyline.close();
                
                // fix the last side
                var lastSegment = this.polyline.getLastSide();	   
                var sidesNumber = this.polyline.getSidesNumber();
                
                // check for the overlapping of the closing side
                // it will never overlap with the first and the last side
                for (var s = 2 ; s < (sidesNumber-1); s++){                 
                    var intersectionPoint = this.polyline.getSide(s).intersection(lastSegment);
                    if (intersectionPoint != null){                  
                        alert(_p('digitize_over'));
                        this.polyline.delPoint(this.polyline.getPointsNumber()-1);
                        return false;                  
                    }                
                }	    	
                                                            
                if(lastSegment != null){    	
                    this.drawLineSegment(jg,lastSegment);
                }
              
                // calls the handler of the polygon digitation before reset the polygon
                this.onDigitizedPolygon(this.polyline);
                
                // remove all points from the polygon          
                //polyline.reset();
                            
            }                   
        }        
        this.geoPolyline = this.toGeoPolygon(this.polyline);
    },


    /** 
     * Handler of the digitized polygon action. It is called when a double click
     * close tha drawing polygon
     * @param poly: Polygon object passed to the handler
     */
    onDigitizedPolygon: function(poly){
        
        var polyGEO = this.toGeoPolygon(poly);
        var perimGEO = polyGEO.getPerimeter()/PM.measureUnits.factor;	
        
        var cntPerLen = Math.round(perimGEO).toString().length;
        this.numSize = Math.max(0, (4 - cntPerLen));
        
        perimGEO = PM.roundN(perimGEO, this.numSize); 
        
        var areaGEO = Math.abs(PM.roundN (polyGEO.getArea() / (PM.measureUnits.factor * PM.measureUnits.factor), this.numSize-1)) ;
                    
        // Change input text box to 'Area'
        $('#measureFormSum').val(perimGEO);
        $("#mSegTxt").html(_p('Area') + PM.measureUnits.area); 
        $('#measureFormSeg').val(areaGEO);
    },

    /** 
     * Handler of the digitized line action. It is called when a new click cause draw a new line
     * @param poly: Polygon object passed to the handler
     */
    onDigitizedSide: function(poly){
        // Polygon in map coordinates
         var polyGEO = this.toGeoPolygon(poly);
            
        // Segment length in  map coordinates,  write values to input boxes
        var segLenGEO_0 = polyGEO.getSideLength(polyGEO.getSidesNumber()) / PM.measureUnits.factor ;
        var perimGEO_0  = polyGEO.getPerimeter() / PM.measureUnits.factor ;
        
        var cntSegLen = Math.round(segLenGEO_0).toString().length;
        this.numSize = Math.max(0, (4 - cntSegLen));
        var segLenGEO = PM.roundN(segLenGEO_0, this.numSize); 
        var perimGEO  = PM.roundN(perimGEO_0, this.numSize);     

        var measureSegment = false;
        if (measureSegment){
            $('#measureFormSeg').val(segLenGEO);
            if (polyGEO.getPointsNumber() >= 2){
                poly.reset();
            }
        } else {
            $('#measureFormSum').val(perimGEO);
            $('#measureFormSeg').val(segLenGEO);
        }        
    },

    /**
     * REDRAW THE LAST AND THE CLOSING SIDE OF THE POLYGON
     */
    redrawAll: function(currX, currY) {

        if(this.polyline.isClosed())
          return;

        if (this.polyline.getPointsNumber()>0) {    	

            var mousePoint = new Point(currX,currY);
            jg_tmp.clear();
            jg_tmp.setColor(PM.measureObjects.line.color); 
            jg_tmp.setStroke(PM.measureObjects.line.width);
            // Drawing last side	    
            var lastPoint = this.polyline.getPoint(this.polyline.getPointsNumber()-1);
                    
            this.drawLineSegment(jg_tmp,new Line(lastPoint,mousePoint));
                            
            jg_tmp.setStroke(Stroke.DOTTED); 
            var firstPoint = this.polyline.getPoint(0);
                  
            this.drawLineSegment(jg_tmp,new Line(firstPoint,mousePoint));
        }		    
    },

    drawPolyline: function(jg,poly) {  
        var n = poly.getSidesNumber();
        for (var i=1;i<=n;i++) {    
            this.drawLineSegment(jg,poly.getSide(i));
        }
    },

    /**
     * DRAW LINE USING JSGRAPHICS
     */
    drawLineSegment: function(jg,line) {

        var xfrom = line.getFirstPoint().x;
        var yfrom = line.getFirstPoint().y;
        var xto = line.getSecondPoint().x;
        var yto = line.getSecondPoint().y;
        
        var limitSides = this.getLimitSides();
        var xList = limitSides.getXList();
        var yList = limitSides.getYList();
        
        var xMin = Math.min.apply({},xList);
        var yMin = Math.min.apply({},yList);        
        var xMax = Math.max.apply({},xList);
        var yMax = Math.max.apply({},yList);    
        
        var points = new Array();
        
        if  (xfrom >= xMin && xfrom <= xMax && yfrom >= yMin && yfrom <= yMax) {
            points.push(line.getFirstPoint());       
        }
      
        if  (xto >= xMin && xto <= xMax && yto >= yMin && yto <= yMax) {
            points.push(line.getSecondPoint());      
        }
        
        var s = 1;
        
        while(points.length < 2 && s <= limitSides.getSidesNumber()){    
            var intersectionPoint = limitSides.getSide(s).intersection(line);
            if (intersectionPoint != null) {
                points.push(intersectionPoint);
            }
            s++;
        }
                              
        if(points.length == 2){    
            jg.drawLine(points[0].x, points[0].y, points[1].x,points[1].y);                 
            jg.paint();      
        }
    },

    /**
     * GET THE RECTANGLE OF THE DRAWING AREA
     */
    getLimitSides: function(){

        var mapimgLayer     = _$('mapimgLayer');
        var mapimgLayerL    = objL(mapimgLayer);
        var mapimgLayerH    = objT(mapimgLayer);
        var mapW = mapimgLayer.style.width;
        var mapH = mapimgLayer.style.height;
        
        var xMin = mapimgLayerL;
        var xMax = mapimgLayerL + parseInt(mapW);
        var yMin = mapimgLayerH;
        var yMax = mapimgLayerH + parseInt(mapH);        
        
        var limitSides = new Polygon();
        
        limitSides.addPoint( new Point(xMin,yMin) );
        limitSides.addPoint( new Point(xMax,yMin) );
        limitSides.addPoint( new Point(xMax,yMax) );
        limitSides.addPoint( new Point(xMin,yMax) );
        limitSides.close();
        
        return limitSides;
    },

    /**
     * Remove all measure settings
     */
    resetMeasure: function() {
        // remove lines
        this.polyline.reset();
        jg.clear();    
        jg_tmp.clear();
        
        this.reloadData();
    },

    clearMeasure: function(){
        this.resetMeasure();
        this.geoPolyline.reset();
    },

    reloadData: function(){
        if (this.polyline.getSidesNumber() == 0) {
            // Reset form fields 
            $('#measureFormSum').val('');
            $('#measureFormSeg').val('');
            $("#mSegTxt").html(_p('Segment') + PM.measureUnits.distance);  
        } else if(this.polyline.isClosed()) {
            this.onDigitizedPolygon(this.polyline);
        } else {
            this.onDigitizedSide(this.polyline);
        }
    },

    reloadDrawing: function(){
        if (PM.Map.mode == 'measure') {
            this.resetMeasure();
            this.polyline = this.toPxPolygon(this.geoPolyline);
            if (this.polyline.getPointsNumber()>0) {
                this.drawPolyline(jg,this.polyline);
            }
            this.reloadData();
        }
    },

    delLastPoint: function(){
        var nPoints = this.polyline.getPointsNumber();
        //alert(nPoints);
        if (nPoints > 0) {
            this.polyline.delPoint(nPoints - 1);
            this.geoPolyline.delPoint(nPoints - 1);
            this.reloadDrawing();
        }
        //alert(this.polyline.getPointsNumber());
    }
});