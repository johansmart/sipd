/******************************************************************************
 *
 * Purpose: JS functions for XML based search definition
 * Author:  Armin Burger
 *
 ******************************************************************************
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

$.extend(PM.Query,
{
    /** Keep selected value in search box */
    seachBoxKeepSelectedValue: false,
    
    /** Default options for suggest/autocomplete box */
    suggestOptions: { 
        delay:300, 
        cacheLength: 20,
        matchSubset: true,
        selectFirst: false,
        max: 0,
        scrollHeight: 250
    },
    
    /**
     * Disable ENTER input for search form
     * Patch provided by Walter Lorenzetti
     */
    disableEnterKey: function(e)
    {
        var key;
        if (window.event) {
            key = window.event.keyCode;     //IE
        } else {
            key = e.which;     //firefox
        }
        if (key == 13) {
            this.submitSearch();
            return false;
        } else {
            return true;
        }
    },
    
    /**
     * Start attribute search
     */
    submitSearch: function() {
        PM.ajaxIndicatorShow(false, false);
        
        var searchForm = _$('searchForm');
        var skvp = PM.Form.getFormKVP('searchForm');
        //alert(skvp);
        
        if (PM.infoWin != 'window') {
            searchForm.target='infoZone';
        } else {
            var resultwin = openResultwin('blank.html');
            searchForm.target='resultwin';
        }
        
        var queryurl = PM_XAJAX_LOCATION + 'x_info.php';
        var params = SID + '&' + skvp + '&mode=search';
        //alert(queryurl);
        this.getQueryResult(queryurl, params);
    },
    

    /**
     * Attribute search: create items for search definitions 
     */
    createSearchItems: function(url) {
        $.ajax({
            url: url,
            dataType: "json",
            success: function(response){
                var searchJson = response.searchJson;
                var action = response.action;
                
                if (action == 'searchitem') {
                    PM.Query.createSearchInput(searchJson);
                } else {
                    var searchHtml = PM.Query.json2Select(searchJson, "0");
                    $('#searchoptions').html(searchHtml);
                }
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                if (window.console) console.log(errorThrown);
            }
        });
    },


    /**
     * Launch AJAX request to parse search.xml and get optionlist for serach
     */
    setSearchOptions: function() {
        var url = PM_XAJAX_LOCATION + 'x_search.php?' + SID +'&action=optionlist';
        this.createSearchItems(url);
    },

    /**
     * Launch AJAX request to parse search.xml and get params for chosen searchitem
     */
    setSearchInput: function() {
        var searchForm = _$('searchForm');
        // normal searchbox behaviour (reset the fields)
        if (!this.seachBoxKeepSelectedValue) {
	        var searchitem = searchForm.findlist.options[searchForm.findlist.selectedIndex].value;
	        var url = PM_XAJAX_LOCATION + 'x_search.php?' + SID +'&action=searchitem&searchitem=' + searchitem;
	        _$('searchForm').findlist.options[0].selected = true;  // reset parent select box to "Search for..."
	        this.createSearchItems(url);
	    // new searchbox behaviour (reset the fields)
        } else {
	    	if (searchForm.findlist.selectedIndex == 0) {
	    		$('#searchitems').html('');
	    		_$('searchForm').findlist.options[0].selected = true;  // reset parent select box to "Search for..."
	    	} else {
	            var searchitem = searchForm.findlist.options[searchForm.findlist.selectedIndex].value;
	            var url = PM_XAJAX_LOCATION + 'x_search.php?' + SID +'&action=searchitem&searchitem=' + searchitem;
	            this.createSearchItems(url);
	    	}
        }
    },

    /**
     * Convert a JSON string to HTML <select><option> list
     */
    json2Select: function(jsonObj, fo) {
        var html = '<select name="' + jsonObj.selectname + '" id="pmsfld_' + jsonObj.selectname + '"' ;
        var events = jsonObj.events;
        var size = jsonObj.size;
        
        if (size > 0) html += ' size="' + size +'" multiple="multiple" ';
        
        if (events) {
        	if (typeof(events) == 'object') {
	            for (var e in events) {
	                html += e + '="' + events[e] + '" '; 
	            }
        	// if "events" is a string, the HTML is bad written:
        	} else {
        		html += events;
        	}
        }

        html += '>';
        
        var options = jsonObj.options;
        var htmlOptions = '';
        var numOptions = 0;
        for (var o in options) {
        	htmlOptions += '<option value=\"' + o + '\">' + options[o] + '</option>';
        	numOptions++;
        }
        if (fo != "0" && numOptions > 1) html += '<option value=\"#\">' + fo + '</option>';
        html += htmlOptions;
        html += '</select>';
        
        return html;
    },


    /**
     * Create the input tag for every field of the attribute search
     */
    createSearchInput: function(jsonObj) {

        var searchitemsElem = $('#searchitems');
        var itemLayout = searchitemsElem.attr('class').replace(/pm_search_/, '');
        
        var searchitem = jsonObj.searchitem;
        var fields     = jsonObj.fields;

        var hc = '<table id="searchitems_container1" class="pm-searchitem" border="0" cellspacing="0" cellpadding="0">';
        var itemsAppendTo = 'searchitems_container1';
        if (itemLayout == 'inline') {
            hc += '<tr id="searchitems_container2"></tr>';
            itemsAppendTo = 'searchitems_container2';
        }
        hc += '</table>';
        
        searchitemsElem.html('');
        $(hc).appendTo(searchitemsElem);
        
        var html = '';
        var htmlend = '';
        for (var i=0; i<fields.length; i++) {
            var description = fields[i].description;
            var fldname     = fields[i].fldname;
            var fldsize     = fields[i].fldsize;
            var fldsizedesc = fields[i].fldsizedesc;
            var fldinline   = fields[i].fldinline;
            var definition  = fields[i].definition;
            
            var inputsize = fldsize ? ' size="' + fldsize + '" ' : '';
            var sizedesc = fldsizedesc ? ' style="position:absolute; left:' + fldsizedesc + 'em"' : '';
            
            if (!definition) {
                var hi = ' <td class="pm-searchdesc">' + description + '</td>';
                hi += ' <td' + sizedesc + '>' + '<input type="text" class="pm-search-textinput" id="pmsfld_' + fldname + '" name="' + fldname + '"' + inputsize + '></td>';
                if (itemLayout != "inline") hi = '<tr>' + hi + '</tr>';
                $(hi).appendTo('#'+itemsAppendTo);
                
            } else {
                if (definition.type == 'options') {
                    var ho = ' <td class="pm-searchdesc">' + description + '</td>';
                    ho += ' <td>' + this.json2Select(definition, definition.firstoption) + '</td>';
                    if (itemLayout != "inline") ho = '<tr>' + ho + '</tr>';
                    $(ho).appendTo('#'+itemsAppendTo);
                    
                } else if (definition.type == 'suggest') {
                    var hs = '<td class="pm-searchdesc">' + description + '</td>';
                    hs += '<td><input type="text" id="pmsfld_' + fldname + '" name="' + fldname + '" alt="Search Criteria"' + inputsize + ' ' + definition.events + ' /></td>';
                    if (itemLayout != "inline") hs = '<tr>' + hs + '</tr>';
                    $(hs).appendTo('#'+itemsAppendTo);
                    
                    var searchitem  = definition.searchitem;
                    var minlength   = definition.minlength;
                    var suggesturl = PM_XAJAX_LOCATION + 'x_suggest.php?' + SID + '&searchitem=' + searchitem + '&fldname=' + fldname;

                    // many dependfields
                    var dependFields = definition.dependfld;
                    var xParamsParts = {};
                    if (dependFields) {
	                    dependFields = dependFields.split(',');
	                    $.each(dependFields, function() {
	                    	var dependfld = this;
	                    	xParamsParts['dependfldval_' + dependfld] = function() {
		                    	var fldName = eval('dependfld');
		                    	return $('#pmsfld_' + fldName + ':checkbox').is(':not(:checked)') ? '' : $('#pmsfld_' + fldName).val();
		                    };
	                    });
                    }
                    var xParams = xParamsParts ? xParamsParts : false;
                    
                    //var xParams = dependfld ? PM.Form.getFormKvpObj('searchForm') : false;
                    $('#pmsfld_' + fldname)
                        .autocomplete(suggesturl, PM.Query.suggestOptions)
                        .setOptions({ minChars: minlength, extraParams: xParams });
                    if (definition.nosubmit != 1 && PM.suggestLaunchSearch)
                        $('#pmsfld_' + fldname).result(function(event, data, formatted) {
                            if (data) PM.Query.submitSearch();
                        });
                
                } else if (definition.type == 'checkbox') {
                    var value      = definition.value;
                    var defchecked = ''; //(definition.checked == 1) ? ' checked ' : '' ; //" checked="checked" ' : '' ;                
                    var hcb = '<td class="pm-searchdesc">' + description + '</td>';
                    hcb += '<td><input type="checkbox" id="pmsfld_' + fldname + '" name="' + fldname + '" ' + '" value="' + value + '" ' + defchecked + ' /></td>';
                    if (itemLayout != "inline") hcb = '<tr>' + hcb + '</tr>';
                    $(hcb).appendTo('#'+itemsAppendTo);
                    
                // Radio Button
                } else if (definition.type == 'radio') {
                    var inputlist  = definition.inputlist;
                    var hra = "";
                    for (var ipt in inputlist) {
                        //alert(definition.checked);
                        var defchecked = (definition.checked == ipt) ? ' checked="checked" ' : '' ; //" checked="checked" ' : '' ;                
                        hra += '<td><input type="radio" id="pmsfld_' + fldname + '" name="' + fldname + '" ' + '" value="' + ipt + '" ' + defchecked + ' /></td>';
                        hra += '<td>' + inputlist[ipt]+ '</td>';
                    }
                    if (itemLayout != "inline") hra = '<tr>' + hra + '</tr>';
                    $(hra).appendTo('#'+itemsAppendTo);
                
                } else if (definition.type == 'operator') {
                    //if (fldinline) html += '<div class="search_inline">';
                    var hop = '<td class="pm-searchdesc">' + description + '</td>';
                    hop += ' <td' + sizedesc +'>' + this.json2Select(definition, false);
                    hop += ' <input type="text" class="pm-search-textinput-compare" id="pmsfld_' + fldname + '" name="' + fldname + '" ' + inputsize + '></td>';
                    if (itemLayout != "inline") hop = '<tr>' + hop + '</tr>';
                    $(hop).appendTo('#'+itemsAppendTo);
                
                } else if (definition.type == 'hidden') {
                    htmlend += '<input type="hidden" id="pmsfld_' + fldname + '" name="' + fldname + '" value="' + definition.value + '">';
                }
            }
            
        }
        /*
        html += '<td colspan="2" class="pm-searchitem">';
        html += '<div><input type="button" value="' + _p('Search') + '" size="20" ';
        html += 'onclick="PM.Query.submitSearch()" onmouseover="PM.changeButtonClr(this, \'over\')" onmouseout="PM.changeButtonClr (this, \'out\')"></div>';
        html += '<div><img src="images/close.gif" alt="" onclick="$(\'#searchitems\').html(\'\')" /></div>';
        html += '</td>';
        */
        html += '<td colspan="2" class="pm-searchitem">';
        html += '<table><tr><td><input type="button" value="' + _p('Search') + '" size="20" ';
        html += 'onclick="PM.Query.submitSearch()" onmouseover="PM.changeButtonClr(this, \'over\')" onmouseout="PM.changeButtonClr (this, \'out\')"></td>';
        if (!this.seachBoxKeepSelectedValue) {
        	html += '<td><img src="images/close.gif" alt="" onclick="$(\'#searchitems\').html(\'\')" /></td>';
	    } else {
	    	html += '<td><img src="images/close.gif" alt="" onclick="$(\'#searchitems\').html(\'\');_$(\'searchForm\').findlist.options[0].selected	= true;" /></td>';
	    }
        html += '</tr></table></td>';
        
        htmlend += '<input type="hidden" name="searchitem" value="' + searchitem + '" />';
        if (itemLayout != "inline") html = '<tr>' + html + '</tr>';
        $(html).appendTo('#'+itemsAppendTo);
		$(htmlend).appendTo(searchitemsElem);
    }


    /***
    // sample function for executing attribute searches with external search parameter definitions
    submitSearchExt: function() {
        var searchForm = _$('searchForm');
        if (PM.infoWin != 'window') {
            searchForm.target='infoZone';
        } else {
            var resultwin = openResultwin('blank.html');
            searchForm.target='resultwin';
        }
        //var qStr = '(([POPULATION]<12000))';
        var qStr = '(  ( "[NAME]" =~ /(B|b)(E|e)(R|r)(L|l)(I|i)/ ) )';
        var queryurl = PM_XAJAX_LOCATION + 'x_info.php';
        var params = SID + '&externalSearchDefinition=y&mode=search&layerName=cities10000eu&layerType=shape&fldName=POPULATION&qStr=' + qStr ; 
        getQueryResult(queryurl, params);
    }
    ***/

});