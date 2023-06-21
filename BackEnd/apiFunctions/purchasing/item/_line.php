<?php
//*************************************************************************************************
// FileName : _line.php
// FilePath : apiFunctions/purchasing/item/
// Author   : Christian Marty
// Date		: 19.06.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../util/_barcodeFormatter.php";

function purchaseOrderItem_getLineQuery($purchaseOrderId, $lineId = null):string
{
    $query = <<<STR
    SELECT 
        purchasOrder_itemOrder.LineNo,
        purchasOrder_itemOrder.Price,
        purchasOrder_itemOrder.Sku,
        purchasOrder_itemOrder.Type AS LineType,
        purchasOrder_itemOrder.Quantity,
        purchasOrder_itemOrder.Id AS OrderLineId, 
        unitOfMeasurement.Symbol AS UnitOfMeasurementSymbol, 
        unitOfMeasurement.Id AS UnitOfMeasurementId,
        purchasOrder_itemOrder.PurchasOrderId,
        purchasOrder_itemOrder.PartNo,
        purchasOrder_itemOrder.ManufacturerName,
        purchasOrder_itemOrder.ManufacturerPartNumber,
        purchasOrder_itemOrder.SupplierPartId,
        purchasOrder_itemOrder.Description,
        purchasOrder_itemOrder.OrderReference,
        purchasOrder_itemOrder.Note,
        purchasOrder_itemOrder.ExpectedReceiptDate,
        purchasOrder_itemOrder.VatTaxId,
        finance_tax.Value AS VatValue, 
        purchasOrder_itemOrder.Discount,
        purchasOrder_itemOrder.StockPart,
    
        purchasOrder_itemOrder.ManufacturerPartNumber AS  ManufacturerPartNumber,  
        manufacturerPart_partNumber.Id AS  ManufacturerPartNumberId, 
        purchasOrder_itemReceive.Id AS ReceiveId,
        purchasOrder_itemReceive.QuantityReceived,
        purchasOrder_itemReceive.ReceivalDate
    
    FROM purchasOrder_itemOrder 
    LEFT JOIN purchasOrder_itemReceive ON purchasOrder_itemReceive.ItemOrderId = purchasOrder_itemOrder.Id 
    LEFT JOIN unitOfMeasurement ON unitOfMeasurement.Id = purchasOrder_itemOrder.UnitOfMeasurementId 
    LEFT JOIN finance_tax ON finance_tax.Id = purchasOrder_itemOrder.VatTaxId 
    LEFT JOIN supplierPart ON purchasOrder_itemOrder.SupplierPartId = supplierPart.Id
    LEFT JOIN manufacturerPart_partNumber ON manufacturerPart_partNumber.Id = supplierPart.ManufacturerPartNumberId
    STR;

    if($lineId == null) $query .= " WHERE PurchasOrderId = $purchaseOrderId ";
    else $query .= " WHERE purchasOrder_itemOrder.Id = $lineId ";
    $query .=" ORDER BY LineNo";

    return $query;
}

function purchaseOrderItem_getCostCenterQuery($lineId):string
{
    $query = <<<STR
        SELECT * 
        FROM purchasOrder_itemOrder_costCenter_mapping 
        LEFT JOIN finance_costCenter on purchasOrder_itemOrder_costCenter_mapping.CostCenterId = finance_costCenter.Id
        WHERE ItemOrderId = $lineId
    STR;
    return $query;
}

function purchaseOrderItem_getCostCenterData($result):array
{
    $output = array();
    while($r = mysqli_fetch_assoc($result))
    {
        $temp = array();
        $temp['Barcode'] = barcodeFormatter_CostCenter($r['CostCenterNumber']);
        $temp['Quota'] = floatval($r['Quota']);

        $output[] = $temp;
    }

    return $output;
}

function purchaseOrderItem_getLineIdFromQueryResult($data):?int
{
    return intval( $data['OrderLineId'], 10);
}

function purchaseOrderItem_getDataFromQueryResult($purchaseOrderNumber, $data):?array
{
    $output = array();

    $lineNumber = intval($data['LineNo']);

    $output["PurchaseOrderBarcode"] = barcodeFormatter_PurchaseOrderNumber($purchaseOrderNumber, $lineNumber);
    $output['LineNo'] = $lineNumber;
    $output['LineNumber'] = $lineNumber;
    $output['Price'] = $data['Price'];
    $output['SupplierSku'] = $data['Sku'];
    $output['LineType'] = $data['LineType'];
    $output['QuantityOrdered'] = intval($data['Quantity']);
    $output['OrderLineId'] = intval($data['OrderLineId']);
    $output['UnitOfMeasurement'] = $data['UnitOfMeasurementSymbol'];
    $output['UnitOfMeasurementId'] =  intval($data['UnitOfMeasurementId']);
    $output['PurchaseOrderId'] = intval($data['PurchasOrderId']);
    $output['PartNo'] = $data['PartNo'];
    $output['ManufacturerName'] = $data['ManufacturerName'];
    $output['ManufacturerPartNumber'] = $data['ManufacturerPartNumber'];
    $output['ManufacturerPartNumberId'] = $data['ManufacturerPartNumberId'];
    if($data['SupplierPartId'] != null)$output['SupplierPartId'] = intval($data['SupplierPartId']);
    else $output['SupplierPartId'] = null;
    $output['Description'] = $data['Description'];
    $output['OrderReference'] = $data['OrderReference'];
    $output['Note'] = $data['Note'];
    $output['ExpectedReceiptDate'] = $data['ExpectedReceiptDate'];
    $output['VatTaxId'] = intval($data['VatTaxId']);
    $output['VatValue'] = $data['VatValue'];
    $output['Discount'] = $data['Discount'];
    $output['StockPart'] = filter_var($data['StockPart'], FILTER_VALIDATE_BOOLEAN);
    $output['LinePrice'] = $data['Price']*((100-$data['Discount'])/100);
    $output['Total'] = $output['LinePrice'] * intval($data['Quantity']);
    $output['FullTotal'] = round($output['Total'] *(1+($data['VatValue']/100)), 2, PHP_ROUND_HALF_UP);

    $output['CostCenter'] = Array();
    $output['CostCenter'][] =[ "Barcode" => "CC-00000", "Quota" => 1 ];
    $output['CostCenter'][] =[ "Barcode" => "CC-00001", "Quota" => 1 ];

    return $output;
}

?>