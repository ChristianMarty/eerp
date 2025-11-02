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

if($api->isPost(\Permission::Location_Transfer))
{
	$data = $api->getPostData();

	if(!isset($data->SourceLocationNumber)) $api->returnParameterMissingError("SourceLocationNumber");
	if(!isset($data->DestinationLocationNumber)) $api->returnParameterMissingError("DestinationLocationNumber");

	$oldLocationNr = \Numbering\parser(\Numbering\Category::Location, $data->SourceLocationNumber);
	$newLocationNr = \Numbering\parser(\Numbering\Category::Location, $data->DestinationLocationNumber);

	if($oldLocationNr == null)  $api->returnParameterError("SourceLocationNumber");
	if($newLocationNr == null)  $api->returnParameterError("DestinationLocationNumber");

	$query = <<<STR
		UPDATE inventory SET LocationId = (SELECT `Id` FROM `location` WHERE `LocationNumber`= '$newLocationNr') WHERE LocationId = (SELECT `Id` FROM `location` WHERE `LocationNumber`= '$oldLocationNr');
		UPDATE partStock SET LocationId = (SELECT `Id` FROM `location` WHERE `LocationNumber`= '$newLocationNr') WHERE LocationId = (SELECT `Id` FROM `location` WHERE `LocationNumber`= '$oldLocationNr');
		UPDATE location  SET LocationId = (SELECT `Id` FROM `location` WHERE `LocationNumber`= '$newLocationNr') WHERE LocationId = (SELECT `Id` FROM `location` WHERE `LocationNumber`= '$oldLocationNr');
		UPDATE assembly_unit  SET LocationId = (SELECT `Id` FROM `location` WHERE `LocationNumber`= '$newLocationNr') WHERE LocationId = (SELECT `Id` FROM `location` WHERE `LocationNumber`= '$oldLocationNr');
	STR;

	$database->execute($query);

	$api->returnEmpty();
}
