<?php
//*************************************************************************************************
// FileName : prodParts.php
// FilePath : apiFunctions/process/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";

$title = "Import Production Parts";
$description = "Import Production Parts from PartLookup.";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$query = "INSERT INTO productionPart(PartNo, Description) SELECT PartNo, Description FROM partLookup WHERE NOT EXISTS (SELECT PartNo FROM productionPart WHERE productionPart.PartNo =  partLookup.PartNo)  GROUP BY PartNo;";
	$queryResult = dbRunQuery($dbLink,$query);
	
	dbClose($dbLink);
	
	
	sendResponse(null);
}


?>