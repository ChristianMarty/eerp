<?php
//*************************************************************************************************
// FileName : bulkTransfer.php
// FilePath : apiFunctions/location/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__."/../util/_barcodeParser.php";

if($api->isPost())
{
	$data = $api->getPostData();

	if(!isset($data->SourceLocationNumber)) $api->returnParameterMissingError("SourceLocationNumber");
	if(!isset($data->DestinationLocationNumber)) $api->returnParameterMissingError("DestinationLocationNumber");

	$oldLocationNr = barcodeParser_LocationNumber($data->SourceLocationNumber);
	$newLocationNr = barcodeParser_LocationNumber($data->DestinationLocationNumber);

	if($oldLocationNr == null)  $api->returnParameterError("SourceLocationNumber");
	if($newLocationNr == null)  $api->returnParameterError("DestinationLocationNumber");

	$query = <<<STR
		UPDATE inventory SET LocationId = (SELECT `Id` FROM `location` WHERE `LocNr`= '$newLocationNr') WHERE LocationId = (SELECT `Id` FROM `location` WHERE `LocNr`= '$oldLocationNr');
		UPDATE partStock SET LocationId = (SELECT `Id` FROM `location` WHERE `LocNr`= '$newLocationNr') WHERE LocationId = (SELECT `Id` FROM `location` WHERE `LocNr`= '$oldLocationNr');
		UPDATE location  SET LocationId = (SELECT `Id` FROM `location` WHERE `LocNr`= '$newLocationNr') WHERE LocationId = (SELECT `Id` FROM `location` WHERE `LocNr`= '$oldLocationNr');
		UPDATE assembly_unit  SET LocationId = (SELECT `Id` FROM `location` WHERE `LocNr`= '$newLocationNr') WHERE LocationId = (SELECT `Id` FROM `location` WHERE `LocNr`= '$oldLocationNr');
	STR;

	$database->execute($query);

	$api->returnEmpty();
}
