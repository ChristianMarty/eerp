<?php
//*************************************************************************************************
// FileName : type.php
// FilePath : apiFunctions/inventory/history/
// Author   : Christian Marty
// Date		: 25.09.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../databaseConnector.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
    $dbLink = dbConnect();
    $output = dbGetEnumOptions($dbLink, 'inventory_history','Type');
    dbClose($dbLink);

    if(!$output) sendResponse(null, "Database error for inventory_history Type");

    sendResponse($output);
}
?>