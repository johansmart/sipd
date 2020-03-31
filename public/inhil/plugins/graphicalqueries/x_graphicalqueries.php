<?php
// prevent XSS
if (isset($_REQUEST['_SESSION'])) exit();

require_once('../../incphp/group.php');
require_once('../../incphp/pmsession.php');
require_once($_SESSION['PM_INCPHP'] . '/globals.php');
require_once($_SESSION['PM_INCPHP'] . '/common.php');
require_once('queryExtended.php');
require_once($_SESSION['PM_INCPHP'] . '/query/search.php');
require_once($_SESSION['PM_PLUGIN_REALPATH'] . '/common/selectTools.inc.php');


header("Content-type: text/plain; charset=$defCharset");

$operation = $_REQUEST['operation'];

// Old selection
$jsonPMResult = $_SESSION['JSON_Results'];

$queryType = $_REQUEST['select_type'];

if ($queryType == 'rectangle') {
	$queryType = 'polygon';
}

// Run QUERY
$mapQuery = new QueryExtended($map, $queryType, $poly);
$mapQuery->q_processQuery();
$queryResult = $mapQuery->q_returnQueryResult();

$queryResult = SelectTools::mixSelection($_REQUEST['selectMethode'], $jsonPMResult, $queryResult);

if ($queryResult) {
	// Update selection
	$_SESSION['JSON_Results'] = $queryResult;

	// Highlight
	SelectTools::updateHighlightJson($queryResult);

// Empty selection
} else {
	unset ($_SESSION['JSON_Results']);
	unset ($_SESSION['resultlayers']);

	unset ($queryResult);
}

$mode = $_REQUEST['mode'];

if (isset($queryResult)) {
	echo "{\"mode\":\"$mode\", \"queryResult\":$queryResult}";
} else {
	// remove selection case
	echo "{\"mode\":\"$mode\", \"queryResult\":0}";
}
?>