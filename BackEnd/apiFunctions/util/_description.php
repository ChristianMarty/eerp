<?php
//*************************************************************************************************
// FileName : itemDescription.php
// FilePath : apiFunctions/utils/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../util/_barcodeFormatter.php";
require_once __DIR__ . "/../util/_barcodeParser.php";
require_once __DIR__ . "/../location/_location.php";

//Generates a universal description of an item of any category

function description_generateSummary($locationNr): array
{
    global $database;

	$response['data'] = null;
	$response['error'] = null;

	$temp = explode("-", $locationNr);
	$itemPrefix = strtolower($temp[0]);
	$itemNr = $database->escape(trim(strtolower($temp[1])));

	$data = array();
	$locationId = null;
	
	if($itemPrefix == "stk")
	{
		$query = <<< STR
		SELECT
			vendor_displayName(vendor.Id) AS ManufacturerName,
			manufacturerPart_partNumber.Number AS ManufacturerPartNumber, 
			Date, 
			Cache_Quantity AS Quantity, 
			LocationId 
		FROM partStock 
		LEFT JOIN manufacturerPart_partNumber ON manufacturerPart_partNumber.Id = partStock.ManufacturerPartNumberId
		LEFT JOIN vendor ON vendor.Id = manufacturerPart_partNumber_getVendorId(partStock.ManufacturerPartNumberId)
		WHERE StockNo = $itemNr;
		STR;

		$result = $database->query($query);
		
		if(count($result) == 0)
		{
			$response['error'] ="Item not found";
			return $response;
		}			
		
		$itemData = $result[0];
		
		$descriptor = $itemData->ManufacturerName." ".$itemData->ManufacturerPartNumber;
		$descriptor .= ", ".$itemData->Date.", Qty: ".$itemData->Quantity;
		
		$locationId = $itemData->LocationId;
		
		$data["Item"] =  barcodeFormatter_StockNumber($itemNr);
		$data["Category"] = "Stock";
		$data["Description"] = $descriptor; 
		$data["Movable"] = true;
	}
	else if($itemPrefix == "inv")  
	{
        $query = <<< STR
		SELECT
			Title, 
			Manufacturer, 
			Type, 
			LocationId
		FROM inventory
		WHERE InvNo = $itemNr
		STR;
        $result = $database->query($query);

        if(count($result) == 0)
        {
            $response['error'] ="Item not found";
            return $response;
        }

        $itemData = $result[0];

		$descriptor = $itemData->Title;
		$descriptor .= " - ".$itemData->Manufacturer." ".$itemData->Type;
		
		$locationId = $itemData->LocationId;
		
		$data["Item"] = barcodeFormatter_InventoryNumber($itemNr);
		$data["Category"] = "Inventory";
		$data["Description"] = $descriptor; 
		$data["Movable"] = true;
	}
	else if($itemPrefix == "asu")  
	{
		$query = <<<STR
			SELECT 
				Name, 
				Description, 
				SerialNumber, 
				LocationId
			FROM assembly_unit
			LEFT JOIN assembly ON assembly.Id = assembly_unit.AssemblyId
			WHERE AssemblyUnitNumber = '$itemNr'
		STR;
        $result = $database->query($query);

        if(count($result) == 0)
        {
            $response['error'] ="Item not found";
            return $response;
        }

        $itemData = $result[0];

		$descriptor = $itemData->Name;
		$descriptor .= " - ".$itemData->Description." SN: ".$itemData->SerialNumber;
		
		$locationId = $itemData->LocationId;
		
		$data["Item"] = barcodeFormatter_AssemblyUnitNumber($itemNr);
		$data["Category"] = "Assembly Item";
		$data["Description"] = $descriptor; 
		$data["Movable"] = true;
	}
	else if($itemPrefix == "loc")
	{
		$query = <<<STR
			SELECT 
			    Id,
			    LocNr, 
			    Movable, 
			    LocationId
			FROM location 
			WHERE LocNr = $itemNr
		STR;
        $result = $database->query($query);

        if(count($result) == 0)
        {
            $response['error'] ="Item not found";
            return $response;
        }

        $itemData = $result[0];

		$location = new Location();
		
		$data["Item"] = barcodeFormatter_LocationNumber($itemNr);
		$data["Category"] = "Location";
		$data["Description"] = $location->name($itemData->Id);
		$data["LocationNr"] = barcodeFormatter_LocationNumber($itemData->LocNr);
		$data["Location"] = $location->name($itemData->LocationId);
		if($itemData->Movable == "1") $data["Movable"] = true;
		else $data["Movable"] = false;
	}
	else 
	{
		$response['error'] ="Unknown Item Category";
		return $response;
	}

	if($locationId != null)
	{
		$query = <<<STR
			SELECT 
			    Id,
			    LocNr, 
			    NAME
			FROM location 
			WHERE Id = $locationId
		STR;
        $result = $database->query($query);

        if(count($result) == 0)
        {
            $response['error'] ="Item not found";
            return $response;
        }

        $itemData = $result[0];

		$data["LocationNr"] = barcodeFormatter_LocationNumber($itemData->LocNr);
		$data["Location"] = (new Location())->name($itemData->Id);
	}
	
	$response['data'] = $data;
	return $response;
}
