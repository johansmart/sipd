
/******************************************************************************
 *
 * Purpose: Geometry library for measurements and digitizing
 * Author:  Federico Nieri, Commune di Prato
 *
 ******************************************************************************
 *
 * Copyright (c) 2006 Federico Nieri
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
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 ******************************************************************************/



/**************************************/
/*************   POINT   **************/
/**************************************/

/**
 * Point constructor
 * @param x: x coordinate
 * @param y: y coordinate
 */
function Point(x,y){	
	this.x = parseFloat(x);
	this.y = parseFloat(y);
}

/**
 * Overriting of the standard toString Object method.
 * @param xySeparator: chars separating x coordinate from y coordinate of a point. Default " ".
 * @return a string that is the sequence of the point coordinates
 *
 * Example:
 * var p = new Point(1,2);
 * p.toString();    // return "1 2"
 * p.toString("|"); // return "1|2"
 */
Point.prototype.toString = function(xySeparator){
	xySeparator = !xySeparator ? " " : ("" + xySeparator);
	return (this.x + xySeparator + this.y);
}

/**
 * Return true if this point has the same coordinates of the passed point
 * @param otherPoint: Point object to compare
 */
Point.prototype.equals = function(otherPoint){
	return (this.x == otherPoint.x && this.y == otherPoint.y);
}

/**************************************/
/*************    LINE    *************/
/**************************************/

/**
 * Line constructor
 * @param firstPoint:  first Point objetc
 * @param secondPoint: second Point objetc
 */
function Line(firstPoint,secondPoint){

	this.firstPoint = firstPoint;
	this.secondPoint = secondPoint;	

	// y = a x + b;
	if(secondPoint.x == firstPoint.x){
	  // x = b;
    this.a = (secondPoint.y - firstPoint.y)<0 ? Number.NEGATIVE_INFINITE : Number.POSITIVE_INFINITE;
    this.b =  firstPoint.x;
    this.vertical = true;
  }else{
    this.a = (secondPoint.y - firstPoint.y)/(secondPoint.x-firstPoint.x);
    this.b = firstPoint.y - this.a * firstPoint.x;
    this.vertical = false;
  }  	
  
}

/**
 * Return true if this line has defining points with same coordinates of those
 * defining the passed line
 * @param otherPoint: Line object to compare
 */
Line.prototype.equals = function(otherLine){
	return (this.getFirstPoint().equals(otherLine.getFirstPoint()) && this.getSecondPoint().equals(otherLine.getSecondPoint()));
}

/**
 * Return the length of the line (distance from first point to second point)
 */
Line.prototype.getLength = function(){
	return Math.sqrt((Math.pow(this.secondPoint.x - this.firstPoint.x, 2)) + (Math.pow(this.secondPoint.y - this.firstPoint.y, 2)));
}

/**
 * Return the first Point object of the line
 */
Line.prototype.getFirstPoint = function(){
	return this.firstPoint;
}

/**
 * Return the second Point object of the line
 */
Line.prototype.getSecondPoint = function(){
	return this.secondPoint;
}

/*
 * Return true if the line is vertical
 */ 
Line.prototype.isVertical = function(){
  return this.vertical;
}

/*
 * Return true if the line is parallel to the line passed
 * @param otherLine: Line object to compare
 */ 
Line.prototype.isParallel = function(otherLine){
  return (otherLine.isVertical() && this.isVertical()) || (Math.abs(otherLine.a) == Math.abs(this.a)); 
}

/*
 * Return the Point object of intesection if found, null otherwise
 * @param otherLine: Line object to check for the intersection
 */
Line.prototype.intersection = function(otherLine){

  if(this.isParallel(otherLine)) return null;
  
  var xInt;
  var yInt;
  
  if(this.isVertical()){
    xInt = this.getFirstPoint().x;
    yInt = (otherLine.a * xInt) + otherLine.b;
  }else if(otherLine.isVertical()){
    xInt = otherLine.getFirstPoint().x;
    yInt = (this.a * xInt) + this.b;
  }else{
    xInt = (this.b - otherLine.b) / (otherLine.a - this.a);
    yInt = (this.a * xInt) + this.b;
  }
      
  if( ! (xInt >= Math.min(this.getFirstPoint().x,this.getSecondPoint().x) && xInt <= Math.max(this.getFirstPoint().x,this.getSecondPoint().x) && xInt >= Math.min(otherLine.getFirstPoint().x,otherLine.getSecondPoint().x) && xInt <= Math.max(otherLine.getFirstPoint().x,otherLine.getSecondPoint().x)))
    return null;
  
  if( ! (yInt >= Math.min(this.getFirstPoint().y,this.getSecondPoint().y) && yInt <= Math.max(this.getFirstPoint().y,this.getSecondPoint().y) && yInt >= Math.min(otherLine.getFirstPoint().y,otherLine.getSecondPoint().y) && yInt <= Math.max(otherLine.getFirstPoint().y,otherLine.getSecondPoint().y)))
    return null;
    
  return new Point(xInt,yInt);
}

/**
 * Overriting of the standard toString Object method.
 * @param xySeparator: chars separating x coordinate from y coordinate of a point. Default " ".
 * @param ptSeparator: chars separating first point coordinates from second point coordinates. Default ",".
 * @return a string that is the sequence of the first point coordinates and the second point coordinates
 *
 * Example:
 * var p1 = new Point(1,2);
 * var p2 = new Point(3,4);
 * var ln = new Line(p1,p2);
 * ln.toString();         // return "1 2,3 4"
 * ln.toString("|","-");  // return "1|2-3|4"
 * var s = "" + ln;       // s = "1 2,3 4"
 */
Line.prototype.toString = function(xySeparator, ptSeparator){

	if(!xySeparator) xySeparator=" ";
	if(!ptSeparator) ptSeparator=",";
	
	return (this.firstPoint.toString() + ptSeparator + this.secondPoint.toString());
}

/***************************************/
/******   POLYGON (POLYLINE)  **********/
/***************************************/

/**
 * Polygon constructor
 * @param points: array of Point objects (vertexes of polygon)
 */
function Polygon(points){
	this.setPoints(points);
}

/**
 * Return the area of the polyon. If the polyline is not closed or the number of
 * points is less than 4 (closed triangol) the returned value is 0.
 */
Polygon.prototype.getArea = function(){
	
	if(!this.isClosed()) return 0;
	
	var points = this.getPoints();
	
	if(points.length < 4) return 0;
		    
    var area = 0;
    for(var k=0; k < (points.length-1) ; k++) {        	
       area += (( points[k+1].x - points[k].x ) * ( points[k+1].y + points[k].y ));                     
    }
    area = area / 2;    
    return area;	
    
}

/**
 * Return the length of the polyline (perimeter of polygon).
 */
Polygon.prototype.getPerimeter = function(){
	
	var nSides = this.getSidesNumber();
	var perimeter = 0;
	
    for(var n = 1; n <= nSides ; n++) {        	
       perimeter += this.getSideLength(n);
    }
    
    return perimeter;	
}

/**
 * Return an array containing all the points of the polyline
 */
Polygon.prototype.getPoints = function(){
	var tmpPoints = new Array();
	for(var i = 0 ; i < this.points.length; i++){
		tmpPoints[i] = this.points[i];
	}
	return tmpPoints;
}

/**
 * Return the point specified. Indexes start from 0.
 * @param index: index of the point in the list
 */ 
Polygon.prototype.getPoint = function(index){
	return this.points[index];
}

/**
 * Set the array of points defining the polyline
 * @param points: array of Point objects
 */
Polygon.prototype.setPoints = function(points){
	if(points && points instanceof Array){
		this.points = points;
	}else{
		this.points = new Array();
	}
}

/**
 * Add a point a the end of the polyline
 * @param point: Point object
 */
Polygon.prototype.addPoint = function(point){
	this.points.push(point);
}

/**
 * Return the number of points of the polyline
 */
Polygon.prototype.getPointsNumber = function(){	
	return this.points.length;
}

/**
 * Return the number of sides
 */
Polygon.prototype.getSidesNumber = function(){	
  if(this.points.length == 0) return 0;
	return this.points.length-1;
}

/**
 * Return an array containing the list of the x coordinate of all points 
 */
Polygon.prototype.getXList = function(){	
	var xList = new Array();
	for(var i = 0 ; i < this.points.length; i++){
		xList[i] = this.points[i].x;
	}
	return xList;
}

/**
 * Return an array containing the list of the y coordinate of all points 
 */
Polygon.prototype.getYList = function(){	
	var yList = new Array();
	for(var i = 0 ; i < this.points.length; i++){
		yList[i] = this.points[i].y;
	}
	return yList;
}

/**
 * Delete the point specified by index
 * @param index: index of the point to delete
 */
Polygon.prototype.delPoint = function(index){
	this.points.splice(index,1);
}

/**
 * Close the polyline, to obtain a polygon, if this isn't closed yet.
 * A polyline is closed if the last point is equals to the first one.
 */
Polygon.prototype.close = function(){
	if(!this.isClosed()){
		this.addPoint(this.getPoint(0));
	}
}

/**
 * Return true if the last point is equals to the first one, 
 * false otherwise.
 */
Polygon.prototype.isClosed = function(){
	var points = this.getPoints();	
	return (points.length>2 && points[0].equals(points[points.length-1]));
}

/* resituisce la lunghezza del lato indicato.
   Il numero dei lati comincia dal lato 1 */
Polygon.prototype.getSideLength = function(sideNumber){
	return Math.sqrt((Math.pow(this.points[sideNumber].x - this.points[sideNumber-1].x, 2)) + (Math.pow(this.points[sideNumber].y - this.points[sideNumber-1].y, 2)));
}

/**
 * Return a Line object that is the last side of the polyline
 * Indexes start from 1
 * @param sideNumber: index of the sides
 */
Polygon.prototype.getSide = function(sideNumber){
	if(sideNumber==0) return null;
	if(sideNumber > this.getSidesNumber()) return null;
	
	return new Line(this.getPoint(sideNumber-1),this.getPoint(sideNumber));
}

/**
 * Return the last Line object of the polyline
 */
Polygon.prototype.getLastSide = function(){
	return this.getSide(this.getSidesNumber());
}

/**
 * Return the first Line object of the polyline
 */
Polygon.prototype.getFirstSide = function(){
	return this.getSide(1);
}

/**
 * Reset the array of points defining the polyline
 */
Polygon.prototype.reset = function(){
	this.points.length = 0;
}


/**
 * Overriting of the standard toString Object method.
 * @param xySeparator: chars separating x coordinate from y coordinate of a point. Default " ".
 * @param ptSeparator: chars separating points coordinates from one to another. Default ",".
 * @return a string that is the sequence of points coordinates of the polyline
 *
 * Example:
 * var p1 = new Point(1,2);
 * var p2 = new Point(3,4);
 * var p3 = new Point(5,6);
 * var points = new Array(p1,p2,p3);
 * var poly = new Polygon(points);
 *
 * poly.toString();       // return "1 2,3 4,5 6"
 * poly.toString("|","-");  // return "1|2-3|4-5|6"
 * var s = "" + poly;       // s = "1 2,3 4,5 6"
 */
Polygon.prototype.toString = function(xySeparator, ptSeparator){
	
	if(!xySeparator) xySeparator=" ";
	if(!ptSeparator) ptSeparator=",";
			
	var pointsString = "";
	var points = this.getPoints();
	
	for(var i = 0; i < points.length; i++){
		pointsString += points[i].toString(xySeparator);
		if(i < (points.length-1)){
			pointsString += ptSeparator;
		}
	}
	return pointsString;
}



/*
var p1 = new Point(1,1); // 1
var p2 = new Point(4,4); // 2
var p3 = new Point(3,6); // 1
var p4 = new Point(3,0); // 2
//var p5 = new Point(4,0); // 3

var l1 = new Line(p1,p2);
var l2 = new Line(p3,p4);
//var l3 = new Line(p4,p5);

//alert("l1 is vertical = " + l1.isVertical());
//alert("l2 is vertical = " + l2.isVertical());

//alert("l1 is parallel to l2 = " + l1.isParallel(l2));

alert("intersect = " + l1.intersection(l2));
*/
//var pol = new Polygon([p1,p2,p3]);
/*
alert(" p1 uguale a p2 ? "+p1.equals(p2));
alert(" p1 uguale a p3 ? "+p1.equals(p3));
alert(" l1 uguale a l2 ? "+l1.equals(l2));
alert(" l1 uguale a l3 ? "+l1.equals(l3));
alert(" l1.toString() = "+l1.toString());
alert(" pol.toString('_','|') = "+pol.toString("_","|"));
*/
/*
var points = new Array();

points[0] = p1;
points[1] = p2;
points[2] = p3;

var pol = new Polygon();

pol.addPoint(p1);
pol.addPoint(p2);
pol.addPoint(p3);

alert("1 - is closed : ("+pol+") " + pol.isClosed());

alert("1 - Area = " + pol.getArea());
pol.close();

alert("2 - is closed : ("+pol+") " + pol.isClosed());
alert("2 - Area  = " + pol.getArea());
alert("2 - Perim = " + pol.getPerimeter());

*/
