<?php
//*************************************************************************************************
// FileName : match.php
// FilePath : apiFunctions/purchasing/item/
// Author   : Christian Marty
// Date		: 05.01.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../databaseConnector.php";
require_once __DIR__ . "/../../../config.php";
require_once __DIR__ . "/../../util/_barcodeParser.php";
require_once __DIR__ . "/../../vendor/_vendor.php";

function loadDatabaseData($purchaseOrderNo)
{
	$dbLink = dbConnect();
	if($dbLink == null) return null;

    $purchaseOrderNo = dbEscapeString($dbLink, $purchaseOrderNo);
    $query = <<<STR
        SELECT 
            purchaseOrder_itemOrder.Id AS OrderLineId, 
            LineNo, 
            purchaseOrder_itemOrder.Type, 
            supplier.Name AS SupplierName,
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
        LEFT JOIN (SELECT Id, Name FROM vendor)supplier on supplier.Id = supplierPart.VendorId
        WHERE purchaseOrder.PoNo = $purchaseOrderNo
        ORDER BY LineNo
    STR;
	
	$result = dbRunQuery($dbLink,$query);
	$lines = array();
	
	while($r = mysqli_fetch_assoc($result)) 
	{
		if($r['ManufacturerId'] != null) $r['ManufacturerName'] = $r['ManufacturerNameDatabase'];
		unset($r['ManufacturerNameDatabase']);

        if($r['OrderLineId'] != null) $r['OrderLineId'] = intval($r['OrderLineId']);
        if($r['SupplierPartId'] != null) $r['SupplierPartId'] = intval($r['SupplierPartId']);
        if($r['ManufacturerPartNumberId'] != null)$r['ManufacturerPartNumberId'] = intval($r['ManufacturerPartNumberId']);
        if($r['ManufacturerId'] != null)$r['ManufacturerId'] = intval($r['ManufacturerId']);
		
		$lines[] = $r;
	}
	
	dbClose($dbLink);
	return $lines;
}

function manufacturerPartNumberId($manufacturerId, $manufacturerPartNumber ): Null|int
{
    $dbLink = dbConnect();
    $query = <<<STR
        SELECT manufacturerPart_partNumber.Id FROM manufacturerPart_partNumber 
        LEFT JOIN manufacturerPart_item ON manufacturerPart_item.Id = manufacturerPart_partNumber.ItemId
        LEFT JOIN manufacturerPart_series ON manufacturerPart_item.SeriesId = manufacturerPart_series.Id
        WHERE manufacturerPart_partNumber.Number = '$manufacturerPartNumber' AND (
            manufacturerPart_partNumber.VendorId = $manufacturerId OR 
            manufacturerPart_item.VendorId = $manufacturerId OR 
            manufacturerPart_series.VendorId = $manufacturerId)
        LIMIT 1
    STR;
    $result = dbRunQuery($dbLink,$query);
    dbClose($dbLink);
    if(!$result) return Null;
    $r = mysqli_fetch_assoc($result);
    if($r == null) return null;
    return intval($r['Id']);
}

function supplierPart_create($supplierId, $supplierPartNumber, $manufacturerId, $manufacturerPartNumber ): void
{
    $partNumberId = manufacturerPartNumberId($manufacturerId,$manufacturerPartNumber);

    if($partNumberId == NULL)
    {
        $dbLink = dbConnect();
        $query = <<<STR
            INSERT IGNORE INTO manufacturerPart_partNumber (VendorId, Number)  
            VALUES ('$manufacturerId', '$manufacturerPartNumber')
        STR;
        dbRunQuery($dbLink,$query);
        dbClose($dbLink);

        $partNumberId = manufacturerPartNumberId($manufacturerId,$manufacturerPartNumber);
    }

    $dbLink = dbConnect();
    $query = <<<STR
        INSERT IGNORE INTO supplierPart (VendorId, SupplierPartNumber, ManufacturerPartNumberId)  
        VALUES ( '$supplierId', '$supplierPartNumber', '$partNumberId')
    STR;
    dbRunQuery($dbLink,$query);
    dbClose($dbLink);

    $dbLink = dbConnect();
    $query = <<<STR
        UPDATE supplierPart SET ManufacturerPartNumberId = $partNumberId 
        WHERE ManufacturerPartNumberId IS NULL AND
              VendorId = $supplierId AND 
              SupplierPartNumber = '$supplierPartNumber'
    STR;
    dbRunQuery($dbLink,$query);
    dbClose($dbLink);
}

if(!isset($_GET["PurchaseOrderNumber"])) sendResponse(null, "Purchase Order Number missing!");
$purchaseOrderNumber = barcodeParser_PurchaseOrderNumber($_GET["PurchaseOrderNumber"]);
if(!$purchaseOrderNumber) sendResponse(NULL, "Purchase Order Number Parser Error");

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$output["Lines"] = array();
	
	$lines = loadDatabaseData($purchaseOrderNumber);
	
	foreach($lines as $line)
	{
		if($line['Type'] ==  "Part") // TODO: Add Generic -> make ui better
		{
			$output["Lines"][] = $line;
		}
	}
		
	sendResponse($output);
}
else if($_SERVER['REQUEST_METHOD'] == 'POST')
{
    $data = json_decode(file_get_contents('php://input'),true);

    $dbLink = dbConnect();
    $query = <<<STR
        SELECT VendorId FROM purchaseOrder WHERE PoNo = $purchaseOrderNumber
    STR;
    $supplierId = intval( mysqli_fetch_assoc(dbRunQuery($dbLink,$query))['VendorId'] );
    dbClose($dbLink);

    foreach($data as $itemOrderId)
    {
        $itemOrderId = intval($itemOrderId);

        $dbLink = dbConnect();
        $query = <<<STR
            SELECT * FROM purchaseOrder_itemOrder WHERE Id = $itemOrderId
        STR;
        $result = dbRunQuery($dbLink,$query);

        if($result == null) continue;
        $orderLine = mysqli_fetch_assoc($result);

        $manufacturerId = \vendor\getIdByName($orderLine['ManufacturerName']);
        $manufacturerPartNumber = dbEscapeString($dbLink, $orderLine['ManufacturerPartNumber']);
        $supplierPartNumber = dbEscapeString($dbLink, $orderLine['Sku']);
        dbClose($dbLink);

        supplierPart_create($supplierId, $supplierPartNumber, $manufacturerId, $manufacturerPartNumber);

        $dbLink = dbConnect();
        $query = <<<STR
            UPDATE purchaseOrder_itemOrder 
            LEFT JOIN purchaseOrder ON purchaseOrder.Id = purchaseOrder_itemOrder.PurchaseOrderId 
            SET SupplierPartId = (
                SELECT supplierPart.Id FROM supplierPart 
                WHERE supplierPart.SupplierPartNumber = purchaseOrder_itemOrder.Sku AND supplierPart.VendorId = purchaseOrder.VendorId  
            )
            WHERE purchaseOrder_itemOrder.Type = 'Part' AND purchaseOrder.PoNo = $purchaseOrderNumber;
        STR;
        dbRunQuery($dbLink,$query);
        dbClose($dbLink);
    }
    sendResponse(null);
}
?>