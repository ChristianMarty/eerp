<?php
//*************************************************************************************************
// FileName : api.php
// FilePath : /
// Author   : Christian Marty
// Date		: 01.08.2021
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


// TODO: Fix this
header("Access-Control-Allow-Methods: POST, PATCH, GET, DELETE, OPTIONS");
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

$tmp = explode('/', $filePath);
if(str_starts_with(end($tmp), "_")) sendResponse(null, "Invalid URL"); //Files starting with "_" are hidden

if( $apiRequest == "user/login" || $apiRequest == "user/logout" || $apiRequest == "user/info")
{
	require $filePath;
}
else if ((isset($_SESSION['loggedin']) && $_SESSION['loggedin'])||$devMode)
{
	$idempotencyToken = null;
	if(isset(getallheaders()['Idempotency-Key'])) $idempotencyToken = getallheaders()['Idempotency-Key'];
	if(isset(getallheaders()['idempotency-key'])) $idempotencyToken = getallheaders()['idempotency-key'];
	
	if($_SERVER['REQUEST_METHOD'] == 'POST' && !$devMode && $filePath != 'apiFunctions/document/ingest/upload.php')
	{
		if($idempotencyToken !== $_SESSION['idempotency'])
		{
			sendResponse(null, "Idempotency Key Expired.");
		}
		$_SESSION['idempotency'] = generateIdempotenceToken();
	}

	require $filePath;
}
else
{
	sendResponse(null, "User Session Invalid. Please Log In.");
}

function generateIdempotenceToken(): string
{
	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$charactersLength = strlen($characters);
	$randomString = '';
	for ($i = 0; $i < 20; $i++) {
		$randomString .= $characters[rand(0, $charactersLength - 1)];
	}
	return $randomString;
}

function sendResponse($data, $error = null): void
{
	header("Content-Type:application/json; charset=UTF-8");
	
	require_once __DIR__ . "/config.php";
	global $devMode;

	if($devMode)
	{
		$loginState = true;
		$_SESSION['idempotency'] = "abcdefghijklmnopqrstuvwxyz";
	}
	else
	{
		if(!isset($_SESSION['idempotency'])) $_SESSION['idempotency'] = generateIdempotenceToken();
		$loginState = $_SESSION['loggedin'];
	}

	$response['data'] = $data;
	$response['error'] = $error;
	$response['loggedin'] = $loginState;
	$response['idempotency'] = $_SESSION['idempotency'];

	$json_response = json_encode($response);
	if(!$json_response)
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
