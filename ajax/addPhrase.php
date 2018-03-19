<?php
define("ABSPATH", dirname(dirname(__FILE__)));
require(ABSPATH."/functions.php");

$output = array();
if (empty($_POST["phraseText"]))
{
	exit(json_encode(array("error" => "Missing parameters.")));
}

$phrase = $_POST["phraseText"];
$p = dbQuery("SELECT * FROM `bot_phrases` WHERE `phrase_text` = '$phrase'", true);
if ($p != null)
{
	exit(json_encode(array("error" => "Excluded word '$phrase' already exists.")));
}

$newId = dbQuery("INSERT INTO `bot_phrases` (`phrase_text`) VALUES ('$phrase')");
$output["phrase_id"] = $newId;

echo json_encode($output);
?>