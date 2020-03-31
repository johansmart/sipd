/******************************************************************************
 *
 * Purpose: functions for query result export
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

 
/**
 * Export query results in various file formats
 */

$.extend(PM.Plugin, 
{
    Export:
    {
        /** 
         * run PHP export functions via AJAX 
         */ 
        exportQueryResult: function(format) {
            $('#exportLinkDL').hide();
            // PDF or (XLS + IE) --> open in new window 
            var target = (format == 'PDF' || (format == 'XLS' && $.browser.msie)) ? ' target="_blank"' : '';
            $.ajax({
                url: PM_PLUGIN_LOCATION + '/export/x_export.php?' + SID + '&format=Export' + format,
                dataType: "json",
                success: function(response){
                    $('#exportLinkDL').html('<a href="' + response.expFileLocation + '" ' + target + '>' + _p('Download')+ '</a>').show();
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    if (window.console) console.log(errorThrown);
                } 
            });  
        },

        /** 
         * Add controls to result display (called from pmjson.js) 
         */ 
        addToQueryResultHtml: function() {

            var pmExport = '';
            try {

                pmExport = [];
                var pluginConfig = PM.ini.pluginsConfig['export']; 
                if (typeof(pluginConfig.formats) != 'undefined') {
                    if (typeof(pluginConfig.formats) == 'object') {
                        pmExport = pluginConfig.formats;
                    } else {
                        pmExport = [pluginConfig.formats];
                    }
                }
            } catch(e) {
                pmExport = ['XLS', 'CSV', 'PDF'];
            }
            
            var html = "";
            if (pmExport.length > 0) {
                html += '<div id="selectexport">';
                html += '<div style="display:block; padding-bottom:4px">' + _p('Export result as') + '</div>';
            
                $.each(pmExport, function() {
                    html += '<div class="exportFormat"><input type="radio" name="exportformat" onclick="PM.Plugin.Export.exportQueryResult(' + '\'' + this + '\')" /><img src="plugins/export/images/' + this.toLowerCase() + '.gif" title="' + this + '" alt="' + this + '"/></div>';
                });
            
                html += '<div style="height:30px"><div id="exportLinkDL"></div></div>';
                html += '</div>';
            }
            
            return html;
        }
    }
});

$.merge(PM.Custom.queryResultAddList, ['PM.Plugin.Export.addToQueryResultHtml()']);



