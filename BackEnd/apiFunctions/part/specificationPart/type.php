<?php
//*************************************************************************************************
// FileName : type.php
// FilePath : apiFunctions/part/specificationPart/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../databaseConnector.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
    $dbLink = dbConnect();
    $output = dbGetEnumOptions($dbLink, 'specificationPart','Type');
    dbClose($dbLink);

    if(!$output) sendResponse(null, "Database error for Specification Part Type");

    sendResponse($output);
}
?>