/******************************************************************************
 *
 * Purpose: sample plugin to demonstrate integration with mouse events 
 *          and existing PM objects/classes
 * Author:  Armin Burger
 *
 ******************************************************************************
 *
 * Copyright (c) 2003-2009 Armin Burger
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


$.extend(PM.Map,
{
    /**
     * Sample script for custom actions/modes
     * called from mapserver.js/zoombox_apply()
     * must be named '*_start(imgxy)'
     */
    coordinates_start: function(imgxy) {
        PM.Plugin.Coordinates.openCoordinatesDlg(imgxy);
        //alert(imgxy);
    },

    /**
     * custom sample script for extending tool functions
     * called from mapserver.js/domouseclick()
     * must be named '*_click()'
     */
    coordinates_click: function() {
        PM.Map.mode = 'coordinates';
        PM.Map.maction = 'click';
        PM.Map.tool = 'coordinates';
        
        // define the cursor
        if (PM.useCustomCursor) {
            PM.setCursor(false, 'crosshair');
        }
    }
});


$.extend(PM.Plugin,
{
    Coordinates: 
    {
        options: {css:{width:380}},
        
        /**
         * Custom function what to do with mouse click pixel coordinates
         */
        openCoordinatesDlg: function(imgxy) {
            var pixccoords = imgxy.split('+');
            var pixX = pixccoords[0];
            var pixY = pixccoords[1];
            
            var mpoint = PM.ZoomBox.getGeoCoords(pixX, pixY, false);
            
            $.ajax({
                url: PM_PLUGIN_LOCATION + '/coordinates/x_coords.php?' + SID + '&x=' + mpoint.x + '&y=' + mpoint.y,
                dataType: "json",
                success: function(response){
                    var res = response.prjJson;
                    var restab = $('<table>');
                    $.each(res, function(i, n){
                    	var xTxt = 'X: ';
                    	var yTxt = 'Y: ';
                    	if (n.prjName == "WGS84") {
                    		xTxt = 'lat: ';
                    		yTxt = 'long: ';
                    	}
                        restab.append(($('<tr>').append($('<td>').html(n.prjName))
                                                .append($('<td>').html(xTxt + n.x))
                                                .append($('<td>').html(yTxt + n.y)))); 
                    });
                    var container = $('#pmPluginCoordsDisplay').is(':visible') ? 
                        $('#pmPluginCoordsDisplay') : 
                        $('<div>').id('pmPluginCoordsDisplay')
                                  .addClass('pm-map-link')
                                  .addClass('pm-coordinates-dialog')
                                  .css(PM.Plugin.Coordinates.options.css)
                                  .appendTo('.ui-layout-center');
                                
                    container.html('')
                             .html(_p('Coordinates'))
                             .append(restab)
                             .append($('<img src="images/close.gif" alt="close" />').click(function () {$(this).parent().remove();}).css('cursor','pointer'))
                             .show();
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    alert('Missing entries for plugin config. See PHP error log');
                }
            });  
        }
    }
});