<?php
//*************************************************************************************************
// FileName : parsePartClass.php
// FilePath : apiFunctions/process/octopart
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../../config.php";
require_once __DIR__ . "/../../databaseConnector.php";

$title = "Octopart Parse Part Class";
$description = "Parse Octopart Class and converts it to BlueNova Part Class.";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$dbLink = dbConnect();

    $query = <<<STR
        SELECT Id, OctopartPartData
        FROM `manufacturerPart`
        WHERE OctopartPartData IS NOT NULL
    STR;
	
	$queryResult = dbRunQuery($dbLink,$query);
	
	dbClose($dbLink);
	
	while($partQueryData = mysqli_fetch_assoc($queryResult))
	{
		$partData = json_decode($partQueryData['OctopartPartData']);
	
		$dbLink = dbConnect();
		if($dbLink == null) return null;
		
		$query = "UPDATE manufacturerPart SET OctopartCategory = '".$partData->category->name."' WHERE Id = ".$partQueryData['Id'];
		dbRunQuery($dbLink,$query);
		dbClose($dbLink);
	}
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	$query = "CALL `manufacturerPart_octopart`();";
	dbRunQuery($dbLink,$query);
	dbClose($dbLink);
	
	sendResponse($partData);
}

?>