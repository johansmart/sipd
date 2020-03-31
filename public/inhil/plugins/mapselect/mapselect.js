/******************************************************************************
 *
 * Purpose: let user select map file configs from predefined list
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

$.extend(PM.Plugin,
{
    Mapselect:
    {
        /**
         * Initialize select box and add to specified DOM element
         */
        init: function() {
            //var select =  this.settings.displayText + ' <select id="mapSelector" onchange="mapSelectChange()">';
            var select = $('<select>').id("mapselect_mapSelector")
                                      .css(this.settings.cssSelect)  
                                      .bind("change", function(e){PM.Plugin.Mapselect.mapSelectChange()});

            $numConfigs = 0;
            $.each(this.settings.configList, function(key, val) {
                var presel = key == PM.config ? 'selected="selected"' : '';
                select.append('<option value="' + key + '" ' + presel + '>' + val + '</option>');
            });
            
               
            $(this.settings.appendToDiv).append($('<div>').css(this.settings.cssDiv).append(this.settings.displayText).append(select));

            // hide mapselect if empty or only 1 element:
            if (typeof(this.settings.hideIfOnlyOneElement) != 'undefined' 
            && this.settings.hideIfOnlyOneElement 
            && $numConfigs <= 1) {
            	$('#mapselect_mapSelector').parent().hide();
            }
        },

        /**
         * OnChange function for select box; 
         * reloads application with selected config parameter
         */
        mapSelectChange: function() {
            var settings = PM.Plugin.Mapselect.settings;
            $("#mapselect_mapSelector option:selected").each(function () {
                var baseLoc = location.href.split(/\?/)[0];
                var searchLoc = location.search;
                var sessId = (!settings.resetSession) || (settings.resetSession != 'ALL') ? '&' + SID : '';
                var resetSession = settings.resetSession ? '&resetsession=' + settings.resetSession  : '';
                var configUrl = baseLoc;
                
                if (searchLoc.length > 0) {
                    if (searchLoc.match(/config=[a-zA-Z0-9\_\-]+/)) {
                        configUrl += searchLoc.replace(/config=[a-zA-Z0-9\_\-]+/,'config=' + $(this).val());
                    } else {
                        configUrl += searchLoc + '&config=' + $(this).val();
                    }
                    
                    if (!searchLoc.match(/PHPSESS/))  configUrl += sessId;
                    if (!searchLoc.match(/resetsession/)) configUrl += resetSession;
                } else {
                    configUrl += '?config=' + $(this).val() + sessId + resetSession;
                }
                
                //alert(configUrl);
                window.location = configUrl.replace(/&&/, '&');
            });
        }
    }
});
