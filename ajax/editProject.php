<?php
define("ABSPATH", dirname(dirname(__FILE__)));
require(ABSPATH."/functions.php");

$output = array();
if (empty($_POST["projectName"]))
{
	exit(json_encode(array("error" => "Missing parameters.")));
}

$projectName = $_POST["projectName"];
if ($_POST["type"] == 1)
{
	$projectId = $_POST["projectId"];
	$p = dbQuery("SELECT * FROM `bot_projects` WHERE `project_id` = '$projectId'", true);
	if ($p != null)
	{
		dbQuery("UPDATE `bot_projects` SET `project_name` = '$projectName' WHERE `project_id` = '$projectId'");
	}
	else
	{
		exit(json_encode(array("error" => "Project doesn't exist.")));
	}
}
else
{
	$p = dbQuery("SELECT * FROM `bot_projects` WHERE `project_name` = '$projectName'", true);
	if ($p != null)
	{
		exit(json_encode(array("error" => "Project '$projectName' already exists.")));
	}
	
	$newId = dbQuery("INSERT INTO `bot_projects` (`project_name`) VALUES ('$projectName')");
	$output["project_id"] = $newId;
}

echo json_encode($output);
?>