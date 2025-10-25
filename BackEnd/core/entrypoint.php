<?php
//*************************************************************************************************
// FileName : entrypoint.php
// FilePath : /core
// Author   : Christian Marty
// Date		: 23.10.2023
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/database.php";
require_once __DIR__ . "/user/userAuthentication.php";
require_once __DIR__ . "/apiRouter.php";

if(session_status() === PHP_SESSION_NONE){
    session_start();
}
global $database;
$database = new database();

global $user;
$user = new userAuthentication();

if(isset($_GET["user"]) && isset($_GET["token"]))
{
    $user->loginWithToken($_GET["user"],$_GET["token"]);
}

if($user->showPhpErrors())
{
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

// TODO: Fix this
header("Access-Control-Allow-Methods: POST, PATCH, GET, DELETE, OPTIONS");
