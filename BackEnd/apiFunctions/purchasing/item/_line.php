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
        purchaseOrder_itemOrder.LineNo,
        purchaseOrder_itemOrder.Price,
        purchaseOrder_itemOrder.Sku,
        purchaseOrder_itemOrder.Type AS LineType,
        purchaseOrder_itemOrder.Quantity,
        purchaseOrder_itemOrder.Id AS OrderLineId, 
        unitOfMeasurement.Symbol AS UnitOfMeasurementSymbol, 
        unitOfMeasurement.Id AS UnitOfMeasurementId,
        purchaseOrder_itemOrder.PurchaseOrderId,
        purchaseOrder_itemOrder.PartNo,
        purchaseOrder_itemOrder.ManufacturerName,
        purchaseOrder_itemOrder.ManufacturerPartNumber,
        purchaseOrder_itemOrder.SupplierPartId,
        purchaseOrder_itemOrder.Description,
        purchaseOrder_itemOrder.OrderReference,
        purchaseOrder_itemOrder.Note,
        purchaseOrder_itemOrder.ExpectedReceiptDate,
        purchaseOrder_itemOrder.VatTaxId,
        finance_tax.Value AS VatValue, 
        purchaseOrder_itemOrder.Discount,
        purchaseOrder_itemOrder.StockPart,
        purchaseOrder_itemOrder.ManufacturerPartNumber AS  ManufacturerPartNumber,  
        manufacturerPart_partNumber.Id AS  ManufacturerPartNumberId, 
        purchaseOrder_itemReceive.Id AS ReceiveId,
        purchaseOrder_itemReceive.QuantityReceived,
        purchaseOrder_itemReceive.ReceivalDate,
        specificationPart.Number AS SpecificationPartNumber,
        partStock.AddedStockQuantity
    FROM purchaseOrder_itemOrder 
    LEFT JOIN purchaseOrder_itemReceive ON purchaseOrder_itemReceive.ItemOrderId = purchaseOrder_itemOrder.Id 
    LEFT JOIN unitOfMeasurement ON unitOfMeasurement.Id = purchaseOrder_itemOrder.UnitOfMeasurementId 
    LEFT JOIN finance_tax ON finance_tax.Id = purchaseOrder_itemOrder.VatTaxId 
    LEFT JOIN supplierPart ON purchaseOrder_itemOrder.SupplierPartId = supplierPart.Id
    LEFT JOIN manufacturerPart_partNumber ON manufacturerPart_partNumber.Id = supplierPart.ManufacturerPartNumberId
    LEFT JOIN specificationPart ON purchaseOrder_itemOrder.SpecificationPartId = specificationPart.Id
    LEFT JOIN (
        SELECT SUM(Quantity) AS AddedStockQuantity, partStock.ReceivalId
        FROM partStock
        LEFT JOIN partStock_history ON partStock_history.StockId = partStock.Id
        WHERE partStock_history.ChangeType = 'Create'
        GROUP BY partStock.ReceivalId
    )partStock ON partStock.ReceivalId = purchaseOrder_itemReceive.Id
    STR;

    if($lineId == null) $query .= " WHERE PurchaseOrderId = $purchaseOrderId ";
    else $query .= " WHERE purchaseOrder_itemOrder.Id = $lineId ";
    $query .=" ORDER BY LineNo";

    return $query;
}

function purchaseOrderItem_getCostCenterQuery($lineId):string
{
    $query = <<<STR
        SELECT * 
        FROM purchaseOrder_itemOrder_costCenter_mapping 
        LEFT JOIN finance_costCenter on purchaseOrder_itemOrder_costCenter_mapping.CostCenterId = finance_costCenter.Id
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
    $output['Price'] = floatval($data['Price']);
    $output['SupplierSku'] = $data['Sku'];
    $output['LineType'] = $data['LineType'];
    $output['QuantityOrdered'] = intval($data['Quantity']);
    $output['OrderLineId'] = intval($data['OrderLineId']);
    $output['UnitOfMeasurement'] = $data['UnitOfMeasurementSymbol'];
    $output['UnitOfMeasurementId'] =  intval($data['UnitOfMeasurementId']);
    $output['PurchaseOrderId'] = intval($data['PurchaseOrderId']);
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

    if($data['SpecificationPartNumber'] !== null){
        $output['SpecificationPartNumber'] = intval($data['SpecificationPartNumber']);
        $output['SpecificationPartBarcode'] = barcodeFormatter_SpecificationPart($data['SpecificationPartNumber']);
    }else{
        $output['SpecificationPartNumber'] = null;
        $output['SpecificationPartBarcode'] = null;
    }

    return $output;
}

?>