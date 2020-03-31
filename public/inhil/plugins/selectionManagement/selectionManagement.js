/******************************************************************************
 *
 * Purpose: selectionManagement
 * Author:  Vincent Mathis, SIRAP
 *
 ******************************************************************************
 *
 * Copyright (c) 2009 SIRAP
 *
 * This is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version. See the COPYING file.
 *
 * This software is distributed in the hope that it will be useful,
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
	SelectionManagement: 
	{
		dlgOptions: {width:400, height:330, left:250, top:250, resizeable:true, newsize:true, container:'pmSavSelectionContainer', name:_p('selectionManagement_savSelectionDlg')},
		selectMethode: 'new',
		useMenuDlg: false,
		
		/**
		 * Link: remove from selection
		 */
		obj_results_links: {},
		
		/**
		 * Init function
		 * 
		 * Only init the link(s)
		 */
		init: function() {
			var link = '<div class="selectionManagement_linkImg" ';
			link += 'alt="' + _p('selectionManagement_removeSelected__object') + '" ';
			link += 'title="' + _p('selectionManagement_removeSelected__object') + '" ';
			link += 'onclick="javascript:PM.Plugin.SelectionManagement.removeObjectFromSelection(\'--shpLayer--\',\'--shpIndex--\')"';
			link += '>&nbsp;</div>';
			this.obj_results_links = {"selectionManagement_removeSelected__header": link};
			
			if (typeof(PM.ini.pluginsConfig.selectionManagement) != 'undefined') {
				if (typeof(PM.ini.pluginsConfig.selectionManagement.useMenuDlg) != 'undefined') {
					if (PM.ini.pluginsConfig.selectionManagement.useMenuDlg == 1) {
						this.useMenuDlg = true;
					}
				}
			}
		},
		
		/**
		 * AJAX call to remove an object from selection (by layer and index)
		 */
		removeObjectFromSelection: function (layer, shapeIndex) {
			var url = PM_PLUGIN_LOCATION + "/selectionManagement/x_selectionManagement.php";
			var params = SID;
			params += '&operation=remove_selected';
			params += '&layerName=' + layer;
			params += '&objIndex=' + shapeIndex;
	
			$.ajax({
				url: url,
				dataType: "json",
				type: "POST",
				data: params,
				success: function(response) {
					// Select the objects
					PM.Query.writeQResult(response.queryResult, PM.infoWin);	
					// Si selection vide
					if (response.queryResult == 0) {
						PM.Map.clearInfo();
					}
				},
				error: function (XMLHttpRequest, textStatus, errorThrown) {
					if (window.console) {
						console.log(errorThrown);
					}
				}	
			});
		},
		
		/**
		 * Add header
		 */
		extendQueryLayersHeaders : function(queryLayers, tplName) {
	        if (queryLayers && tplName != 'iquery') {
		        for (var iLayer = 0 ; iLayer < queryLayers.length ; iLayer++) {
		        	var currentLayer = queryLayers[iLayer];
					// ajout de la nouvelle en-tête dans le tableau
	        		$.each(this.obj_results_links, function(name, value) {
		        		currentLayer.header.push(_p(name));
		        		currentLayer.stdheader.push(_p(name));
	        		});
		        }
	        }
		    return queryLayers;
		},

		/**
		 * Add link to each object
		 */
		extendQueryLayersValues: function(queryLayers, tplName) {
			if (queryLayers && tplName != 'iquery') {
		        for (var iLayer = 0 ; iLayer < queryLayers.length ; iLayer++) {
		        	var currentLayer = queryLayers[iLayer];
		        	
		        	var links = this.obj_results_links;
		        	$.each(currentLayer.values, function(iVal, val) {
		        		var objValues = val;
		        		$.each(links, function(name, link) {
		        			var newlink = link.replace(/--shpLayer--/g, val[0].shplink[0]);
		        			newlink = newlink.replace(/--shpIndex--/g, val[0].shplink[1]);
		        			val.push(newlink);
		        		});
		        	});
		        }
	        }
		    return queryLayers;
		},
		
		/**
		 * remove selection
		 */
		removeSelection: function() {
			var urlreq = PM_PLUGIN_LOCATION + "/selectionManagement/x_selectionManagement.php";
			var params = SID;
			params += "&operation=remove_selection";

			$.ajax({
				url: urlreq,
				dataType: "json",
				type: "POST",
				data: params,
				success: function(response) {
					PM.Map.clearInfo();
				},	
				error: function(response) {
					alert("Echec de la suppression");
				}
			});
			
			// hide selection 
			if ($('#pmQueryContainer').length > 0) {
	            $('#pmQueryContainer .jqmClose').click();
	        }
		},
		
		/**
		 * reload selection
		 */
		reloadSelection: function() {
			var urlreq = PM_PLUGIN_LOCATION + "/selectionManagement/x_selectionManagement.php";
			var params = SID;
			params += "&operation=reload_selection";

			$.ajax({
				url: urlreq,
				dataType: "json",
				type: "POST",
				data: params,
				success: function(response) {
					// select the objects
					PM.Query.writeQResult(response.queryResult, PM.infoWin);
				},		
				error: function(response) {
					alert(_p("selectionManagement_reloadError"));
				}
			});
		},
		
		/**
		 * Reload application
		 */
		reloadMap: function(remove) {
			var mapurl = PM_XAJAX_LOCATION + 'x_load.php?'+SID+'&zoom_type=zoompoint';
			if (remove) {
				PM.extentSelectedFeatures = null;
			}
			PM.Map.updateMap(mapurl);
		},
		
		addSelectionOperator: function(container, pluginName, bInline) {
			var pluginRealName = pluginName.replace('Plugin.','');
			
			var text = '<fieldset id="' + pluginRealName + '_selOperators" class="selOperators';
			if (bInline){
				text += ', selOperators_inline';
			}
			
			text += '" border=1">';
			
			if (!bInline){
				text += '<legend><b>' + _p('selectionManagement_selOperator_text') + '</b></legend>';
			}
			
			text += '<table><tr>';
			text += '<td id="' + pluginRealName + '_selOperator_new" class="selOperator_new_on" onclick="javascript:PM.' + pluginName + '.setSelection(\'new\')" alt="'+ _p('selectionManagement_selOperator_new_text')+ '" title="' + _p('selectionManagement_selOperator_new_text')+ '"></td>';
			text += '<td id="' + pluginRealName + '_selOperator_add" class="selOperator_add_on" onclick="javascript:PM.' + pluginName + '.setSelection(\'add\')" alt="'+ _p('selectionManagement_selOperator_add_text')+ '" title="' + _p('selectionManagement_selOperator_add_text')+ '"></td>';
			text += '<td id="' + pluginRealName + '_selOperator_intersec" class="selOperator_intersec_on" onclick="javascript:PM.' + pluginName + '.setSelection(\'intersec\')" alt="'+ _p('selectionManagement_selOperator_intersec_text')+ '" title="' + _p('selectionManagement_selOperator_intersec_text')+ '"></td>';
			text += '<td id="' + pluginRealName + '_selOperator_del" class="selOperator_del_on" onclick="javascript:PM.' + pluginName + '.setSelection(\'del\')" alt="'+ _p('selectionManagement_selOperator_del_text')+ '" title="' + _p('selectionManagement_selOperator_del_text')+ '"></td>';
			text += '</tr></table></fieldset>';
			
			 $(container).append(text);
		},
		
		setSelectionOperator: function (pluginName, type) {
			switch(type){
				case 'add': 
					$('#' + pluginName + '_selOperator_add').removeClass('selOperator_add_off').addClass('selOperator_add_on');
					$('#' + pluginName + '_selOperator_del').removeClass('selOperator_del_on').addClass('selOperator_del_off');
					$('#' + pluginName + '_selOperator_new').removeClass('selOperator_new_on').addClass('selOperator_new_off');
					$('#' + pluginName + '_selOperator_intersec').removeClass('selOperator_intersec_on').addClass('selOperator_intersec_off');
					break;
				case 'del': 
					$('#' + pluginName + '_selOperator_add').removeClass('selOperator_add_on').addClass('selOperator_add_off');
					$('#' + pluginName + '_selOperator_del').removeClass('selOperator_del_off').addClass('selOperator_del_on');
					$('#' + pluginName + '_selOperator_new').removeClass('selOperator_new_on').addClass('selOperator_new_off');
					$('#' + pluginName + '_selOperator_intersec').removeClass('selOperator_intersec_on').addClass('selOperator_intersec_off');
					break;
				case 'new':
					$('#' + pluginName + '_selOperator_add').removeClass('selOperator_add_on').addClass('selOperator_add_off');
					$('#' + pluginName + '_selOperator_del').removeClass('selOperator_del_on').addClass('selOperator_del_off');
					$('#' + pluginName + '_selOperator_new').removeClass('selOperator_new_off').addClass('selOperator_new_on');
					$('#' + pluginName + '_selOperator_intersec').removeClass('selOperator_intersec_on').addClass('selOperator_intersec_off');
					break;
				case 'intersec':
					$('#' + pluginName + '_selOperator_add').removeClass('selOperator_add_on').addClass('selOperator_add_off');
					$('#' + pluginName + '_selOperator_del').removeClass('selOperator_del_on').addClass('selOperator_del_off');
					$('#' + pluginName + '_selOperator_new').removeClass('selOperator_new_on').addClass('selOperator_new_off');
					$('#' + pluginName + '_selOperator_intersec').removeClass('selOperator_intersec_off').addClass('selOperator_intersec_on');
					break;
				default :
					$('#' + pluginName + '_selOperator_add').removeClass('selOperator_add_on').addClass('selOperator_add_off');
					$('#' + pluginName + '_selOperator_del').removeClass('selOperator_del_on').addClass('selOperator_del_off');
					$('#' + pluginName + '_selOperator_new').removeClass('selOperator_new_on').addClass('selOperator_new_off');
					$('#' + pluginName + '_selOperator_intersec').removeClass('selOperator_intersec_on').addClass('selOperator_intersec_off');
					break;
			}
		},
		
		setLayerSelection: function(itemId) {
			var bChecked;
			var itemIdInt = itemId + 1;
			// be careful: because of js compression bad algorithm, do not use ending parentesis followed by space in string
			var chkBox = $('#selectionManagement_selList tr:eq(' + itemIdInt + ')' + ' .selectionManagement_saveSelection_showLayer');
			var grpName = chkBox.val();
			
			bChecked = chkBox.attr('checked');
			$('#ginput_' + grpName).attr('checked', bChecked); 
			PM.Toc.setlayers(grpName, false);
		}
	}
});