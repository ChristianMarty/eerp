<?php
//*************************************************************************************************
// FileName : type.php
// FilePath : apiFunctions/inventory/purchase/
// Author   : Christian Marty
// Date		: 21.01.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../databaseConnector.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
    $dbLink = dbConnect();
    $output = dbGetEnumOptions($dbLink, 'inventory_purchasOrderReference','Type');
    dbClose($dbLink);

    if(!$output) sendResponse(null, "Database error for document Type");

    sendResponse($output);
}
?>