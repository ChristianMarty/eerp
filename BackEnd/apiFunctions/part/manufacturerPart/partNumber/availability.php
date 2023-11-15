<?php
//*************************************************************************************************
// FileName : availability.php
// FilePath : apiFunctions/manufacturerPart/partNumber/
// Author   : Christian Marty
// Date		: 16.07.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/../../../databaseConnector.php";
require_once __DIR__ . "/../../../../config.php";
require_once __DIR__ . "/../../../externalApi/octopart.php";

if($api->isGet())
{
    $parameters = $api->getGetData();
    if(!isset($parameters->ManufacturerPartNumberId))$api->returnParameterMissingError("ManufacturerPartNumberId");
    $manufacturerPartNumberId =  intval($parameters->ManufacturerPartNumberId);
    if(!$manufacturerPartNumberId) $api->returnParameterError('ManufacturerPartNumberId');

    $authorizedOnly = false;
    if(isset($parameters->AuthorizedOnly)) $authorizedOnly = $parameters->AuthorizedOnly;
    if(!is_bool($authorizedOnly))$api->returnParameterError('AuthorizedOnly');

    $includeBrokers = false;
    if(isset($parameters->Brokers)) $includeBrokers = $parameters->Brokers;
    if(!is_bool($includeBrokers))$api->returnParameterError('Brokers');

    $query = <<<STR
        SELECT 
            Id, 
            OctopartId 
        FROM manufacturerPart_partNumber 
        WHERE Id = '$manufacturerPartNumberId'
        LIMIT 1;
    STR;
	$queryResult = $database->query($query);
	$part = $queryResult[0]??null;
	if($part == null) $api->returnError("Manufacturer Part Number Id not found");
    $part = (array)$part;

    $vendorList = octopart_getVendorList();
	$dbLink = dbConnect();
	$data = octopart_getPartData($dbLink, $part['OctopartId']);
    dbClose($dbLink);
    $availability = octopart_formatAvailabilityData ($data, $vendorList, $authorizedOnly, $includeBrokers );
    

	$output = array();
	$output['Data'] = $availability;
	$output['Timestamp'] = date("d.m.Y - H:i", time());

	$api->returnData($output);
}
