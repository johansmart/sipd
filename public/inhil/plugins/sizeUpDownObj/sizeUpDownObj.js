/*****************************************************************************
 *
 * Purpose: increase or decrease object's size
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
	SizeUpDownObj:
    {
		cmSizeUpObj: function(gid) {
			PM.Plugin.SizeUpDownObj.setlayerSizeUpDown(gid.replace(/ligrp_/, ''), 1);
        },

		cmSizeDownObj: function(gid) {
			PM.Plugin.SizeUpDownObj.setlayerSizeUpDown(gid.replace(/ligrp_/, ''), -1);
		},

		cmResetSizeObj: function(gid) {
			PM.Plugin.SizeUpDownObj.setlayerSizeUpDown(gid.replace(/ligrp_/, ''), 'reset');
		},

		cmResetSizeAllObj: function(gid) {
			PM.Plugin.SizeUpDownObj.setlayerSizeUpDown(gid.replace(/ligrp_/, ''), 'clear');
		},

        setlayerSizeUpDown: function(layer, action) {
        	var url = PM_PLUGIN_LOCATION + '/sizeUpDownObj/x_setLayerSizeUpDownObj.php?';
        	var params = SID ;
        	params += '&layer=' + layer;
        	params += '&action=' + action;

        	$.ajax({
        		url: url,
        		data: params,
        		dataType: "json",
        		type: 'POST',
        		success: function(response) {
           			var coderet = response.coderet;
        			if (coderet != 0) {
        				alert(_p('sizeUpDownObj__ret_' + coderet));
        			} else {
        				PM.Map.reloadMap(false);
        			}
        		},
        		error: function (XMLHttpRequest, textStatus, errorThrown) {
                    if (window.console) console.log(errorThrown);
                }
        	});
        }
    }
});
