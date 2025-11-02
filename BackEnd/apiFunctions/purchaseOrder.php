<?php
//*************************************************************************************************
// FileName : purchaseOrder.php
// FilePath : apiFunctions/
// Author   : Christian Marty
// Date		: 21.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

if($api->isGet(Permission::PurchaseOrder_List))
{
	$parameter = $api->getGetData();

	$baseQuery = <<<STR
		SELECT 
		    purchaseOrder.PurchaseOrderNumber AS PurchaseOrderNumber, 
		    purchaseOrder.PurchaseDate, 
		    purchaseOrder.Title, 
		    purchaseOrder.Description, 
		    purchaseOrder.Status, 
		    purchaseOrder.Id AS PoId,
		    vendor_name_recursive(vendor.Id) AS SupplierName, 
		    vendor.Id AS SupplierId, 
		    purchaseOrder.AcknowledgementNumber, 
		    purchaseOrder.OrderNumber, 
		    purchaseOrder.QuotationNumber, 
			SUM(purchaseOrder_itemOrder.Quantity) AS TotalQuantityOrdered, 
			SUM(Received.TotalQuantityReceived) AS TotalQuantityReceived,
			MAX(purchaseOrder_itemOrder.ExpectedReceiptDate) AS ExpectedCompletionDate
		FROM purchaseOrder
		LEFT JOIN vendor ON vendor.Id = purchaseOrder.VendorId
		LEFT JOIN finance_currency ON finance_currency.Id = purchaseOrder.CurrencyId
		LEFT JOIN purchaseOrder_itemOrder ON purchaseOrder_itemOrder.PurchaseOrderId = purchaseOrder.Id
		LEFT JOIN (
			SELECT ItemOrderId, SUM(QuantityReceived) AS TotalQuantityReceived FROM purchaseOrder_itemReceive GROUP BY purchaseOrder_itemReceive.ItemOrderId
		)Received  ON Received.ItemOrderId = purchaseOrder_itemOrder.Id
	STR;

	$queryParam = array();
	
	if(isset($parameter->PurchaseOrderNo))
	{
		$purchaseOrderNo = \Numbering\parser(\Numbering\Category::PurchaseOrder, $parameter->PurchaseOrderNo);
		if($purchaseOrderNo === false) $api->returnParameterError("PurchaseOrderNo");
		$queryParam[] = "PurchaseOrderNumber = " . $purchaseOrderNo;
	}
	
	if(isset($parameter->VendorId))
	{
		$vendorId = intval($parameter->VendorId);
		if($vendorId === 0) $api->returnParameterError("VendorId");
		$queryParam[] = "VendorId = $vendorId";
	}

	if(isset($parameter->SupplierPartId))
	{
		$supplierPartId = intval($parameter->SupplierPartId);
		if($supplierPartId === 0) $api->returnParameterError("SupplierPartId");
		$queryParam[] = "SupplierPartId = $supplierPartId";
	}
	
	if(isset($parameter->HideClosed) && $parameter->HideClosed)
	{
		$queryParam[] = "Status != 'Closed'";
	}
	else if(isset($parameter->Status))
	{
		$status = $database->escape($parameter->Status);
		$queryParam[] = "Status = $status";
	}

	$result = $database->query($baseQuery,$queryParam,"GROUP BY purchaseOrder.Id ORDER BY purchaseOrder.PurchaseOrderNumber DESC");
    \Error\checkErrorAndExit($result);

	foreach ($result as $item)
	{
        $item->ItemCode = \Numbering\format(\Numbering\Category::PurchaseOrder, $item->PurchaseOrderNumber);

		if($item->Title == null) $item->Title = $item->SupplierName." - ".$item->PurchaseDate;

        $item->TotalQuantityOrdered =  intval($item->TotalQuantityOrdered);
        $item->TotalQuantityReceived =  intval($item->TotalQuantityReceived);

		if($item->TotalQuantityOrdered != 0) $item->ReceiveProgress = intval($item->TotalQuantityReceived/$item->TotalQuantityOrdered*100);
		else $item->ReceiveProgress = 0;

        $item->AcknowledgementNumber = $item->AcknowledgementNumber??"";
        $item->QuotationNumber = $item->QuotationNumber??"";

        unset($item->PoId);
        unset($item->CurrencyId);
        unset($item->TotalQuantityOrdered);
        unset($item->TotalQuantityReceived);

	}

	$api->returnData($result);
}
