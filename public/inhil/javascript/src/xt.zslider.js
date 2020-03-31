/************************************************************
                    Slider control creator
              By Mark Wilton-Jones 12-13/10/2002
Version 1.1 updated 22/10/2003 to provide hand cursor option
*************************************************************

Please see http://www.howtocreate.co.uk/jslibs/ for details and a demo of this script
Please see http://www.howtocreate.co.uk/jslibs/termsOfUse.html for terms of use

___________________________________________________________________________________________*/


/* Modifications and new functions by Armin Burger for use with MapServer */

// ***************** START OF CODE BY Armin Burger ************************

/*
 * Functions for setting scale to form input and load map
 **************************************************************************/
var myslider;
var slScale;

function createZSlider(sliderElemId) {
    if (_$(sliderElemId)) {
        myslider = new slider(
        sliderElemId,  // id of DIV where slider is inserted
        140,        //height of track
        14,       //width of track
        '#666666', //colour of track
        1,         //thickness of track border
        '#000000', //colour of track border
        2,         //thickness of runner (in the middle of the track)
        '#666666', //colour of runner
        14,        //height of button
        20,        //width of button
        '#999999', //colour of button
        1,         //thickness of button border (shaded to give 3D effect)
        '<img src="images/slider_updown.gif" style="display:block; margin:auto;" alt="" />', //text of button (if any)
        //'', //text of button (if any)
        false,      //direction of travel (true = horizontal, false = vertical)
        'sliderMove', //the name of the function to execute as the slider moves
        'sliderStop', //the name of the function to execute when the slider stops
        true          //the functions must have already been defined (or use null for none)
        
        );
    }
}



/**
 * Set Scale by moving slider
 */
function sliderMove(sliderPosition) {
    currScale = PM.scale; //pMap_getMapScale(); 
    if (PM.zsliderVertical) sliderPosition = 1 - sliderPosition;
    slScale = sliderx2Scale(sliderPosition);
    var strlenSlScale = parseInt(slScale).toString().length;
    var redFact = Math.pow(10, strlenSlScale - 2);
    
    slScale = Math.round(slScale/redFact) * redFact;
    
    /* FEDE */
    _$("scaleform").scale.value = slScale;
    
    var scaleRatio = currScale / slScale;
    //window.status = scaleRatio;
        
    // Resize map image according to new scale
    // call resizeMap() from zoombox.js
    PM.ZoomBox.resizeMap(scaleRatio);

    // Resize refbox according to new scale
    // call resizeRefBox() from zoombox.js
    PM.ZoomBox.resizeRefBox(scaleRatio);
}

function sliderx2Scale(x) {
    var sliderScale = (1 - x) * PM.s1  + (x * PM.s2) - (x * (1 - x) * PM.s1)  ;  
    return sliderScale;
}

function sliderStop() {
    PM.Map.zoom2scale(slScale);
    mouseIsPressed = false;
    return false;
}


/**
 * Returns the slider position value (0 to 1) with regard to the new map scale
 * Contribution by Paul Hasenohr
 */
function getSliderPosition(curscale) {
    var s1 = PM.s1;
    var s2 = PM.s2;
    var eqPart = Math.sqrt((s2*s2) + (4*s1*curscale) - (4*s1*s2));
    
    var pos = ((2 * s1) - s2 + eqPart) / (2*s1) ;
    if (pos < 0 || pos > 1) {
        pos = ((2 * s1) - s2 - eqPart) / (2*s1) ;
    }
    if (pos > 1 || isNaN(pos)) pos = 1;
    
    if (PM.zsliderVertical) pos = 1-pos;
    
    return pos;
}




// ***************** START OF ORIGINAL CODE ************************


var mouseIsPressed = false;

var MWJ_slider_controls = 0;

function sliderMousePos(e) {
	//get the position of the mouse
	if( !e ) { e = window.event; } if( !e || ( typeof( e.pageX ) != 'number' && typeof( e.clientX ) != 'number' ) ) { return [0,0]; }
	if( typeof( e.pageX ) == 'number' ) { var xcoord = e.pageX; var ycoord = e.pageY; } else {
		var xcoord = e.clientX; var ycoord = e.clientY;
		if( !( ( window.navigator.userAgent.indexOf( 'Opera' ) + 1 ) || ( window.ScriptEngine && ScriptEngine().indexOf( 'InScript' ) + 1 ) || window.navigator.vendor == 'KDE' ) ) {
			if( document.documentElement && ( document.documentElement.scrollTop || document.documentElement.scrollLeft ) ) {
				xcoord += document.documentElement.scrollLeft; ycoord += document.documentElement.scrollTop;
			} else if( document.body && ( document.body.scrollTop || document.body.scrollLeft ) ) {
				xcoord += document.body.scrollLeft; ycoord += document.body.scrollTop; } } }
	return [xcoord,ycoord];
}

function slideIsDown(e) {
	//make note of starting positions and detect mouse movements
	window.msStartCoord = sliderMousePos(e); window.lyStartCoord = this.style?[parseInt(this.style.left),parseInt(this.style.top)]:[parseInt(this.left),parseInt(this.top)];
	if( document.captureEvents && Event.MOUSEMOVE ) { document.captureEvents(Event.MOUSEMOVE); document.captureEvents(Event.MOUSEUP); }
	window.storeMOUSEMOVE = document.onmousemove; window.storeMOUSEUP = document.onmouseup; window.storeLayer = this;
	mouseIsPressed = true;
    document.onmousemove = slideIsMove; document.onmouseup = slideIsMove; return false;
}

function slideIsMove(e) {
    if (mouseIsPressed) {
        //move the slider to its newest position
        var msMvCo = sliderMousePos(e); if( !e ) { e = window.event ? window.event : ( new Object() ); }
        var theLayer = window.storeLayer.style ? window.storeLayer.style : window.storeLayer; var oPix = document.childNodes ? 'px' : 0;
        if( window.storeLayer.hor ) {
            var theNewPos = window.lyStartCoord[0] + ( msMvCo[0] - window.msStartCoord[0] );
            if( theNewPos < 0 ) { theNewPos = 0; } if( theNewPos > window.storeLayer.maxLength ) { theNewPos = window.storeLayer.maxLength; }
            theLayer.left = theNewPos + oPix;
        } else {
            var theNewPos = window.lyStartCoord[1] + ( msMvCo[1] - window.msStartCoord[1] );
            if( theNewPos < 0 ) { theNewPos = 0; } if( theNewPos > window.storeLayer.maxLength ) { theNewPos = window.storeLayer.maxLength; }
            theLayer.top = theNewPos + oPix;
        }
        //run the user's functions and reset the mouse monitoring as before
        if( e.type && e.type.toLowerCase() == 'mousemove' ) {
            if( window.storeLayer.moveFunc ) { window.storeLayer.moveFunc(theNewPos/window.storeLayer.maxLength); }
        } else {
            document.onmousemove = storeMOUSEMOVE; document.onmouseup = window.storeMOUSEUP;
            if( window.storeLayer.stopFunc ) { window.storeLayer.stopFunc(theNewPos/window.storeLayer.maxLength); }
        }
    } 
}

function setSliderPosition(oPortion) {
	//set the slider's position
	if( isNaN( oPortion ) || oPortion < 0 ) { oPortion = 0; } if( oPortion > 1 ) { oPortion = 1; }
	var theDiv = document.getElementById(this.id); if (!theDiv) {return false}; if( theDiv.style ) { theDiv = theDiv.style; }
	oPortion = Math.round( oPortion * this.maxLength ); var oPix = document.childNodes ? 'px' : 0;
	if( this.align ) { theDiv.left = oPortion + oPix; } else { theDiv.top = oPortion + oPix; }
}

function slider(sliderElemId,oThght,oTwdth,oTcol,oTBthk,oTBcol,oTRthk,oTRcol,oBhght,oBwdth,oBcol,oBthk,oBtxt,oAlgn,oMf,oSf,oCrs) {
    //--- Modifications by Armin Burger ---//
    //draw the slider using huge amounts of nested layers (makes the borders look normal in as many browsers as possible)
    var sliderStr = (
        '<div style="position:relative;left:0px;top:0px;height:'+(oThght+(2*oTBthk))+'px;width:'+(oTwdth+(2*oTBthk))+'px;background-color:'+oTBcol+';font-size:0px;">'+
        '<div style="position:relative;left:'+oTBthk+'px;top:'+oTBthk+'px;height:'+oThght+'px;width:'+oTwdth+'px;background-color:'+oTcol+';font-size:0px;">'+
        '<div style="position:absolute;left:'+(oAlgn?0:Math.floor((oTwdth-oTRthk)/2))+'px;top:'+(oAlgn?Math.floor((oThght-oTRthk)/2):0)+'px;height:'+(oAlgn?oTRthk:oThght)+'px;width:'+(oAlgn?oTwdth:oTRthk)+'px;background-color:'+oTRcol+';font-size:0px;"><\/div>'+
        '<div style="position:absolute;left:'+(oAlgn?0:Math.floor((oTwdth-(oBwdth+(2*oBthk)))/2))+'px;top:'+(oAlgn?Math.floor((oThght-(oBhght+(2*oBthk)))/2):0)+'px;height:'+(oBhght+(2*oBthk))+'px;width:'+(oBwdth+(2*oBthk))+'px;font-size:0px;" ondragstart="return false;" onselectstart="return false;" onmouseover="this.hor='+oAlgn+';this.maxLength='+((oAlgn?oTwdth:oThght)-((oAlgn?oBwdth:oBhght)+(2*oBthk)))+';this.moveFunc='+oMf+';this.stopFunc='+oSf+';this.onmousedown=slideIsDown;" id="MWJ_slider_controls'+MWJ_slider_controls+'">'+
        '<div style="border-top:'+oBthk+'px solid #ffffff;border-left:'+oBthk+'px solid #ffffff;border-right:'+oBthk+'px solid #000000;border-bottom:'+oBthk+'px solid #000000;">'+
        '<div style="height:'+oBhght+'px;width:'+oBwdth+'px;font-size:0px;background-color:'+oBcol+';cursor:'+(oCrs?'pointer;cursor:move':'default')+';">'+
        '<span style="width:100%;text-align:center;">'+oBtxt+'<\/span><\/div><\/div><\/div><\/div><\/div>'
    );
    
    document.getElementById(sliderElemId).innerHTML = sliderStr;
    
    this.id = 'MWJ_slider_controls'+MWJ_slider_controls; this.maxLength = (oAlgn?oTwdth:oThght)-((oAlgn?oBwdth:oBhght)+(2*oBthk));
	this.align = oAlgn; this.setPosition = setSliderPosition; MWJ_slider_controls++;
}

