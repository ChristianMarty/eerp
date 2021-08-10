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

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	if(!isset($_GET["LocationNr"])) sendResponse(null,"LocationNr not specified");
	$locationNr = strtolower($_GET["LocationNr"]);
	
	if(substr($locationNr,0,4) != "loc-")  sendResponse(null,"Invalid Location Number");
	$locationNr = str_replace("loc-","",$locationNr);
	
	$dbLink = dbConnect();
	$locationNr = dbEscapeString($dbLink,$locationNr);
	
	$response = array();
	
	$query  = "SELECT StockNo, partManufacturer.name AS ManufacturerName, ManufacturerPartNumber, Quantity, Date ";
	$query .= "FROM partStock ";
	$query .= "LEFT JOIN partManufacturer ON partStock.ManufacturerId = partManufacturer.id ";
	$query .= "WHERE LocationId = (SELECT Id FROM location where LocNr = '".$locationNr."')";
	
	$query  = "SELECT * FROM partStock_view WHERE LocationId = (SELECT Id FROM location where LocNr = '".$locationNr."')";
	
	$result = dbRunQuery($dbLink,$query);
		
	while($itemData = mysqli_fetch_assoc($result))
	{
		$descriptor = $itemData["ManufacturerName"]." ".$itemData["ManufacturerPartNumber"];
		$descriptor .= ", ".$itemData["Date"].", Qty: ".$itemData["Quantity"];
		
		$data = array();
		$data["Item"] =  "Stk-".$itemData["StockNo"];
		$data["Category"] = "Stock";
		$data["Description"] = $descriptor; 
		
		array_push($response, $data);
	}
	
	$query  = "SELECT InvNo, Titel, Manufacturer, Type, LocationId FROM inventory ";
	$query .= "WHERE LocationId = (SELECT Id FROM location where LocNr = '".$locationNr."')";
	
	$result = dbRunQuery($dbLink,$query);
		
	while($itemData = mysqli_fetch_assoc($result))
	{
		$descriptor = $itemData["Titel"];
		$descriptor .= " - ".$itemData["Manufacturer"]." ".$itemData["Type"];
		
		$data = array();
		$data["Item"] = "Inv-".$itemData["InvNo"];
		$data["Category"] = "Inventory";
		$data["Description"] = $descriptor; 
		
		array_push($response, $data);
	}
	
	
	$query  = "SELECT LocNr, location_getName(Id) AS Name FROM location ";
	$query .= "WHERE LocationId = (SELECT Id FROM location where LocNr = '".$locationNr."')";
	
	$result = dbRunQuery($dbLink,$query);
		
	while($itemData = mysqli_fetch_assoc($result))
	{
		$descriptor = $itemData["Name"];
		
		$data = array();
		$data["Item"] = "Loc-".$itemData["LocNr"];
		$data["Category"] = "Location";
		$data["Description"] = $descriptor; 
		
		array_push($response, $data);
	}
	
	dbClose($dbLink);	
	sendResponse($response);
}
?>
