
/******************************************************************************
 *
 * Purpose: Reload timing map for pmapper framework
 * Author:  Walter Lorenzetti, gis3w, lorenzetti@gis3w.it
 *
 ******************************************************************************
 *
 * Copyright (c) 2008 gis3w
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
 * along with p.mapper; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 ******************************************************************************/

$.extend(PM.Plugin,
{
    TimingMap:
    {
        displayLoadingTimingMap: false, //to show or not the 
        delayTimingMap: 10000,
        
        init: function() {
          $.extend(this,PM.ini.pluginsConfig.timingmap);
          var mapurl = PM_XAJAX_LOCATION + 'x_load.php?'+SID+'&zoom_type=zoompoint';
          PM.Map.updateMap(mapurl);
          setTimeout('PM.Plugin.TimingMap.init()',this.delayTimingMap);
        }
    }
});