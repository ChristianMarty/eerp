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
global $database;
global $api;

require_once __DIR__ . "/util/_barcodeFormatter.php";
require_once __DIR__ . "/util/_getDocuments.php";

if($api->isGet("document.view"))
{
    $api->returnData(getDocuments());
}
