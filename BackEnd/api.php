<?php
//*************************************************************************************************
// FileName : api.php
// FilePath : /
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
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

header("Content-Type:application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, PATCH, GET, OPTIONS"); 

if($devMode)
{
	header("Access-Control-Allow-Origin: *");      
	header("Access-Control-Allow-Headers: *");
}

if($_SERVER['REQUEST_METHOD'] == 'OPTIONS')
{
	header("Allow: GET, PUT, OPTIONS"); 
	exit;
}

session_start();

$params = array();

$apiRequestParts = explode('api.php/', $_SERVER['REQUEST_URI']);

$filePath = "apiFunctions/";
$apiRequest = explode('?',$apiRequestParts[1])[0];
$filePath .= $apiRequest;
$filePath = rtrim($filePath, "/");
$filePath .= ".php";

if(!file_exists($filePath)) sendResponse(null, "Invalid URL");

if( $apiRequest == "user/login" || $apiRequest == "user/logout" || $apiRequest == "user/info")
{
	require $filePath;
}
else if ((isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true)||$devMode) 
{
	require $filePath;
}
else
{
	sendResponse(null, "User Session Invalid. Please Log In.");
}


function sendResponse($data,$error = null)
{
	require_once __DIR__ . "/config.php";
	global $devMode;

	if($devMode) $loginState = true;
	else $loginState = $_SESSION['loggedin'];
	
	$response['data'] = $data;
	$response['error'] = $error;
	$response['loggedin'] = $loginState;

	$json_response = json_encode($response);
	if( $json_response  == false)
	{
		$errorResponse['error'] = "JSON encoding error";
		$errorResponse['loggedin'] = $_SESSION['loggedin'];
		
		echo json_encode($errorResponse);
		exit;
	}

	echo $json_response;
	exit;
}

?>

