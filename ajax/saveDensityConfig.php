<?php
define("ABSPATH", dirname(dirname(__FILE__)));
require(ABSPATH."/functions.php");

$output = array();
if (empty($_POST["projectId"]) || empty($_POST["density_option"]))
{
	exit(json_encode(array("error" => "Missing parameters.")));
}

//unescape strings
if (get_magic_quotes_gpc()) 
{
	$process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
	while (list($key, $val) = each($process)) {
		foreach ($val as $k => $v) {
			unset($process[$key][$k]);
			if (is_array($v)) {
				$process[$key][stripslashes($k)] = $v;
				$process[] = &$process[$key][stripslashes($k)];
			} else {
				$process[$key][stripslashes($k)] = stripslashes($v);
			}
		}
	}
	unset($process);
}

$projectId = $_POST["projectId"];

setProjectConfig($projectId, "density_option", $_POST["density_option"]);

echo true;
?>