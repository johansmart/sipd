
/******************************************************************************
 *
 * Purpose: functions related to jquery-ui-layout 
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

$.extend(PM.Layout,
{
    
    resizeTimer: null,
    
    resizeTimeoutThreshold: 1000,
        
    /**
     * Resize the map zone in dependency to the parent element
     * called by 'onresize' event of ui element containing the map
     */
    resizeMapZone: function() {
        var mapParent = $('#map').parent();
        PM.mapW = mapParent.width();
        PM.mapH = mapParent.height();    
        $('#map, #mapimgLayer, #mapImg').width(PM.mapW).height(PM.mapH); 
        var loadimg = $('#loadingimg');
        $('#loading').left(PM.mapW/2 - loadimg.width()/2).top(PM.mapH/2 - loadimg.height()/2 ).showv();
        var mapurl = PM_XAJAX_LOCATION + 'x_load.php?'+SID+ '&mapW=' + PM.mapW + '&mapH=' + PM.mapH + '&zoom_type=zoompoint';
        
        // avoid multiple resize events 
        clearTimeout(this.resizeTimer);
        this.resizeTimer = setTimeout("PM.Map.updateMap('" + mapurl + "', '')", this.resizeTimeoutThreshold);     
        
        PM.Init.updateSlider_s1(PM.mapW, PM.mapH) ;        
    }
    
});