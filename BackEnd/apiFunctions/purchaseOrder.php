<?php
//*************************************************************************************************
// FileName : PurchaseOrder.php
// FilePath : apiFunctions/
// Author   : Christian Marty
// Date		: 21.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/util/_barcodeFormatter.php";
require_once __DIR__ . "/util/_barcodeParser.php";

if($api->isGet())
{
	$parameter = $api->getGetData();

	$baseQuery = <<<STR
		SELECT 
		    purchaseOrder.PoNo, 
		    purchaseOrder.CreationDate, 
		    purchaseOrder.PurchaseDate, 
		    purchaseOrder.Title, 
		    purchaseOrder.Description, 
		    purchaseOrder.Status, 
		    purchaseOrder.Id AS PoId,
		    vendor_name_recursive(vendor.Id) AS SupplierName, 
		    vendor.Id AS SupplierId, 
		    purchaseOrder.AcknowledgementNumber, 
		    purchaseOrder.OrderNumber, 
		    finance_currency.CurrencyCode, 
		    finance_currency.Id AS CurrencyId, 
		    purchaseOrder.ExchangeRate, 
		    purchaseOrder.QuotationNumber, 
			SUM(purchaseOrder_itemOrder.Quantity) AS TotalQuantityOrdered, 
			SUM(Received.TotalQuantityReceived) AS TotalQuantityReceived
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
		$purchaseOrderNo = barcodeParser_PurchaseOrderNumber($parameter->PurchaseOrderNo);
		if($purchaseOrderNo === false) $api->returnParameterError("PurchaseOrderNo");
		$queryParam[] = "PoNo = " . $purchaseOrderNo;
	}
	
	if(isset($parameter->VendorId))
	{
		$vendorId = intval($parameter->SupplierPartId);
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

	$result = $database->query($baseQuery,$queryParam,"GROUP BY purchaseOrder.Id ORDER BY purchaseOrder.PoNo DESC");

	foreach ($result as $item)
	{
		if($item->Title == null) $item->Title = $item->SupplierName." - ".$item->PurchaseDate;

		$totalQuantityOrdered =  intval($item->TotalQuantityOrdered);
		$totalQuantityReceived =  intval($item->TotalQuantityReceived);

		$item->TotalQuantityOrdered = $totalQuantityOrdered;
		$item->TotalQuantityReceived = $totalQuantityReceived;

		if($totalQuantityOrdered != 0) $item->ReceiveProgress = intval($totalQuantityReceived/$totalQuantityOrdered*100);
		else $item->ReceiveProgress = 0;
	}

	$api->returnData($result);
}
