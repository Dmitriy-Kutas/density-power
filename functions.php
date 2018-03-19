<?php
session_start();

$environment = "stage";
//$environment = "production";

if ($environment == "stage")
{
	define('TIMEZONE', 'America/Sao_Paulo');
	define("HOMEURL", "http://localhost");
	define("DBHOST", "localhost");
	define("DBUSER", "root");
	define("DBPASS", "");
	define("DB", "bhavickbot");
}
else
{
	define('TIMEZONE', 'America/New_York');
	define("HOMEURL", "http://f4b6b6d.ddns.net");
	define("DBHOST", "bhavickbot.db.5458152.hostedresource.com");
	define("DBUSER", "bhavickbot");
	define("DBPASS", "nBwX7Rg!s");
	define("DB", "bhavickbot");
}

date_default_timezone_set(TIMEZONE);

function dbConnect()
{
	global $mysqli, $environment;
	$mysqli = mysqli_connect(DBHOST, DBUSER, DBPASS, DB);
	
	if (mysqli_connect_errno($mysqli)) 
	{
		echo $mysqli->error;
		return false;
	}
	
	if ($environment == "production")
	{
		dbQuery("SET time_zone='-4:00'");
	}
	
	return true;
}

function dbQuery($query, $single = false)
{
	global $mysqli;
	
	if (empty($mysqli))
	{
		dbConnect();
	}
	
	$output = array();
	$res = $mysqli->query($query);
	if ($res === FALSE)
	{
		echo $mysqli->error;
		die();
	}
	
	if (strpos($query, "SELECT") !== false)
	{
		while($row = $res->fetch_assoc())
		{
			$output[] = $row;
		}
		
		if ($single)
		{
			if (count($output) > 0)
			{
				$output = $output[0];
			}
			else
			{
				$output = null;
			}
		}
	}
	else if (strpos($query, "INSERT") !== false)
	{
		$output = $mysqli->insert_id;
	}
	
	return $output;
}

function countWords($text, $articleId, $projectId)
{
	if (empty($text)) return;
	
	/*
	$excludedWords = dbQuery("SELECT `excluded_word_text` FROM `bot_excluded_words` WHERE 1");
	$excluded = array();
	foreach($excludedWords as $word)
	{
		$excluded[strtolower($word["excluded_word_text"])] = 1;
	}
	*/
	$foundWords = array();
	if (preg_match_all("/\b[\w-]+\b/m", $text, $matches) !== false)
	{ 
		foreach($matches[0] as $match)
		{
			$word = strtolower($match);
// 			if (isset($excluded[$word])) continue; //skip exluded words
			
			if (!isset($foundWords[$word]))
			{
				$foundWords[$word] = array("article_id" => $articleId, "word_text" => $word, "word_count" => 1);
			}
			else
			{
				$foundWords[$word]["word_count"]++;
			}
		}
	}
	
	$includedWords = dbQuery("SELECT `phrase_id`, `phrase_text` FROM `bot_phrases` WHERE 1");
	foreach($includedWords as $phrase)
	{
		$word = strtolower($phrase["phrase_text"]);
		$phraseId = $phrase["phrase_id"];
		
		if (preg_match_all("/$word/mi", $text, $matches))
		{
			$foundWords[$word] = array("article_id" => $articleId, "phrase_id" => $phraseId, "word_count" => count($matches[0]));
		}
	}
	
	if (!empty($foundWords))
	{
		$insert = "";
		foreach($foundWords as $word)
		{
			if (isset($word["phrase_id"]))
			{
				$insert .= ", ('{$word["article_id"]}', '{$word["phrase_id"]}', NULL, '{$word["word_count"]}')";
			}
			else
			{
				$insert .= ", ('{$word["article_id"]}', NULL, '{$word["word_text"]}', '{$word["word_count"]}')";
			}
		}
			
		dbQuery("INSERT INTO `bot_words` (`article_id`, `phrase_id`, `word_text`, `word_count`) VALUES ".substr($insert, 2));
	}
	
}

function removeCountedWordFrom($articleId)
{
	dbQuery("DELETE FROM `bot_words` WHERE `article_id` = '$articleId'");
}

function fillChartDataForWord($projectId, $wordObj, &$chartData, $initDate, $endDate)
{
	if (!empty($wordObj["phrase_text"]))
	{
		$wordText = $wordObj["phrase_text"];
		$data = dbQuery("SELECT SUM(`w`.`word_count`) as 'total', UNIX_TIMESTAMP(`a`.`article_time`) as 'article_time' FROM `bot_words` `w`, `bot_articles` `a`, `bot_phrases` `p` WHERE `a`.`project_id` = '$projectId' AND UNIX_TIMESTAMP(DATE(`a`.`article_time`)) >= $initDate AND UNIX_TIMESTAMP(DATE(`a`.`article_time`)) <= $endDate AND `a`.`article_id` = `w`.`article_id` AND `w`.`phrase_id` = `p`.`phrase_id` AND `p`.`phrase_text` = '$wordText' GROUP BY DATE(`a`.`article_time`) ORDER BY `a`.`article_time` ASC");
	}
	else 
	{
		$wordText = $wordObj["word_text"];
		$data = dbQuery("SELECT SUM(`w`.`word_count`) as 'total', UNIX_TIMESTAMP(`a`.`article_time`) as 'article_time' FROM `bot_words` `w`, `bot_articles` `a` WHERE `a`.`project_id` = '$projectId' AND UNIX_TIMESTAMP(DATE(`a`.`article_time`)) >= $initDate AND UNIX_TIMESTAMP(DATE(`a`.`article_time`)) <= $endDate AND `a`.`article_id` = `w`.`article_id` AND `w`.`word_text` = '$wordText' GROUP BY DATE(`a`.`article_time`) ORDER BY `a`.`article_time` ASC");
	}
	
	$wordsCounted = dbQuery("SELECT SUM(`w`.`word_count`) as 'total', UNIX_TIMESTAMP(`a`.`article_time`) as 'article_time' FROM `bot_words` `w`, `bot_articles` `a` WHERE `a`.`project_id` = '$projectId' AND UNIX_TIMESTAMP(DATE(`a`.`article_time`)) >= $initDate AND UNIX_TIMESTAMP(DATE(`a`.`article_time`)) <= $endDate AND `a`.`article_id` = `w`.`article_id` GROUP BY DATE(`a`.`article_time`) ORDER BY `a`.`article_time` ASC");
	$wordsCountedPerDate = array();
	foreach($wordsCounted as $wordCount)
	{
		$time = $wordCount["article_time"];
		$nDays = floor(($time - $initDate)/(24*60*60));
		$wordsCountedPerDate[$nDays] = $wordCount["total"];
	}
	
	$pointPerDate = array();
	if (!isset($chartData[$wordText]))
	{
		$chartData[$wordText] = array();
	}
	
	foreach($data as $point)
	{
		$time = $point["article_time"];
		$nDays = floor(($time - $initDate)/(24*60*60));
		$pointPerDate[$nDays] = array($point["total"], $wordsCountedPerDate[$nDays]);
	}
	
	$chartData[$wordText] = $pointPerDate; 
	/*
	$interval = floor(($endDate - $initDate)/(24*60*60));
	for ($i = 0; $i <= $interval; $i++)
	{
		if (isset($pointPerDate[$i]))
		{
			$chartData[$wordText][] = $pointPerDate[$i]["total"];
		}
		else
		{
			$chartData[$wordText][] = 0;
		}
	}
	*/
}

function getProjectConfig($projectId, $configKey)
{
	$c = dbQuery("SELECT `config_value` FROM `bot_config` WHERE `config_key` = '$configKey' AND `project_id` = '$projectId'", true);
	if ($c)
	{
		return $c["config_value"];
	}
	
	return null;
}

function setProjectConfig($projectId, $configKey, $configValue)
{
	$c = dbQuery("SELECT `config_id` FROM `bot_config` WHERE `config_key` = '$configKey' AND `project_id` = '$projectId'", true);
	if ($c)
	{
		$configId = $c["config_id"];
		dbQuery("UPDATE `bot_config` SET `config_value` = '$configValue' WHERE `config_id` = '$configId'");
	}
	else
	{
		dbQuery("INSERT INTO `bot_config` (`project_id`, `config_key`, `config_value`) VALUES ('$projectId', '$configKey', '$configValue')");
	}
}

function getArticles($projectId, $initTimestamp, $endTimestamp)
{
	$articles = dbQuery("SELECT `article_text` FROM `bot_articles` WHERE `project_id` = '$projectId' AND UNIX_TIMESTAMP(DATE(`article_time`)) >= $initTimestamp AND UNIX_TIMESTAMP(DATE(`article_time`)) <= $endTimestamp ORDER BY `article_time` ASC, `article_id` ASC");
	return $articles;
}

function getArticleIds($projectId, $initTimestamp)
{
	$articles = dbQuery("SELECT `article_id`, `article_text` FROM `bot_articles` WHERE `project_id` = '$projectId' AND DATE(`article_time`) >= '$initTimestamp' ORDER BY `article_time` ASC, `article_id` ASC");
	return $articles;
}

function getWords($projectId)
{
	$words = dbQuery("SELECT a.`word_text` FROM `bot_words` a, `bot_projects` b, `bot_articles` c WHERE b.`project_id` = '$projectId' AND b.`project_id` = c.`project_id` AND a.`article_id` = c.`article_id` GROUP BY a.`word_text`");
	return $words;
}
