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

	$oldLocationNr = dbEscapeString($dbLink,strtolower($data['OldLocationNr']));
	$newLocationNr = dbEscapeString($dbLink,strtolower($data['NewLocationNr']));
	
	if(substr($oldLocationNr,0,4) != "loc-")  sendResponse(null,"Invalid sourse location");
	$oldLocationNr = str_replace("loc-","",$oldLocationNr);
	
	if(substr($newLocationNr,0,4) != "loc-")  sendResponse(null,"Invalid destination location");
	$newLocationNr = str_replace("loc-","",$newLocationNr);
	
	$invQuery = "UPDATE inventory SET LocationId = (SELECT `Id` FROM `location` WHERE `LocNr`= '".$newLocationNr."') WHERE LocationId = (SELECT `Id` FROM `location` WHERE `LocNr`= '".$oldLocationNr."'); ";
	$stkQuery = "UPDATE partStock SET LocationId = (SELECT `Id` FROM `location` WHERE `LocNr`= '".$newLocationNr."') WHERE LocationId = (SELECT `Id` FROM `location` WHERE `LocNr`= '".$oldLocationNr."'); ";
	$locQuery = "UPDATE location  SET LocationId = (SELECT `Id` FROM `location` WHERE `LocNr`= '".$newLocationNr."') WHERE LocationId = (SELECT `Id` FROM `location` WHERE `LocNr`= '".$oldLocationNr."'); ";
	
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
