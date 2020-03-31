/******************************************************************************
 *
 * Purpose: let user select layers from a pool to add to or remove from TOC
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
    Layerselect:
    {
        root: null,
        layerselectDlgOptions: {width:260, height:400, left:200, top:150, resizeable:true, newsize:false, container:'pmDlgContainer'},
        
        /** 
         * Open dialog and launch tree update
         */
        openDlg: function() {
            var dlgwin = PM.Dlg.createDnRDlg(this.layerselectDlgOptions, _p('Select Layers'), false );
            $('#pmDlgContainer_MSG').append($('<span>' + _p('Available Layers') + '</span>'))
                                    .append(this.createTree())
                                    .treeview()
                                    .append($('<div>').append($('<input type="button" value="' + _p('Update') + '">').click(function() { PM.Plugin.Layerselect.updateToc() } )));
            
            PM.getSessionVar('categories', 'PM.Plugin.Layerselect.updateTree(response)');
        },
        
        /** 
         * Create and return tree structure
         * takes as input the categories pool defined for the plugin
         */
        createTree: function() {
            this.root = PM.ini.pluginsConfig.layerselect.categories.category;
            var tree = $('<ul>').addClass('pm-toctree');            
            $.each(this.root, function() {
                var catName = this.name;
                var catDescr = this.description ? _p(this.description) : _p(this.name);
                
                var cat = $('<li>').append($('<input type="checkbox" id="lscat_' + this.name + '" name="ls_catcbx"><span class="pm-toctree-cat">' + catDescr +'</span>'))
                                   .find('input').each(function() {
                                        $(this).click(function() {
                                            var checked = $(this).is(':checked');
                                            $(this).parent().find('li>input').each(function() {
                                                $(this).attr('checked', checked);
                                            });
                                        });
                                    })
                                  .end();
                var grpList = $('<ul>');
                
                var groupArray = $.isArray(this.group) ? this.group : [this.group];
                $.each(groupArray, function(key, value) {
                    try {
                        $('<li>').append($('<input type="checkbox" id="lsgrp_' + value + '" name="ls_grpcbx"><span class="pm-toctree-grp">' + PM.grouplist[value].description +'</span>'))
                                 .find('input').each(function() {
                                        $(this).click(function() {
                                            $(this).parent().parent().parent().find('input[name="ls_catcbx"]').each(function(){$(this).attr('checked', true)});
                                        });
                                  }).end()
                                 .appendTo(grpList);
                    } catch(e) {
                        console.log(e);
                        console.log('Group "' + value + '" is not defined correctly in <categories>. Check config XML!'); 
                    }
                });
                grpList.appendTo(cat);
                tree.append(cat);
            });
            
            return tree;
        },
        
        /** 
         * Update tree based on values returned from PHP $_SESSION['categories'] 
         */
        updateTree: function(response) {
            $.each(response, function(cat, params) {
                $('#lscat_' + cat).attr('checked', true);
                $.each(params.groups, function() {
                    $('#lsgrp_' + this).attr('checked', true);
                });
            });
        },
        
        /** 
         * Click event for button 
         *  updates TOC according to selected elements
         *  updates PHP $_SESSION['categories']
         */
        updateToc: function() {
            $('#layerform').find('input[name="groupscbx"]')
                           .each(function() {
                                var cbxId = $(this).id().replace(/ginput_/, '');
                                $(this).is(':checked') ? PM.defGroupList.addunique(cbxId) : PM.defGroupList.remove(cbxId);
                            });
            //alert(PM.defGroupList);
            
            var val = '{';
            $('#pmDlgContainer_MSG').find('input[name="ls_catcbx"]:checked').each(function(i) { 
                var sepc = i < 1 ? '' : ', ';
                val += sepc + '"' + $(this).id().replace(/lscat_/, '') + '":'; 
                
                var catDescr = $(this).parent().find('span.pm-toctree-cat').html();
                val += '{"description":"' + catDescr + '", "groups": [';
                
                $(this).parent().find('input[name="ls_grpcbx"]:checked').each(function(j) {
                    var sepg = j < 1 ? '' : ', ';
                    val += sepg + '"' + $(this).id().replace(/lsgrp_/, '') + '"';
                });
                val += ']}';
            });
            val += '}';
            
            //alert(val);
            PM.Map.forceRefreshToc = true;
            var sessionval = val.replace(/,\]/g, ']').replace(/,\}/, '}');
            PM.setSessionVar('categories', sessionval, 'PM.Toc.init("PM.Toc.setlayers(false, false)")');
        }

    }
});
