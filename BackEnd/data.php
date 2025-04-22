<?php
//*************************************************************************************************
// FileName : data.php
// FilePath : /
// Author   : Christian Marty
// Date		: 01.08.2021
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/core/entrypoint.php";
global $user;
global $serverDataPath;

if (!$user->loggedIn()) {
	http_response_code(401);
	echo "<p>Error 401 - User Session Invalid. Please Log In.<p>";
	exit;
}

if($_SERVER['REQUEST_METHOD'] !== 'GET'){
	http_response_code(405);
	echo "<p>Error 405 - Method Not Allowed</p>";
}

$params = array();
$apiRequestParts = explode('data.php/', $_SERVER['REQUEST_URI']);

$filePath = $serverDataPath."/";
$apiRequest = explode('?',rawurldecode($apiRequestParts[1]))[0];
$filePath .= $apiRequest;

if(file_exists($filePath)) {
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
} else {
	http_response_code(404);
	echo "<p>Error 404 - File not found</p>";
}

?>

