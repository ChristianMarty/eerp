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

	$query = "SELECT manufacturerPart.Id AS PartId, partManufacturer.Name AS ManufacturerName, manufacturerPart.ManufacturerPartNumber, manufacturerPart.Status AS LifecycleStatus, partStock.StockNo, partStock.Date, partStock_getQuantity(partStock.StockNo) AS Quantity, productionPart_stockNotification.StockMinimum, productionPart_stockNotification.StockMaximum, productionPart_stockNotification.StockWarning, location_getName(partStock.LocationId) AS LocationName FROM manufacturerPart "; 
	$query .= "LEFT JOIN partManufacturer ON partManufacturer.Id = manufacturerPart.ManufacturerId ";
	$query .= "LEFT JOIN partStock ON partStock.ManufacturerPartId = manufacturerPart.Id ";
	$query .= "LEFT JOIN productionPart ON productionPart.ManufacturerPartId = manufacturerPart.Id ";
	$query .= "LEFT JOIN productionPart_stockNotification ON productionPart_stockNotification.PartNo = productionPart.PartNo ";
	$query .= "WHERE productionPart.PartNo = '".$partNo."'";
	

	$result = mysqli_query($dbLink,$query);
	
	$rows = array();
	$rowcount = mysqli_num_rows($result);
	

	$manufacturerParts = array();
	
	array_push($manufacturerParts, array());
	
	while($r = mysqli_fetch_assoc($result)) 
	{
		$rows['StockMinimum'] = $r['StockMinimum'];
		$rows['StockMaximum'] = $r['StockMaximum'];
		$rows['StockWarning'] = $r['StockWarning'];
	
		if(!array_key_exists($r['PartId'],$manufacturerParts))
		{
			$Part = array();
			$Part['ManufacturerName'] = $r['ManufacturerName'];
			$Part['ManufacturerPartNumber'] = $r['ManufacturerPartNumber'];
			$Part['LifecycleStatus'] = $r['LifecycleStatus'];
			$Part['PartId'] = $r['PartId'];
			$Part['Description'] = "";
			
		
			$manufacturerParts[$r['PartId']] = $Part;
			$manufacturerParts[$r['PartId']]['Stock'] = array();
		}
		
		$StockRow = array();
		$StockRow['StockNo'] = $r['StockNo'];
		$StockRow['Date'] = $r['Date'];
		$StockRow['Quantity'] = $r['Quantity'];
		$StockRow['LocationName'] = $r['LocationName'];
		$StockRow['PartId'] = $r['PartId']+10;
		
				
		array_push($manufacturerParts[$r['PartId']]['Stock'], $StockRow);	
	}
	unset($manufacturerParts[0]);
	$manufacturerParts = array_values($manufacturerParts);
	
	$totalStockQuantity = 0;
	foreach($manufacturerParts as &$item)
	{
		$totalPartQuantity = 0;
		foreach($item['Stock'] as $StockItem) $totalPartQuantity += $StockItem['Quantity'];
		$item['Quantity'] = $totalPartQuantity;
		$totalStockQuantity += $totalPartQuantity;
	}
	
	$rows['TotalStockQuantity'] = $totalStockQuantity;
	$rows['ManufacturerParts'] = $manufacturerParts;
	
	
	dbClose($dbLink);

	sendResponse($rows);
}

?>