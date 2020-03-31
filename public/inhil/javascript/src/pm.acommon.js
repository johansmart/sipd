
/******************************************************************************
 *
 * Purpose: common JS util functions, setting defaults
 * Author:  Armin Burger
 *
 ******************************************************************************
 *
 * Copyright (c) 2003-2008 Armin Burger
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

/**
 * Locales function to get locale string
 */
function _p(str) {
    if (PM.Locales.list[str]) {
        return PM.Locales.list[str];
    } else {
        return str;
    }
}



/**
 * Global PMap object; 
 * stores main status variables set via incphp/js_init.php
 */
var PM = {
    scale: null,
    resize_timer: null,
    useCustomCursor: true,
    scaleSelectList: [100000, 250000, 500000, 1000000, 2500000, 5000000, 10000000, 25000000],
    enableRightMousePan: true,
    queryResultLayout: 'table',
    queryTreeStyle: {treeview: {collapsed: true, unique: true}},
    zsliderVertical: true,
    autoIdentifyFollowMouse: false,
    useInternalCursors: false,
    suggestLaunchSearch: true,
    measureUnits: {distance:" [km]", area:" [km&sup2,]", factor:1000},
    measureObjects: {line: {color:"#FF0000", width:2}}, 
    contextMenuList: false,
    exportFormatList: ['XLS', 'CSV', 'PDF'],
    scaleBarOptions: {divisions:2, subdivisions:2 ,resolution:96, minWidth:120, maxWidth:160, abbreviateLabel:true},
    categoriesClosed: [],
    tocTreeviewStyle: {collapsed:true, persist:false},
    minx_geo: null,
    maxy_geo: null,
    xdelta_geo: null,
    ydelta_geo: null,
    Custom: {
        queryResultAddList: []
    },
    Draw: {},
    Form: {},
    Init: {},
    Layout: {},
    Locales: {list:[]},
    Map: {
        mode: 'map',
        zoom_type: 'zoomrect',
        zoom_factor: 1,
        maction: 'box',
        tool: 'zoomin',
        forceRefreshToc: false,
        zoomJitter: 10,
        bindOnMapRefresh: function(bindData, bindFunction) {
			var data, fct;
			
			if ($.isFunction(bindData) ) {
				fct = bindData;
				data = null;
			} else {
				fct = bindFunction;
				data = bindData;
			}
            //$("#mapUpdateForm").bind("reset", bindData, bindFunction);
            $("#pm_mapUpdateEvent").bind("change", data, fct);
        }
    },
    Plugin: {},
    Query: {},
    Toc: {},
    UI: {},
    ZoomBox: {},
    Util: {}
};




/*  Prototype JavaScript framework, version 1.4.0
 *  (c) 2005 Sam Stephenson <sam@conio.net>
 *
 *  Prototype is freely distributable under the terms of an MIT-style license.
 *  For details, see the Prototype web site: http://prototype.conio.net/
/*--------------------------------------------------------------------------*/
function _$() {
    var elements = new Array();

    for (var i = 0; i < arguments.length; i++) {
        var element = arguments[i];
        if (typeof element == 'string')
            element = document.getElementById(element);

        if (arguments.length == 1)
            return element;

        elements.push(element);
    }

    return elements;
}


/**
 * Generic number functions
 */
Number.prototype.roundTo=function(precision){return parseFloat(parseFloat(this).toFixed(precision));};



/**
 * DOM generic functions
 */
function objL(obj) {	
    return parseInt(obj.style.left || obj.offsetLeft);
}

function objT(obj) {
    return parseInt(obj.style.top || obj.offsetTop);
}

function objW(obj) {
	return parseInt( obj.style.width || obj.clientWidth );
}

function objH(obj) {		
    return parseInt( obj.style.height || obj.clientHeight);    
}

function hideObj(obj) {
    obj.style.visibility = 'hidden';
}

function showObj(obj) {
    obj.style.visibility = 'visible';
}





