/******************************************************************************
 *
 * Purpose: common js function for pmapper plugins
 * Author:  Thomas Raffin, SIRAP
 *
 ******************************************************************************
 *
 * Copyright (c) 2008 SIRAP
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

function openResultwin(winurl) {
    try {
        if (PM.queryResultLayout == 'tree') {
            var winw = 300;
            var winh = 450;
        } else {
            var winw = 500;
            var winh = 200;
        }
    } catch(e) {
        var winw = 500;
        var winh = 200;
    }
    
    var w = window.open(winurl, 'resultwin', 'width=' + winw + ',height=' + winh + ',status=yes,resizable=yes,scrollbars=yes');
    w.focus();
    return w;
}

// typewin can be the id of the element like "frame" (with "#")
function openAjaxQueryIn(typewin, dlgOptions, dlgTitle, url, params) {
	if (typewin == 'window') {
		if (url.indexOf('?') == -1) {
			url += '?';
		} else {
			url += '&';
		}
		url += 'addjsandcss=true';
		if (params) {
			url += '&' + params;
		}
		openResultwin(url);
	} else {
		if (url.indexOf('?') > 0) {
			params += (params ? '&' : '') + url.substr(url.indexOf('?') + 1);
		}
		
		PM.ajaxIndicatorShow(false, false);
		$.ajax({
		    url: url,
		    data: params,
	        type: 'POST',
		    dataType: 'html',
		    success: function(response) {
//				PM.ajaxIndicatorHide();
				var resContainer = '';
				if (typewin == 'dynwin') {
					if (!dlgOptions.width) {
						dlgOptions.width = 450;
					}
					if (!dlgOptions.height) {
						dlgOptions.height = 250;
					}
					if (!dlgOptions.container) {
						dlgOptions.container = 'pmDlgContainer';
					}
					PM.Dlg.createDnRDlg(dlgOptions, dlgTitle, false);
					resContainer = '#' + dlgOptions.container + '_MSG';
				} else {
					if (typewin == 'frame' && $('#infoFrame').length > 0) {
						resContainer = '#infoFrame';
					} else if (typewin[0] == '#' && $(typewin).length > 0) { 
						resContainer = typewin;
					}
				}
				$(resContainer).html(response);
			},
			error: function (XMLHttpRequest, textStatus, errorThrown) {
                if (window.console) console.log(errorThrown);
            },
			complete: function() {
				PM.ajaxIndicatorHide();
			}
		});
	} 
}

/**
 * Convert HEXA color to RGB 
 */
function convertHexToRGB(hexColor){
	var r, g, b;
	if (hexColor == '') {
		r = -1;
		g = -1;
		b = -1;
	} else {
	    r = HexToR(hexColor);
	    g = HexToG(hexColor);
	    b = HexToB(hexColor);
	}
	// Do not change the way to add "," and space because of js compression algorythm...
    var rgb = r.toString() + "," + " " + g.toString() + "," + " " + b.toString();

    return rgb;
}
function HexToR(h) {
	return parseInt((cutHex(h)).substring(0,2),16);
} 
function HexToG(h) {
	return parseInt((cutHex(h)).substring(2,4),16);
} 
function HexToB(h) {
	return parseInt((cutHex(h)).substring(4,6),16);
} 
function cutHex(h) {
	return (h.charAt(0)=="#") ? h.substring(1,7):h;
}  

/**
 * Convert RGB color to HEXA 
 */
function convertRgbToHex(num) {
	var decToHex="";
	var arr = [];
	var numStr = new String();
	numStr = num;

	arr = numStr.split(",");

	for(var i=0;i<3;i++){
		var hexArray = new Array( "0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "A", "B", "C", "D", "E", "F" );
		var code1 = Math.floor(arr[i] / 16);
		var code2 = arr[i] - code1 * 16;
		decToHex += hexArray[code1];
		decToHex += hexArray[code2];
	}
	return (decToHex);
}	

function generateColor(iClass, nbClass, hexColor1, hexColor2) {
	var r1 = HexToR(hexColor1);
    var g1 = HexToG(hexColor1);
    var b1 = HexToB(hexColor1);
	var r2 = HexToR(hexColor2);
    var g2 = HexToG(hexColor2);
    var b2 = HexToB(hexColor2);
	var nb = (nbClass > 1) ? nbClass - 1 : 1;
	var rOffset = Math.round((r2 - r1)/nb);
	var gOffset = Math.round((g2 - g1)/nb);
	var bOffset = Math.round((b2 - b1)/nb);
	var r = Math.max(Math.min(255, r1 + iClass * rOffset),0);
	var g = Math.max(Math.min(255, g1 + iClass * gOffset),0);
	var b = Math.max(Math.min(255, b1 + iClass * bOffset),0);

	var hexaColor = convertRgbToHex(r + ',' + g + ',' + b);
    
	return hexaColor;
}

/** function used to upper the first letter of a word
 * parameters: word  
 * @return: Word
 */
function upperWord(word) {
    var w = word.charAt(0).toUpperCase() + word.substring(1).toLowerCase();
    return w;
}

/**
 * 
 * get PM.Plugin[............] object
 * return null if error
 * 
 * @param strIn string (with "." separator).
 * 
 * For instance : "drawing" will return PM.Plugin.drawing object
 */
function getPMPluginObjFromString(strIn) {
	var retPluginObj = null;
	
	var retPluginObjTmp = PM.Plugin;
	var pluginNameCltArray = strIn.split(".");
	var ok = false;
	$.each(pluginNameCltArray, function(index, value) {
		var objTmp = retPluginObjTmp[value];
		if (typeof(objTmp) != "undefined") {
			retPluginObjTmp = objTmp;
			ok = true;
		} else {
			ok = false;
		}
		return ok;
	});
	if (ok) {
		retPluginObj = retPluginObjTmp;
	}
	
	return retPluginObj;
}