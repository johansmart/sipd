/******************************************************************************
 *
 * Purpose: initialize various p.mapper settings 
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


$.extend(PM.Init,
{


     /**
     * Initialize function; called by 'onload' event of map.phtml
     * initializes several parameters by calling other JS function
     */
    main: function() {      
        // initialization of toolbar, menu, slider HOVER's (and others)
        this.toolbar();
        this.menu();
        this.slider();
        this.domElements();
        
        // Add properties to mapImg
        $("#mapImg").load(function(){PM.resetMapImgParams();}).mouseover(function(){PM.ZoomBox.startUp();});
        
        // Initialize TOC/legend
        this.tabs('#tocTabs', 'tab_toc');
        PM.Toc.init(false);

        createZSlider('zslider');
        PM.Query.setSearchOptions();
        PM.Map.domouseclick('zoomin');
        PM.setTbTDButton('zoomin');
        this.indicatorCont();
        
        // Add jQuery events
        $('#mapimgLayer').mouseout( function() { setTimeout('PM.Query.mapImgMouseOut()', 800); });  
        $('#refMapImg').mouseover( function() {PM.ZoomBox.startUpRef();} );
        
        // Enables actions for keyboard keys
        PM.ZoomBox.initKeyNavigation();
    },


    domElements: function() {
        $('<div>').id('mapToolArea').appendTo('.ui-layout-center');
    },

    /**
     * HOVER effect for slider
     * initialized in pm_init()
     */
    slider: function() {
        $('#sliderArea').hover(
            function(){ $(this).addClass("slider-area-over").removeClass("slider-area-out"); },
            function(){ $(this).addClass("slider-area-out").removeClass("slider-area-over"); }
        );
    },

    /**
     * DHTML jQuery menu
     * initialized in pm_init()
     */
    menu: function() {
        $('ul.pm-menu > li').each(function() {            
            $(this).hover(
                function() { $(this).addClass('pm-menu_hover'); },
                function() { $(this).removeClass('pm-menu-hover'); }
            );

            $(this).click(function() {
                this.menu_toggle($(this).parent().id());
                eval($(this).id().replace(/pmenu_/, '') + '()');
            });
        });
    },

    /**
     * Show/hide pm-menu
     */
    menu_toggle: function(menu)
    {
        var obj = $('#' + menu); 
        if (obj.css('display') == 'none') {
            obj.show('fast');
            $('#' + menu + '_start > img').src('images/menuup.gif');
        } else {
            obj.hide('fast');
            $('#' + menu + '_start > img').src('images/menudown.gif');
        }
    },

    /**
     * Initialize toolbar hover's
     */
    toolbar: function() {
        if (PM.tbImgSwap != 1) {
            $('td.pm-toolbar-td').each(function() {            
                $(this).hover(
                    function(){ if (! $(this).hasClass("pm-toolbar-td-on")) $(this).addClass("pm-toolbar-td-over"); },
                    function(){ $(this).removeClass("pm-toolbar-td-over"); }
                );
            });
        } else {
             $('td.pm-toolbar-td').each(function() {            
                $(this).hover(
                    function(){ if (!$(this).find('>img').src().match(/_on/)) $(this).find('>img').imgSwap('_off', '_over'); },
                    function(){ $(this).find('>img').imgSwap('_over', '_off'); }
                );
            });
        }
    },

    /**
     * Initialize buttons
     */
    cButton: function(but) {
        $("#" + but).hover(
            function(){ $(this).addClass("button_on").removeClass("button_off"); },
            function(){ $(this).addClass("button_off").removeClass("button_on"); }
        );
    },

    cButtonAll: function() {
        $("[name='custombutton']").each(function() {            
            $(this).hover(
                function(){ $(this).addClass("button_on").removeClass("button_off"); },
                function(){ $(this).addClass("button_off").removeClass("button_on"); }
            );
        });
    },


    /**
     * Initialize Tabs
     */
    tabs: function(tabdiv, activated) {   
        $(tabdiv + '>ul>li>a#'+activated).parent().addClass('tabs-selected');   
        var numTabs = $(tabdiv + '>ul>li').length;
        var tabW = parseInt(100 / numTabs) + '%';
        $(tabdiv + '>ul>li>a').each(function() {            
            $(this).click(function() {  
                $(tabdiv + '>ul>li').removeClass('tabs-selected');
                $(this).parent().addClass('tabs-selected');         
            });
            $(this).parent().css('width',tabW);
        });
    },

    /**
     * add div for wait indicator
     */
    indicatorCont: function() {
        $('body').append('<div id="pmIndicatorContainer" style="display:none; position:absolute; z-index:99"><img src="images/indicator.gif" alt="wait" /></div>');
    },


    /**
     * Initialize all context menus
     */
    contextMenus: function() {
        if (PM.contextMenuList) {
            $.each(PM.contextMenuList, function() {
                var cmdiv = '<div style="display:none" class="contextMenu" id="' + this.menuid + '">';
                var cmbindings = {};
                
                cmdiv += '<ul>';
                $.each(this.menulist, function() {
                    cmdiv += '<li id="' + this.id + '">';
                    var text = _p(this.text);
                    if (this.imgsrc) cmdiv += '<img src="images/menus/' + this.imgsrc + '" alt="' + text + '"/>';
                    cmdiv += text + '</li>';
                    
                    var run = this.run;
                    cmbindings[this.id] = function(t) {eval(run + '("' + t.id + '")');};
                });
                
                $('body').append(cmdiv);
                $(this.bindto).contextMenu(this.menuid, {
                    bindings: cmbindings, 
                    menuStyle: this.styles.menuStyle,
                    itemStyle: this.styles.itemStyle,
                    itemHoverStyle: this.styles.itemHoverStyle
                });
            });
        }
    },
    
    /**
     * Update s1 value for slider settings
     */
    updateSlider_s1: function(pixW, pixH) {
        var maxScale1 = ((PM.dgeo_x * PM.dgeo_c) / pixW) / (0.0254 / 96);
        var maxScale2 = ((PM.dgeo_y * PM.dgeo_c) / pixH) / (0.0254 / 96);
        PM.s1 = Math.max(maxScale1, maxScale2);
    }


});