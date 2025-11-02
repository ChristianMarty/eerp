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

$api = new ApiRouter($user, Entrypoint::PROCESS, $_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);

if($api->isGet(Permission::Process_Run)){

    try {
        $path = $api->getRunPath();
        require $path;
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}else{
    $api->returnMethodNotAllowedError("Processes must be used via the GET methode");
}
