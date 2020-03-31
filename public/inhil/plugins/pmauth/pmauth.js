/******************************************************************************
*
* Purpose: Simple authentication plugin for pmapper with users administration
* Author:  Walter Lorenzetti, gis3w, lorenzetti@gis3w.it
*
******************************************************************************
*
* Copyright (c) 2008-2011 gis3w
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

 var ConfigGuiLayout = {};
           
$.extend(PM.Map,{
  
});

$.extend(PM.Plugin,
{
    pmAuth:
    {
        pmAuthDlgOptions: {width:500, height:300, left:100, top:50, resizeable:true, newsize:true, container:'pmDlgContainer', name:"pmAuth"},
	
      	layoutOptions: {
      		      name: "pmAuthLayout",
      		      north: {
                                            size:30,
            			spacing_open:		      1,
            			togglerLength_open:	  0,
            			togglerLength_closed:	 -1,
            			resizable: 		        false,
            			slidable:		          false,
            			fxName:			          "none"
            			},
      		      west:{
                            size:                 100
                },
      		      center:{}
      	},
        
        //globals var
        mods: ['edit','todel'],

        actions: ['save','del'],

        roles: [],

        configs: [],

        ckpass : false,
        
        /**
         * Method to call at start of pmapper
         */
        init: function() {
          //overwrite default parameter if they set in ini xml file
          
          $.ajax({
            url: PM_PLUGIN_LOCATION + '/pmauth/incphp/xajax/x_session.php?' + SID,
            dataType: "json",
            success: function(res){
                
              // retreive role
              PM.Plugin.pmAuth.idRole = res.vars.idRole;
              // erase private area from array
              PM.linksDefault.links.splice(4,1);
              if ( PM.Plugin.pmAuth.idRole == 0){
                PM.linksDefault.links.push({linkid:'pmAuthAdmin', name:'Auth Admin', run:'PM.Plugin.pmAuth.open', imgsrc:'admin.png'});
              }
              PM.Plugin.pmAuth.roles = res.vars.roles;
              PM.Plugin.pmAuth.configs = res.vars.configs;
              PM.linksDefault.links.push({linkid:'pmAuthLogOut', name:'Logout', run:'PM.Plugin.pmAuth.logout', imgsrc:'logout.png'});
              $('#toolLinkContainer').html('');
    	      $('#uiLayoutNorth').pmToolLinks(PM.linksDefault);

              

              // initialize the mapselect
              var totConfigs;
              //console.log(PM.Plugin.pmAuth.idRole);
              if(PM.Plugin.pmAuth.idRole == 0){
                totConfigs = res.vars.configs.concat(res.vars.configs_noauth);
              }else{
                totConfigs = res.vars.userconfigs.concat(res.vars.configs_noauth);
              }
              
              if(typeof(PM.Plugin.Mapselect) != 'undefined'){
                 var msplg = PM.Plugin.Mapselect;
                 var preConfigList = msplg.settings.configList;
                 msplg.settings.configList = {};
                  $(".map-top-bar").empty();
                   $.each(totConfigs,function(nr){
                       
                       
                       if(typeof(preConfigList[this.toString()]) != 'undefined'){
                            if(typeof(res.vars.mapselectNames[this.toString()]) != 'undefined'){
                                var name = res.vars.mapselectNames[this.toString()];
                                msplg.settings.configList[this.toString()] = name;
                            }else{
                                msplg.settings.configList[this.toString()] =  preConfigList[this.toString()];
                            }
                       }
                   });
                   
                  msplg.init();
              }
            },
            error:function(err){
                window.alert(err.responseText);
                console.log(err);
            }
          });

        },
        
        /**
         * Open the admin dialog window
         */
        open: function() {
           PM.Dlg.createDnRDlg(this.pmAuthDlgOptions, _p('Config'), 'plugins/pmauth/pmauth.phtml?'+SID);
           
        },
        
        /**
         * Method for logout authentication
         */
        logout: function() {
          var href = window.location.href;
          $.ajax({
          url: PM_PLUGIN_LOCATION + '/pmauth/incphp/xajax/x_logout.php?' + SID,
            dataType: "json",
            success: function(response){
              window.location.href = href;
            }
          });
        },
        
        //at the moment this method is not used... is used with redeclaration of PM.Dlg.createDnRDlg method... see below
        layout: function() {
          
          pmAuthLayout = $('#pmAuthId').layout(this.layoutOptions).resizeAll();
	        $('img.resizeHandle').mouseup(function(){pmAuthLayout.resizeAll()});
        },
        
       
        /** 
         * Ajax call for get user/s data 
         */
        getUsers: function(mod,idU){
          $.ajax({
          url: PM_PLUGIN_LOCATION + '/pmauth/incphp/xajax/x_users.php?' + SID,
            data: {mod:mod,idU:idU},
            dataType: "json",
            success: function(response){
              switch(mod){
                case "view":
                  PM.Plugin.pmAuth.parseUsersTb(response.users);
                break;

                case "edit":
                  PM.Plugin.pmAuth.parseUsersEdit(response.users[0]);
                break;

                case "todel":
                  PM.Plugin.pmAuth.parseUsersDelete(response.users[0]);
                break;
                              
              } 
            },
             error:function(err){
                window.alert(err.responseText);
            }
          });
          },
          
          setUsers: function(mod,data){
          $.ajax({
          url: PM_PLUGIN_LOCATION + '/pmauth/incphp/xajax/x_users.php?' + SID,
            data: {mod:mod,data:data},
            dataType: "json",
            success: function(res){
              var msg;
              if(res.errDb){
                  msg = _p("Sorry an error was occured, during save/delete data!");
              }else{
                switch(mod){
                  case "save":
                    msg = _p("User saved!");
                  break;

                  case "del":
                    msg = _p("User erased!");
                  break;

                }
              }
              $("#pmAuthId div.ui-layout-center").html(msg);
            },
             error:function(err){
                window.alert(err.responseText);
            }
          });
        },
        
        /** 
         * Method to parse ajax call into a table 
         */
        parseUsersTb: function(data){
          //console.log('ARRIVA');
          var tb,tr,th,td,bt,span,cfgs,def;
          var mods = this.mods;
          var title = _p("Users list");
          tb = $('<table>').attr("id","tbusers").addClass("sortable");
          $.each(data,function(nr){
            // head table
            if(nr == 0){
              tr = $('<tr>');
              for (var c in this) {
                td = $('<td>');
                td.html(_p(c));
                tr.append(td);
              }
              td = $('<td>');
              td.html(_p('Action'));
              tr.append(td);
              tb.append(tr);
            }
            tr = $('<tr>');
            for(var c in this){
              td = $('<td>');
              if(c == 'configs'){
                cfgs = this[c].cfgs;
                def = this[c].def;
                $.each(cfgs,function(){
                  span = $('<span>');
                  span.html(this.toString()).append('<br>');
                  if (this.toString() == def) span.addClass('evidence');
                  td.append(span);
                });
              }else{
                td.html(this[c]);
              }
              tr.append(td);
            }
            // adding editing and delete button
            td = $('<td>');
            var idU = this.id;
            $.each(mods,function(){
              bt = $('<a>').attr("href","javascript:PM.Plugin.pmAuth.getUsers('" + this.toString() + "',"+ idU +");").html(_p(this.toString()));
              bt.prepend($('<img>').attr("src",PM_PLUGIN_LOCATION  + "/pmauth/" + this + ".png").attr("alt","link"));
              td.append(bt).append(' ');
            });
            tr.append(td);
            tb.append(tr);
          });

          bt = $('<a>').attr("href","javascript:PM.Plugin.pmAuth.parseUsersEdit('new');").html(_p("Add new User"));
          bt.prepend($('<img>').attr("src",PM_PLUGIN_LOCATION  + "/pmauth/add.png").attr("alt","link"));
          $("#pmAuthId div.ui-layout-center").html(tb).append(bt).prepend($("<b>").append(title).append($("<br>")));
          sortables_init();
        },


        /**
         * Method to check username in db
         */
        ckuser: function(e){
         errMsg = $("<img>").attr("alt","link");
         if(this.value.length > 3){
             $.ajax({
              url: PM_PLUGIN_LOCATION + '/pmauth/incphp/xajax/x_users.php?' + SID,
                dataType: "json",
                data:{mod:'ckuser',username:this.value},
                success: function(res){
                  if(res.userindb){
                    errMsg.after(_p('User in DB'));
                  }else{
                    errMsg.after(_p('NO User in DB'));
                  }
                  $("#confUser").html(errMsg);
                },
                error:function(err){
                    window.alert(err.responseText);
                }
              });
         }
        },
        
        /** 
         * Method to parse ajax call into a form
         */
        parseUsersEdit: function(data){
          var title = _p("Edit user");
          if(data == 'new') {
            data = {};
            title = _p("Insert new user");
          }

          var frm,lbl,inp,opt,optAtt,rdo,span;
          frm = $('<form>').attr('id','userForm');
          
          inp = $("<input>").attr({id: "idU",type: "hidden",name: "idU",value: data.id});
          frm.append(lbl).append(inp);

          lbl = $("<label>").attr("for","username").html(_p("username"));
          inp = $("<input>").attr({id: "username",type: "text",name: "username",value: data["username"]}).keyup(this.ckuser);
          frm.append(lbl).append(inp).append($("<span id='confUser'>")).append("<br>");
          
          lbl = $("<label>").attr("for","password").html("password");
          inp = $("<input>").attr({id: "password",type: "password",name: "username"});
          frm.append(lbl).append(inp).append("<br>");

          // adding select roles
          lbl = $("<label>").attr("for","id_role").html(_p("role"));
          inp = $("<select>").attr({id: "id_role",name: "id_role"});

          $.each(PM.Plugin.pmAuth.roles, function(){
            optAtt = {value:this.id};
            if(data.role == this.role) optAtt.selected = "selected";
            opt = $("<option>").attr(optAtt).html(this.role);
            inp.append(opt);
          });

          frm.append(lbl).append(inp).append("<br>");

          // adding cofigs choose
          span = $("<span>").html(_p("Select configs and set the default") + ":");
          frm.append(span).append("<br>");
          var c = 0;
          $.each(PM.Plugin.pmAuth.configs, function(){
            var idc = "config" + c.toString();
            var idr = "config_def" + c.toString();
            lbl = $("<label>").attr("for",idc).html(this.toString());
            opt = $("<input>").attr({id:idc,type:"checkbox",name:idc,value:this.toString()});
            if(typeof(data.configs) != 'undefined'){
              if($.inArray(this.toString(),data.configs.cfgs) != -1) opt.attr('checked','checked');
            }else{
              if(this.toString() == 'default') opt.attr('checked','checked');
            }
            rdo = $("<input>").attr({id:idr,type:"radio",name:"config_def",value:this.toString()});
            if(typeof(data.configs) != 'undefined'){
              if(this.toString() == data.configs.def) rdo.attr('checked','checked');
            }else{
              if(this.toString() == 'default') rdo.attr('checked','checked');
            }
            frm.append(lbl).append(opt).append(rdo).append("<br>");
            c++;
          });
          
          var fn = this.setUsers;
          
          inp = $("<input>").attr({type: "button",value: _p("Save")}).click(function(){
            var toSend = {};
            toSend.idU = $("#idU").val();
            toSend.username = $("#username").val();
            if($("#password").val() != ''){
              toSend.password = $.md5($("#password").val());
            }
            toSend.id_role = $("#id_role").val();
            toSend.configs = {}; toSend.configs.cfgs = [];
            $("input:radio[name='config_def']").each(function(){
              if(this.checked) toSend.configs.def = this.value;
            });
            $("#userForm > input:checkbox").each(function(){
              if(this.checked) toSend.configs.cfgs.push(this.value);
            });
            fn("save",$.toJSON(toSend));
          });
          frm.append(inp).append("<br>");
          $("#pmAuthId div.ui-layout-center").html(frm).prepend($("<b>").append(title).append($("<br>")));
          
        },


         parseUsersDelete: function(data){
          var p,frm,inp;
          p = $('<p>').html(_p('Do you want delete user') + ' <b>' + data.username + '</b>?');
          frm = $('<form>').attr('id','userForm');
          inp = $("<input>").attr({id: "idU",type: "hidden",name: "idU",value: data.id});
          frm.append(inp);
          var fn = this.setUsers;
          inp = $("<input>").attr({type: "button",value: "Yes"}).click(function(){
            var toSend = {};
            toSend.idU = $("#idU").val();
            fn("del",$.toJSON(toSend));
          });
          frm.append(inp);
          inp = $("<input>").attr({type: "button",value: "No"}).click(function(){
            PM.Plugin.pmAuth.getUsers('view')
          });
          frm.append(inp);
          p.append(frm);
           $("#pmAuthId div.ui-layout-center").html(p);
           
         }
    }
});


