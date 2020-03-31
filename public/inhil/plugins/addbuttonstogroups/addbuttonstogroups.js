/******************************************************************************
 *
 * Purpose: Additionnal buttons for each groups / layer in TOC plugin
 * Author:  Thomas Raffin, SIRAP
 *          Niccolo Rigacci <niccolo@rigacci.org>
 *
 ******************************************************************************
 *
 * Copyright (c) 2007 SIRAP
 *
 * This is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version. See the COPYING file.
 *
 * The software is distributed in the hope that it will be useful,
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
 * Init function called after TOC loading
 *
 * It will call "createLinkButtonToGroup" function for each couple groups - button to add
 */
function abtgAfterTocInit() {

	//alert('Executing abtgAfterTocInit()');

	// Get the array of buttons to add from an AJAX request.
	var url = PM_PLUGIN_LOCATION + '/addbuttonstogroups/x_addbuttonstogroups.php';
	var params = SID;
	$.ajax({
		url: url,
		data: params,
		type: 'POST',
		dataType: 'json',
		success: function(data, textStatus){
			//alert('AJAX success status: ' + textStatus + ' ' + data.abtgArray);
			addButtonsToGroups(data.abtgArray);
		},
		error: function(XMLHttpRequest, textStatus, errorThrown){
			alert('AJAX error status: ' + textStatus);
			alert('AJAX error: ' + errorThrown);
		}
	});
}

function addButtonsToGroups(abtgArray) {
	$('#toc .tocgrp').each(function() {
		var grpparent = $(this).parent();
		//alert('$(this).html() = ' + $(this).html());
		// Be carrefull : in IE and Firefox, split function return different arrays
		// if test is on the begining of string...
		var gnames = $(this).find('span[id^="spxg_"]').id().split(/spxg_/);
		if (gnames.length > 0) {
			var gname = gnames[gnames.length - 1];
			if (gname.length > 0) {
				//alert('gname.html() = ' + gname);
				//for (var iBtn = 0 ; iBtn < abtgArray.length ; iBtn++) {
				// Add buttons in reverse order, because we add it just "after" the div.
				for (var iBtn = (abtgArray.length - 1); iBtn >= 0; iBtn--) {
					var abtgBtn = abtgArray[iBtn];
					if (typeof(abtgBtn) != 'undefined') {
						//alert('Adding button ' + abtgBtn['prefix'] + ' to ' + gname);
						createLinkButtonToGroup(gname, abtgBtn['prefix'], abtgBtn['hrefjsfunction'], abtgBtn['titleandimgalttext'], abtgBtn['imgsrc']);
					}
				}
			}
		}
	});
}

/**
 * Create a link with an image as last element of the group in TOC
 */
function createLinkButtonToGroup(grpname, idprefix, jsfunction, titleandimgalttext, imgsrc) {
	var grpDivId = 'spxg_' + grpname;
	var grpDiv = $('#' + grpDivId);
	//alert('grpDiv.html() = ' + grpDiv.html());
	if (grpDiv.size() > 0) {
		var btnDivId = 'abtg_' + idprefix + '_' + grpname;
		var btnDiv = $('#' + btnDivId);
		if (btnDiv.size() == 0) {
			grpDiv.after('&nbsp;<a href="javascript:' + jsfunction + '(\'ligrp_' + grpname + '\')" title="' + titleandimgalttext + '"><img alt="' + titleandimgalttext + '" src="' + imgsrc + '" /></a>');
		}
	}
}
