<?php
//*************************************************************************************************
// FileName : bulkTransfer.php
// FilePath : apiFunctions/location/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__."/../databaseConnector.php";
require_once __DIR__."/../util/_barcodeParser.php";

if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$data = json_decode(file_get_contents('php://input'),true);
	
	$dbLink = dbConnect();

	$oldLocationNr = barcodeParser_LocationNumber($data['SourceLocationNumber']);
	$newLocationNr = barcodeParser_LocationNumber($data['DestinationLocationNumber']);

	if(!$oldLocationNr)  sendResponse(null,"Invalid source location");
	if(!$newLocationNr)  sendResponse(null,"Invalid destination location");

	$query = <<<STR
		UPDATE inventory SET LocationId = (SELECT `Id` FROM `location` WHERE `LocNr`= '$newLocationNr') WHERE LocationId = (SELECT `Id` FROM `location` WHERE `LocNr`= '$oldLocationNr');
		UPDATE partStock SET LocationId = (SELECT `Id` FROM `location` WHERE `LocNr`= '$newLocationNr') WHERE LocationId = (SELECT `Id` FROM `location` WHERE `LocNr`= '$oldLocationNr');
		UPDATE location  SET LocationId = (SELECT `Id` FROM `location` WHERE `LocNr`= '$newLocationNr') WHERE LocationId = (SELECT `Id` FROM `location` WHERE `LocNr`= '$oldLocationNr');
		UPDATE assembly_unit  SET LocationId = (SELECT `Id` FROM `location` WHERE `LocNr`= '$newLocationNr') WHERE LocationId = (SELECT `Id` FROM `location` WHERE `LocNr`= '$oldLocationNr');
	STR;

	$error = null;
	
	if(!mysqli_multi_query($dbLink, $query))
	{
		$error = "Error description: " . mysqli_error($dbLink);
	}
		
	dbClose($dbLink);	
	sendResponse(null,$error);
}
?>

