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
require_once __DIR__ . "/../../util/_barcodeFormatter.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	if(!isset($_GET["InventoryNumber"]) ) sendResponse(Null,"Inventory Number not set");
	
	$inventoryNumber = barcodeParser_InventoryNumber($_GET["InventoryNumber"]);
	if(!$inventoryNumber)sendResponse(Null,"Inventory Number invalid");
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	// Get Purchase Information
    $query = <<< STR
        SELECT 
            PoNo, 
            purchaseOrder_itemOrder.LineNo AS LineNumber , 
            purchaseOrder_itemOrder.Description, 
            vendor_displayName(vendor.Id) AS SupplierName, 
            purchaseOrder.VendorId AS SupplierId, 
            Price, 
            PurchaseDate, 
            inventory_purchaseOrderReference.Quantity,  
            finance_currency.CurrencyCode AS Currency, 
            ExchangeRate, 
            purchaseOrder_itemOrder.Sku AS SupplierPartNumber, 
            purchaseOrder_itemReceive.Id AS ReceivalId, 
            inventory_purchaseOrderReference.Type AS CostType
        FROM inventory_purchaseOrderReference
        LEFT JOIN purchaseOrder_itemReceive ON inventory_purchaseOrderReference.ReceivalId = purchaseOrder_itemReceive.Id
        LEFT JOIN purchaseOrder_itemOrder ON purchaseOrder_itemReceive.ItemOrderId = purchaseOrder_itemOrder.Id
        LEFT JOIN purchaseOrder ON purchaseOrder_itemOrder.PurchaseOrderId = purchaseOrder.Id
        LEFT JOIN vendor ON purchaseOrder.VendorId = vendor.Id
        LEFT JOIN finance_currency ON purchaseOrder.CurrencyId = finance_currency.Id
        WHERE inventory_purchaseOrderReference.InventoryId = (SELECT Id from inventory WHERE InvNo = $inventoryNumber)
    
    STR;

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
			$por["Quantity"] = floatval($por['Quantity']);
			$por["PurchaseOrderNumber"] = $por['PoNo'];
			$por["PurchaseOrderBarcode"] = barcodeFormatter_PurchaseOrderNumber($por['PoNo'],$por['LineNumber']);
			$por['PoNo'] = barcodeFormatter_PurchaseOrderNumber($por['PoNo']);
			
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
    $inventoryNumber = barcodeParser_InventoryNumber($data["InventoryNumber"]);
	$purchaseOrderItems =  $data["PurchaseOrderItems"];
	
	$dbLink = dbConnect();
	
	$query  = "SELECT Id FROM inventory ";
	$query .= "WHERE InvNo = {$inventoryNumber}";	
	$result = dbRunQuery($dbLink,$query);
	$id = mysqli_fetch_assoc($result)['Id'];

	// Add elements
	$error = null;
	$receivalIdList = array();
	foreach($purchaseOrderItems as $item)
	{
        $costType = dbEscapeString($dbLink,$item['CostType']);
		$quantity = dbEscapeString($dbLink,$item['Quantity']);
		$receivalId = dbEscapeString($dbLink,$item['ReceivalId']);
		$receivalIdList[] = $receivalId;

        $query = <<< STR
            INSERT IGNORE INTO inventory_purchaseOrderReference 
            SET InventoryId = $id, Quantity = $quantity, ReceivalId = $receivalId, Type = '$costType';
        STR;

		$result = dbRunQuery($dbLink,$query);
		
		if(!$result)
		{
			$error = "Error description: " . mysqli_error($dbLink);
			break;
		}

	}

    $query = <<< STR
        DELETE FROM inventory_purchaseOrderReference WHERE InventoryId = $id;
    STR;

    if(!empty($receivalIdList))
    {
        $temp = implode(", ", $receivalIdList);
        $query .= "AND NOT ReceivalId IN({$temp});";
    }

	$result = dbRunQuery($dbLink,$query);

	if(!$result) $error = "Error description: " . mysqli_error($dbLink);
	
	dbClose($dbLink);	
	sendResponse(null,$error);
}
?>