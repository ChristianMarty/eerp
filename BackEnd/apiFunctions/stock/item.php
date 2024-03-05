<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/stock/
// Author   : Christian Marty
// Date		: 27.08.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;
global $user;

require_once __DIR__ . "/_stock.php";
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../location/_location.php";
require_once __DIR__ . "/../util/_barcodeParser.php";
require_once __DIR__ . "/../util/_barcodeFormatter.php";

function _stockPartQuery(string $stockNo): string
{
	return <<<STR
	SELECT 	partStock.Id AS PartStockId, 
	        partStock.DeleteRequestUserId, 
	        vendor_displayName(supplier.Id) AS SupplierName, 
	        supplierPart.SupplierPartNumber, 
	       	partStock.OrderReference, 
	       	partStock.StockNumber, 
	       	vendor_displayName(manufacturer.Id) AS ManufacturerName, 
	       	manufacturer.Id AS ManufacturerId, 
	       	partStock.LotNumber, 
			manufacturer.Id AS ManufacturerId, 
			manufacturerPart_partNumber.Number AS ManufacturerPartNumber, 
			manufacturerPart_partNumber.Id AS ManufacturerPartNumberId, 
			partStock.Date, 
			manufacturerPart_partNumber.Description,
			manufacturerPart_item.Id AS ManufacturerPartItemId,
			partStock.SpecificationPartRevisionId,
			partStock.LocationId, 
			partStock.HomeLocationId, 
			hc.CreateQuantity,  
			partStock_getQuantity(partStock.StockNumber) AS Quantity, 
			r.ReservedQuantity AS ReservedQuantity, 
			lc.LastCountDate AS LastCountDate, 
			hc.CreateData 
	FROM partStock 
	    
	LEFT JOIN (
		SELECT SupplierPartId, purchaseOrder_itemReceive.Id FROM purchaseOrder_itemOrder
		LEFT JOIN purchaseOrder_itemReceive ON purchaseOrder_itemOrder.Id = purchaseOrder_itemReceive.ItemOrderId
		)poLine ON poLine.Id = partStock.ReceivalId
	LEFT JOIN supplierPart ON (supplierPart.Id = partStock.SupplierPartId AND partStock.ReceivalId IS NULL) OR (supplierPart.Id = poLine.SupplierPartId)   
	LEFT JOIN manufacturerPart_partNumber ON (manufacturerPart_partNumber.Id = partStock.ManufacturerPartNumberId AND supplierPart.ManufacturerPartNumberId IS NULL) OR manufacturerPart_partNumber.Id = supplierPart.ManufacturerPartNumberId
	LEFT JOIN manufacturerPart_item On manufacturerPart_item.Id = manufacturerPart_partNumber.ItemId
	LEFT JOIN manufacturerPart_series On manufacturerPart_series.Id = manufacturerPart_item.SeriesId
	    
	LEFT JOIN (SELECT Id, vendor_displayName(id) FROM vendor)manufacturer ON manufacturer.Id = manufacturerPart_item.VendorId OR manufacturer.Id = manufacturerPart_partNumber.VendorId OR manufacturer.Id = manufacturerPart_series.VendorId
	LEFT JOIN (SELECT Id, vendor_displayName(id) FROM vendor)supplier ON supplier.Id = supplierPart.VendorId
	LEFT JOIN (SELECT SUM(Quantity) AS ReservedQuantity, StockId FROM partStock_reservation GROUP BY StockId)r ON r.StockId = partStock.Id

	LEFT JOIN (
		SELECT StockId, Quantity AS CreateQuantity, CreationDate AS CreateData FROM partStock_history WHERE ChangeType = 'Create' AND StockId = (SELECT ID FROM partStock WHERE StockNumber = $stockNo)
		)hc ON  hc.StockId = partStock.Id
	LEFT JOIN (
		SELECT StockId, CreationDate AS LastCountDate FROM partStock_history WHERE ChangeType = 'Absolute' AND StockId = (SELECT ID FROM partStock WHERE StockNumber = $stockNo) ORDER BY CreationDate DESC LIMIT 1
		)lc ON  lc.StockId = partStock.Id

	WHERE partStock.StockNumber = $stockNo
	STR;
}

if($api->isGet("stock.view"))
{
    $parameter = $api->getGetData();
    if(!isset($parameter->StockCode)) $api->returnParameterMissingError("StockCode");
    $stockNumber = barcodeParser_StockNumber($parameter->StockCode);
    if($stockNumber === null) $api->returnParameterError("StockCode");

    $stockNumber = $database->escape($stockNumber);
	$r = $database->query(_stockPartQuery($stockNumber))[0];

	$r->Barcode = barcodeFormatter_StockNumber($r->StockNumber);
	if($r->Date) {
		$date = new DateTime($r->Date);
		$r->DateCode = $date->format("yW");
	}else{
		$r->DateCode = "";
	}

    $location = new Location();

    $r->Location = $location->name($r->LocationId);
	$r->HomeLocation = $location->name($r->HomeLocationId);
	$r->LocationPath = $location->path($r->LocationId, 100);
	$r->HomeLocationPath = $location->path($r->HomeLocationId, 100);

	if($r->DeleteRequestUserId !== null)$r->Deleted = true;
	else $r->Deleted = false;
	unset($r->DeleteRequestUserId);

    $api->returnData($r);
}
else if($api->isPost("stock.create"))
{
    $data = $api->getPostData();

    if($data->LotNumber == null){
        $lotNumber = null;
    } else {
        $lotNumber = trim($data->LotNumber);
        if(strlen($lotNumber) == 0)$lotNumber = null;
    }

    if($data->OrderReference == null){
        $orderReference = null;
    } else {
        $orderReference = trim($data->OrderReference);
        if(strlen($orderReference) == 0)$orderReference = null;
    }

    if($data->Date == null){
        $date = null;
    } else {
        $date = trim($data->Date);
        if(strlen($date) == 0)$date = null;
    }

	$quantity = intval($data->Quantity);

    $location = barcodeParser_LocationNumber($data->LocationCode ?? null);
    if($location === null) {
        global $defaultLocationBarcode;
        $location = $defaultLocationBarcode;
    }
	
	if(isset($data->ReceivalId))  // If part is created based on purchase receival id
	{
        $stockNumber = \stock\stock::createOnReceival(
            intval($data->ReceivalId),
            $location,
            $quantity,
            $date,
            $lotNumber,
            $orderReference
        );
	}
	else // If new part is created
	{
        $manufacturerId = intval($data->ManufacturerId);
        $manufacturerPartNumber = trim($data->ManufacturerPartNumber);
        $supplierId = intval($data->SupplierId);
        $supplierPartNumber = trim($data->SupplierPartNumber);

        $stockNumber = \stock\stock::create(
            $manufacturerId,
            $manufacturerPartNumber,
            $location,
            $quantity,
            $date,
            $lotNumber,
            $orderReference,
            $supplierId,
            $supplierPartNumber
        );
	}

    $stockPart = $database->query(_stockPartQuery("'$stockNumber'"))[0];

    $orderReference = $stockPart->OrderReference;
    $stockPart->Barcode = barcodeFormatter_StockNumber($stockPart->StockNumber);

    $api->returnData($stockPart);
}
else if($api->isDelete("stock.delete"))
{
	$data = $api->getPostData();
    if(!isset($data->StockNumber)) $api->returnParameterMissingError("StockNumber");
	$stockNumber = barcodeParser_StockNumber($data->StockNumber);
	if($stockNumber === false) $api->returnParameterError("StockNumber");

    $stockNumber = $database->escape($stockNumber);

	$sqlData['DeleteRequestUserId'] = $user->userId();;
	$sqlData['DeleteRequestDate']['raw'] = "current_timestamp()";
	$sqlData['DeleteRequestNote'] = $data->Note;

    $database->update("partStock", $sqlData, "StockNumber = $stockNumber");

    $api->returnEmpty();
}
