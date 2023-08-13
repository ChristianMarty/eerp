<?php
//*************************************************************************************************
// FileName : summery.php
// FilePath : apiFunctions/location/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../util/_barcodeParser.php";
require_once __DIR__ . "/../util/_barcodeFormatter.php";
require_once __DIR__ . "/../location/_location.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	if(!isset($_GET["LocationNumber"])) sendResponse(null,"LocationNumber not specified");
	$locationNr = strtolower($_GET["LocationNumber"]);
	
	if(!str_starts_with($locationNr, "loc-"))  sendResponse(null,"Invalid Location Number");
	$locationNr = str_replace("loc-","",$locationNr);
	
	$dbLink = dbConnect();
	$locationNr = dbEscapeString($dbLink,$locationNr);
	
	$response = array();

	$query = <<<STR
		SELECT 
		    StockNo,
			manufacturerPart_partNumber.Number AS ManufacturerPartNumber,
			vendor_displayName(manufacturerPart_partNumber.VendorId) AS ManufacturerName,
			Date, 
			Cache_Quantity 
		FROM partStock
		LEFT JOIN manufacturerPart_partNumber on partStock.ManufacturerPartNumberId = manufacturerPart_partNumber.Id
		WHERE LocationId = (SELECT Id FROM location where LocNr = '$locationNr')
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
		
		$response[] = $data;
	}

	$query = <<<STR
		SELECT 
		    InvNo, 
		    Title, 
		    Manufacturer, 
		    Type, 
		    LocationId 
		FROM inventory
		WHERE LocationId = (SELECT Id FROM location where LocNr = '$locationNr')
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
		WHERE LocationId = (SELECT Id FROM location where LocNr = '$locationNr')
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
		
		$response[] = $data;
	}

	$query = <<<STR
		SELECT 
			LocNr, 
			location_getName(Id) AS Name 
		FROM location
		WHERE LocationId = (SELECT Id FROM location where LocNr = '$locationNr')
	STR;

	$result = dbRunQuery($dbLink,$query);
		
	while($itemData = mysqli_fetch_assoc($result))
	{
		$descriptor = $itemData["Name"];
		
		$data = array();
		$data["Item"] = barcodeFormatter_LocationNumber($itemData["LocNr"]);
		$data["Category"] = "Location";
		$data["Description"] = $descriptor; 
		
		$response[] = $data;
	}
	
	dbClose($dbLink);	
	sendResponse($response);
}
?>
