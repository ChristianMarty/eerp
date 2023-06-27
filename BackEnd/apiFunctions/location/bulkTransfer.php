<?php
//*************************************************************************************************
// FileName : bulkTransfer.php
// FilePath : apiFunctions/location/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";

if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$data = json_decode(file_get_contents('php://input'),true);
	
	$dbLink = dbConnect();

	$oldLocationNr = dbEscapeString($dbLink,strtolower($data['SourceLocationNumber']));
	$newLocationNr = dbEscapeString($dbLink,strtolower($data['DestinationLocationNumber']));
	
	if(!str_starts_with($oldLocationNr, "loc-"))  sendResponse(null,"Invalid source location");
	$oldLocationNr = str_replace("loc-","",$oldLocationNr);
	
	if(!str_starts_with($newLocationNr, "loc-"))  sendResponse(null,"Invalid destination location");
	$newLocationNr = str_replace("loc-","",$newLocationNr);
	
	$invQuery = "UPDATE inventory SET LocationId = (SELECT `Id` FROM `location` WHERE `LocNr`= '".$newLocationNr."') WHERE LocationId = (SELECT `Id` FROM `location` WHERE `LocNr`= '".$oldLocationNr."'); ";
	$stkQuery = "UPDATE partStock SET LocationId = (SELECT `Id` FROM `location` WHERE `LocNr`= '".$newLocationNr."') WHERE LocationId = (SELECT `Id` FROM `location` WHERE `LocNr`= '".$oldLocationNr."'); ";
	$locQuery = "UPDATE location  SET LocationId = (SELECT `Id` FROM `location` WHERE `LocNr`= '".$newLocationNr."') WHERE LocationId = (SELECT `Id` FROM `location` WHERE `LocNr`= '".$oldLocationNr."'); ";
	$locQuery = "UPDATE assembly_item  SET LocationId = (SELECT `Id` FROM `location` WHERE `LocNr`= '".$newLocationNr."') WHERE LocationId = (SELECT `Id` FROM `location` WHERE `LocNr`= '".$oldLocationNr."'); ";
	
	$query = $invQuery.$stkQuery.$locQuery;
	
	$error = null;
	
	if(!mysqli_multi_query($dbLink, $query))
	{
		$error = "Error description: " . mysqli_error($dbLink);
	}
		
	dbClose($dbLink);	
	sendResponse(null,$error);
}
?>

