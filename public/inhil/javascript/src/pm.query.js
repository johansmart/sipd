
/*****************************************************************************
 *
 * Purpose: Functions for queries
 * Author:  Armin Burger
 *
 *****************************************************************************
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

$.extend(PM.Query,
{
    iquery_timer: null,
    timeW: -1,
    timeA: 2,
    timer_c: 0,
    timer_t: null,
    timer_to: null,

    
    /** default options for query result dialog */
    resultDlgOptions: {width:500, height:250, resizeable:true, newsize:false, container:'pmQueryContainer', name:"query"},
    
    /** Pre-rendering of query results */    
    preRenderedQResult: false,
    
    /** Automatically activate layers in TOC when search successful */
    searchAutoActivateLayers: false,
    
    /**
     * Default template for query
     */
    queryTpl: 
    { 
        "table":
           {"queryHeader": "<div>",
            "queryFooter": "</div>",
            "layers": 
                {"#default":
                   {"layerHeader":"<div class=\"pm-info-layerheader\">_p(Layer): ${description}</div><table class=\"sortable\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">",
                    "theaderTop": "<tr>",
                    "theader": "<th>@</th>",
                    "theaderBottom": "</tr>",
                    "tvaluesTop": "<tr>",
                    "tvalues":
                        {"shplink": "<td class=\"zoomlink\"><a href=\"javascript:PM.Map.zoom2extent('$[0]','$[1]','$[2]','$[3]')\"><img src=\"images/zoomto.gif\" alt=\"zoomto\"></a></td>",
                         "hyperlink": "<td><a href=\"javascript:PM.Custom.openHyperlink('$[0]','$[1]','$[2]')\">$[3]</a></td>",
                         "#default": "<td>$</td>"
                        },
                    "tvaluesBottom": "</tr>",
                    "layerFooter":"</table>"
                   }
                },
            "zoomall": 
                {"top": "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=\"100%\"><tr>",
                 "center": "<td class=\"zoomlink\"><a href=\"javascript:PM.Map.zoom2extent(0,0,'${allextent}',1)\"><img src=\"images/zoomtoall.gif\"alt=\"za\"></a></td>",
                 "bottom": "<td class=\"TDAL\">_p(Zoom to Selected Features)</td></tr></table>"
                },

            "callbackfunction": false
                
           },
           
        "tree":
           {"queryHeader": "<div><ul>",
            "queryFooter": "</div></ul>",
            "layers": 
                {"#default":
                   {"layerHeader":"<li><span>${description}</span><ul>",
                    "theaderTop": false,
                    "theader": false,
                    "theaderBottom": false,
                    "tvaluesTop": '<li><span>$1</span><ul>',
                    "tvalues":
                        {"shplink": "<li><a href=\"javascript:PM.Map.zoom2extent('$[0]','$[1]','$[2]','$[3]')\"><img src=\"images/zoomtiny.gif\" alt=\"zoomto\"> _p(Zoom)</a></li>",
                         "hyperlink": "<li>@: <a href=\"javascript:PM.Custom.openHyperlink('$[0]','$[1]','$[2]')\">$[3]</a></li>",
                         "#default": "<li>@: $</li>"
                        },
                    "tvaluesBottom": '</ul></li>',
                    "layerFooter":"</ul></li>"
                   }
                },
            "zoomall": 
                {"top": "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=\"100%\"><tr>",
                 "center": "<td class=\"zoomlink\"><a href=\"javascript:PM.Map.zoom2extent(0,0,'${allextent}',1)\"><img src=\"images/zoomtoall.gif\"alt=\"za\"></a></td>",
                 "bottom": "<td class=\"TDAL\">_p(Zoom to Selected Features)</td></tr></table>"
                },

            "callbackfunction": false
                
           },
           
        "iquery":
           {"queryHeader": "<div>",
            "queryFooter": "</div>",
            "layers": 
                {"#default":
                   {"layerHeader":"<table class=\"pm-iquery\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\"><tr><th colspan=\"2\" class=\"pm-iquery-header\">${description}</th></tr>",
                    "theaderTop": false,
                    "theader": false,
                    "theaderBottom": false,
                    "tvaluesTop": false,
                    "tvalues":
                        {"shplink": false,
                         "hyperlink": "<tr><th>@</th><td>$[3]</td></tr>",
                         "#default": "<tr><th>@</th><td>$</td></tr>"
                        },
                    "tvaluesBottom": false,
                    "layerFooter":"</table>"
                   }
                },
            "nozoomparams": true
           }
    },
    
    
    /**
     * Start identify (query) or select (nquery) 
     */
    showQueryResult: function(type, xy) {
        var pos = xy.split('+');
        if (type=='query') {
            var mx = pos[0]; 
            var my = pos[1];
        } else {
            var mx = pos[2]; 
            var my = pos[3];
        }
        PM.ajaxIndicatorShow(mx, my);
        
        var queryurl = PM.Query.xInfoPHP ? PM.Query.xInfoPHP : PM_XAJAX_LOCATION + 'x_info.php';
        
        if (type == 'query') {
            var qparams = SID + '&mode='+type + '&imgxy='+xy; // + layerstring;
        } else {
            var qparams = SID + '&mode='+type + '&imgxy='+xy + '&groups=' + this.getSelectLayer();
            PM.Map.zoomselected = '1';
        }
        
        this.getQueryResult(queryurl, qparams);
    },

    /**
     * Get query results and display them by parsing the JSON result string 
     */
    getQueryResult: function(qurl, params) {
        $.ajax({
            type: "POST",
            url: qurl,
            data: params,
            dataType: "json",
            success: function(response){
                var mode = response.mode;
                var queryResult = response.queryResult;
            
                if (mode != 'iquery') {
                    $('#infoFrame').showv();
                    PM.Query.writeQResult(queryResult, PM.infoWin);
                    PM.ajaxIndicatorHide();
                    // Automatically activate layers in TOC when search successful and activation set
                    if (mode == 'search' && PM.Query.searchAutoActivateLayers) {
                        var grpName = queryResult[0][0]['name'];
                        var grpCheckBox = $('#ginput_' + grpName); 
                        grpCheckBox.attr('checked', true);
                        
                        var catCheckBox = grpCheckBox.parents('li.toccat').find('input');
                        if (catCheckBox.length > 0) {
                            catName = catCheckBox.val();
                            catCheckBox.attr('checked', true); 
                            PM.Toc.setcategories(catName, true);
                        } else {
                            PM.Toc.setlayers(grpName, true);
                        }
                    }
                } else {
                    // Display result in DIV and postion it correctly
                    PM.Query.showIQueryResults(queryResult);
                }
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                if (window.console) console.log(errorThrown);
            }
        });
    },
    
    
    /**
     * Collect HTML string for result output
     */     
    html: {
        h: "",
        append: function(t) {
            if (t) {
                this.h += t;
            } else {
                this.h;
            }
        },
        
        text: function() {
            return this.h;
        },
        
        reset: function() {
            this.h = "";
        }
    },
    
    /**
     * Parse query result JSON string with selected template
     * add result to container and return callbackfunction
     */
    parseResult: function(jsonRes, tplName, container) {
        
        var queryLayers = jsonRes[0]; 
        var zoomParams = jsonRes[1]; 
        var tpl = this.queryTpl[tplName];
        
        var h = this.html;
        h.reset();
        h.append(PM.Query.parseLocale(tpl.queryHeader));

        // Query layers: modify query results in js
        $.each(PM.modifyQueryResultsFunctions, function(key, val) {
        	var fct = 0;
        	eval('fct = ' + val);
        	if ($.isFunction(fct)) {
        		queryLayers = eval(val + '(queryLayers, tplName)');
        	}
        });
   
        // Parse each layer from result set
        $.each(queryLayers, function() {
            var layTpl = tpl.layers['#default'];
            
            if (tpl.layers[this.name]) {
                $.extend(true, layTpl, tpl.layers[this.name]);
            }
            
            var rHeader = this.header;
            var customFields = [];
            var skipShpLink = false;
            var noShpLink = false;
            h.append(PM.Query.parseVal(layTpl.layerHeader, this));
            
            h.append(layTpl.theaderTop);
            // Parse result header 
            $.each(this.stdheader, function(i) {
                if (this == '#') noShpLink = true;  // RASTER data
                var fld = this;
                $.each(layTpl.tvalues, function(k,v) {
                    if (k == fld) {
                        customFields[i] = k;
                    }
                    if (k == "shplink" && v == false) {
                        skipShpLink = true;
                    }
                });
            });
            
            $.each(rHeader, function(i) {
                if (!(skipShpLink && this == '@') && this != "#" && layTpl.theader) { 
                    h.append(layTpl.theader.replace(/\@/, this));
                } 
            });
            h.append(layTpl.theaderBottom);
            
            // Parse field values
            $.each(this.values, function() {
                h.append(PM.Query.parseValTop(layTpl.tvaluesTop, this));
                
                $.each(this, function(i) {
                    //alert(this);
                    if (customFields[i]) {
                        if (this.shplink) {
                            h.append(PM.Query.parseLink(layTpl.tvalues[customFields[i]], this.shplink).replace(/@/, rHeader[i]));
                        } else if (this.hyperlink) {
                            h.append(PM.Query.parseLink(layTpl.tvalues[customFields[i]], this.hyperlink).replace(/@/, rHeader[i]));
                        } else {
                            if (!(noShpLink && i == 0))
                                h.append(layTpl.tvalues[customFields[i]].replace(/\$/, this)
                                                                        .replace(/@/, rHeader[i])
                                );
                        }
                    } else if (this.shplink) {
                        if (layTpl.tvalues.shplink) {
                            h.append(PM.Query.parseLink(layTpl.tvalues.shplink, this.shplink).replace(/@/, rHeader[i]));
                        }
                    } else if (this.hyperlink) {
                       h.append(PM.Query.parseLink(layTpl.tvalues.hyperlink, this.hyperlink).replace(/@/, rHeader[i]));
                    } else {
                        if (!(noShpLink && i == 0)) 
                            h.append(layTpl.tvalues['#default'].replace(/\$/, this)
                                                               .replace(/@/, rHeader[i])
                            );
                    }
                    
                });
                h.append(layTpl.tvaluesBottom);
            });

            h.append(PM.Query.parseVal(layTpl.layerFooter, this));
        });
        
        h.append(tpl.queryFooter);
        if (!tpl.nozoomparams) h.append(this.returnZoomParamsHtml(zoomParams, tpl.zoomall));

        if (container) {
            $('#' + container).html(h.text());
            return tpl.callbackfunction;
        } else {
            return h.text();
        }
    },
    
    /**
     * Parse value of result header
     * parse _p(...) and ${...} 
     */
    parseVal: function(v, list) {        
        if (!v) return false;
        var v = this.parseLocale(v);
        var m = v.match(/\$\{(\w+)\}/g);
        if (m) {
            $.each(m, function() {
                var key = this.slice(2,-1);
                var rVal = list[key];
                var reg2 = new RegExp('\\$\\{' + key + '\\}', 'g');
                v = v.replace(reg2, rVal);
            });
            return v;
        } else {
            return v;
        }
    },
    
    /**
     * Parse shapelink and hyperlink fields 
     * search for _p() and $[]
     */
    parseLink: function(t, linkList) {
        var t = this.parseLocale(t);
        var m = t.match(/\$\[\d\]/g);
        $.each(m, function(i) {
            var j = this.substr(2,1);
            var p = new RegExp('\\$\\[' + j + '\\]', 'g');

            t = t.replace(p, linkList[j]);
            mm = t.match(p);
        });
        return t;
    },
    
    /**
     * Replace $1..10 with value of result index 
     */
    parseValTop: function(vt, vlist) {        
        if (!vt) return false;
        var m = vt.match(/\$[0-9]/g);
        if (m) {
            
            //$.each(m, function(i) {
                //alert(m[i].substr(1,1));
                var val = vlist[m[0].substr(1,1)];
                //alert(val);
                if (typeof val == 'object') val = val.hyperlink[2];
            //});
        }
        
        return vt.replace(/\$[0-9]/, val);
    },
    
    /**
     * Search for locale string and return translated string
     */
    parseLocale: function(v) {
        var p = v.match(/_p\([^\(]*\)/);
        if (p) {
            var locStr = _p(p[0].slice(3, -1));
            v = v.replace(/_p\([^\(]*\)/, locStr);
        }
        
        return v;
    },
    
    /**
     * Create the HTML/JS for 'zoomall' and 'autozoom' settings
     */
    returnZoomParamsHtml: function(zp, tpl) {
        var allextent = zp.allextent;
        var autozoom = zp.autozoom;
        var zoomall = zp.zoomall;

        if (allextent) PM.extentSelectedFeatures = allextent;
        
        var html = '';
        if (zoomall && tpl.zoomall != false) {
            $.each(tpl, function(k, v) {
                html += PM.Query.parseVal(v, zp)
            });
        }
        
        // Add image for onload event
        html += '<img id=\"pmQueryResultLoadImg\" src=\"images/blank.gif\" style=\"display:none;\"  onload=\"';  
        if (autozoom) {
            if (autozoom == 'auto') {
                html += 'PM.Map.zoom2extent(0,0,\'' + allextent + '\', 1);';
            } else if (autozoom == 'highlight') {
                html += 'PM.Map.updateMap(PM_XAJAX_LOCATION + \'x_load.php?' + SID +  '&mode=map&zoom_type=zoompoint\', \'\')';
            }
        } else {
            html += '$(\'#zoombox\').hidev();';
        }
        
        html += '\" />';
        
        var qrlLen = PM.Custom.queryResultAddList.length;
        for (var i=0; i<qrlLen ; i++) {
            html += eval(PM.Custom.queryResultAddList[i]);
        }

        return html;
    },


    /**
     * Parse JSON result string with parseJSON()
     * and insert resulting HTML into queryresult DIV
     * run post-processing scripts
     */
    writeQResult: function(resultSet, infoWin) {
        var queryResultContainer = infoWin;
        
        if (infoWin == 'dynwin') {
            PM.Dlg.createDnRDlg(this.resultDlgOptions, _p('Result'), false);
            queryResultContainer = 'pmQueryContainer_MSG';
            $('#' + queryResultContainer).addClass('pm-info').addClass('jqmdQueryMSG');
        } 
        if (!this.preRenderedQResult) {
            if (!resultSet) {
                $('#' + queryResultContainer).html(this.returnNoResultHtml());
            } else {
                // call main result parser
                var callbackfn = PM.Query.parseResult(resultSet, PM.queryResultLayout, queryResultContainer);
                
                if (PM.queryResultLayout == 'table') {
                    sortables_init();
                } else if (PM.queryResultLayout == 'tree') {
                    $('#' + queryResultContainer).treeview(PM.queryTreeStyle.treeview);
                }
                
                eval(callbackfn);
            }
        } else {
            $('#' + queryResultContainer).html(resultSet);
        }
    },

    /**
     * Return HTML for no results found in query
     */
    returnNoResultHtml: function(infoWin) {
        var h = '<table class="restable" cellspacing="0" cellpadding="0">';
        h += '<td>' + _p('No records found') + '</td>'; 
        h += '</tr></table>';
        return h;
    },
    

    /**
     * Return layer/group for selection
     */
    getSelectLayer: function() {
        var selform = _$("selform");
        if (selform) {
            if (selform.selgroup) {
                var sellayer = selform.selgroup.options[selform.selgroup.selectedIndex].value;
                //alert(sellayer);
                return sellayer;
            } else {
                return false;
            }
        } else {
            return false;
        }
    },

    /**
     * Start auto-identify (iquery)
     */
    applyIquery: function(mx, my) {
        var imgxy  = mx + "+" + my;
        var queryurl = PM_XAJAX_LOCATION + 'x_info.php?' +SID+ '&mode=iquery' + '&imgxy='+imgxy + '&groups=' + this.getSelectLayer();
        this.getQueryResult(queryurl, '');
    },

    /**
     * TIMER FOR OAUTO_IDENTIFY ACTION 
     * indicates for how much time the cursor remains firm on the map [by Natalia]
     */
    timedCount: function(moveX, moveY) {  
        if (this.timer_c == 0){
            X = moveX;
            Y = moveY;
        }
        if (this.timer_c == 1){
            this.iquery_timer = setTimeout("applyIquery(" + X + "," + Y + ")", 200);
        }
        this.timer_c += 1;
        this.timer_t = setTimeout("timedCount()",this.timeA);
    },

    /**
     * Display result in DIV and postion it correctly
     */
    showIQueryResults: function(queryResult) {
    	// do not display iquery result if tool has changed
    	if (PM.Map.mode == 'iquery') {
    		var iQL = $('#iqueryContainer');
    		// alert(iQL.height());
    		if (queryResult) {
    			var IQueryResult = PM.Query.parseResult(queryResult, 'iquery', false);
    		} else {
    			return false;
    		}
    		var map = $('#mapImg');

    		if (PM.autoIdentifyFollowMouse){
    			// border limits
    			var limitRG = map.iwidth() - iQL.iwidth() - 4; // Right
    			var limitDN = map.iheight() - iQL.iheight() - 4;    // Down
    			var moveX = PM.ZoomBox.moveX;
    			var moveY = PM.ZoomBox.moveY;

    			//gap between mouse pointer and iqueryLayer:
    			var gap = 10;

    			// right:
    			if (moveX >= limitRG){
    				iQL.left(moveX - iQL.iwidth() - gap + 'px');
    			} else {
    				iQL.left(moveX + gap +'px');
    			}

    			// down:
    			if (moveY >= limitDN){
    				iQL.top(moveY - iQL.iheight() - gap + 'px');
    			} else {
    				iQL.top(moveY + gap +'px');          
    			}

    			if (IQueryResult) {
    				//iQL.css({height:0});
    				iQL.html(IQueryResult).showv().show();
    				if (this.timeW != -1) this.timer_to = setTimeout("hideIQL()",this.timeW);
    			} else {
    				iQL.html('').height(0).hidev().hide();
    				clearTimeout(this.timer_t);
    				clearTimeout(this.iquery_timer);
    			}
    			// no follow, display on fixed position
    		} else {
    			if (IQueryResult) {
    				iQL.html(IQueryResult).showv();
    			} else {
    				iQL.html('').hidev();
    			}
    		}
    	}
    },

    hideIQL: function() {
        clearTimeout(this.iquery_timer);
        $('#iqueryContainer').hidev();
    },

    mapImgMouseOut: function() {
        //alert('out');
        var vMode = PM.Map.mode;
        if (vMode == 'iquery' || vMode == 'nquery') {
            $('#iqueryContainer').hidev();
        }
    }
    
});
