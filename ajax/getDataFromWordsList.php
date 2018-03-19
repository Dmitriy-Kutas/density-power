<?php
define("ABSPATH", dirname(dirname(__FILE__)));
require(ABSPATH."/functions.php");

$output = array();
if (empty($_POST["projectId"]) /*|| empty($_POST["activeWords"]) */|| empty($_POST["initDate"]) || empty($_POST["endDate"]))
{
	exit(json_encode(array("error" => "Missing parameters.")));
}

//unescape strings
if (get_magic_quotes_gpc()) 
{
	$process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
	while (list($key, $val) = each($process)) {
		foreach ($val as $k => $v) {
			unset($process[$key][$k]);
			if (is_array($v)) {
				$process[$key][stripslashes($k)] = $v;
				$process[] = &$process[$key][stripslashes($k)];
			} else {
				$process[$key][stripslashes($k)] = stripslashes($v);
			}
		}
	}
	unset($process);
}

$projectId = $_POST["projectId"];

if(isset($_POST["kind"])) {
	$kind = $_POST["kind"];
}

if(isset($kind) && ($kind == "density")) {
	$output["data"] = getArticleIds($projectId, $_POST["initDate"]);
} else {
	$serverTimezoneDiff = 240 - $_POST["timezone"];
	$initDate = new DateTime();
	$initDate->setTimestamp($_POST["initDate"] + $serverTimezoneDiff*60);
	$endDate = new DateTime();
	$endDate->setTimestamp($_POST["endDate"] + $serverTimezoneDiff*60);

	$output["data"] = getArticles($projectId, $initDate->getTimestamp(), $endDate->getTimestamp());
	setProjectConfig($projectId, "init_date", $initDate->getTimestamp());
	setProjectConfig($projectId, "end_date", $endDate->getTimestamp());
}

echo json_encode($output);
?>