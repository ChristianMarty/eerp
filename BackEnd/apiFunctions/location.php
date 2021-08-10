<?php
//*************************************************************************************************
// FileName : location.php
// FilePath : apiFunctions/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/databaseConnector.php";
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/util/location.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$locationsTree = array();
	$locations = getLocations();

	$classId = 0;
	$locationsTree = buildLocationTree($locations,$classId);
	
	sendResponse($locationsTree);
}

?>