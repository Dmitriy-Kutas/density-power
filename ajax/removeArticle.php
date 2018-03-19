<?php
define("ABSPATH", dirname(dirname(__FILE__)));
require(ABSPATH."/functions.php");

$output = array();
if (empty($_POST["articleId"]))
{
	exit(json_encode(array("error" => "Missing parameters.")));
}

$articleId = $_POST["articleId"];
$a = dbQuery("SELECT * FROM `bot_articles` WHERE `article_id` = '$articleId'", true);
if ($a != null)
{
	dbQuery("DELETE FROM `bot_articles` WHERE `article_id` = '$articleId'");
	$output["status"] = "success";
}
else
{
	exit(json_encode(array("error" => "Article doesn't exist.")));
}

echo json_encode($output);
?>