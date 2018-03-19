<?php
define("ABSPATH", dirname(dirname(__FILE__)));
require(ABSPATH."/functions.php");

$output = array();
if (empty($_POST["excludedWordText"]))
{
	exit(json_encode(array("error" => "Missing parameters.")));
}

$excludedWord = $_POST["excludedWordText"];
$eW = dbQuery("SELECT * FROM `bot_excluded_words` WHERE `excluded_word_text` = '$excludedWord'", true);
if ($eW != null)
{
	exit(json_encode(array("error" => "Excluded word '$excludedWord' already exists.")));
}

$newId = dbQuery("INSERT INTO `bot_excluded_words` (`excluded_word_text`) VALUES ('$excludedWord')");
$output["excluded_word_id"] = $newId;

echo json_encode($output);
?>