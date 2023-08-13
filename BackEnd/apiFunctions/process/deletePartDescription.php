<?php
//*************************************************************************************************
// FileName : deletePartDescription.php
// FilePath : apiFunctions/process/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";

$title = "Delete Description";
$description = "Delete production and manufacturer part description";
$parameter = null;


if($_SERVER['REQUEST_METHOD'] == 'GET')
{
    $dbLink = dbConnect();

    $query = <<<STR
        UPDATE productionPart SET Description = NULL;
        UPDATE manufacturerPart_item SET Description = NULL; 
    STR;
    dbRunQuery($dbLink, $query);

    dbClose($dbLink);
    exit;
}


?>