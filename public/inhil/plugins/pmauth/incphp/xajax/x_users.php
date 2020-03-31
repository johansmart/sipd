<?php
/******************************************************************************
*
* Purpose: Main CRUD user php script
* Author:  Walter Lorenzetti, gis3w, lorenzetti@gis3w.it
*
******************************************************************************
*
* Copyright (c) 2008-2010 gis3w
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
require_once("x_common.php");
// check if user has a role admin
header('Content-type:application/json');

if($a->id_role != 0){ exit();}

// get http var
if(isset($_POST['mod'])) $mod = $_POST['mod'];
if(isset($_POST['idU'])) $idU = $_POST['idU'];
$data = isset($_POST['data']) ? json_decode(stripslashes($_POST['data'])) : NULL;

require_once($_SESSION['PM_INCPHP'] . "/common.php");
require_once($_SESSION['PM_INCPHP'] . "/globals.php");

switch($mod){
  case "ckuser":
    // check username in db
    $res = $db->checkData('pmauth_users',array('username'=>$_POST['username']));
    $res = $res ? 'true' : 'false';
    echo '{"userindb":'.$res.'}';
    exit();
  break;

  case "view":
  case "edit":
  case "todel":
  	//get data from tb pmauth_users from db
    $and = "";
    if($mod == "edit" || $mod == "todel") $and = " AND a.id = ".$idU;
    $q = "SELECT a.id, a.username, b.role, c.configs FROM pmauth_users a, pmauth_roles b, pmauth_configs c WHERE a.id_role = b.id and a.id = c.id_users ".$and." ORDER BY a.username";
    $users = $db->eQuery($q);
    foreach($users as $i => $d){
      $users[$i]['configs'] = unserialize($users[$i]['configs']);
    }

    $dtUsers = '{"users":'.json_encode($users).'}';
    
    echo $dtUsers;
    exit();
  break;
  
  case "save":
    // retrieve data as object
//    $data = json_decode($data);
    $upDataAcc = $upDataAccCfg = $keyForUpAcc = "";
    $fields = $values = "";
    //error_log(print_r($data,true));
    if(isset($data->idU) && $data->idU != ""){
     // update
     $keyForUpAcc = $data->idU;
     foreach($data as $c => $v){
        if($c != 'idU'){
          $pre = $post = "";
          if(is_string($v)){$pre = $post = "'";}
          if($c == 'configs'){
            $upDataAccCfg = ",".$c."='".serialize(array('def'=>$v->def, 'cfgs'=>$v->cfgs))."'";
          }else{
            $upDataAcc .= ",".$c."=".$pre.$v.$post;
          }
        }
     }
     $upDataAcc = substr($upDataAcc,1); $upDataAccCfg = substr($upDataAccCfg,1);
     $q[] = "UPDATE pmauth_users SET ".$upDataAcc." WHERE id = ".$keyForUpAcc;
     $q[] = "UPDATE pmauth_configs SET ".$upDataAccCfg." WHERE id_users = ".$keyForUpAcc;
    }else{
      // insert
      foreach($data as $c => $v){
        if($c != 'idU'){
          $pre = $post = "";
          if(is_string($v)){$pre = $post = "'";}
          if($c == 'configs'){
            $fieldsCfg = $c;
            $valuesCfg = "'".serialize(array('def'=>$v->def, 'cfgs'=>$v->cfgs))."'";
          }else{
            $fields .= ",".$c;
            $values .= ",".$pre.$v.$post;
          }
        }
     }
     $fields = substr($fields,1);
     $values = substr($values,1);
     // adding the id_user in the pmauth_configs
     $fieldsCfg .= ',id_users';
     switch($db->driver){
         case 'sqlite':
             $valuesCfg .= ",last_insert_rowid()";
         break;

         default:
             $valuesCfg .= ",currval('pmauth_users_id_seq')";
     }
     
     $q[] = "INSERT INTO pmauth_users (".$fields.") VALUES (".$values.")";
     $q[] = "INSERT INTO pmauth_configs(".$fieldsCfg.") VALUES (".$valuesCfg.")";
   }
    
 break;

 case "del":
   $keyForUpAcc = $data->idU;
   $q[] = "DELETE FROM pmauth_users WHERE id = ".$keyForUpAcc;
   // erase from pmauth_configs
   $q[] = "DELETE FROM pmauth_configs WHERE id_users = ".$keyForUpAcc;
 break;
  
  
  
  default:
  	
}
// query execute
if(!$db->transaction($q)){
echo '{"errDb":true, "errMsg":"An error was occured "'.$db->id.'}';
  }else{
echo '{"errDb":false}';
}

