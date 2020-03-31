<?php
require_once("../pmsession.php");
require_once("../globals.php");
require_once("../common.php");

$remoteurl = $_REQUEST['remoteurl'];
$http_referer = $_SERVER['HTTP_REFERER'];
$server_name = $_SERVER['SERVER_NAME'];
if (! preg_match("/$server_name/", $http_referer) ) exit();
?>
<html>
<head></head>
<body>
<iframe src="<?php echo $remoteurl  ?>" width="100%" height="99%">
</iframe>
</body>
</html>