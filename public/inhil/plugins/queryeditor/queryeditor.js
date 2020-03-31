/******************************************************************************
 *
 * Purpose: Query Editor plugin
 * Author:  Thomas Raffin, SIRAP
 *
 ******************************************************************************
 *
 * Copyright (c) 2008 SIRAP
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
	QueryEditor:
    {
    	dlgOptions: {width:450, height:265, left:250, top:250, resizeable:true, newsize:true, container:'pmQueryEditorContainer', name:'QueryEditor'},
    	dlgType: 'dynwin',
    	selectMethode: '',

    	init: function() {
    		if (typeof(PM.ini.pluginsConfig.queryeditor.dlgType) != 'undefined') {
    			this.dlgType = PM.ini.pluginsConfig.queryeditor.dlgType;
    		}
    		if (typeof(PM.ini.pluginsConfig.queryeditor.dlgOptions) != 'undefined') {
    			$.extend(this.dlgOptions, PM.ini.pluginsConfig.queryeditor.dlgOptions);
    			this.dlgOptions.width = parseInt(this.dlgOptions.width);
    			this.dlgOptions.height = parseInt(this.dlgOptions.height);
    			this.dlgOptions.left = parseInt(this.dlgOptions.left);
    			this.dlgOptions.top = parseInt(this.dlgOptions.top);
    		}
    	},

    	openDlg: function() {
			var url = PM_PLUGIN_LOCATION + '/queryeditor/queryeditordlg.phtml';
			var params = SID;

//			openAjaxQueryIn(this.dlgType, this.dlgOptions, this.dlgOptions.name, url, params);
			
			if (this.dlgType == 'window') {
				url += '?addjsandcss=true';
				url += '&' + params;
				openResultwin(url);
			} else {
				PM.ajaxIndicatorShow(false, false);
				$.ajax({
				    url: url,
				    data: params,
				    type: 'POST',
				    dataType: 'html',
				    success: function(response) {
				    	var resContainer = '';
				    	
				    	if (PM.Plugin.QueryEditor.dlgType == 'dynwin') {
					    	PM.Dlg.createDnRDlg(PM.Plugin.QueryEditor.dlgOptions, PM.Plugin.QueryEditor.dlgOptions.name, false);
					    	resContainer = '#' + PM.Plugin.QueryEditor.dlgOptions.container + '_MSG';
				    	} else {
							if (PM.Plugin.QueryEditor.dlgType == 'frame' && $('#infoFrame').length > 0) {
								resContainer = '#infoFrame';
							} else if (PM.Plugin.QueryEditor.dlgType[0] == '#' && $(PM.Plugin.QueryEditor.dlgType).length > 0) { 
								resContainer = PM.Plugin.QueryEditor.dlgType;
							}
				    	}
				    	$(resContainer).html(response);

				    	var selectMethode = PM.Plugin.QueryEditor.selectMethode ? PM.Plugin.QueryEditor.selectMethode : 'new';
				    	if (typeof(PM.Plugin.SelectionManagement) != 'undefined') {
							PM.Plugin.SelectionManagement.addSelectionOperator('#queryeditor-main', 'Plugin.QueryEditor', false);
							PM.Plugin.QueryEditor.setSelection(selectMethode);
				    	}				
				    },
				    error: function (XMLHttpRequest, textStatus, errorThrown) {
				        if (window.console) console.log(errorThrown);
				    },
					complete: function() {
						PM.ajaxIndicatorHide();
					}
				});
			}
		},
	
		/**
		 * Name of the selected group / layer
		 */
		getLayerName: function() {
			var retVal = "";
			if ($("#queryeditor-layerName").length > 0) {
				var layerName = $("#queryeditor-layerName").val();
				if (layerName) {
					if ((layerName.length > 0) && (layerName != "#")) {
						retVal = layerName;
					}
				}
			}
			return retVal;
		},
	
		/**
		 * Apply the layer that use has chosen
		 * 
		 * If none selected, reset interface
		 * Ask for the available fields for this layer (AJAX request)
		 * Refresh interface
		 */
		setLayerName: function() {
			var layerName = this.getLayerName();
			$("#queryeditor-attributeName").html('');
			$('#queryeditor-attributeType').val('');
			this.setAttributeName();
			this.resetQuery();
			if (layerName.length > 0) {
				var url = qeDirUrl + 'x_queryeditor.php';
				var params = SID + '&operation=getattributes&layername=' + layerName;
				PM.ajaxIndicatorShow(false, false);
				$.ajax({
					url: url,
					data: params,
					dataType: "json",
					success: function(response) {
						if ($('#queryeditor-attributeName').length) {
							var options = '<option value="#"></option>\n';
							var attributes = response.attributes;
							$.each(attributes, function() {
								if (this['field'] && this['header']) {
									options += '<option value=\"' + this['field'] + '\" label=\"' + this['header'] + '\">' + this['header'] + '</option>\n';
								}
							});
							$("#queryeditor-attributeName").html(options);
							$("#queryeditor-attributeName").val("");
						}
//						PM.ajaxIndicatorHide();
					},
	                error: function (XMLHttpRequest, textStatus, errorThrown) {
	                    if (window.console) console.log(errorThrown);
	                },
					complete: function() {
						PM.ajaxIndicatorHide();
					}
				});
			}
		},
	
		/**
		 * Real name of the selected field
		 */
		getAttributeRealName: function() {
			var retVal = "";
			if ($("#queryeditor-attributeName").length > 0) {
				var indicatorRealName = $("#queryeditor-attributeName").val();
				if (indicatorRealName) {
					if ((indicatorRealName.length > 0) && (indicatorRealName != "#")) {
						retVal = indicatorRealName;
					}
				}
			}
			return retVal;
		},
	
		/**
		 * Readable name of the selected field (=header)
		 */
		getAttributeReadName: function() {
			var retVal = "";
			var elemTmp = document.getElementById("queryeditor-attributeName");
			if (typeof(elemTmp) != 'undefined' && elemTmp) {
				if (elemTmp.selectedIndex > 0) {
					var indicatorReadName = elemTmp.options[elemTmp.selectedIndex].text;
					if (typeof(indicatorReadName) != 'undefined') {
						retVal = indicatorReadName;
					}
				}
			}
			return retVal;
		},
	
		/**
		 * Apply the chosen field
		 *
		 * Refresh interface and call setAttributeType
		 */
		setAttributeName: function() {
			var attrRealName = this.getAttributeReadName();
			$('#queryeditor-attributeType').val('');
			$('#queryeditor-attributeType').attr('disabled','disabled');
			this.setAttributeType();
	
			if (attrRealName) {
				if (attrRealName.length > 0) {
					$('#queryeditor-attributeType').removeAttr('disabled');
				}
			}
		},
	
		/**
		 * Apply the field type
		 *
		 * Refresh interface
		 */
		setAttributeType: function() {
			$('#queryeditor-attributeValue').val('');
			$('#queryeditor-attributeValue').attr('disabled','disabled');
			$('.queryeditor-attributeCriteriaComparison').hide();
			$('#queryeditor-attributeCriteriaComparisonNone').parent().show();
			
			this.applyAttributeType();
		},

		/**
		 * Get the field type by querying DB
		 */
		applyAttributeType: function() {
			var attributeName = this.getAttributeRealName();
			var layerName = this.getLayerName();
			var attrType = '#';

			if (layerName.length > 0 && attributeName.length > 0) {

				var url = qeDirUrl + 'x_queryeditor.php';
				var params = SID + '&operation=getattributetype&layername=' + layerName + '&attributename=' + attributeName;
				PM.ajaxIndicatorShow(false, false);
				$.ajax({
					url: url,
					data: params,
					dataType: "json",
					success: function(response) {
						if ($('#queryeditor-attributeType').length) {
							if (response.attributeType) {
								attrType = response.attributeType;
							} else {
								attrType =$('#queryeditor-attributeType').val();
							}
							if (attrType) {
								$('#queryeditor-attributeCriteriaComparisonNone').parent().hide();
								if (attrType == 'N') {
									$('#queryeditor-attributeCriteriaComparisonNum').parent().show();
									$('#queryeditor-attributeValue').removeAttr('disabled');
									$('#queryeditor-attributeType').val(attrType);
								} else if (attrType == 'S') {
									$('#queryeditor-attributeCriteriaComparisonTxt').parent().show();
									$('#queryeditor-attributeValue').removeAttr('disabled');
									$('#queryeditor-attributeType').val(attrType);
								} else {
									$('#queryeditor-attributeCriteriaComparisonNone').parent().show();
								}
							}

						}
					},
	                error: function (XMLHttpRequest, textStatus, errorThrown) {
	                    if (window.console) console.log(errorThrown);
	                },
					complete: function() {
						PM.ajaxIndicatorHide();
					}
				});
			}
		},

		/**
		 * OnKeyPress event
		 * 
		 * Avoid default ENTER key behaviour:
		 * - if ENTER is press, then apply the attribute value (= call setAttributeValue).
		 * - if an other key is press, the onkeyup will call changeAttributeValue.
		 */
		attributeValueKeyPress: function(e) {
			var key;
			// IE :
			if (window.event) {
				key = window.event.keyCode;
			} else { // Firefox
				key = e.which;
			}
	
			// ENTER key :
			if (key == 13) {
				setAttributeValue();
				return false;
			} else {
				return true;
			}
		},
	
		/**
		 * OnKypUp event
		 *
		 * Enable on disable the "add" button
		 */
		changeAttributeValue: function() {
			var attrval = $('#queryeditor-attributeValue').val();
			var btnEnable = false;
			if (attrval) {
				if (attrval.length > 0) {
					btnEnable = true;
				}
			}
			btnEnable ? $('#queryeditor-attributeBtnAdd').removeAttr('disabled') : $('#queryeditor-attributeBtnAdd').attr('disabled','disabled') ;
		},
	
		/**
		 * Apply the attribute value
		 *
		 * Generate the new query part (depending on the field type and comparison operator)
		 * Add the query part in the textarea
		 * Reset the attribute name and then refresh the interface by calling setAttributeName
		 */
		setAttributeValue: function() {
			var bContinue = true;
			var queryPartToAdd = '';
	
			if (bContinue) {
				bContinue = false;
				var attrName = this.getAttributeReadName();
				if (attrName) {
					if (attrName.length > 0) {
						queryPartToAdd += '[' + attrName + ']';
						bContinue = false;
						var attrVal = $('#queryeditor-attributeValue').val();
						if (attrVal) {
							var attrType = $('#queryeditor-attributeType').val();
							if (attrType) {
								bContinue = true;
								if (attrType == 'N') {
									var attrOperator = $('#queryeditor-attributeCriteriaComparisonNum').val();
									if (attrOperator) {
										if (attrOperator == 'equal') {
											queryPartToAdd += ' = ' + attrVal;
										} else if (attrOperator == 'inferiororequal') {
											queryPartToAdd += ' <= ' + attrVal;
										} else if (attrOperator == 'superiororequal') {
											queryPartToAdd += ' >= ' + attrVal;
										} else if (attrOperator == 'strictlyinferior') {
											queryPartToAdd += ' < ' + attrVal;
										} else if (attrOperator == 'strictlysuperior') {
											queryPartToAdd += ' > ' + attrVal;
										} else if (attrOperator == 'different') {
											queryPartToAdd += ' <> ' + attrVal;
										} else {
											bContinue = false;
										}
									}
								} else if (attrType == 'S') {
									var attrOperator = $('#queryeditor-attributeCriteriaComparisonTxt').val();
									if (attrOperator) {
										var caseSensitiveOperator = $('#queryeditor-attributeCriteriaComparisonTxtCS').attr('checked') ? ' LIKE ' : ' ILIKE ';
										queryPartToAdd += caseSensitiveOperator;
										attrVal = attrVal.replace(/'/, "\\'");
										if (attrOperator == 'equal') {
											queryPartToAdd += "'" + attrVal + "'";
										} else if (attrOperator == 'different') {
											queryPartToAdd = 'NOT ' + queryPartToAdd;
											queryPartToAdd += "'" + attrVal + "'";
										} else if (attrOperator == 'contain') {
											queryPartToAdd += "'%" + attrVal + "%'";
										} else if (attrOperator == 'notcontain') {
											queryPartToAdd = 'NOT ' + queryPartToAdd;
											queryPartToAdd += "'%" + attrVal + "%'";
										} else if (attrOperator == 'startwith') {
											queryPartToAdd += "'" + attrVal + "%'";
										} else if (attrOperator == 'endwith') {
											queryPartToAdd += "'%" + attrVal + "'";
										} else {
											bContinue = false;
										}
									}
								} else {
									bContinue = false;
								}
							}
						}
					}
				}
			}
	
			if (bContinue) {
				this.addToQuery(queryPartToAdd);
				$('#queryeditor-attributeBtnAdd').attr('disabled','disabled');
				$('#queryeditor-attributeName').val('');
				this.setAttributeName();
			}
		},
	
		/**
		 * Apply operator choice: add it to query
		 */
		setOperator: function(id) {
			var op = '';
			switch (id) {
				case 'queryeditor-operatorBtnOpenBracket':
					op = '(';
					break;
				case 'queryeditor-operatorBtnCloseBracket':
					op = ')';
					break;
				case 'queryeditor-operatorBtnNot':
					op = 'NOT';
					break;
				case 'queryeditor-operatorBtnAnd':
					op = 'AND';
					break;
				case 'queryeditor-operatorBtnOr':
					op = 'OR';
					break;
				default:
					break;
			}
			this.addToQuery(op);
		},
	
		/**
		 * Add text to the current query
		 */
		addToQuery: function(queryPartToAdd) {
			var currentQuery = $('#queryeditor-generatedQuery').val();
			if (currentQuery) {
				if (currentQuery.length > 0 ){
					queryPartToAdd = currentQuery + '\n' + queryPartToAdd;
				}
			}
			this.updateQuery(queryPartToAdd);
		},
	
		/**
		 * Delete the urrent query
		 */
		resetQuery: function() {
			this.updateQuery('');
		},
	
		/**
		 * Update query
		 *
		 * Change the query with the parameter value
		 * Refresh interface by calling queryHasBeenUpdated
		 */
		updateQuery: function(query) {
			$('#queryeditor-generatedQuery').val(query);
			this.queryHasBeenUpdated();
		},
	
		/**
		 * Refresh interface depending on the current query content
		 */
		queryHasBeenUpdated: function() {
			$('#queryeditor-btnReset').attr('disabled','disabled');
			$('#queryeditor-btnApply').attr('disabled','disabled');
			$('#queryeditor-operatorGroup2 input').attr('disabled','disabled');
	
			var currentQuery = $('#queryeditor-generatedQuery').val();
			if (currentQuery) {
				if (currentQuery.length > 0) {
					$('#queryeditor-btnReset').removeAttr('disabled');
					$('#queryeditor-btnApply').removeAttr('disabled');
					$('#queryeditor-operatorGroup2 input').removeAttr('disabled');
				}
			}
		},
	
		/**
		 * Reset interface
		 */
		reset: function() {
			$('#queryeditor-LayerName').val('');
			this.setLayerName();
			this.resetQuery();
		},
	
		/**
		 * Cancel (close the query window)
		 */
		cancel: function() {
			if ($('#' + this.dlgOptions.container).length > 0) {
				$('#' + this.dlgOptions.container + ' .jqmClose').click();
			}
		},
	
		/**
		 * Execute the current query
		 *
		 * Use standard getQueryResult function to show result, select and zoom to selected...
		 */
		apply: function() {
			var layerName = this.getLayerName();
			if (layerName.length > 0) {
				var query = $('#queryeditor-generatedQuery').val();
				query = query.replace('%','%25');
				if (query) {
					if (query.length > 0) {
						var url = qeDirUrl + 'x_queryeditor.php';
						var params = SID + '&operation=query&layername=' + layerName + '&layerType=shape&query=' + query;
						var selectMethode = this.selectMethode;
						params += "&selectMethode=" + selectMethode;
						var urltmp = '';
						
						if (document.URL.indexOf('queryeditor') > 0) {
							opener.PM.Query.getQueryResult(url, params);
						} else {
							PM.Query.getQueryResult(url, params);
							//		        	this.cancel();
							PM.Map.reloadMap();
						}
					}
				}
			}
		},
		
		setSelection: function(type) {
			// reset interface for the new selection
			if (type == 'new') {
				this.reset();
			}
			this.selectMethode = type;
			if (typeof(PM.Plugin.SelectionManagement) != 'undefined') {
				PM.Plugin.SelectionManagement.setSelectionOperator('QueryEditor', this.selectMethode);
			}
		}

    }
});

