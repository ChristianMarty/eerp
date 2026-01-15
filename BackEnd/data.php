<?php
//*************************************************************************************************
// FileName : phoneBook.php
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
	exit;
}

$apiRequestParts = explode('data.php/', $_SERVER['REQUEST_URI']);

$filePath = $serverDataPath."/";
$apiRequest = explode('?',rawurldecode($apiRequestParts[1]))[0];
$filePath .= $apiRequest;

if(!file_exists($filePath)) {
	http_response_code(404);
	echo "<p>Error 404 - File not found</p>";
	exit;
}

$filename = pathinfo($filePath)['filename'];
$extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

$contentType = match($extension){
    'pdf' => 'application/pdf',
    'jpg',
    'jpeg' => 'image/jpeg',
    default => 'text/html'
};

header('Content-Description: File Transfer');
header('Content-Type: '.$contentType);
header('Content-Disposition: inline; filename="'.$filename.'.'.$extension.'"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filePath));

readfile($filePath);
