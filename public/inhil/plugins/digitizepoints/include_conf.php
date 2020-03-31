<?php

//-------------------------------------------------------------------------
// Digitizepoint plugin configuration.
//-------------------------------------------------------------------------
$dsn        = 'pgsql://dbuser:password@localhost:5432/dbname';
$db_table   = 'tablename';       // Table name.
$pkey       = 'id';              // Table's primary key.
$the_geom   = 'the_geom';        // Table's geometry field, type POINT.
$srid_geom  = 4326;              // SRID of the geometry.
$srid_map   = 3035;              // SRID of the map (clicked screen point).
$tolerance  = 500;               // Distance in meteres to pick existing point for editing.
$hide_fields = array('id');      // Table fields not displayed in the edit form.

?>
