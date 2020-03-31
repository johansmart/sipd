/******************************************************************************
 *
 * Purpose: functions for wmsclient
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
 
$.extend(PM.Plugin,
{
    WMSClient: {
        
        dlgOptions: {width:400, height:500, left:200, top:200, resizeable:true, newsize:true, container:'pmDlgContainer', name:"download"},
        
        openDlg: function() {
            PM.Dlg.createDnRDlg(this.dlgOptions, _p('Add WMS layers'), PM_PLUGIN_LOCATION + '/wmsclient/wmsdlg.phtml?'+SID);
        },

        init: function() {
            $('#wmsdlg_urlOk').bind("click", function(e){
                PM.Plugin.WMSClient.getCapabilities();
            });
            
            $('#wmsdlg_button_ok').bind("click", function(e){
                PM.Plugin.WMSClient.addWmsLayer();
            });
        },

        getCapabilities: function() {
            var wmsUrl = $('#wmsdlg_url').val();
            if (wmsUrl.length < 15) return false;
            $('#wmsdlg_loadingIndicator').show();

            $.ajax({
                url: PM_PLUGIN_LOCATION + '/wmsclient/x_wmsclient.php?',
                data: SID + '&wmsrequest=GetCapabilities&wmsurl=' + wmsUrl,
                dataType: "json",
                success: function(response){     
                    var layers = response.layers;
                    var imgFormats = response.imgFormats;
                    var srsList = response.srsList;
                    
                    var layerUL = $("<ul/>");
                    $(layers).each(function() {
                        $("<li/>").attr("id", 'wmsli_' + this.name || "").html('<span><input type="checkbox" id="wmslayer_' + this.name + '" "name="wmslayer" />' + this.title + "</span>").appendTo(layerUL);
                    });
                    $('#wmsdlg_layers').html(layerUL);
                    
                    $.each(imgFormats, function() {
                        $('<option />').val(this + '').text(this + '').appendTo('#wmsdlg_format');
                    });
                    
                    $.each(srsList, function(i, val) {
                        $('<option />').val(i + '').text(val + '').appendTo('#wmsdlg_srs');
                    });

                    $('#wmsdlg_loadingIndicator').hide();
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    //alert(_p('Error when retrieving capabilities from WMS server.'));
                    $('#wmsdlg_loadingIndicator').hide();
                }

            });   

        },
        
        addWmsLayer: function() {
            //var kvp = PM.Form.getFormKVP2('wmsdlg_form');
            //alert(kvp.wmslayer);
            
            alert($('#wmsdlg_form').serialize());


            
        }
    }

});


