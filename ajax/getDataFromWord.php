<?php
define("ABSPATH", dirname(dirname(__FILE__)));
require(ABSPATH."/functions.php");

$output = array();
if (empty($_POST["projectId"]) || empty($_POST["text"]) || empty($_POST["initDate"]) || empty($_POST["endDate"]))
{
	exit(json_encode(array("error" => "Missing parameters.")));
}

$serverTimezoneDiff = 240 - $_POST["timezone"];
$initDate = new DateTime();
$initDate->setTimestamp($_POST["initDate"] + $serverTimezoneDiff*60);
$endDate = new DateTime();
$endDate->setTimestamp($_POST["endDate"]  + $serverTimezoneDiff*60);
$projectId = $_POST["projectId"];
$wordText = $_POST["text"];
if ($_POST["isPhrase"] == "true")
{
	$wordObj = array("phrase_text" => $wordText);
}
else
{
	$wordObj = array("word_text" => $wordText);
}
$chartData = array();
fillChartDataForWord($projectId, $wordObj, $chartData, $initDate->getTimestamp(), $endDate->getTimestamp());
setProjectConfig($projectId, "init_date", $initDate->getTimestamp());
setProjectConfig($projectId, "end_date", $endDate->getTimestamp());
$output["data"] = $chartData[$wordText];
echo json_encode($output);
?>