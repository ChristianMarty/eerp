<?php
//*************************************************************************************************
// FileName : process.php
// FilePath : /
// Author   : Christian Marty
// Date		: 25.10.2023
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/config.php";
global $devMode;
global $showPhpError;

if($showPhpError)
{
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
}


// TODO: Fix for prod version

header("Access-Control-Allow-Methods: POST, PATCH, GET, OPTIONS"); 


if($devMode)
{
	header("Access-Control-Allow-Origin: *");      
	header("Access-Control-Allow-Headers: *");
	//header("Access-Control-Allow-Headers:{$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
}

if($_SERVER['REQUEST_METHOD'] == 'OPTIONS')
{
	header("Allow: GET, PUT, OPTIONS"); 
	exit;
}

session_start();

$params = array();

$apiRequestParts = explode('process.php/', $_SERVER['REQUEST_URI']);

$filePath = "apiFunctions/process/";
$apiRequest = explode('?',$apiRequestParts[1])[0];
$filePath .= $apiRequest;
$filePath = rtrim($filePath, "/");

if(str_ends_with($apiRequestParts[1], '.css')) 
{
	//$filePath .= ".css";
}else{
	$filePath .= ".php";
}


if(!file_exists($filePath)) echo"Invalid URL";

if ((isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true)||$devMode) 
{
	require $filePath;
}
else
{
	echo "User Session Invalid. Please Log In.";
}

?>

