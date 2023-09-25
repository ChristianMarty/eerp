<?php
//*************************************************************************************************
// FileName : availability.php
// FilePath : apiFunctions/manufacturerPart/partNumber/
// Author   : Christian Marty
// Date		: 16.07.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../../databaseConnector.php";
require_once __DIR__ . "/../../../../config.php";
require_once __DIR__ . "/../../../externalApi/octopart.php";


if($_SERVER['REQUEST_METHOD'] == 'GET')
{
   if(!isset($_GET["ManufacturerPartNumberId"]))  sendResponse(null, "ManufacturerPartNumberId unspecified");
    $manufacturerPartNumberId =  intval($_GET["ManufacturerPartNumberId"]);

    $authorizedOnly = false;
    $includeBrokers = false;
    if(isset($_GET["AuthorizedOnly"])) $authorizedOnly = filter_var($_GET["AuthorizedOnly"],FILTER_VALIDATE_BOOLEAN);
    if(isset($_GET["Brokers"])) $includeBrokers = filter_var($_GET["Brokers"],FILTER_VALIDATE_BOOLEAN);

	$dbLink = dbConnect();
    $query = <<<STR
        SELECT Id, OctopartId FROM manufacturerPart_partNumber WHERE Id = $manufacturerPartNumberId
    STR;
	$queryResult = dbRunQuery($dbLink,$query);
	dbClose($dbLink);
	
	$part = mysqli_fetch_assoc($queryResult);

	if($part == null) sendResponse(null, "Manufacturer Part Number Id not found");
	
	$data = octopart_getPartData($part['OctopartId']);
    $availability = octopart_formatAvailabilityData ($data, $authorizedOnly, $includeBrokers, );

	$output = array();
	$output['Data'] = $availability;
	$output['Timestamp'] = date("d.m.Y - H:i", time()); //*/

	sendResponse($output);
}
?>