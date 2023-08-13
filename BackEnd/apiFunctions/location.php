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
require_once __DIR__ . "/location/_location.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$locationsTree = array();
	$locations = location_getLocations();

	$classId = 0;
	$locationsTree = location_buildTree($locations,$classId);
	
	sendResponse($locationsTree);
}

?>