<?php
//*************************************************************************************************
// FileName : prodPartsFromStock.php
// FilePath : apiFunctions/process/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";

$title = "Production Parts from Order Reference";
$description = "Import Production Parts based on Order Reference.";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$query  = "INSERT INTO productionPart(PartNo) SELECT OrderReference FROM partStock WHERE NOT EXISTS (SELECT PartNo FROM productionPart ";
	$query .= "WHERE productionPart.PartNo =  partStock.OrderReference ) AND OrderReference IS NOT NULL AND OrderReference != '' GROUP BY OrderReference;";
	$queryResult = dbRunQuery($dbLink,$query);
	
	dbClose($dbLink);
	
	
	sendResponse(null);
}


?>