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
	       	partStock.StockNo, 
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
			partStock_getQuantity(partStock.StockNo) AS Quantity, 
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
		SELECT StockId, Quantity AS CreateQuantity, Date AS CreateData FROM partStock_history WHERE ChangeType = 'Create' AND StockId = (SELECT ID FROM partStock WHERE StockNo = '$stockNo')
		)hc ON  hc.StockId = partStock.Id
	LEFT JOIN (
		SELECT StockId, Date AS LastCountDate FROM partStock_history WHERE ChangeType = 'Absolute' AND StockId = (SELECT ID FROM partStock WHERE StockNo = '$stockNo') ORDER BY Date DESC LIMIT 1
		)lc ON  lc.StockId = partStock.Id

	WHERE partStock.StockNo = '$stockNo'
	STR;
}

if($api->isGet("stock.view"))
{
    $parameter = $api->getGetData();
    if(!isset($parameter->StockNo)) $api->returnParameterMissingError("StockNo");
    $stockNumber = barcodeParser_StockNumber($parameter->StockNo);
    if($stockNumber === null) $api->returnParameterError("StockNumber");

	$r = $database->query(_stockPartQuery($stockNumber))[0];

	$r->Barcode = barcodeFormatter_StockNumber($r->StockNo);
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


	$orderReference = $database->escape($data->OrderReference);
    $date = trim($data->Date);
    if(strlen($date) == 0)$date = 'NULL';
	else $date = $database->escape($date);
	$quantity = intval($data->Quantity);

    $userId = $user->userId();

    $location = barcodeParser_LocationNumber($data->LocationCode ?? null);
    if($location === null) {
        global $defaultLocationBarcode;
        $location = $defaultLocationBarcode;
    }
	
	if(isset($data->ReceivalId))  // If part is created based on purchase receival id
	{
		$receivalId = intval($data->ReceivalId);
		$lotNumber = $database->escape($data->LotNumber);

        $query = <<<STR
            SELECT partStock_create_onReceival(
                $receivalId,
                (SELECT `Id` FROM `location` WHERE `LocNr`= '$location'),
                $quantity, 
                $date, 
                $orderReference, 
                $lotNumber, 
                $userId
            ) AS StockNo;
        STR;
	}
	else // If new part is created
	{
		$manufacturerId = intval($data->ManufacturerId);
		$manufacturerPartNumber = $database->escape($data->ManufacturerPartNumber);
		$supplierId = intval($data->SupplierId);
		$supplierPartNumber = $database->escape($data->SupplierPartNumber);
		$lotNumber = $database->escape($data->LotNumber);

        $query = <<<STR
            SELECT partStock_create(
                $manufacturerId,
                $manufacturerPartNumber,
                (SELECT `Id` FROM `location` WHERE `LocNr`= '$location'),
                $quantity, 
                $date, 
                $orderReference, 
                $supplierId,
                $supplierPartNumber,
                $lotNumber, 
                $userId
            ) AS StockNo;
        STR;
	}

    $stockNo = $database->query($query)[0]->StockNo;
	$stockPart = $database->query(_stockPartQuery($stockNo))[0];

    $orderReference = $stockPart->OrderReference;
    $stockPart->Barcode = barcodeFormatter_StockNumber($stockPart->StockNo);
    $stockPart->Description = "";

    if(!empty($orderReference) )  //TODO: Fix
    {
        // Get Description -> Still a hack
        $descriptionQuery = "SELECT Description FROM `partLookup` WHERE PartNo = '$orderReference' LIMIT 1";
        $descriptionResult = $database->query($descriptionQuery);

        if(count($descriptionResult) !== 0)
        {
            $stockPart->Description = $descriptionResult[0]->Description;
        }
    }

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

    $database->update("partStock", $sqlData, "StockNo = $stockNumber");

    $api->returnEmpty();
}
