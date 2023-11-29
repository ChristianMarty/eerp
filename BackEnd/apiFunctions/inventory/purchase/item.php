<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/inventory/purchase/
// Author   : Christian Marty
// Date		: 21.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/../../util/_barcodeParser.php";
require_once __DIR__ . "/../../util/_barcodeFormatter.php";

if($api->isGet("inventory.purchase.view"))
{
    $parameter = $api->getGetData();
    if(!isset($parameter->InventoryNumber)) $api->returnParameterMissingError("InventoryNumber");
    $inventoryNumber = barcodeParser_InventoryNumber($parameter->InventoryNumber);
    if(empty($inventoryNumber)) $api->returnParameterError("InventoryNumber");

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

    $result = $database->query($query);
    foreach($result as &$item)
    {
        $item->PurchaseOrderNumber = $item->PoNo;
        $item->PurchaseOrderBarcode = barcodeFormatter_PurchaseOrderNumber($item->PoNo, $item->LineNumber);
        $item->PoNo = barcodeFormatter_PurchaseOrderNumber($item->PoNo);
    }

    $api->returnData($result);
}
else if($api->isPatch("inventory.purchase.edit"))
{
    $data = $api->getPostData();
    if(!isset($data->InventoryNumber)) $api->returnParameterMissingError('InventoryNumber');
    if(!isset($data->PurchaseOrderItems)) $api->returnParameterMissingError('PurchaseOrderItems');
    $inventoryNumber = barcodeParser_InventoryNumber($data->InventoryNumber);
    if(empty($inventoryNumber)) $api->returnParameterError("InventoryNumber");

	$purchaseOrderItems =  $data->PurchaseOrderItems;

    $query = <<< STR
        SELECT 
            Id 
        FROM inventory 
        WHERE InvNo = {$inventoryNumber};
    STR;
    $id = $database->query($query)[0]->Id;

	$receivalIdList = array();
	foreach($purchaseOrderItems as $item)
	{
        $costType = $database->escape($item->CostType);
		$quantity = $database->escape($item->Quantity);
		$receivalId = $database->escape($item->ReceivalId);
		$receivalIdList[] = $receivalId;

        $query = <<< STR
            INSERT IGNORE INTO inventory_purchaseOrderReference 
            SET InventoryId = $id, Quantity = $quantity, ReceivalId = $receivalId, Type = $costType;
        STR;

		$database->query($query);
	}

    $query = <<< STR
        DELETE FROM inventory_purchaseOrderReference WHERE InventoryId = $id
    STR;

    if(!empty($receivalIdList))
    {
        $temp = implode(", ", $receivalIdList);
        $query .= " AND NOT ReceivalId IN({$temp});";
    }

    $database->query($query);
    $api->returnEmpty();
}
