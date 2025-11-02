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
global $database;

global $api;
$api = new ApiRouter($user, Entrypoint::API, $_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);

if($api->isOptions()) {
    header("Allow: ".$api->optionsString());
    exit;
}

try {
    $path = $api->getRunPath();
    require $path;
} catch (Exception $e) {
    $api->returnData(\Error\generic($e->getMessage()));
}

// this point should not be reached. The above lines should terminate the program.
$api->returnMethodNotAllowedError();

