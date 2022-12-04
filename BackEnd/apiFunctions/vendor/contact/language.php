<?php
//*************************************************************************************************
// FileName : language.php
// FilePath : FilePath : apiFunctions/vendor/contact
// Author   : Christian Marty
// Date		: 25.11.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../databaseConnector.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
    $dbLink = dbConnect();
    $output = dbGetEnumOptions($dbLink, 'vendor_contact','Language');
    dbClose($dbLink);

    if(!$output) sendResponse(null, "Database error for vendor_contact Language");

    sendResponse($output);
}
?>