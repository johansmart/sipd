/******************************************************************************
 *
 * Purpose: functions for UI elements
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
 * UI elements
 * Configuration via js_config.php 
 */
;(function($){ 
    $.fn.extend({  
        /** Toolbar   */
        pmToolBar: function(tb) {
            var container  = $(this);          
            var defaults = {
                    orientation:'v',
                    css:{},
                    theme:'default',
                    imagetype:'gif'    
            };
            var options = $.extend(defaults, tb.options);
            
            var tbtab = $('<table />').addClass('pm-toolbar');
            var trh = (options.orientation == "v") ? false : $('<tr/>');
            
            $.each(tb.buttons, function() {
                var tool = this.tool;
                var run = this.run; 
                
                if (tool.match(/^space/i)) {
                    var tdb = $('<td/>').addClass('pm-tsepspace').css({height:this.dimension, width:this.dimension});
                } else if (tool.match(/^separator/i)) {
                    var tdb = $('<td/>').addClass('pm-tsep' + options.orientation).css({height:this.dimension, width:this.dimension});
                    if ($.browser.msie && parseInt($.browser.version) < 8) tdb.append($('<img />').src('images/blank.gif').attr('alt', 'separator'));
                } else {
                    var tdb = $('<td/>').id('tb_' + tool).addClass('pm-toolbar-td');
                    if (run) {
                        tdb.mousedown(function(){PM.TbDownUp(tool,'d');})
                           .mouseup(function(){PM.TbDownUp(tool,'u');})
                           .bind('click', function(e){eval(run + '()');});
                    } else {
                        tdb.mousedown(function(){PM.setTbTDButton(tool);})
                           .click(function(){PM.Map.domouseclick(tool);});
                    }
                    var toolTitle = _p(this.name);
                    
                    $('<img />').id('img_' + tool)
                                .src('images/buttons/'+ options.theme + '/' + tool + '_off.' + options.imagetype)
                                .attr('alt', toolTitle)
                                .attr('title', toolTitle)
                                .appendTo(tdb)
                    ;
                }
                if (trh) {
                    trh.append(tdb);
                } else {
                    $('<tr />').append(tdb).appendTo(tbtab);
                }
            });
            if (trh) trh.appendTo(tbtab);
            
            $("<div />").id(tb.toolbarid).addClass('pm-toolframe').css(options.css).append(tbtab).appendTo(container);
        },
        
        /** Tool links   */
        pmToolLinks: function(tl) {
            var container = $(this);     
            var ul = $('<ul/>').addClass('pm-tool-links');
            $.each(tl.links, function() {
                var linkName = _p(this.name); //;
                var target = this.target ? 'onclick="this.target = \'' + this.target + '\';"' : '';
                var a = '<a href="' + (this.run.substr(0,4) == 'http' ? this.run : 'javascript:' + this.run + '()') + '"' + target + '>';
                a += '<img style="background:transparent url(images/menus/' + this.imgsrc + ')' + ' no-repeat;height:16px;width:16px" src="images/transparent.png" alt="' + linkName +'" />';
                a += '<span>' + linkName + '</span></a>';
                $('<li/>').html(a).appendTo(ul);
            });
            
            $("<div />").id(tl.containerid).append(ul).appendTo(container);  
        },
        
        /** Tabs */ 
        pmTabs: function(tb) {
            var container = $(this);
            var options = tb.options;
            var ul = $('<ul/>').addClass(options.mainClass);
            var tabW = parseInt(100 / tb.tabs.length) -1 ;
            $.each(tb.tabs, function() {
                var tabName = _p(this.name); 
                var run = this.run; 
                var tab = $('<div>').html(tabName);
                if (this.active) tab.addClass('pm-tabs-selected');
                tab.bind('click', function() {  
                        tab.parent().parent().find('>li>div').each(function() {$(this).removeClass('pm-tabs-selected');});
                        tab.addClass('pm-tabs-selected');
                        eval(run + '()');
                });
                $('<li>').css({width:tabW+'%'}).append(tab).appendTo(ul);
            });
            ul.appendTo(container);
        },
        
        /** Append a new element to another */
        appendElement: function(el) {
            var dom = $('<'+el+'/>');
            $(this).append(dom); 
            return dom;
        },
        
        /** Indicator   */
        pmShowIndicator: function(ind) {
            var container = $(this);   
            var defaults = {
                    imgSrc:'images/indicator.gif',
                    css:{position:'absolute', top:'0px', left:'0px'}
            };
            var options = $.extend(defaults, ind.options);
            //console.log(ind.options);
            
            var img = $('<img>').src(options.imgSrc);
            $('<div>').addClass('pm-indicator').css(options.css).append(img).appendTo(container);
        },
        
        pmHideIndicator: function(ind) {
            var container = $(this);  
            var defaults = {
                    fadeOutSpeed: 500
            };
            var options = $.extend(defaults, ind.options);
            container.find('div.pm-indicator').each(function (i) {
                $(this).fadeOut(options.fadeOutSpeed, function () {
                    $(this).remove();
                });
            });
        }
            
    });
    
})(jQuery); 


$.extend(PM.UI,
{
    /**
     * Show div with link to current map
     */
    showMapLink: function() {
        function getLinkHref(response) {
            var urlPntStr = response.urlPntStr;
            var dg = PM.Toc.getLayers();
            var maxx_geo = PM.xdelta_geo + PM.minx_geo;
            var miny_geo = PM.maxy_geo - PM.ydelta_geo;
            var me = PM.minx_geo + ',' + miny_geo + ',' + maxx_geo + ',' + PM.maxy_geo;
            var confpar = PM.config.length > 0 ? '&config=' + PM.config : '';
            var urlPntStrPar = urlPntStr.length > 1 ? '&up=' + urlPntStr.replace(/\%5C\%27/g, '%27') : '';
            var loc = window.location;
            var reqList = loc.search.substr(1).split('&');
            var defReqStr = "";
            $.each(reqList, function(index, value) {
                if (value.search(/(dg|me|language|config|up)=/) < 0) {
                    defReqStr += '&' + value;
                }
            });
            //console.log(defReqStr);
            var port = loc.port > 0 ? ':' + loc.port : '';
            var linkhref = loc.protocol + '/' + '/' + loc.hostname + port + loc.pathname + '?dg=' + dg + '&me=' + me + '&language=' + PM.gLanguage + confpar + urlPntStrPar + defReqStr;
            return linkhref;
        };
        
        
        $.ajax({
            type: "POST",
            url: PM_XAJAX_LOCATION + 'x_maplink.php?'+ SID,
            dataType: "json",
            success: function(response){
                var linkhref = getLinkHref(response); 
                $('<div>').id('pmMapLink')
                          .addClass('pm-map-link')
                          //.append($('<div>').text(_p('Press [CTRL-C] to copy link')))
                          .append($('<div>').text(_p('Link to current map')))
                          .append($('<input type="text" class="pm-map-link-url" />').val(linkhref).click(function() {$(this).select();})) 
                          .append($('<img src="images/close.gif" alt="close" />').click(function () {$(this).parent().remove();}))
                          .append($('<br /><a href="' + linkhref + '">' + _p('Load link in current window') + '</a>').click(function() {$(this).parent().remove();}))
                          .appendTo('.ui-layout-center')
                          //.find('input').each(function() {this.select()})
                          .show();
                          
                PM.Map.bindOnMapRefresh(function(e){
                    $('#pmMapLink').remove();
                    //PM.UI.showMapLink();
                });
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                if (window.console) console.log(errorThrown);
            }
        });
    },

    /**
     * Create the measure input elements
     */
    createMeasureInput: function() {
        var mStr =  '<form id="measureForm"><div class="pm-measure-form"><table class="pm-toolframe"><tr><td NOWRAP>' + _p('Total') + PM.measureUnits.distance + '</td><td><input type=text size=9 id="measureFormSum"></td>';
        mStr += '<td id="mSegTxt" value="&nbsp;&nbsp;' + _p('Segment') + '" NOWRAP>&nbsp;&nbsp;' + _p('Segment') + PM.measureUnits.distance + '</td><td><input type=text size=9 id="measureFormSeg"></td>';
        mStr += '<td width=130 class="TDAR"><input type="button" id="cbut_measure" value="' + _p('Clear');
        mStr += '"  class="button_off"  name="custombutton" onClick="javascript:PM.Draw.clearMeasure()" >';
        mStr += '</td></tr></table></form>';
        
        $('#helpMessage').html(_p('digitize_help')).show();
        $('#mapToolArea').html(mStr).show();
        PM.Init.cButton('cbut_measure');    
    }
});




