
/*****************************************************************************
 *
 * Purpose: Functions for forms and scale selection list
 * Author:  Armin Burger
 *
 *****************************************************************************
 *
 * Copyright (c) 2003-2006 Armin Burger
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

$.extend(PM.Form,
{
    scale_timeout: null,

    initScaleSelect: function() {
        try {
            this.writeScaleList(PM.scaleSelectList);
        } catch(e) {
            return false;
        }
    },

    writeScaleList: function(scaleList) {
        var scaleListLen = scaleList.length;
        
        // If no scales defined don't use select function
        if (scaleListLen < 1) {
            return false;
        } else {
            $('#scaleArea input').attr("autocomplete", "off");
        }
        var sobj = $('#scaleSuggest');
        sobj.show();
        sobj.html('');

        var suggest_all = '';
        for(var i=0; i < scaleListLen ; i++) {
            var sclink = i<1?'scale-link-over':'scale-link';
            var suggest = '<div onmouseover="javascript:PM.Form.scaleOver(this);" ';
            suggest += 'onmouseout="javascript:PM.Form.scaleOut(this);" ';
            suggest += 'onclick="PM.Form.insertScaleTxt(this.innerHTML);" ';
            suggest += 'class="' + sclink + '">' + scaleList[i] + '</div>';
            suggest_all += suggest;
        }
        sobj.html(suggest_all);
    },

    insertScaleTxt: function(value) {
        var newScale = value.replace(/,|'|\.|\s/g, '');
        $('#scaleinput').val(newScale);
        $('#scaleSuggest').html('');
        this.hideScaleSuggest();
        PM.Map.zoom2scale(newScale);
    },

    scaleOver: function(div_value) {
        div_value.className = 'scale-link-over';
    },


    scaleOut: function(div_value) {
        div_value.className = 'scale-link';
    },

    scaleMouseOut: function(force) {
        var sobj = _$('scaleSuggest');
        var scaleDivList = sobj.getElementsByTagName('DIV');
        var hlStyle = false;

        for (var i=0; i<scaleDivList.length; i++) {
            if (scaleDivList[i].className == 'scale-link-over') {
                hlStyle = true;
            }
        }
        
        if (force) {
            setTimeout("PM.Form.hideScaleSuggest()", 500);
            //return false;
        } else {
        
            clearTimeout(this.scale_timeout);
            if (hlStyle) {
                
            } else {
                this.scale_timeout = setTimeout("PM.Form.hideScaleSuggest()", 500);
            }
        }
    },

    hideScaleSuggest: function() { 
        $('#scaleSuggest').hide();
    },

    setScaleMO: function() {
        scale_mouseover = true;
    },
    
    
    
    /**
     * Return form values in key=value pair notation
     */
    getFormKVP: function(formid) {
        var htmlform = document.getElementById(formid);
        //alert(searchForm.elements);
        var el = htmlform.elements;
        var s = '';
        for (var i=0; i < el.length; i++) {
            var e = el[i]; 
            var ename = e.name;
            var evalue = e.value;
            var etype = e.type;
            var delim = (i>0 ? '&' : '');
            
            if (evalue && evalue.length > 0 && evalue != '#') {
                //alert(etype + ' - ' + evalue);
                switch (etype) {
                    //case 'text':
                    case 'select-one':
                        s += delim + ename + '=' + e.options[e.selectedIndex].value;
                        break;
                
                    case 'select-multiple':
                        var ol = e.options;
                        var opttxt = '';
                        for (var o=0; o < ol.length; o++) {
                            if (ol[o].selected) {
                                opttxt += ol[o].value + ',';
                            }
                        }
                        s += delim + ename + '=' + opttxt.substr(0, opttxt.length - 1); 
                        break;
                        
                    case 'checkbox':
                        if (e.checked) {
                            s += delim + ename  + '=' + evalue;
                        }
                        break;
                        
                    case 'radio':
                        if (e.checked) {
                            s += delim + ename  + '=' + evalue;
                        }
                        break;
                        
                    default:
                        s += delim + ename  + '=' + evalue;
                        break;
                }
            }
        }
        //alert(s);
        return s;
    },
    
    
    getFormKvpObjAll: function(formid) {
        var htmlform = document.getElementById(formid);
        //alert(searchForm.elements);
        var el = htmlform.elements;
        var q = {};
        for (var i=0; i < el.length; i++) {
            var e = el[i]; 
            var ename = e.name;
            var evalue = e.value;
            var etype = e.type;
            var eid = e.id;
            
            if (evalue.length > 0 && evalue != '#') {
                //alert(etype + ' - ' + evalue);
                switch (etype) {
                    //case 'text':
                    case 'select-one':
                        q[ename] = e.options[e.selectedIndex].value;
                        break;
                
                    case 'select-multiple':
                        var ol = e.options;
                        var opttxt = '';
                        for (var o=0; o < ol.length; o++) {
                            if (ol[o].selected) {
                                opttxt += ol[o].value + ',';
                            }
                        }
                        q[ename] = opttxt.substr(0, opttxt.length - 1); 
                        break;
                        
                    case 'checkbox':
                        if (e.checked) {
                            if (q[ename]) {
                                q[ename] += ',' + eid;
                            } else {
                                q[ename] = eid;
                            }
                        }
                        break;
                        
                    case 'radio':
                        if (e.checked) {
                            q[ename] = evalue;
                        }
                        break;
                        
                    default:
                        q[ename] = evalue;
                        break;
                }
            }
        }
        
        //alert(s);
        return q;
    },
    
    getFormKvpObj: function(el) {
        if (el.is("input[type='text']")) {
            //alert('text');
        } else if (el.is("input[type='select']")) {
            //alert('select-one');
        }
        //alert(el.id());

        
        //alert(s);
        return q;
    }
    

});