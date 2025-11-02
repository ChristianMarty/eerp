<?php
//*************************************************************************************************
// FileName : renderer.php
// FilePath : /
// Author   : Christian Marty
// Date		: 01.08.2021
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
require_once __DIR__ . "/core/entrypoint.php";
global $api;
global $user;

$api = new ApiRouter($user, Entrypoint::RENDERER, $_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);

if($api->isGet(Permission::Renderer_View)){
    try {
        $path = $api->getRunPath();
        require $path;
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}else{
    $api->returnMethodNotAllowedError("Renderer must be used via the GET methode");
}
