
/******************************************************************************
 *
 * Purpose: functions for formatting TOC and legend output
 * Author:  Armin Burger
 *
 ******************************************************************************
 *
 * Copyright (c) 2003-2011 Armin Burger
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



$.extend(PM.Toc,
{
    legendContainer: 'toclegend',
    
    updateHiddenLegend: false,
    
    /**
     * initialize and write TOC by calling XMLHttp function 'updateToc()' 
     * 
     */
    init: function(callfunction) {
        var legurl = PM_XAJAX_LOCATION + 'x_toc.php?'+SID;
        $.ajax({
            url: legurl,
            dataType: "html",
            success: function(response){   
                $('#toc').html(response);
                
                //if (response.grpStyle == 'tree') {
                if (PM.ini.ui.tocStyle == 'tree') {
                    // Open category tree
                    $('#toc').find('li.toccat').each(function() { 
                        if ($.inArray($(this).id().replace(/licat_/, ''), PM.categoriesClosed) < 0) $(this).addClass('open');
                    });
                    // Open group tree for defGroups
                    $.each(PM.defGroupList, function() { 
                        $('#ginput_' + this).check(); 
                        $('#ligrp_' + this).each(function() { $(this).addClass('open') });
                    });
                    $('#toc').treeview(PM.tocTreeviewStyle);
                } else {   
                    $('#toc').addClass('treeview treeview-blank');
                    $.each(PM.defGroupList, function() { 
                        $('#ginput_' + this).check(); 
                    });
                }
                    
                // Bind click function to groups and categories checkboxes
                $("#layerform :input[name='groupscbx']")
                    .click(function () { 
                        PM.Toc.setlayers(this.value,false); 
                    });
                $("#layerform :input[name='catscbx']")
                    .click(function () { 
                        PM.Toc.setcategories(this.value, false); 
                    })
                    .check();
                
                // run all scripts after init of toc
                PM.Toc.tocPostLoading();
                
                // check if there is a function to execute
                eval(callfunction);
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                if (window.console) console.log(errorThrown);
            }
        });
    },
    
    /**
     * Run scripts after TOC has been loaded
     */
    tocPostLoading: function() {
        // enable all context menus
        PM.Init.contextMenus();
        
        // execute all init scripts after TOC full loading
        for (var i=0; i<PM.pluginTocInit.length; i++) {
            eval(PM.pluginTocInit[i]);
        }
    },


    /**
     * Update toc applying different styles to visible/not-visible layers
     * called from 'updateMap()'
     */
    tocUpdateScale: function(tocurl) {
        $.ajax({
            url: tocurl,
            dataType: "json",
            success: function(response){
                var legendStyle = response.legendStyle;
                var layers = response.layers;
                
                $.each(layers, function(l, cl) {
                    $('#toc #spxg_' + l).each(function() {
                        $(this).removeClass('unvis vis').addClass(cl)
                               .parent().find('span').each(function() {
                                    $(this).removeClass('unvis vis').addClass(cl);
                        });
                    });
                });
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                if (window.console) console.log(errorThrown);
            }
        });  
    },
    
    /** Options for show/hide of legend indicator  */
    optionsLegendIndicator: {
        show:{css:{position:'absolute', top:'0px', right:'0px'}},
        hide:{fadeOutSpeed:300}
    },
    
    /**
     * Update legend 
     * called from 'updateTocScale()'
     */
    updateLegend: function(callfunction) {
        var legurl = PM_XAJAX_LOCATION + 'x_legend.php?' + SID;
        var legendContainer = $('#' + PM.Toc.legendContainer);
        legendContainer.parent().pmShowIndicator({options:this.optionsLegendIndicator.show});
        $.ajax({
            url: legurl,
            dataType: "html",
            success: function(response){
                legendContainer.html(response).addClass('treeview treeview-blank');
                // check if there is a function to execute
                eval(callfunction);
            },
            error: function(a,b,c) {
                if (window.console) console.log(c);
            },
            complete: function() {
            	legendContainer.parent().pmHideIndicator({options:PM.Toc.optionsLegendIndicator.hide});
            }
        });   
    },
    

    /**
     * for legendStyle 'swap': swap from LAYER view to LEGEND view
     * attached as onClick script to button
     */
    swapToLegendView: function() {
        this.updateLegend();
        $('#toc').hide();
        $('#' + this.legendContainer).show(); 

    },

    /**
     * for legendStyle 'swap': swap from LEGEND view to LAYER view
     * attached as onClick script to button
     */
    swapToLayerView: function() {
        $('#toclegend').hide();
        $('#toc').show();
        // update TOC CSS depending on scale
        var tocurl = PM_XAJAX_LOCATION + 'x_toc_update.php?' + SID;
        this.tocUpdateScale(tocurl);
    },

    /**
     * Change layers, called from
     * - onclick event of group checkbox)
     * - and setcategories()
     */
    setlayers: function(selelem, noreload) {
        // if request comes from group checkbox
        if (selelem) {
            // Check if layer is not visible at current scale
            if (($('#spxg_' + selelem).hasClass('unvis')) && (!noreload)) {
                noreload = true;
            }
            
            // Check if layers should be mutually disabled
            if (PM.mutualDisableList) {
                if ($.inArray(selelem, PM.mutualDisableList) > -1) {
                    $.each(PM.mutualDisableList, function() { 
                        if (this != selelem)  $('#ginput_' + this).attr('checked', false); 
                    });
                }
            }
        }
        
        var layerstring = '&groups=' + this.getLayers();    
        
        // reload whole map
        if ((PM.layerAutoRefresh == '1') && (!noreload)) {     
            var mapurl = PM_XAJAX_LOCATION + 'x_load.php?'+SID+'&zoom_type=zoompoint'+ layerstring;
            PM.Map.updateMap(mapurl);
        // just update 'groups' array of session, no map reload
        } else {
            var passurl = PM_XAJAX_LOCATION + 'x_layer_update.php?'+SID+layerstring;
            PM.Map.updateSelLayers(passurl);
        }
    },

    /**
     * Return layers/groups
     */
    getLayers: function() {
        var laystr = '';
        $("#layerform :input[name='groupscbx'][checked]").not(':disabled').each(function() { 
            laystr += $(this).val() + ','; 
        });
        laystr = laystr.substr(0, laystr.length - 1);
        return laystr;
    },

    /**
     * Set categories and child groups
     * (called from onclick event of categories checkbox)
     */
    setcategories: function(cat, noreload) {
        var checkedLayers = false;
        var visLayers = false;
        $('#licat_' + cat).find('input[name="groupscbx"]').each(function() {
            //(dis|en)able groups below category
            if ($('#cinput_' + cat).is(':checked')) {
                $(this).attr('disabled', false);
            } else {
                $(this).attr('disabled', true);
            }
            
            if ($(this).is(':checked')) {
                checkedLayers = true;
                if ($('#spxg_' + ($(this).id().replace(/ginput_/, ''))).hasClass('vis')) {
                    visLayers = true;
                }
            }
        });
        
        if (checkedLayers && visLayers) {
            this.setlayers(false, noreload);
        } else {    
            this.setlayers(false, true);
        }
    },

    /**
     * Functions to switch on/off all layers of a category
     * typically added to context menu of a category
     */
    catLayersSwitchOn: function(cat) {
        this.catLayersSwitch(cat, 'on');
    },

    catLayersSwitchOff: function(cat) {
        this.catLayersSwitch(cat, 'off');
    },

    catLayersSwitch: function(cat, action) {
        $('#' + cat).find('input[name="groupscbx"]').each( function() {
            $(this).check(action);
        });
        // set active layers and reload map
        this.setlayers(false, false);
    },
    
    
    toggleLegendContainer: function() {
        var layoutPane = $('#' + PM.Toc.legendContainer).parents('[pane]').attr('pane');
        myLayout.toggle(layoutPane);
    }
    
    
});