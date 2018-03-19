<?php
define("ABSPATH", dirname(__FILE__));
require(ABSPATH."/functions.php");

if (empty($_GET["page"]))
{
	$_GET["page"] = "";
}
$page = $_GET["page"];

if ($page == "" && empty($_SESSION["selected-project"]))
{
	header("Location: /projects");
	exit;
}

include(ABSPATH."/header.php");
switch($page)
{
	case "":
		require(ABSPATH."/report.php");
		break;
	case "articles":
		require(ABSPATH."/articles.php");
		break;
	case "projects":
		require(ABSPATH."/projects.php");
		break;
	case "phrases":
		require(ABSPATH."/phrases.php");
		break;
	case "excluded-words":
		require(ABSPATH."/excluded-words.php");
		break;
	case "density-power":
		require(ABSPATH."/density-power.php");
		break;
	default:
		break;
}
include(ABSPATH."/footer.php");
?>
