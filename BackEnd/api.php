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

if(isset($_GET["user"]) && isset($_GET["token"]))
{
    $user->loginWithToken($_GET["user"],$_GET["token"]);
}

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
    // this point should not be reached. The above lines should terminate the program.
    $api->returnMethodNotAllowedError();
}
