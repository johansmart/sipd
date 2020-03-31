<?php
//-------------------------------------------------------------------------
// This file is part o digitizepoints, a plugin for p.mapper.
// It allow to digitize points into a PostgreSQL/PostGIS table.
// See http://www.pmapper.net/
//
// Copyright (C) 2009 Niccolo Rigacci, Thomas Raffin
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
// Authors:      Niccolo Rigacci <niccolo@rigacci.org>
//               Thomas Raffin
//-------------------------------------------------------------------------

// prevent XSS
if (isset($_REQUEST['_SESSION'])) exit();
session_start();

// If plugin is not activated, do not execute.
if (!isset($_SESSION['digitizepoints_activated']) or !$_SESSION['digitizepoints_activated']) {
    exit();
}


require_once($_SESSION['PM_INCPHP'] . '/common.php');
require_once($_SESSION['PM_INCPHP'] . '/globals.php');
require_once('DB.php');
require_once('include.php');
require_once('include_conf.php');

// TODO:
// * Now the pkey must be numeric, add proper escaping if needed.
// * Trap errors on fetchRow, when getting lon/lat of existing point.

$distance  = 'st_distance';     // Name for 'SELECT ... AS', must not conflict with other table fields.
$prefix    = '__db';            // Prefix for database fields used into the html form.

print "<html>\n";
print "<head>\n";
print "</head>\n";
print "<body>\n";

$lon = (float)$_REQUEST['lon'];
$lat = (float)$_REQUEST['lat'];

//------------------------------------------------------------------------
// Connect to the database.
//------------------------------------------------------------------------
$db = DB::connect($dsn, true);
if (DB::isError($db)) die ($db->getMessage());


//------------------------------------------------------------------------
// What to do?
//------------------------------------------------------------------------
$action = isset($_REQUEST['__action']) ? $_REQUEST['__action'] : false;
switch($action) {

    //--------------------------------------------------------------------
    // Do insert.
    //--------------------------------------------------------------------
    case 'insert':
        // Get all the fields from the web form.
        list($columns, $values) = get_columns_and_values($_REQUEST, $prefix, $db);
        // Add the geometry.
        $val = sprintf('ST_SetSRID(ST_MakePoint(%f, %f), %d)', $lon, $lat, $srid_map);
        if ($srid_geom != $srid_map) $val = "ST_Transform($val, $srid_geom)";
        array_push($columns, $the_geom);
        array_push($values,  $val);
        // Make the SQL statement.
        $sql  = 'INSERT INTO ' . $db_table . ' (' . implode(', ', $columns) . ')';
        $sql .= ' VALUES (' . implode(', ', $values) . ')';
        $result = $db->query($sql);
        if (DB::isError($result)) {
            print "<b>Error executing statement:</b> " . my_html($sql) ."<p>\n";
            die ($result->getMessage());
        } else {
            msg_and_close(_p('Insert successful.'));
        }
        break;

    //--------------------------------------------------------------------
    // Do update.
    //--------------------------------------------------------------------
    case 'update':
        // Get the record ID.
        $id = (int)$_REQUEST['__id'];
        // Get all the fields from the web form.
        list($columns, $values) = get_columns_and_values($_REQUEST, $prefix, $db);
        // Add the geometry.
        $val = sprintf('ST_SetSRID(ST_MakePoint(%f, %f), %d)', $lon, $lat, $srid_map);
        if ($srid_geom != $srid_map) $val = "ST_Transform($val, $srid_geom)";
        array_push($columns, $the_geom);
        array_push($values,  $val);
        // Make the SQL statement.
        $sql  = 'UPDATE ' . $db_table . ' SET (' . implode(', ', $columns) . ')';
        $sql .= ' = (' . implode(', ', $values) . ')';
        $sql .= ' WHERE ' . $pkey . ' = ' . $id;
        $result = $db->query($sql);
        if (DB::isError($result)) {
            print "<b>Error executing statement:</b> " . my_html($sql) ."<p>\n";
            die ($result->getMessage());
        } else {
            msg_and_close(_p('Update successful.'));
        }
        break;

    //--------------------------------------------------------------------
    // Do delete.
    //--------------------------------------------------------------------
    case 'delete':
        // Get the record ID.
        $id = (int)$_REQUEST['__id'];
        // Make the SQL statement.
        $sql  = 'DELETE FROM ' . $db_table . ' WHERE ' . $pkey . ' = ' . $id;
        $result = $db->query($sql);
        if (DB::isError($result)) {
            print "<b>Error executing statement:</b> " . my_html($sql) ."<p>\n";
            die ($result->getMessage());
        } else {
            msg_and_close(_p('Delete successful.'));
        }
        break;

    //--------------------------------------------------------------------
    // Get points near the clik and get table info.
    //--------------------------------------------------------------------
    default:
        $point = sprintf("ST_PointFromText('POINT(%f %f)', %d)", $lon, $lat, $srid_map);
        $geom_ll = $the_geom;

        // Function ST_Distance_Sphere() requires EPSG:4326 lon/lat points.
        if ($srid_map  != 4326) $point   = "ST_Transform($point, 4326)";
        if ($srid_geom != 4326) $geom_ll = "ST_Transform($the_geom, 4326)";

        $sql  = 'SELECT *, ST_Distance_Sphere(%s, %s) AS %s';
        $sql .= ' FROM %s WHERE ST_Distance_Sphere(%s, %s) < %f';
        $sql .= ' ORDER BY %s';
        $sql = sprintf($sql, $geom_ll, $point, $distance, $db_table, $geom_ll, $point, $tolerance, $distance);
        print '<!-- ' . my_html($sql) . " -->\n";
        $result = $db->query($sql);
        if (DB::isError($result)) die ($result->getMessage());
        $tableinfo = $result->tableInfo();

        // If there is a near point, we will do an update.
        if (!isset($_REQUEST['addnew']) and $result->numRows() > 0) {
            $record = $result->fetchRow(DB_FETCHMODE_ASSOC);
            $point = $the_geom;
            if ($srid_geom != $srid_map) $point = "ST_Transform($point, $srid_map)";
            $sql = 'SELECT ST_X(%s), ST_Y(%s) FROM %s WHERE %s = %s';
            $sql = sprintf($sql, $point, $point, $db_table, $pkey, $record[$pkey]);
            list($point_lon, $point_lat) = $db->query($sql)->fetchRow(DB_FETCHMODE_ORDERED);
            $new_record = false;
            $action = 'update';
            $id = $record[$pkey];
        } else {
            list($point_lon, $point_lat) = array($lon, $lat);
            $new_record = true;
            $action = 'insert';
            $id = '';
        }

        //------------------------------------------------------------------------
        // Display the insert/update form.
        //------------------------------------------------------------------------
        $html = '';
        $heading = ($new_record) ? _p('Insert new point') : _p('Update point');
        $html .= '<h2>' . $heading . "</h2>\n";
        $html .= '<form id="digitizepoints_form" name="inputform" method="post" action="' . $_SERVER['SCRIPT_NAME'] . '">' . "\n";
        $html .= '<input type="hidden" name="__action" value="' . $action . "\">\n";
        $html .= '<input type="hidden" name="__id"        id="point_id"  value="' . my_html($id) . "\">\n";
        $html .= '<input type="hidden" name="__click_lon" id="click_lon" value="' . my_html($lon) . "\">\n";
        $html .= '<input type="hidden" name="__click_lat" id="click_lat" value="' . my_html($lat) . "\">\n";

        // Display the form for record insert/update.
        $html .= "<table>\n";
        foreach ($tableinfo as $f) {
            if (in_array($f['name'], $hide_fields)) continue;
            if ($f['name'] == $the_geom) continue;
            if ($f['name'] == $distance) continue;
            $align = numeric_type($f['type']) ? 'right' : 'left';
            $type = numeric_type($f['type']) ? 'n': 'c';
            $html .= '<tr><th>' . my_html($f['name']) . "</th><td align=\"${align}\">";
            if ($new_record) {
                $value = '';
            } else {
                $value = $record[$f['name']];
            }
            $html_name = $prefix . '_' . $type . '_' . $f['name'];
            $html .= sprintf('<input type="text" size="36" name="%s" value="%s">', my_html($html_name), my_html($value));
            $html .= "</td></tr>\n";
        }
        // Input fields for longitude and latitude.
        $html .= '<tr><th>' . _p('Longitude') . '</th><td align="right">';
        $html .= sprintf('<input type="text" size="36" name="lon" value="%s">', my_html($point_lon));
        $html .= "</td></tr>\n";
        $html .= '<tr><th>' . _p('Latitude') . '</th><td align="right">';
        $html .= sprintf('<input type="text" size="36" name="lat" value="%s">', my_html($point_lat));
        $html .= "</td></tr>\n";

        $addnew_url = sprintf('?addnew=yes&lon=%f&lat=%f', $lon, $lat);
        $delete_url = sprintf('?__action=delete&__id=%d', my_html($id));
        $disabled = $new_record ? 'disabled' : '';

        $html .= "<tr><th>&nbsp;</th><td>\n";
        $html .= '<input type="button" value="' . _p('Save')   . "\" onClick=\"javascript: PM.Plugin.Digitizepoints.pntSave();\" />\n";
        $html .= '<input type="button" value="' . _p('Cancel') . "\" onClick=\"javascript: PM.Plugin.Digitizepoints.closeDlg();\" />\n";
        $html .= "<p>\n";
        $html .= '<input type="button" value="' . _p('Delete point')         . '" onClick="javascript: if (!confirm(\'Delete point?\')) return false; PM.Plugin.Digitizepoints.pntDelete();"' . $disabled . ">\n";
        $html .= '<input type="button" value="' . _p('Do not edit, add new') . '" onClick="javascript: PM.Plugin.Digitizepoints.pntAddNew();"' . $disabled . ">\n";
        $html .= "</td>\n";
        $html .= "</table>\n";
        $html .= "</form>\n";
        $html .= "</body>\n";
        $html .= "</html>\n";

        echo $html;
        break;
}

$db->disconnect();
