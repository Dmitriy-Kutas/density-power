<?php
define("ABSPATH", dirname(dirname(__FILE__)));
require(ABSPATH."/functions.php");

$output = array();
if (empty($_POST["projectId"]))
{
	exit(json_encode(array("error" => "Missing parameters.")));
}

$projectId = $_POST["projectId"];
$p = dbQuery("SELECT * FROM `bot_projects` WHERE `project_id` = '$projectId'", true);
if ($p != null)
{
	dbQuery("DELETE FROM `bot_projects` WHERE `project_id` = '$projectId'");
	$output["status"] = "success";
	if ($_SESSION["selected-project"] == $projectId)
	{
		unset($_SESSION["selected-project"]);
	}
}
else
{
	exit(json_encode(array("error" => "Project doesn't exist.")));
}

echo json_encode($output);
?>