<?php
define("ABSPATH", dirname(dirname(__FILE__)));
require(ABSPATH."/functions.php");

$output = array();
if (empty($_POST["excludedWordId"]))
{
	exit(json_encode(array("error" => "Missing parameters.")));
}

$excludedWordId = $_POST["excludedWordId"];
$eW = dbQuery("SELECT * FROM `bot_excluded_words` WHERE `excluded_word_id` = '$excludedWordId'", true);
if ($eW != null)
{
	dbQuery("DELETE FROM `bot_excluded_words` WHERE `excluded_word_id` = '$excludedWordId'");
	$output["status"] = "success";
}
else
{
	exit(json_encode(array("error" => "Excluded word doesn't exist.")));
}

echo json_encode($output);
?>