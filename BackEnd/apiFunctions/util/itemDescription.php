<?php
//*************************************************************************************************
// FileName : itemDescription.php
// FilePath : apiFunctions/utils/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require __DIR__ . "/../databaseConnector.php";


//Generates an universal description of a item of any catogery

function generateSummary($locationNr)
{
	$response['data'] = null;
	$response['error'] = null;
 
	$dbLink = dbConnect();
	if($dbLink == null)
	{
		$response['error'] ="Database connection failed";
		return $response;
	}

	$itemPrefix = "";
	$itemNr = "";
	$itemPostfix = "";
	
	$temp = explode("-", $locationNr);
	$itemPrefix = strtolower($temp[0]);
	$itemNr = dbEscapeString($dbLink, strtolower($temp[1]));	
	
	$data = array();
	$locationId = null;
	
	if($itemPrefix == "stk")
	{
		$query = "SELECT * FROM partStock_view WHERE StockNo = '".$itemNr."'";

		$result = dbRunQuery($dbLink,$query);
		
		if(mysqli_num_rows($result) == 0)
		{
			$response['error'] ="Item not found";
			return $response;
		}			
		
		$itemData = mysqli_fetch_assoc($result);
		
		$descriptor = $itemData["ManufacturerName"]." ".$itemData["ManufacturerPartNumber"];
		$descriptor .= ", ".$itemData["Date"].", Qty: ".$itemData["Quantity"];
		
		$locationId = $itemData["LocationId"];
		
		$data["Item"] =  "Stk-".strtoupper($itemNr);
		$data["Category"] = "Stock";
		$data["Description"] = $descriptor; 
		$data["Movable"] = true;
	}
	else if($itemPrefix == "inv")  
	{
		$query  = "SELECT Title, Manufacturer, Type, LocationId FROM inventory ";
		$query .= "WHERE InvNo = '".$itemNr."'";
		
		$result = dbRunQuery($dbLink,$query);
		
		if(mysqli_num_rows($result) == 0)
		{
			$response['error'] ="Item not found";
			return $response;
		}
		
		$itemData = mysqli_fetch_assoc($result);
		
		$descriptor = $itemData["Title"];
		$descriptor .= " - ".$itemData["Manufacturer"]." ".$itemData["Type"];
		
		$locationId = $itemData["LocationId"];
		
		$data["Item"] = "Inv-".$itemNr;
		$data["Category"] = "Inventory";
		$data["Description"] = $descriptor; 
		$data["Movable"] = true;
	}
	else if($itemPrefix == "loc")
	{
		$query = "SELECT LocNr, location_getName(Id) AS Name, Movable, location_getName(LocationId) AS LocationName from location where LocNr = ".$itemNr;
		$result = dbRunQuery($dbLink,$query);
		
		if(mysqli_num_rows($result) == 0)
		{
			$response['error'] ="Item not found";
			return $response;
		}
		
		$itemData = mysqli_fetch_assoc($result);
		
		$data["Item"] = "Loc-".$itemNr;
		$data["Category"] = "Location";
		$data["Description"] = $itemData["Name"]; 
		$data["LocationNr"] = "Loc-".$itemData["LocNr"];
		$data["Location"] = $itemData["LocationName"]; 
		if($itemData["Movable"] == "1") $data["Movable"] = true;
		else $data["Movable"] = false;
	}
	else 
	{
		
		
		
		$response['error'] ="Unknown Item Category";
		return $response;
	}

	if($locationId != null)
	{
		$query = "SELECT LocNr, NAME, location_getName(Id) AS LocationName from location where Id = ".$locationId;
		$result = dbRunQuery($dbLink,$query);
		$itemData = mysqli_fetch_assoc($result);
		
		$data["LocationNr"] = "Loc-".$itemData["LocNr"];
		$data["Location"] = $itemData["LocationName"]; 
	}
	
	dbClose($dbLink);
	
	$response['data'] = $data;
	return $response;
}

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	if(!isset($_GET["Item"])) sendResponse($output,"No item specified");
	
	$data = generateSummary($_GET["Item"]);
		
	sendResponse($data['data'], $data['error']);
} 


	
?>