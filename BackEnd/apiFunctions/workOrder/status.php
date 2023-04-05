<?php
//*************************************************************************************************
// FileName : status.php
// FilePath : apiFunctions/workOrder/
// Author   : Christian Marty
// Date		: 05.04.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
    $dbLink = dbConnect();
    $output = dbGetEnumOptions($dbLink, 'workOrder','Status');
    dbClose($dbLink);

    if(!$output) sendResponse(null, "Database error for workOrder Status");

    sendResponse($output);
}
?>