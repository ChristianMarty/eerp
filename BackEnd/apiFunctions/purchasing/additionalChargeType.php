<?php
//*************************************************************************************************
// FileName : additionalChargeType.php
// FilePath : apiFunctions/purchasing/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
    $dbLink = dbConnect();
    $output = dbGetEnumOptions($dbLink, 'purchasOrder_additionalCharges','Type');
    dbClose($dbLink);

    if(!$output) sendResponse(null, "Database error for purchasOrder_additionalCharges Type");

    sendResponse($output);
}
?>