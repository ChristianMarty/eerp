<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/productionPart
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	
	if(!isset($_GET["PartNo"])) sendResponse(NULL, "Part Number Undefined");

	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$partNo = dbEscapeString($dbLink, $_GET["PartNo"]);

	$query = "SELECT manufacturerPart.Id AS PartId, partManufacturer.Name AS ManufacturerName, manufacturerPart.ManufacturerPartNumber, manufacturerPart.Status AS LifecycleStatus, partStock.StockNo, partStock.Date, partStock_getQuantity(partStock.StockNo) AS Quantity, location_getName(partStock.LocationId) AS LocationName FROM manufacturerPart "; 
	$query .= "LEFT JOIN partManufacturer ON partManufacturer.Id = manufacturerPart.ManufacturerId ";
	$query .= "LEFT JOIN partStock ON partStock.ManufacturerPartId = manufacturerPart.Id ";
	$query .= "LEFT JOIN productionPart ON productionPart.ManufacturerPartId = manufacturerPart.Id ";
	$query .= "WHERE productionPart.PartNo = '".$partNo."'";
	

	$result = mysqli_query($dbLink,$query);
	
	$rows = array();
	$rowcount = mysqli_num_rows($result);
	
	$rows['Stock'] = array();
	array_push($rows['Stock'], array());
	
	while($r = mysqli_fetch_assoc($result)) 
	{
		if(!array_key_exists($r['PartId'],$rows['Stock']))
		{
			$Part = array();
			$Part['ManufacturerName'] = $r['ManufacturerName'];
			$Part['ManufacturerPartNumber'] = $r['ManufacturerPartNumber'];
			$Part['LifecycleStatus'] = $r['LifecycleStatus'];
			$Part['PartId'] = $r['PartId'];
		
			$rows['Stock'][$r['PartId']] = $Part;
			$rows['Stock'][$r['PartId']]['Stock'] = array();
		}
		
		$StockRow = array();
		$StockRow['StockNo'] = $r['StockNo'];
		$StockRow['Date'] = $r['Date'];
		$StockRow['Quantity'] = $r['Quantity'];
		$StockRow['LocationName'] = $r['LocationName'];
		$StockRow['PartId'] = $r['PartId']+10;
				
		array_push($rows['Stock'][$r['PartId']]['Stock'], $StockRow);	
	}
	unset($rows['Stock'][0]);
	$rows['Stock'] = array_values($rows['Stock']);
	
	$totalStockQuantity = 0;
	foreach($rows['Stock'] as &$item)
	{
		$totalPartQuantity = 0;
		foreach($item['Stock'] as $StockItem) $totalPartQuantity += $StockItem['Quantity'];
		$item['Quantity'] = $totalPartQuantity;
		$totalStockQuantity += $totalPartQuantity;
	}
	
	$rows['TotalStockQuantity'] = $totalStockQuantity;
	
	dbClose($dbLink);
	
	
	$rows['ManufacturerPart'] = array();
	$query = "SELECT partManufacturer.Name AS ManufacturerName, ManufacturerPartNumber, Description FROM partLookup "; 
	$query .= "LEFT JOIN partManufacturer ON partManufacturer.Id = partLookup.ManufacturerId ";
	$query .= "WHERE partLookup.PartNo = '".$partNo."'";
	

	
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	
	$result = mysqli_query($dbLink,$query);
	
	
	while($r = mysqli_fetch_assoc($result)) 
	{
		array_push($rows['ManufacturerPart'], $r);
	}
	
dbClose($dbLink);

	sendResponse($rows);
}

?>