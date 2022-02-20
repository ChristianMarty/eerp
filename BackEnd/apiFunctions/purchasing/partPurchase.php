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
	
	$query  = "SELECT *, SUM(QuantityReceived) AS TotalQuantityReceived FROM purchasOrder_itemOrder ";
	$query .= "LEFT JOIN supplierPart ON supplierPart.Id = purchasOrder_itemOrder.SupplierPartId  ";
	$query .= "LEFT JOIN purchasOrder ON purchasOrder.Id = purchasOrder_itemOrder.PurchasOrderId ";
	$query .= "LEFT JOIN productionPartMapping ON productionPartMapping.ManufacturerPartId = supplierPart.ManufacturerPartId ";
	$query .= "LEFT JOIN purchasOrder_itemReceive ON purchasOrder_itemReceive.ItemOrderId = purchasOrder_itemOrder.Id ";
	$query .= "LEFT JOIN productionPart ON productionPart.Id = productionPartMapping.ProductionPartId ";
	
	$parameters = array();
	
	if(isset($_GET["ManufacturerPartId"]))
	{
		array_push($parameters, 'supplierPart.ManufacturerPartId = '.dbEscapeString($dbLink, $_GET["ManufacturerPartId"]));
	}
	else if(isset($_GET["ProductionPartNo"]))
	{
		array_push($parameters, "productionPart.PartNo = '".dbEscapeString($dbLink, $_GET["ProductionPartNo"])."'");
	}
	else
	{
		sendResponse(NULL,"Parameter Error!");
	}
	
	$query = dbBuildQuery($dbLink, $query, $parameters);
	
	$query .= " GROUP BY purchasOrder_itemOrder.PurchasOrderId";
	
	$result = dbRunQuery($dbLink,$query);

	$rows = array();
	$rowcount = mysqli_num_rows($result);
	$totalQuantity = 0;
	$receivedQuantity = 0;
	
	while($r = mysqli_fetch_assoc($result)) 
	{
		unset($r['Id']);
		$totalQuantity += $r['Quantity'];
		$receivedQuantity += $r['TotalQuantityReceived'];
		
		array_push($rows,$r);	
	}
	
	$output = array();
	$output['TotalOrderQuantity'] = $totalQuantity;
	$output['PendingOrderQuantity'] = $totalQuantity - $receivedQuantity;
	$output['ReceivedOrderQuantity'] = $receivedQuantity;
	$output['PurchaseOrderData'] = $rows;
	
	dbClose($dbLink);	
	sendResponse($output);
}
?>