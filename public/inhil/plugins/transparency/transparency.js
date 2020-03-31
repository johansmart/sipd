/*****************************************************************************
 *
 * Purpose: set transparency of groups/layers
 * Author:  Armin Burger
 *
 *****************************************************************************
 *
 * Copyright (c) 2003-2007 Armin Burger
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
    Transparency:
    {
        transpdlg_slider: null,
        dlgOptions: {width:200, height:100, left:250, top:250, resizeable:false, newsize:true, container:'pmDlgContainer', name:'transparency'},
        
        /**
         * Post the Transparency value to PHP GROUP object
         */
        setGroupTransparency: function(pos) {
            var groupname = $('#transpdlg_groupsel option:selected').val();
            if (typeof(groupname)=='undefined') {
                var groupname = $('#layerSliderCont').attr('name');
                var cmenu = 1;
            }
            if (groupname == '#') return false;
            if (cmenu) $('#layerSliderContTab').remove();
            
            var transparency = Math.round(pos  * 100);
            var url = PM_PLUGIN_LOCATION + '/transparency/x_set-transparency.php?' + SID + '&transparency=' + transparency + '&groupname=' + groupname;
            $.ajax({
                type: "POST",
                url: url,
                dataType: "json",
                success: function(response){
                    PM.groupTransparencies[groupname] = transparency;
                    if (response.reload && (PM.layerAutoRefresh == '1')) {
                        //showloading();
                        PM.Map.reloadMap(false);
                    }
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    if (window.console) console.log(errorThrown);
                }
            });
        },

        /**
         * Initialize transparency values of groups and create a transparency object for PMap
         */
        initGroupTransparencies: function() {
            var url = PM_PLUGIN_LOCATION + '/transparency/x_get-transparencies.php?' + SID;
            $.ajax({
                type: "POST",
                url: url,
                dataType: "json",
                success: function(response){
                    PM.groupTransparencies = response.transparencies;
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    if (window.console) console.log(errorThrown);
                }
            });
        },

        /**
         * Set slider to transparency value of group
         */
        setTransprarencySlider: function(pgrp) {
            var groupname = pgrp ? pgrp : $('#transpdlg_groupsel option:selected').val();
            if (groupname == '#') return false;
            this.transpdlg_slider.setPosition(PM.groupTransparencies[groupname]/100);
        },

        /**
         * Open the Transparency dialog
         */
        openTransparencyDlg: function() {
			var url = PM_PLUGIN_LOCATION + '/transparency/transparencydlg.phtml?'+SID;
			PM.Dlg.createDnRDlg(this.dlgOptions, _p('Layer transparency'), url);
        },

        /**
         * Create slider for transparency setting
         */
        createTransparencySlider: function(sliderDiv, w) {
            this.transpdlg_slider = new slider(
                sliderDiv,  // id of DIV where slider is inserted
                8,        //height of track
                w,       //width of track
                '#eeeeee', //colour of track
                1,         //thickness of track border
                '#000000', //colour of track border
                2,         //thickness of runner (in the middle of the track)
                '#666666', //colour of runner
                20,        //height of button
                10,        //width of button
                '#999999', //colour of button
                1,         //thickness of button border (shaded to give 3D effect)
                //'<img src="images/slider_updown.gif" style="display:block; margin:auto;" />', //text of button (if any)
                '', //text of button (if any)
                true,      //direction of travel (true = horizontal, false = vertical)
                false, //the name of the function to execute as the slider moves
                'PM.Plugin.Transparency.setGroupTransparency', //the name of the function to execute when the slider stops
                null          //the functions must have already been defined (or use null for none)
                );
        },

        cmOpenTranspDlg: function(gid) {
            $('#layerSliderContTab').remove();
            var dlgx = ($('#jqContextMenu').ileft() - 60) + 'px';
            var dlgy = ($('#jqContextMenu').itop() + 10) + 'px';
            var groupname = gid.replace(/ligrp_/, '');
            //alert(groupname);
            
            var cont = $('<div id="layerSliderContTab"><table><tr><td style="padding:6px 3px"><img src="images/menus/transparency-b.png"/></td><td><div id="layerSliderCont" name="'+groupname+'"></div></tr></table></div>')
                         .css({display:'inline',backgroundColor:'#fff',border:'1px solid #999', position:'absolute',zIndex:99, left:dlgx, top:dlgy, width:'140px', height:'auto'})
                         .dblclick( function () {$(this).remove() })
                         .appendTo('body')
                         .show();
            $().keydown(function (e) {if (e.which == 27) $('#layerSliderContTab').remove()});
            
            this.createTransparencySlider('layerSliderCont', 100);
            this.setTransprarencySlider(groupname);
        }
    }
});
