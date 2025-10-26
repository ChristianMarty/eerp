<?php
//*************************************************************************************************
// FileName : permission.php
// FilePath : apiFunctions/user/
// Author   : Christian Marty
// Date		: 26.10.2025
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $api;

require_once __DIR__ . "/../../core/user/user.php";

if($api->isGet())
{
    $api->returnData(\Permission::toUnFlat());
}