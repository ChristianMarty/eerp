<?php
//*************************************************************************************************
// FileName : report.php
// FilePath : /
// Author   : Christian Marty
// Date		: 25.10.2023
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
require_once __DIR__ . "/core/entrypoint.php";
global $api;
global $user;

$api = new ApiRouter($user, Entrypoint::REPORT, $_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);

if($api->isGet(Permission::Report_Run)){
    try {
        $path = $api->getRunPath();
        require $path;
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}else{
    $api->returnMethodNotAllowedError("Report must be used via the GET methode");
}
