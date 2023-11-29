<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/finance/costCenter/
// Author   : Christian Marty
// Date		: 29.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/../../util/_barcodeFormatter.php";
require_once __DIR__ . "/../../util/_barcodeParser.php";

if($api->isGet())
{
    $parameter = $api->getGetData();
    if(!isset($parameter->CostCenterNumber)) $api->returnParameterMissingError("CostCenterNumber");
    $costCenterNumber = barcodeParser_CostCenter($parameter->CostCenterNumber);
    if(empty($costCenterNumber)) $api->returnParameterError("CostCenterNumber");

    $query = <<<STR
        SELECT * FROM finance_costCenter
        WHERE CostCenterNumber = '$costCenterNumber'
    STR;

	$costCenterData = $database->query($query)[0];
    $costCenterData->Barcode = barcodeFormatter_CostCenter($costCenterData->CostCenterNumber);
    $costCenterDataId =  $costCenterData->Id;

    $query = <<<STR
        SELECT 
            purchaseOrder.PurchaseDate, 
            vendor_displayName(supplier.Id) AS Supplier, 
            purchaseOrder.PoNo AS PoNo,
            purchaseOrder_itemOrder.LineNo, 
            purchaseOrder_itemOrder.OrderReference, 
            purchaseOrder_itemOrder.PartNo,
            purchaseOrder_itemOrder.ManufacturerName, -- manufacturer.Name AS Manufacturer,
            purchaseOrder_itemOrder.ManufacturerPartNumber, 
            purchaseOrder_itemOrder.Description,
            purchaseOrder_itemOrder.Note,
            purchaseOrder_itemOrder.ExpectedReceiptDate, 
            purchaseOrder_itemOrder.Quantity,
            purchaseOrder_itemOrder.Price,
            finance_currency.CurrencyCode AS Currency,
            purchaseOrder.ExchangeRate, 
            purchaseOrder.PaymentTerms, 
            purchaseOrder.Title,
            GROUP_CONCAT(CONCAT(numbering.Prefix,'-',productionPart.Number) ) AS ProductionPartNumberList 
        FROM purchaseOrder_itemOrder
        LEFT JOIN purchaseOrder_itemOrder_costCenter_mapping ON purchaseOrder_itemOrder_costCenter_mapping.ItemOrderId = purchaseOrder_itemOrder.Id 
        LEFT JOIN purchaseOrder ON purchaseOrder.Id = purchaseOrder_itemOrder.PurchaseOrderId 
        LEFT JOIN finance_currency ON finance_currency.Id = purchaseOrder.CurrencyId 
        LEFT JOIN vendor AS supplier ON supplier.Id = purchaseOrder.VendorId
        LEFT JOIN supplierPart ON supplierPart.Id = purchaseOrder_itemOrder.SupplierPartId 
        LEFT JOIN manufacturerPart_partNumber ON manufacturerPart_partNumber.Id = supplierPart.ManufacturerPartNumberId
        LEFT JOIN productionPart_manufacturerPart_mapping ON  productionPart_manufacturerPart_mapping.ManufacturerPartNumberId = manufacturerPart_partNumber.Id
        LEFT JOIN productionPart ON productionPart.Id = productionPart_manufacturerPart_mapping.ProductionPartId
        LEFT JOIN numbering on productionPart.NumberingPrefixId = numbering.Id
        -- LEFT JOIN vendor AS manufacturer ON manufacturer.Id = manufacturerPart.VendorId
        WHERE purchaseOrder_itemOrder_costCenter_mapping.CostCenterId = '$costCenterDataId'
        GROUP BY purchaseOrder_itemOrder.Id
    STR;

    $result = $database->query($query);
    foreach($result as $item) {
        $item->PurchaseOrderBarcode = barcodeFormatter_PurchaseOrderNumber($item->PoNo, $item->LineNo);
        $item->LineTotal = round($item->Price*$item->Quantity,6);
        $item->ProductionPartNumber = $item->ProductionPartNumberList;
    }

    $costCenterData->PurchaseItem = $result;
    $api->returnData($costCenterData);
}
