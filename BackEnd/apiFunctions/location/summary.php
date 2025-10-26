<?php
//*************************************************************************************************
// FileName : summery.php
// FilePath : apiFunctions/location/
// Author   : Christian Marty
// Date		: 21.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/../util/_barcodeParser.php";
require_once __DIR__ . "/../util/_barcodeFormatter.php";
require_once __DIR__ . "/../location/_location.php";

if($api->isGet(\Permission::Location_View))
{
	$parameter = $api->getGetData();

	if(!isset($parameter->LocationNumber)) $api->returnParameterMissingError("LocationNumber");
	$locationNumber = barcodeParser_LocationNumber($parameter->LocationNumber);
	if($locationNumber == null) $api->returnParameterError("LocationNumber");

	$response = [];

	$query = <<<STR
		SELECT 
		    StockNumber,
			manufacturerPart_partNumber.Number AS ManufacturerPartNumber,
			vendor_displayName(manufacturerPart_partNumber.VendorId) AS ManufacturerName,
			Date, 
			Cache_Quantity 
		FROM partStock
		LEFT JOIN manufacturerPart_partNumber on partStock.ManufacturerPartNumberId = manufacturerPart_partNumber.Id
		WHERE LocationId = (SELECT Id FROM location where LocationNumber = '$locationNumber')
	STR;
	$result = $database->query($query);
	foreach ($result as $r)
	{
		$descriptor = $r->ManufacturerName." ".$r->ManufacturerPartNumber;
		$descriptor .= ", ".$r->Date.", Qty: ".$r->Cache_Quantity;
		
		$data = array();
		$data["Item"] =  barcodeFormatter_StockNumber($r->StockNumber);
		$data["Category"] = "Stock";
		$data["Description"] = $descriptor; 
		
		$response[] = $data;
	}

	$query = <<<STR
		SELECT 
		    InventoryNumber, 
		    Title, 
		    Manufacturer, 
		    Type, 
		    LocationId 
		FROM inventory
		WHERE LocationId = (SELECT Id FROM location where LocationNumber = '$locationNumber')
	STR;
	$result = $database->query($query);
	foreach ($result as $r)
	{
		$descriptor = $r->Title;
		$descriptor .= " - ".$r->Manufacturer." ".$r->Type;
		
		$data = array();
		$data["Item"] =  barcodeFormatter_InventoryNumber($r->InventoryNumber);
		$data["Category"] = "Inventory";
		$data["Description"] = $descriptor; 
		
		$response[] = $data;
	}

	$query = <<<STR
		SELECT 
		    AssemblyUnitNumber, 
		    SerialNumber, 
		    Name, 
		    Description,  
		    LocationId 
		FROM assembly_unit
		LEFT JOIN assembly on assembly.Id =  assembly_unit.AssemblyId
		WHERE LocationId = (SELECT Id FROM location where LocationNumber = '$locationNumber')
	STR;
	$result = $database->query($query);
	foreach ($result as $r)
	{
		$descriptor = $r->Name;
		$descriptor .= " - ".$r->Description." SN:".$r->SerialNumber;
		
		$data = array();
		$data["Item"] = barcodeFormatter_AssemblyUnitNumber($r->AssemblyUnitNumber);
		$data["Category"] = "Assembly Unit";
		$data["Description"] = $descriptor; 
		
		$response[] = $data;
	}

	$query = <<<STR
		SELECT 
		    Id,
			LocationNumber
		FROM location
		WHERE LocationId = (SELECT Id FROM location where LocationNumber = '$locationNumber')
	STR;
	$result = $database->query($query);
    $location = new Location();
	foreach ($result as $r)
	{
		$data = array();
		$data["Item"] = barcodeFormatter_LocationNumber($r->LocationNumber);
		$data["Category"] = "Location";
		$data["Description"] = $location->name(intval($r->Id));
		
		$response[] = $data;
	}

	$api->returnData($response);
}
