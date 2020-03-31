/*****************************************************************************
 *
 * Purpose: Add unit and projection information in "showcoords" div
 * Author:  Christophe Arioli, SIRAP
 *
 *****************************************************************************
 *
 * Copyright (c) 2011 SIRAP
 *
 * This is free software; you can redistribute it and/or modify
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

$.extend(PM.Plugin,
{
	UnitAndProj :
	{
		init: function() {
			var url = PM_PLUGIN_LOCATION + '/unitAndProj/x_getUnitAndProj.php';
	    	var params = SID;
	    	if (PM.ZoomBox.coordsDisplayReproject && typeof(PM.ZoomBox.coordsDisplayDstPrj) != "undefined") {
	    		params += "&proj=" + PM.ZoomBox.coordsDisplayDstPrj;
	    	}
	    	$.ajax({
	    		url: url,
	    		data: params,
	    		dataType: "json",
	    		type: 'POST',
	    		success: function(response) {
	    			var unitStr = "";
	    			var projStr = _p('unitAndProj__proj') + " : ";

	    			if (response.projInfo != '') {
	    				projStr += response.projInfo;
	    			} else {
	    				projStr += _p('unitAndProj__projNotDef');
	    			}
	    			
	    			if (response.units != -1) {
	    				unitStr = " - " + _p('unitAndProj__units') + " : " + _p('unitAndProj__' + response.units);
	    			}

	    			var unitAndProjDiv = $('<div />').id('unitAndProj').html(projStr + unitStr);
	       			
	       			$('#unitAndProj').remove();
	       			$('#showcoords').append(unitAndProjDiv);
	    		},
	    		error: function (XMLHttpRequest, textStatus, errorThrown) {
	                if (window.console) console.log(errorThrown);
	            }
	    	});
		}
	}
});