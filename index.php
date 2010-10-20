<?php
$start = microtime(true);

global $smarty, $conn;

session_start();

require_once("include.php");

//Handle case where user wants to logout
if (!isset($_SESSION[userId]) || $_REQUEST['p'] == 'logout')
{
	$_SESSION[userId] = null;
}
else
{
	$user = $conn->queryRow("SELECT * FROM users WHERE user_id = $_SESSION[userId]");
	$userId = $user[user_id];
}

if (!isset($pageName))
    $pageName = $_REQUEST['p'];

if (strlen($pageName) == 0)
    $pageName = 'resource';

switch ($pageName)
{
	case "login":
	case "register":
		break;
	default:
		if (!isset($userId)) $pageName = "login";
		break;
}

//Setup page information.
$exists = file_exists("pages/$pageName.php");
if ($exists && strpos($pageName,".") === FALSE && strpos($pageName,"/") === FALSE)
{
	include("pages/$pageName.php");
}

echo "<center><small>falco struggled for " . round((microtime(true) - $start)*1000,1)."ms to bring you this page</small></center>";
?>
