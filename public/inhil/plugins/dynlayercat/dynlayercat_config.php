<?php

// TODO : This should move to plugin conf ?
$connections = array();
$connections['pg:gis_sdi'] = array(
    "type" => "POSTGIS", 
    "connection" => "dbname=gis_sdi host=localhost user='gis_sdi_user' password='password'"
);
?>