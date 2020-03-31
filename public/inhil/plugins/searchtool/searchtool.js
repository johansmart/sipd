/******************************************************************************
 *
 * Purpose: SearchTool plugin
 * Author:  Thomas Raffin, SIRAP
 *
 ******************************************************************************
 *
 * Copyright (c) 2007 SIRAP
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
	SearchTool:
    {
		container: '#uiLayoutCenter',
		type: 'dynwin',
		style: 'block',
		dlgOptions: {width:220, height:180, left:300, top:100, resizeable:true, newsize:false, container:'pmSearchToolContainer', name:'Search'},
		
		init: function() {
			if (typeof(PM.ini.pluginsConfig.searchtool) != 'undefined') {
				if (typeof(PM.ini.pluginsConfig.searchtool.dlgOptions) != 'undefined') {
					$.extend(this.dlgOptions, PM.ini.pluginsConfig.searchtool.dlgOptions);
					this.dlgOptions.width = parseInt(this.dlgOptions.width);
					this.dlgOptions.height = parseInt(this.dlgOptions.height);
					this.dlgOptions.left = parseInt(this.dlgOptions.left);
					this.dlgOptions.top = parseInt(this.dlgOptions.top);
				}
				if (typeof(PM.ini.pluginsConfig.searchtool.type) != 'undefined') {
					this.container = PM.ini.pluginsConfig.searchtool.type;
				}
				if (typeof(PM.ini.pluginsConfig.searchtool.style) != 'undefined') {
					this.container = PM.ini.pluginsConfig.searchtool.style;
				}
				if (typeof(PM.ini.pluginsConfig.searchtool.container) != 'undefined') {
					this.container = PM.ini.pluginsConfig.searchtool.container;
				}
			}

			if (typeof(PM.Plugin.SelectionManagement) != 'undefined') {
				this.dlgOptions.height += 40;
			}
			$('#searchContainer').remove();
		},
		click: function() {
		    PM.Map.resetFrames();
		    this.getSearchForm();
		}, 
		getSearchForm: function() {
			$("#searchForm").remove();
			var url = PM_PLUGIN_LOCATION + '/searchtool/x_searchtool.php';
			var params = SID + '&style=' + PM.ini.pluginsConfig.searchtool.style;
			$.ajax({
		    	type: "POST",
		        url: url,
		        dataType: "html",
				data: params,
		        success: function(response){
					if (response.length > 0) {
						//$('#searchContainer').remove();
						if (PM.Plugin.SearchTool.type == 'dynwin') {
							PM.Dlg.createDnRDlg(PM.Plugin.SearchTool.dlgOptions, PM.Plugin.SearchTool.dlgOptions.name, false);
							var resContainer = '#' + PM.Plugin.SearchTool.dlgOptions.container + '_MSG';
							
							$(resContainer).append(response).show();
							$('#searchContainer').css('position', 'relative');
							
						} else {
							var searchContainer = $('#searchContainer');
							if (searchContainer.length) {
								searchContainer.replaceWith(response);
							} else {
								$(PM.Plugin.SearchTool.container).append(response).show();
							}
						}
						PM.Query.setSearchOptions();
					}
		        },
	            error: function (XMLHttpRequest, textStatus, errorThrown) {
	                if (window.console) console.log(errorThrown);
	            }
		    });
		},
		showSearchForm: function() {
//			 PM.Map.resetFrames();
			$('#searchContainer').remove();
			this.getSearchForm();
		}
	}
});

