<?php
//*************************************************************************************************
// FileName : partPurchase.php
// FilePath : apiFunctions/purchasing
// Author   : Christian Marty
// Date		: 15.01.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/../util/_barcodeFormatter.php";
require_once __DIR__ . "/../util/_barcodeParser.php";

if($api->isGet())
{
    $parameters = $api->getGetData();

    if(!isset($parameters->ManufacturerPartNumberId)) $api->returnParameterMissingError('ManufacturerPartNumberId');
    if(!isset($parameters->ProductionPartNumber)) $api->returnParameterMissingError('ProductionPartNumber');

    $query = <<<STR
        SELECT
            *, 
            SUM(QuantityReceived) AS TotalQuantityReceived,
            vendor_displayName(vendor.Id) AS SupplierName,
            vendor.Id AS SupplierId
        FROM purchaseOrder_itemOrder
        LEFT JOIN supplierPart ON supplierPart.Id = purchaseOrder_itemOrder.SupplierPartId
        LEFT JOIN purchaseOrder ON purchaseOrder.Id = purchaseOrder_itemOrder.PurchaseOrderId
        LEFT JOIN vendor ON vendor.Id = purchaseOrder.VendorId
        LEFT JOIN productionPart_manufacturerPart_mapping ON productionPart_manufacturerPart_mapping.ManufacturerPartNumberId = supplierPart.ManufacturerPartNumberId
        LEFT JOIN purchaseOrder_itemReceive ON purchaseOrder_itemReceive.ItemOrderId = purchaseOrder_itemOrder.Id
        LEFT JOIN productionPart ON productionPart.Id = productionPart_manufacturerPart_mapping.ProductionPartId
        LEFT JOIN numbering ON numbering.Id = productionPart.NumberingPrefixId
    STR;


    $queryParameters = array();
	
	if(isset($parameters->ManufacturerPartNumberId))
	{
        $queryParameters[] = 'supplierPart.ManufacturerPartNumberId = '. $database->escape($parameters->ManufacturerPartNumberId);
	}
	else if(isset($parameters->ProductionPartNumber))
	{
        $queryParameters[] = "CONCAT(numbering.Prefix,'-',productionPart.Number) = " . $database->escape($parameters->ProductionPartNumber);
	}
	else
	{
		$api->returnError("Parameter Error!");
	}

    $result = $database->query($query, $queryParameters, "GROUP BY purchaseOrder_itemOrder.Id");

	$rows = array();
	$rowcount = count($result);
	$totalQuantity = 0;
	$receivedQuantity = 0;

    $priceMinimum = 100000000;
    $priceMaximum = 0;
    $priceAverageSum = 0;
    $priceWeightedAverageSum = 0;
    $priceWeightSum = 0;
	
	foreach ($result as $r)
	{
		unset($r->Id);
		$totalQuantity += $r->Quantity;
		$receivedQuantity += $r->TotalQuantityReceived;

        if($r->Price < $priceMinimum ) $priceMinimum = $r->Price;
        if($r->Price > $priceMaximum ) $priceMaximum = $r->Price;

        $priceAverageSum +=  $r->Price;
        $priceWeightedAverageSum +=  $r->Price * $r->Quantity;
        $priceWeightSum += $r->Quantity;

        $r->PurchaseOrderBarcode = barcodeFormatter_PurchaseOrderNumber( $r->PoNo);
        $r->PurchaseOrderNumber = $r->PoNo;
		
		$rows[] = $r;
	}
	
	$output = array();

    $output['Statistics'] = array();
    $output['Statistics']['Quantity'] = array();
	$output['Statistics']['Quantity']['Ordered'] = $totalQuantity;
	$output['Statistics']['Quantity']['Pending'] = $totalQuantity - $receivedQuantity;
	$output['Statistics']['Quantity']['Received'] = $receivedQuantity;

    $output['Statistics']['Price'] = array();
    if(count($rows) != 0)
    {
        $output['Statistics']['Price']['Minimum'] = round($priceMinimum, 6);
        $output['Statistics']['Price']['Maximum'] = round($priceMaximum, 6);
        $output['Statistics']['Price']['Average'] = round($priceAverageSum / count($rows), 6);
        $output['Statistics']['Price']['WeightedAverage'] = round($priceWeightedAverageSum / $priceWeightSum, 6);
    }
    else
    {
        $output['Statistics']['Price']['Minimum'] = null;
        $output['Statistics']['Price']['Maximum'] = null;
        $output['Statistics']['Price']['Average'] = null;
        $output['Statistics']['Price']['WeightedAverage'] = null;
    }

	$output['Data'] = $rows;
	
	$api->returnData($output);
}
