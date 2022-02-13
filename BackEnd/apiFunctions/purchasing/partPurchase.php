<?php
//*************************************************************************************************
// FileName : partPurchase.php
// FilePath : apiFunctions/purchasing
// Author   : Christian Marty
// Date		: 15.01.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	
	if(!isset($_GET["ManufacturerPartId"]) && !isset($_GET["ProductionPartNo"])) sendResponse(NULL,"ManufacturerPartId or ProductionPartNo Required!");
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	if(isset($_GET["ManufacturerPartId"]))
	{
		
		$query  = "SELECT * FROM purchasOrder_itemOrder ";
		$query .= "LEFT JOIN purchasOrder ON purchasOrder.Id = purchasOrder_itemOrder.PurchasOrderId ";
		
		$parameters = array();
		array_push($parameters, 'purchasOrder_itemOrder.ManufacturerPartId= '.dbEscapeString($dbLink, $_GET["ManufacturerPartId"]));
	}
	else if(isset($_GET["ProductionPartNo"]))
	{
		$query  = "SELECT * FROM purchasOrder_itemOrder ";
		$query .= "LEFT JOIN purchasOrder ON purchasOrder.Id = purchasOrder_itemOrder.PurchasOrderId ";
		$query .= "LEFT JOIN productionPartMapping ON productionPartMapping.ManufacturerPartId = purchasOrder_itemOrder.ManufacturerPartId ";
		$query .= "LEFT JOIN productionPart ON productionPart.Id = productionPartMapping.ProductionPartId ";
		
		$parameters = array();
		array_push($parameters, "productionPart.PartNo = '".dbEscapeString($dbLink, $_GET["ProductionPartNo"])."'");
	}
	else
	{
		sendResponse(NULL,"Parameter Error!");
	}
	
	$query = dbBuildQuery($dbLink, $query, $parameters);
	$result = dbRunQuery($dbLink,$query);

	$rows = array();
	$rowcount = mysqli_num_rows($result);
	$totalQuantity = 0;
	$pendingQuantity = 0;
	
	while($r = mysqli_fetch_assoc($result)) 
	{
		unset($r['Id']);
		$totalQuantity += $r['Quantity'];
		
		array_push($rows,$r);	
	}
	
	$output = array();
	$output['TotalOrderQuantity'] = $totalQuantity;
	$output['PendingOrderQuantity'] = $pendingQuantity;
	$output['PurchaseOrderData'] = $rows;
	
	dbClose($dbLink);	
	sendResponse($output);
}
?>