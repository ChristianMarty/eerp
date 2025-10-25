<?php
//*************************************************************************************************
// FileName : location.php
// FilePath : apiFunctions/
// Author   : Christian Marty
// Date		: 15.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $api;

require_once __DIR__ . "/location/_location.php";

if($api->isGet(Permission::Location_List))
{
    $location = new Location();
    $api->returnData($location->tree());
}

