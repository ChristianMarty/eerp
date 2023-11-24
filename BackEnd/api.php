<?php
//*************************************************************************************************
// FileName : api.php
// FilePath : /
// Author   : Christian Marty
// Date		: 01.08.2021
// Website  : www.christian-marty.ch
//*************************************************************************************************
use JetBrains\PhpStorm\NoReturn;

require_once __DIR__ . "/core/entrypoint.php";
global $api;
global $user;

$api = new apiRouter($user, entrypoint::API, $_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);

try {
    require $api->getRunPath();
} catch (Exception $e) {
    $api->returnError($e->getMessage());
}

if($api->isOptions()) {
    header("Allow: ".$api->optionsString());
    exit;
} else {
    // this point should not be reached. The above line should terminate the program.
    $api->returnMethodNotAllowedError();
}

// legacy
#[NoReturn] function sendResponse(array|null|string $data, string|null $error = null): void
{
	global $api;
	$api->returnData($data,$error);
}
