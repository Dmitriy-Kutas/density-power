<?php
define("ABSPATH", dirname(dirname(__FILE__)));
require(ABSPATH."/functions.php");

$output = array();
if (empty($_POST["article"]) || empty($_POST["projectId"]))
{
	exit(json_encode(array("error" => "Missing parameters.")));
}

if (empty($_POST["article"]["articleText"]) /* || empty($_POST["article"]["articleUrl"])*/)
{
	exit(json_encode(array("error" => "Missing parameters.")));
}

if (!isset($_POST["article"]["articleUrl"]))
{
	$_POST["article"]["articleUrl"] = "";
}

global $mysqli;
dbConnect();

$serverTimezoneDiff = 240 - $_POST["timezone"];
$article = $_POST["article"];
//$article["articleTime"] = $article["articleTime"]/1000 + $serverTimezoneDiff*60; //seconds precision on database
$article["articleTime"] = $article["articleTime"]/1000;
$projectId = $_POST["projectId"];
$article["articleText"] = $mysqli->real_escape_string($article["articleText"]);
$article["articleUrl"] = $mysqli->real_escape_string($article["articleUrl"]);
if ($_POST["type"] == 1)
{
	$articleId = $article["articleId"];
	$a = dbQuery("SELECT * FROM `bot_articles` WHERE `article_id` = '$articleId'", true);
	if ($a != null)
	{
		dbQuery("UPDATE `bot_articles` SET `article_url` = '{$article["articleUrl"]}', `article_time` = DATE(FROM_UNIXTIME('{$article["articleTime"]}')), `article_text` = '{$article["articleText"]}' WHERE `article_id` = '$articleId'");
		
		//vfranchi - check if text changed to recount words
		if (crc32($a["article_text"]) != crc32($article["articleText"]))
		{
			removeCountedWordFrom($articleId);
			countWords($article["articleText"], $articleId, $projectId);
		}
		
		$output["status"] = "success";
	}
	else
	{
		exit(json_encode(array("error" => "This article is not on the database.")));
	}
}
else
{
	if ($article["articleUrl"] != "")
	{
		$a = dbQuery("SELECT `article_id` FROM `bot_articles` WHERE `article_url` = '{$article["articleUrl"]}'", true);
		if ($a != null)
		{
			exit(json_encode(array("error" => "Article with that URL already exists.")));
		}
	}	
	$newId = dbQuery("INSERT INTO `bot_articles` (`project_id`, `article_url`, `article_time`, `article_text`) VALUES ('$projectId', '{$article["articleUrl"]}', DATE(FROM_UNIXTIME('{$article["articleTime"]}')), '{$article["articleText"]}')");
	$output["article_id"] = $newId;
	$output["status"] = "success";
	
	countWords($article["articleText"], $newId, $projectId);
	setProjectConfig($projectId, "end_date", time());
}

$configInit = getProjectConfig($projectId, "init_date");
if ($configInit && $configInit > $article["articleTime"])
{
	setProjectConfig($projectId, "init_date", $article["articleTime"]);
}
echo json_encode($output);
?>