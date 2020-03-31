<?php
//-------------------------------------------------------------------------
// This file is part o digitizepoints, a plugin for p.mapper.
// It allow to digitize points into a PostgreSQL/PostGIS table.
// See http://www.pmapper.net/
//
// Copyright (C) 2009 Niccolo Rigacci
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Affero General Public License as
// published by the Free Software Foundation, either version 3 of the
// License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Affero General Public License for more details.
//
// You should have received a copy of the GNU Affero General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
// Author:       Niccolo Rigacci <niccolo@rigacci.org>
//-------------------------------------------------------------------------

//-------------------------------------------------------------------------
// Return the string 'left' or 'right'.
//-------------------------------------------------------------------------
function type_align($type) {
    print "<!-- type = $type -->\n";
    switch ($type) {
        case 'int2':
        case 'int4':
        case 'int8':
        case 'numeric':
        case 'float4':
        case 'float8':
            $align = 'right';
            break;
        default:
            $align = 'left';
    }
    return $align;
}

//-------------------------------------------------------------------------
// Return the string with proper html entities.
//-------------------------------------------------------------------------
function my_html($str) {
    return htmlentities($str, ENT_COMPAT, 'UTF-8');
}

//-------------------------------------------------------------------------
// Clean $_REQUEST values from automagically added escape chars.
//-------------------------------------------------------------------------
function clean_request($str) {
    if (get_magic_quotes_gpc()) {
        return stripslashes($str);
    } else {
        return $str;
    }
}

//-------------------------------------------------------------------------
// Return true if type is a number (PostgreSQL data type).
//-------------------------------------------------------------------------
function numeric_type($type) {
    switch ($type) {
        case 'int2':
        case 'int4':
        case 'int8':
        case 'numeric':
        case 'float4':
        case 'float8':
            return true;
            break;
        default:
            return false;
    }
}

//-------------------------------------------------------------------------
// Get column names and values from a submitted web form.
// Input field names are prefixed with $prefix.
//-------------------------------------------------------------------------
function get_columns_and_values($request, $prefix, $db) {
    $columns = array();
    $values  = array();
    $pl = strlen($prefix);
    foreach ($request as $key => $val) {
        if (substr($key, 0, $pl) != $prefix) continue;
        $val = clean_request($val); // Removes magic quotes, if any.
        if (substr($key, $pl + 1, 1) == 'c') {
            // This database field should be quoted.
            $val = $db->quoteSmart($val);
        } elseif ($val == '') {
            $val = $db->quoteSmart(NULL);
        } elseif (!is_numeric($val)) {
            $val = $db->quoteSmart($val);
        }
        $key = substr($key, $pl + 3);
        array_push($columns, $key);
        array_push($values,  $val);
    }
    return array($columns, $values);
}

//-------------------------------------------------------------------------
// Print a message in a web page and close the window after a timeout.
// Use JavaScript to close the window, $timeout is in milliseconds.
//-------------------------------------------------------------------------
function msg_and_close($msg, $color='green') {
    print '<h2><font color="' . $color . '">';
    print my_html($msg) . "</font></h2>\n";
    print "<p id=\"auto_close\">\n";
}
