<?php

session_start();

require_once($_SESSION['PM_PLUGIN_REALPATH'].'/pmauth/incphp/db.php');
require_once($_SESSION['PM_PLUGIN_REALPATH'].'/pmauth/incphp/auth.php');
$db = new Db();
// only autenticated user can use ajax script
$a = Auth::getInstance($db);
$a->getAuth();
