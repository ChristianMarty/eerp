<?php
//*************************************************************************************************
// FileName : match.php
// FilePath : apiFunctions/purchasing/item/
// Author   : Christian Marty
// Date		: 25.10.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/../../util/_barcodeParser.php";
require_once __DIR__ . "/../../vendor/_vendor.php";
require_once __DIR__ . "/../../vendor/_preprocessor/_partNumberPreprocessing.php";

function loadDatabaseData(int $purchaseOrderNo):array
{
    global $database;
    $query = <<<QUERY
        SELECT 
            purchaseOrder_itemOrder.Id AS OrderLineId, 
            LineNo, 
            purchaseOrder_itemOrder.Type, 
            vendor_displayName(supplier.Id) AS SupplierName,
            purchaseOrder_itemOrder.Sku AS SupplierPartNumber, 
            purchaseOrder_itemOrder.SupplierPartId AS SupplierPartId,
            purchaseOrder_itemOrder.ManufacturerName, 
            vendor_displayName(vendor_names.Id) AS ManufacturerNameDatabase, 
            vendor_names.Id AS ManufacturerId, 
            purchaseOrder_itemOrder.ManufacturerPartNumber, 
            manufacturerPart_partNumber.Id AS ManufacturerPartNumberId, 
            purchaseOrder_itemOrder.Description
        FROM purchaseOrder_itemOrder
        LEFT JOIN purchaseOrder ON purchaseOrder.Id = purchaseOrder_itemOrder.PurchaseOrderId
        LEFT JOIN vendor_names ON vendor_names.Name = purchaseOrder_itemOrder.ManufacturerName
        LEFT JOIN supplierPart ON supplierPart.VendorId = purchaseOrder.VendorId AND supplierPart.SupplierPartNumber =  purchaseOrder_itemOrder.Sku
        LEFT JOIN manufacturerPart_partNumber ON supplierPart.ManufacturerPartNumberId = manufacturerPart_partNumber.Id
        LEFT JOIN (SELECT Id, vendor_displayName(Id) FROM vendor)supplier on supplier.Id = supplierPart.VendorId
        WHERE purchaseOrder.PoNo = '$purchaseOrderNo'
        ORDER BY LineNo
    QUERY;

	$result = $database->query($query);

	$lines = array();
	foreach($result as $line)
	{
        $r = (array)$line;
		if($r['ManufacturerId'] != null) $r['ManufacturerName'] = $r['ManufacturerNameDatabase'];
		unset($r['ManufacturerNameDatabase']);

        if($r['OrderLineId'] != null) $r['OrderLineId'] = intval($r['OrderLineId']);
        if($r['SupplierPartId'] != null) $r['SupplierPartId'] = intval($r['SupplierPartId']);
        if($r['ManufacturerPartNumberId'] != null)$r['ManufacturerPartNumberId'] = intval($r['ManufacturerPartNumberId']);
        if($r['ManufacturerId'] != null)$r['ManufacturerId'] = intval($r['ManufacturerId']);
		
		$lines[] = $r;
	}
	return $lines;
}

function manufacturerPartNumberId(int $manufacturerId, string $manufacturerPartNumber ): null|int
{
    global $database;

    $query = <<<QUERY
        SELECT 
            manufacturerPart_partNumber.Id 
        FROM manufacturerPart_partNumber 
        LEFT JOIN manufacturerPart_item ON manufacturerPart_item.Id = manufacturerPart_partNumber.ItemId
        LEFT JOIN manufacturerPart_series ON manufacturerPart_item.SeriesId = manufacturerPart_series.Id
        WHERE manufacturerPart_partNumber.Number = $manufacturerPartNumber AND (
            manufacturerPart_partNumber.VendorId = $manufacturerId OR 
            manufacturerPart_item.VendorId = $manufacturerId OR 
            manufacturerPart_series.VendorId = $manufacturerId)
        LIMIT 1
    QUERY;

    $result = $database->query($query);

    if(empty($result[0])) return null;
    return intval($result[0]->Id);
}

function supplierPart_create($supplierId, $supplierPartNumber, $manufacturerId, $manufacturerPartNumber ): void
{
    $partNumberPreprocess = new PartNumberPreprocess($manufacturerId);
    $manufacturerPartNumber = $partNumberPreprocess->clean($manufacturerPartNumber);

    $supplierNumberPreprocess = new PartNumberPreprocess($supplierId);
    $supplierPartNumber = $supplierNumberPreprocess->clean($supplierPartNumber);

    global $database;
    $partNumberId = manufacturerPartNumberId($manufacturerId,$manufacturerPartNumber);

    if($partNumberId == NULL)
    {
        $query = <<<STR
            INSERT IGNORE INTO manufacturerPart_partNumber (VendorId, Number)  
            VALUES ('$manufacturerId', $manufacturerPartNumber)
        STR;
        $database->execute($query);

        $partNumberId = manufacturerPartNumberId($manufacturerId,$manufacturerPartNumber);
    }

    $query = <<<STR
        INSERT IGNORE INTO supplierPart (VendorId, SupplierPartNumber, ManufacturerPartNumberId)  
        VALUES ( '$supplierId', $supplierPartNumber, '$partNumberId')
    STR;
    $database->execute($query);

    $query = <<<STR
        UPDATE supplierPart SET ManufacturerPartNumberId = $partNumberId 
        WHERE ManufacturerPartNumberId IS NULL AND
              VendorId = $supplierId AND 
              SupplierPartNumber = $supplierPartNumber
    STR;
    $database->execute($query);
}


$parameters = $api->getGetData();
if(!isset($parameters->PurchaseOrderNumber))$api->returnParameterMissingError('PurchaseOrderNumber');
$purchaseOrderNumber = barcodeParser_PurchaseOrderNumber($parameters->PurchaseOrderNumber);
if(!$purchaseOrderNumber) $api->returnParameterError('PurchaseOrderNumber');


if($api->isGet())
{
	$output["Lines"] = array();
	$lines = loadDatabaseData($purchaseOrderNumber);
	foreach($lines as $line)
	{
		if($line['Type'] ==  "Part") // TODO: Add Generic and spec parts -> make ui better
		{
			$output["Lines"][] = $line;
		}
	}
	$api->returnData($output);
}
else if($api->isPost())
{
    $data = $api->getPostData();

    $query = <<<STR
        SELECT 
            VendorId 
        FROM purchaseOrder 
        WHERE PoNo = '$purchaseOrderNumber'
    STR;
    $supplierId = $database->query($query)[0]->VendorId;

    foreach($data->Lines as $itemOrderId)
    {
        $itemOrderId = intval($itemOrderId);

        $query = <<<STR
            SELECT * FROM purchaseOrder_itemOrder WHERE Id = $itemOrderId
        STR;
        $orderLine = $database->query($query)[0];

        $manufacturerId = \vendor\vendor::getIdByName($orderLine->ManufacturerName);
        $manufacturerPartNumber = $database->escape($orderLine->ManufacturerPartNumber);
        $supplierPartNumber = $database->escape( $orderLine->Sku);

        supplierPart_create($supplierId, $supplierPartNumber, $manufacturerId, $manufacturerPartNumber);

        $query = <<<STR
            UPDATE purchaseOrder_itemOrder 
            LEFT JOIN purchaseOrder ON purchaseOrder.Id = purchaseOrder_itemOrder.PurchaseOrderId 
            SET SupplierPartId = (
                SELECT supplierPart.Id FROM supplierPart 
                WHERE supplierPart.SupplierPartNumber = purchaseOrder_itemOrder.Sku AND supplierPart.VendorId = purchaseOrder.VendorId  
            )
            WHERE purchaseOrder_itemOrder.Type = 'Part' AND purchaseOrder.PoNo = $purchaseOrderNumber;
        STR;
        $database->execute($query);
    }

    $api->returnEmpty();
}
