<?php
//*************************************************************************************************
// FileName : supplierPartNumber.php
// FilePath : apiFunctions/purchasing
// Author   : Christian Marty
// Date		: 13.12.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

if($api->isGet())
{
    $parameters = $api->getGetData();

    if(!isset($parameters->VendorId)) $api->returnParameterMissingError("VendorId");
    $vendorId = intval($parameters->VendorId);
    if($vendorId === 0) $api->returnParameterError("VendorId");

    $query = <<< STR
    SELECT
        purchaseOrder_itemOrder.Sku AS SupplierPartNumber,
        purchaseOrder_itemOrder.ManufacturerName,
        purchaseOrder_itemOrder.ManufacturerPartNumber,
        purchaseOrder_itemOrder.Description,
        COUNT(purchaseOrder_itemOrder.Sku) AS NumberOfOrders,
        SUM(purchaseOrder_itemOrder.Quantity) AS TotalOrdered
    FROM purchaseOrder_itemOrder 
    LEFT JOIN purchaseOrder ON purchaseOrder.Id = purchaseOrder_itemOrder.PurchaseOrderId
    WHERE purchaseOrder_itemOrder.Sku IS NOT NULL AND purchaseOrder.VendorId = $vendorId
    GROUP BY Sku
    STR;

    $output = $database->query($query);

    $api->returnData($output);
}