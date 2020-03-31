/******************************************************************************
 *
 * This file is part o digitizepoints, a plugin for p.mapper.
 * It allow to digitize points into a PostgreSQL/PostGIS table.
 * See http://www.pmapper.net/
 *
 * Copyright (C) 2009 Niccolo Rigacci
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Author:       Niccolo Rigacci <niccolo@rigacci.org>
 *               Based on examples by Armin Burger.
 *
 ******************************************************************************
 *
 * Integrate the plugin with mouse events and existing PM objects/classes.
 *
 ******************************************************************************/

$.extend(PM.Map,
{
    /**
     * Script for custom actions/modes
     * called from mapserver.js/zoombox_apply()
     * must be named '*_start(imgxy)'
     */
    digitizepoints_start: function(imgxy) {
        PM.Plugin.Digitizepoints.openCoordinatesDlg(imgxy);
        //alert(imgxy);
    },

    /**
     * Sript for extending tool functions
     * called from mapserver.js/domouseclick()
     * must be named '*_click()'
     */
    digitizepoints_click: function() {
        var varform = _$("varform");
            PM.Map.mode = 'digitizepoints';
            PM.Map.maction = 'click';
            PM.Map.tool = 'digitizepoints';

        // define the cursor
        if (PM.useCustomCursor) {
            PM.setCursor(false, 'crosshair');
        }
    }
});


$.extend(PM.Plugin,
{
    Digitizepoints:
    {
        // Dialog box options.
        dlgOptions: {
            width: 640,
            height: 480,
            left: 100,
            top: 50,
            resizeable: true,
            newsize: true,
            container: 'pmDlgContainer',
            name: 'DigitizePoint'
        },

        // How many decimal places in lat/lon.
        decimals: 5,

        // Timeout in ms for dialog self-close.
        dlgTimeout: 2000,

        /*
         * Init window settings
         */
        init: function() {
            // if config_XXX.xml contains paramters for this plugin, they
            // will be transmitted in PM.ini.pluginsConfig.digitizepoints.
            if (typeof(PM.ini.pluginsConfig.digitizepoints) != 'undefined') {
                if (typeof(PM.ini.pluginsConfig.digitizepoints.dlgOptions) != 'undefined') {
                    $.extend(this.dlgOptions, PM.ini.pluginsConfig.digitizepoints.dlgOptions);
                    this.dlgOptions.width = parseInt(this.dlgOptions.width);
                    this.dlgOptions.height = parseInt(this.dlgOptions.height);
                    this.dlgOptions.left = parseInt(this.dlgOptions.left);
                    this.dlgOptions.top = parseInt(this.dlgOptions.top);
                }
                if (typeof(PM.ini.pluginsConfig.digitizepoints.decimals) != 'undefined') {
                    this.decimals = parseInt(PM.ini.pluginsConfig.digitizepoints.decimals);
                }
                if (typeof(PM.ini.pluginsConfig.digitizepoints.dlgTimeout) != 'undefined') {
                    this.dlgTimeout = parseInt(PM.ini.pluginsConfig.digitizepoints.dlgTimeout);
                }
            }
        },

        /**
         * Close the pop-up window.
         */
        closeDlg: function() {
            if ($('#' + this.dlgOptions.container).length > 0) {
                $('#' + this.dlgOptions.container + ' .jqmClose').click();
            }
        },

        /**
         * What to do with mouse click pixel coordinates
         */
        openCoordinatesDlg: function(imgxy) {

            var pixccoords = imgxy.split('+');
            var mpoint = PM.ZoomBox.getGeoCoords(pixccoords[0], pixccoords[1], false);

            // Round values (function 'roundN()' in 'measure.js')
            var px = isNaN(mpoint.x) ? '' : PM.roundN(mpoint.x, PM.Plugin.Digitizepoints.decimals);
            var py = isNaN(mpoint.y) ? '' : PM.roundN(mpoint.y, PM.Plugin.Digitizepoints.decimals);

            var popupUrl = PM_PLUGIN_LOCATION + '/digitizepoints/digitizepoints.php?lon=' + px + '&lat=' + py + '&' + SID;
            var dlg = PM.Dlg.createDnRDlg(this.dlgOptions, _p('Digitize Point'), popupUrl);

        },

        pntSave: function() {
            var myUrl = PM_PLUGIN_LOCATION + '/digitizepoints/digitizepoints.php?';
            myUrl += PM.Form.getFormKVP('digitizepoints_form');
            myUrl += '&' + SID;
            $.ajax({
                url: myUrl,
                dataType: 'html',
                success: function(response) {
                    $('#pmDlgContainer_MSG').html(response);
                    if ($('#auto_close').length > 0) {
                        PM.Map.reloadMap();
                        setTimeout('PM.Plugin.Digitizepoints.closeDlg()', PM.Plugin.Digitizepoints.dlgTimeout);
                    }
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    if (window.console) console.log(errorThrown);
                }
            });
        },

        pntAddNew: function() {
            var myUrl = PM_PLUGIN_LOCATION + '/digitizepoints/digitizepoints.php?addnew=yes';
            myUrl += '&lon=' + $('#click_lon').val();
            myUrl += '&lat=' + $('#click_lat').val();
            myUrl += '&' + SID;
            $.ajax({
                url: myUrl,
                dataType: 'html',
                success: function(response) {
                    $('#pmDlgContainer_MSG').html(response);
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    if (window.console) console.log(errorThrown);
                }
            });
        },

        pntDelete: function() {
            var myUrl = PM_PLUGIN_LOCATION + '/digitizepoints/digitizepoints.php?__action=delete';
            myUrl += '&__id=' + $('#point_id').val();
            myUrl += '&' + SID;
            $.ajax({
                url: myUrl,
                dataType: 'html',
                success: function(response) {
                    $('#pmDlgContainer_MSG').html(response);
                    if ($('#auto_close').length > 0) {
                        PM.Map.reloadMap(false);
                        setTimeout('PM.Plugin.Digitizepoints.closeDlg()', PM.Plugin.Digitizepoints.dlgTimeout);
                    }
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    if (window.console) console.log(errorThrown);
                }
            });
        }

    }
});
