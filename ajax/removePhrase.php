<?php
define("ABSPATH", dirname(dirname(__FILE__)));
require(ABSPATH."/functions.php");

$output = array();
if (empty($_POST["phraseId"]))
{
	exit(json_encode(array("error" => "Missing parameters.")));
}

$phraseId = $_POST["phraseId"];
$p = dbQuery("SELECT * FROM `bot_phrases` WHERE `phrase_id` = '$phraseId'", true);
if ($p != null)
{
	dbQuery("DELETE FROM `bot_phrases` WHERE `phrase_id` = '$phraseId'");
	$output["status"] = "success";
}
else
{
	exit(json_encode(array("error" => "Included phrase doesn't exist.")));
}

echo json_encode($output);
?>