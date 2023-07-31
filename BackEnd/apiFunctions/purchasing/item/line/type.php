<?php
//*************************************************************************************************
// FileName : type.php
// FilePath : apiFunctions/purchasing/item/line/
// Author   : Christian Marty
// Date		: 29.07.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../../databaseConnector.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
    $dbLink = dbConnect();
    $output = dbGetEnumOptions($dbLink, 'purchaseOrder_itemOrder','Type');
    dbClose($dbLink);

    if(!$output) sendResponse(null, "Database error for purchaseOrder_itemOrder Type");

    sendResponse($output);
}
?>