<?php
//*************************************************************************************************
// FileName : process.php
// FilePath : /
// Author   : Christian Marty
// Date		: 25.10.2023
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
require_once __DIR__ . "/core/entrypoint.php";
global $api;
global $user;

$api = new apiRouter($user, entrypoint::PROCESS, $_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);

if(!$api->isGet(Permission::Process_Run)){
    $api->returnError("Processes must be used via the GET methode");
}

try {
	require $api->getRunPath();
} catch (Exception $e) {
	echo $e->getMessage();
}

