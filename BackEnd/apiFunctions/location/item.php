<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/location/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/../util/_barcodeParser.php";
require_once __DIR__ . "/../util/_barcodeFormatter.php";
require_once __DIR__ . "/_location.php";


if($api->isGet())
{
	$parameters = $api->getGetData();
	if(!isset($parameters->LocationNumber)) $api->returnParameterMissingError('LocationNumber');

    $locationNumber = barcodeParser_LocationNumber($parameters->LocationNumber);
	if($locationNumber === false) $api->returnParameterError('LocationNumber');


	$output = array();
// get main item
	$query = <<<STR
		SELECT 
		    Id,
			LocNr, 
			ParentId,
			Name,
			Title,
			Description,
			Movable,
			ESD,
			RecursionDepth
		FROM location 
		WHERE LocNr = '$locationNumber'
		LIMIT 1;
	STR;
	$result = $database->query($query);
	$locationData = $result[0];
	if($locationData === null) $api->returnError("Location barcode not found");

	$locationId = intval($locationData->Id);
	$parentId = intval($locationData->ParentId);
	$output['LocationNumber'] = $locationData->LocNr;
	$output['LocationBarcode'] = barcodeFormatter_LocationNumber($locationData->LocNr);
	$output['Name'] = $locationData->Name;
	$output['Title'] = $locationData->Title;
	$output['Description'] = $locationData->Description;
	$output['Movable'] = boolval($locationData->Movable);
	$output['ESD'] = boolval($locationData->ESD);

	// get parent
	$parent= array();
	if($parentId !== 0)
	{
		$query = <<<STR
			SELECT 
				LocNr, 
				Name,
				Description
			FROM location 
			WHERE Id = $parentId
			LIMIT 1;
		STR;
		$item = $database->query($query)[0];

		$parent['LocationNumber'] = $item->LocNr;
		$parent['LocationBarcode'] = barcodeFormatter_LocationNumber($item->LocNr);
		$parent['Name'] = $item->Name;
		$parent['Description'] = $item->Description;
	}
	$output['Parent'] = $parent;

// get children
	$query = <<<STR
		SELECT 
			LocNr, 
			Name,
			Description
		FROM location WHERE ParentId = $locationId
	STR;
	$result = $database->query($query);

	$children = array();
	foreach ($result as $item)
	{
		$data = array();
		$data['LocationNumber'] = $item->LocNr;
		$data['LocationBarcode'] = barcodeFormatter_LocationNumber($item->LocNr);
		$data['Name'] = $item->Name;
		$data['Description'] = $item->Description;
		$children[] = $data;
	}
	$output['Children'] = $children;

    if($parameters->Items??null === true) $output['Items'] = location_getItems($locationId);
    else $output['Items'] = [];

	$api->returnData($output);
}
else if($api->isPatch('location.edit'))
{
    $data = $api->getPostData();
    if(!isset($data->LocationNumber)) $api->returnParameterMissingError('LocationNumber');

    $locationNumber = barcodeParser_LocationNumber($data->LocationNumber);
    if($locationNumber === false) $api->returnParameterError('LocationNumber');

    $updateData = [];
    $updateData['Name']  = $data->Name;
    $updateData['Title'] = $data->Title;
    $updateData['Description'] = $data->Description;
    $updateData['Movable'] = $data->Movable;
    $updateData['ESD'] = $data->ESD;

    try {
        $database->update("location", $updateData, "LocNr = {$locationNumber}");
    }
    catch (\Exception $e)
    {
        throw new \Exception($e->getMessage());
    }

    $api->returnEmpty();
}
