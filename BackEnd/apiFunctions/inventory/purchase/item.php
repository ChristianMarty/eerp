<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/inventory/purchase/
// Author   : Christian Marty
// Date		: 25.09.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../databaseConnector.php";
require __DIR__ . "/../../../config.php";
require_once __DIR__ . "/../../util/_json.php";
require_once __DIR__ . "/../../util/_barcodeParser.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	if(!isset($_GET["InventoryNumber"]) ) sendResponse(Null,"Inventory Number not set");
	
	$inventoryNumber = barcodeParser_InventoryNumber($_GET["InventoryNumber"]);
	if(!$inventoryNumber)sendResponse(Null,"Inventory Number invalide");
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	// Get Purchase Information
	$query  = "SELECT  PoNo, purchasOrder_itemOrder.LineNo AS LineNumber , purchasOrder_itemOrder.Description, vendor.Name AS SupplierName, purchasOrder.VendorId AS SupplierId, Price, PurchaseDate, inventory_purchasOrderReference.Quantity,  finance_currency.CurrencyCode AS Currency, ExchangeRate, purchasOrder_itemOrder.Sku AS SupplierPartNumber, purchasOrder_itemReceive.Id AS ReceivalId ";
	$query .= "FROM inventory_purchasOrderReference ";
	$query .= "LEFT JOIN purchasOrder_itemReceive ON inventory_purchasOrderReference.ReceivalId = purchasOrder_itemReceive.Id ";
	$query .= "LEFT JOIN purchasOrder_itemOrder ON purchasOrder_itemReceive.ItemOrderId = purchasOrder_itemOrder.Id ";
	$query .= "LEFT JOIN purchasOrder ON purchasOrder_itemOrder.PurchasOrderId = purchasOrder.Id ";
	$query .= "LEFT JOIN vendor ON purchasOrder.VendorId = vendor.Id ";
	$query .= "LEFT JOIN finance_currency ON purchasOrder.CurrencyId = finance_currency.Id ";
	$query .= "WHERE inventory_purchasOrderReference.InventoryId = (SELECT Id from inventory WHERE InvNo = {$inventoryNumber})";
	
	$purchase = array();
	$result = dbRunQuery($dbLink,$query);
	
	$error = null;
	
	if(!$result)
	{
		$error = "Error description: " . mysqli_error($dbLink);
	}
	else
	{
		while($por = mysqli_fetch_assoc($result))
		{	
			$por["ReceivalId"] = intval($por['ReceivalId']);
			$por["Quantity"] = intval($por['Quantity']);
			$por["PurchaseOrderNumber"] = $por['PoNo'];
			$por["PurchaseOrderBarcode"] = "PO-".$por['PoNo']."#".$por['LineNumber'];
			$por['PoNo'] ="PO-".$por['PoNo']; 
			
			
			//$totalPrice += ($por["Price"]*$por["ExchangeRate"])*$por['Quantity']; 
			
			$purchase[] = $por;
		}
	}

	dbClose($dbLink);	
	sendResponse($purchase,$error);
}

else if($_SERVER['REQUEST_METHOD'] == 'PATCH')
{
	$data = json_decode(file_get_contents('php://input'),true);
	
	if(!isset($data["InventoryNumber"]) ) sendResponse(Null,"Inventory Number not set");
	
	$inventoryNumber = $data["InventoryNumber"];
	$inventoryNumber = strtolower($inventoryNumber);
	$inventoryNumber = str_replace("inv-","",$inventoryNumber);
	
	$purchaseOrderItems =  $data["PurchaseOrderItems"];
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$query  = "SELECT Id FROM inventory ";
	$query .= "WHERE InvNo = {$inventoryNumber}";	
	$result = dbRunQuery($dbLink,$query);
	$id = mysqli_fetch_assoc($result)['Id'];

	// Add elements
	$error = null;
	$receivalIdList = array();
	foreach($purchaseOrderItems as $item)
	{
		
		$quantity = dbEscapeString($dbLink,$item['Quantity']);
		$receivalId = dbEscapeString($dbLink,$item['ReceivalId']);
		$receivalIdList[] = $receivalId;
		
		$query  = "INSERT IGNORE INTO inventory_purchasOrderReference SET ";
		$query .= "InventoryId = {$id}, Quantity = {$quantity}, ReceivalId = {$receivalId};";
		
		$result = dbRunQuery($dbLink,$query);
		
		if(!$result)
		{
			$error = "Error description: " . mysqli_error($dbLink);
			break;
		}

	}
	
	$temp = implode(", ", $receivalIdList);
	$query  = "DELETE FROM inventory_purchasOrderReference ";
	$query .= "WHERE InventoryId = {$id} AND NOT ReceivalId IN({$temp});";
	
	$result = dbRunQuery($dbLink,$query);

	if(!$result) $error = "Error description: " . mysqli_error($dbLink);
	
	dbClose($dbLink);	
	sendResponse(null,$error);
}
?>