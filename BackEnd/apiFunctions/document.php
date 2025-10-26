<?php
//*************************************************************************************************
// FileName : document.php
// FilePath : apiFunctions/
// Author   : Christian Marty
// Date		: 21.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $api;

require_once __DIR__ . "/document/_document.php";

if($api->isGet(Permission::Document_List))
{
    $api->returnData(\Document\getDocuments());
}
