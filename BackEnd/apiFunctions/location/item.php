<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/location/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../util/_barcodeParser.php";
require_once __DIR__ . "/../util/_barcodeFormatter.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	if(!isset($_GET["LocationNumber"])) sendResponse(null,"LocationNumber not specified");

    $locationNumber = barcodeParser_LocationNumber($_GET["LocationNumber"]);

	$dbLink = dbConnect();
    $items = array();

	$query = <<<STR
		SELECT 
		    StockNo,
			manufacturerPart_partNumber.Number AS ManufacturerPartNumber,
			vendor_displayName(manufacturerPart_partNumber.VendorId) AS ManufacturerName,
			Date, 
			Cache_Quantity 
		FROM partStock
		LEFT JOIN manufacturerPart_partNumber on partStock.ManufacturerPartNumberId = manufacturerPart_partNumber.Id
		WHERE LocationId = (SELECT Id FROM location where LocNr = '$locationNumber')
	STR;
	$result = dbRunQuery($dbLink,$query);

	while($itemData = mysqli_fetch_assoc($result))
	{
		$descriptor = $itemData["ManufacturerName"]." ".$itemData["ManufacturerPartNumber"];
		$descriptor .= ", ".$itemData["Date"].", Qty: ".$itemData["Cache_Quantity"];
		
		$data = array();
		$data["Item"] =  barcodeFormatter_StockNumber($itemData["StockNo"]);
		$data["Category"] = "Stock";
		$data["Description"] = $descriptor;

        $items[] = $data;
	}

	$query = <<<STR
		SELECT 
		    InvNo, 
		    Title, 
		    Manufacturer, 
		    Type, 
		    LocationId 
		FROM inventory
		WHERE LocationId = (SELECT Id FROM location where LocNr = '$locationNumber')
	STR;
	$result = dbRunQuery($dbLink,$query);
		
	while($itemData = mysqli_fetch_assoc($result))
	{
		$descriptor = $itemData["Title"];
		$descriptor .= " - ".$itemData["Manufacturer"]." ".$itemData["Type"];
		
		$data = array();
		$data["Item"] = "Inv-".$itemData["InvNo"];
		$data["Category"] = "Inventory";
		$data["Description"] = $descriptor;

        $items[] = $data;
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
		WHERE LocationId = (SELECT Id FROM location where LocNr = '$locationNumber')
	STR;
	$result = dbRunQuery($dbLink,$query);
		
	while($itemData = mysqli_fetch_assoc($result))
	{
		$descriptor = $itemData["Name"];
		$descriptor .= " - ".$itemData["Description"]." SN:".$itemData["SerialNumber"];
		
		$data = array();
		$data["Item"] = "Asu-".$itemData["AssemblyUnitNumber"];
		$data["Category"] = "Assembly Unit";
		$data["Description"] = $descriptor;

        $items[] = $data;
	}

	$query = <<<STR
		SELECT 
			LocNr, 
			Name 
		FROM location WHERE LocNr = '$locationNumber'
	STR;
	$result = dbRunQuery($dbLink,$query);
		
	while($itemData = mysqli_fetch_assoc($result))
	{
		$descriptor = $itemData["Name"];
		
		$data = array();
		$data["Item"] = "Loc-".$itemData["LocNr"];
		$data["Category"] = "Location";
		$data["Description"] = $descriptor;

        $items[] = $data;
	}

    $output = array();
    $output['Items'] = $items;

// get main item
    $query = <<<STR
		SELECT 
		    Id,
			LocNr, 
			ParentId,
			Name,
			Description,
			Movable,
			ESD,
			RecursionDepth
		FROM location WHERE LocNr = '$locationNumber'
	STR;
    $result = dbRunQuery($dbLink,$query);

    $locationData = mysqli_fetch_assoc($result);

    $locationId = intval($locationData['Id']);
	$parentId = intval($locationData['ParentId']);
    $output['LocationNumber'] = $locationData['LocNr'];
    $output['LocationBarcode'] = barcodeFormatter_LocationNumber($locationData['LocNr']);
    $output['Name'] = $locationData['Name'];
    $output['Description'] = $locationData['Description'];
    $output['Movable'] = boolval($locationData['Movable']);
    $output['ESD'] = boolval($locationData['ESD']);

// get parent
	$parent= array();
	if($parentId !== 0)
	{
		$query = <<<STR
			SELECT 
				LocNr, 
				Name,
				Description
			FROM location WHERE Id = $parentId
		STR;
		$result = dbRunQuery($dbLink,$query);
		$parentLine = mysqli_fetch_assoc($result);

		$parent['LocationNumber'] = $parentLine['LocNr'];
		$parent['LocationBarcode'] = barcodeFormatter_LocationNumber($parentLine['LocNr']);
		$parent['Name'] = $parentLine['Name'];
		$parent['Description'] = $parentLine['Description'];
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
    $result = dbRunQuery($dbLink,$query);

    $children = array();
    while($child = mysqli_fetch_assoc($result))
    {
        $data = array();
        $data['LocationNumber'] = $child['LocNr'];
        $data['LocationBarcode'] = barcodeFormatter_LocationNumber($child['LocNr']);
        $data['Name'] = $child['Name'];
        $data['Description'] = $child['Description'];
        $children[] = $data;
    }
    $output['Children'] = $children;


	dbClose($dbLink);	
	sendResponse($output);
}
?>
