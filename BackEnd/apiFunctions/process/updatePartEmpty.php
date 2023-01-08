<?php
//*************************************************************************************************
// FileName : updateParEmpty.php
// FilePath : apiFunctions/process/
// Author   : Christian Marty
// Date		: 08.01.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";

$title = "Update Part Empty";
$description = "Update part empty flag";
$parameter = null;


if($_SERVER['REQUEST_METHOD'] == 'GET')
{
    $dbLink = dbConnect();
    $query = <<<STR
        CALL partStock_updateEmpty();
    STR;

    dbRunQuery($dbLink, $query);
	dbClose($dbLink);
	sendResponse(null);
}


?>