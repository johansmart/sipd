/******************************************************************************
 *
 * Purpose: Add / update / remove dynamic layers to pmapper
 * Author:  Thomas Raffin, SIRAP
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

$.extend(PM.Map,
{
	ClientDynamicLayers:
	{
		addOrReplaceLayers: function(layers) {
			this.sendLayers(layers, 'addOrReplace');
		},
		
		removeLayers: function(layers) {
			this.sendLayers(layers, 'remove');
		},
		
		addOrReplaceAndRemoveOtherLayers: function(layers) {
			this.sendLayers(layers, 'replaceAll');
		},
		
		sendLayers: function(layers, action) {
			if (action == 'addOrReplace' || action == 'remove' || action == 'replaceAll') {
				var url = PM_PLUGIN_LOCATION + '/clientdynamiclayers/x_clientDynamicLayers.php';
				var params = SID + '&layers=' + layers + '&action=' + action;
				
				$.ajax({
					url:url,
					data: params,
					type: "POST",
					dataType: "json",
					success: function(response){
						var layerstring = PM.Toc.getLayers();
						
						var refreshToc = false;
						if (response.addedLayers.length > 0) {
							PM.defGroupList = $.merge(eval('["' + layerstring.replace(/,/g, '", "') + '"]'), response.addedLayers);
							$.each(response.addedLayers, function() {
								PM.grouplist[this] = {};
								PM.grouplist[this].name = this;
							});
							if (typeof(delete PM.groupTransparencies) != 'undefined') {
								delete PM.groupTransparencies;
							}
							refreshToc = true;
						}
						else 
							if (response.removedLayers.length > 0) {
								$.each(response.removedLayers, function() {
									delete PM.grouplist[this];
								});
								if (typeof(delete PM.groupTransparencies) != 'undefined') {
									delete PM.groupTransparencies;
								}
								refreshToc = true;
							}
						if (refreshToc) {
							PM.Toc.init();
						}
						
						// Update list in PHP session with new layerstring
						var oldlayerstring = layerstring;
						
						// remove "old" layers:
						$.each(response.removedLayers, function(){
							var search = '';
							search = new RegExp('^\s*' + this + '\s*$');
							layerstring = layerstring.replace(search, '');
							search = new RegExp('^\s*' + this + '\s*,');
							layerstring = layerstring.replace(search, '');
							search = new RegExp(',\s*' + this + '\s*$');
							layerstring = layerstring.replace(search, '');
							search = new RegExp(',\s*' + this + '\s*,');
							layerstring = layerstring.replace(search, ',');
						});
						
						// add new layers:
						$.each(response.activeLayers, function(){
							var found = false;
							var search = '';
							if (!found) {
								search = new RegExp('^\s*' + this + '\s*$');
								found = search.test(layerstring);
							}
							if (!found) {
								search = new RegExp('^\s*' + this + '\s*,');
								found = search.test(layerstring);
							}
							if (!found) {
								search = new RegExp(',\s*' + this + '\s*$');
								found = search.test(layerstring);
							}
							if (!found) {
								search = new RegExp(',\s*' + this + '\s*,');
								found = search.test(layerstring);
							}
							if (!found) {
								layerstring += ',' + this;
							}
						});
						
						// update selected layers:
						layerstring = '&groups=' + layerstring;
						PM.Map.updateSelLayers(PM_XAJAX_LOCATION + 'x_layer_update.php?' + SID + layerstring);
						
/*            
						// Add dyn default layers to PM.defGroupList array
			 			$.merge(PM.defGroupList, response.activeLayers);
*/
						// refresh map
						if (oldlayerstring != layerstring) {
							$("#loading").showv();
							setTimeout('PM.Map.reloadMap(false);', 2000);
						}
						else {
							PM.Map.reloadMap(false);
						}
//						PM.Map.reloadMap(false);
					},
		            error: function (XMLHttpRequest, textStatus, errorThrown) {
		                if (window.console) console.log(errorThrown);
		            }
				});
			}
		}
	}
});
