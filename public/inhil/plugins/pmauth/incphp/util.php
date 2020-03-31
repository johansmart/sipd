<?php
/******************************************************************************
 *
 * Purpose: Utility
 * Author:  Walter Lorenzetti
 *
 ******************************************************************************
 *
 * Copyright (c) 2008-2009 Walter Lorenzetti
 *
 * This file is part of software buste paga.
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
 *****************************************************************************/

class Util {
	public static $newSword;
  
	
	public static function bldRndSword(){
   	$ap = array();
  	  // for #, &, % 
    for ($i=35; $i<38; $i++){$ap[] = chr($i);}
  	  // for number
  	for ($i=48; $i<58; $i++){$ap[] = chr($i);}
  	  // for uppercase letter
  	for ($i=64; $i<91; $i++){$ap[] = chr($i);}
  	  // for lowcase letter
  	for ($i=97; $i<123; $i++){$ap[] =chr($i);}
  	
		self::$newSword = '';
		$to = rand(7,13);
  	for ($i=0; $i<$to; $i++ ){
  		self::$newSword .= $ap[rand(0,count($ap))];
  	}
		return self::$newSword;
	}
}


class minInit_map extends Init_map {
    
    public function __construct() {}
    
    public function __get($name) {
        if($name == 'jsReference'){
            return $this->initJSReference();
        }else{
            return parent::__get($name);
        }
    }
    
}
?>