<?php
//*************************************************************************************************
// FileName : data.php
// FilePath : /
// Author   : Christian Marty
// Date		: 01.08.2021
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/config.php";

global $devMode;
global $serverDataPath;

session_start();

if (!((isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true)||$devMode))
{
	echo "<p>User Session Invalid. Please Log In.<p>";
	exit;
}

$params = array();
$apiRequestParts = explode('data.php/', $_SERVER['REQUEST_URI']);

$filePath = $serverDataPath."/";
$apiRequest = explode('?',rawurldecode($apiRequestParts[1]))[0];
$filePath .= $apiRequest;

if(file_exists($filePath))
{
	if ((isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true)||$devMode) 
	{
		$filename = pathinfo($filePath)['filename'];
		$extension = pathinfo($filePath, PATHINFO_EXTENSION);
		
		header('Content-Description: File Transfer');
		header('Content-Type: application/'.$extension);
		header('Content-Disposition: inline; filename="'.$filename.'"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($filePath));
		readfile($filePath);
		exit;
	}
	else
	{
		echo "<p>User Authentication Failed</p>";
	}
}
else
{
	echo "<p>File not found</p>";
}

?>

