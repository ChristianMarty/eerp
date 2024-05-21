<?php
//*************************************************************************************************
// FileName : received.php
// FilePath : apiFunctions/purchasing/item/
// Author   : Christian Marty
// Date		: 21.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;
global $user;

if($api->isGet())
{
    $parameter = $api->getGetData();
    if(!isset($parameter->ReceivalId)) $api->returnParameterMissingError("ReceivalId");

	$receivalId = intval($parameter->ReceivalId);
    $query = <<<STR
        SELECT 
            purchaseOrder_itemReceive.Id as ReceivalId, 
            manufacturer.Name AS ManufacturerName, 
            manufacturerPart_partNumber.Number AS ManufacturerPartNumber,
            supplier.Name AS SupplierName, 
            supplierPart.SupplierPartNumber, 
            purchaseOrder_itemReceive.QuantityReceived, 
            purchaseOrder_itemOrder.OrderReference, 
            purchaseOrder_itemOrder.SupplierPartId,
            purchaseOrder_itemOrder.Note as OrderNote
        FROM purchaseOrder_itemReceive
        LEFT JOIN purchaseOrder_itemOrder ON purchaseOrder_itemOrder.Id = purchaseOrder_itemReceive.ItemOrderId
        LEFT JOIN purchaseOrder ON purchaseOrder.Id = purchaseOrder_itemOrder.PurchaseOrderId
        LEFT JOIN supplierPart ON supplierPart.Id = purchaseOrder_itemOrder.SupplierPartId
        LEFT JOIN (SELECT Id, vendor_displayName(Id) AS Name FROM vendor)supplier ON supplier.Id = supplierPart.VendorId
        LEFT JOIN manufacturerPart_partNumber ON manufacturerPart_partNumber.Id = supplierPart.ManufacturerPartNumberId
        LEFT JOIN manufacturerPart_item ON manufacturerPart_item.Id = manufacturerPart_partNumber.ItemId
        LEFT JOIN manufacturerPart_series ON manufacturerPart_series.Id = manufacturerPart_item.SeriesId
        LEFT JOIN (SELECT Id, vendor_displayName(Id) AS Name  FROM vendor)manufacturer  ON manufacturer.Id = manufacturerPart_partNumber.VendorId OR manufacturer.Id = manufacturerPart_item.VendorId OR manufacturer.Id = manufacturerPart_series.VendorId
        WHERE purchaseOrder_itemReceive.Id = $receivalId
    STR;

    $output = $database->query($query)[0]??null;
    $api->returnData($output);
}
else if($api->isPost("purchasing.confirm"))
{
    $data = $api->getPostData();
    if(!isset($data->LineId)) $api->returnParameterMissingError("LineId");
    if(!isset($data->ReceivedQuantity)) $api->returnParameterMissingError("ReceivedQuantity");
    if(!isset($data->ReceivedDate)) $api->returnParameterMissingError("ReceivedDate");

	$row = array();
	$row['ItemOrderId'] = $data->LineId;
	$row['QuantityReceived'] = $data->ReceivedQuantity;
	$row['ReceivalDate'] = $data->ReceivedDate;
	$row['CreationUserId'] = $user->userId();

    $output = array();
    $output["ReceivalId"] = $database->insert("purchaseOrder_itemReceive", $row);

    $api->returnData($output);
}
