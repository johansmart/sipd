<?php

// JavaScript files referenced in map.phtml
$jsFiles  = array('digitizepoints.js');

// Init function to be executed at application's loading time
$jsInitFunction = 'PM.Plugin.Digitizepoints.init()';

// PHP files loaded into map.phtml:
// enable PHP code only if plugin is activated
$phpFiles = array('activate.php');
