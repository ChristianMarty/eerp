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

$api = new apiRouter($user, entrypoint::REPORT, $_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);

try {
	require $api->getRunPath();
} catch (Exception $e) {
    echo $e->getMessage();
}
