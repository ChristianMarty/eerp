<?php
//*************************************************************************************************
// FileName : stock.php
// FilePath : apiFunctions/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/location/_location.php";
require_once __DIR__ . "/util/_barcodeParser.php";
require_once __DIR__ . "/util/_barcodeFormatter.php";

if($api->isGet("stock.view"))
{
	$parameters = $api->getGetData();

    $baseQuery = <<<STR
        SELECT
           StockNumber, 
           manufacturerPart_partNumber.Number AS ManufacturerPartNumber, 
           manufacturerPart_item.Id AS ManufacturerPartItemId , 
           vendor_displayName(vendor.Id) AS ManufacturerName, 
           Cache_Quantity AS Quantity, 
           LocationId, 
           vendor.Id AS ManufacturerId,
           user.Initials AS CountingRequest,
           partStock.CountingRequestDate
        FROM partStock 

        LEFT JOIN purchaseOrder_itemReceive ON partStock.ReceivalId  = purchaseOrder_itemReceive.Id
        LEFT JOIN purchaseOrder_itemOrder ON purchaseOrder_itemReceive.ItemOrderId = purchaseOrder_itemOrder.Id
        LEFT JOIN supplierPart ON supplierPart.Id = purchaseOrder_itemOrder.SupplierPartId
        LEFT JOIN manufacturerPart_partNumber ON supplierPart.ManufacturerPartNumberId <=> manufacturerPart_partNumber.Id OR manufacturerPart_partNumber.Id <=> partStock.ManufacturerPartNumberId 
        LEFT JOIN user ON user.Id = partStock.CountingRequestUserId
        LEFT JOIN manufacturerPart_item On manufacturerPart_item.Id = manufacturerPart_partNumber.ItemId
        LEFT JOIN manufacturerPart_series On manufacturerPart_series.Id = manufacturerPart_item.SeriesId
        LEFT JOIN vendor ON vendor.Id <=> manufacturerPart_item.VendorId OR vendor.Id <=> manufacturerPart_partNumber.VendorId OR vendor.Id <=> manufacturerPart_series.VendorId
    STR;

	$queryParam = array();
	
	if(isset($parameters->StockNumber))
	{
		$stockNo = barcodeParser_StockNumber($parameters->StockNumber);
		if($stockNo)
		{
			$queryParam[] = "partStock.StockNo = '" . $stockNo . "'";
		}
	}

	if(isset($parameters->ManufacturerPartNumberId))
	{
		$temp = intval($parameters->ManufacturerPartNumberId);
		$queryParam[] = "manufacturerPart_partNumber.Id = {$temp}";
	}
	
	if(isset($parameters->HideEmpty) && $parameters->HideEmpty === true)
	{
		$queryParam[] = "partStock.Cache_Quantity != '0'";
	}

    if(isset($parameters->CountingRequest) && $parameters->CountingRequest === true)
    {
        $queryParam[] = "partStock.CountingRequestUserId IS NOT NULL";
    }

    $queryParam[] = "partStock.DeleteRequestUserId IS NULL";

	$data = $database->query($baseQuery,$queryParam);
    $location = new Location();
	foreach ($data as $line)
	{
        $line->ItemCode = barcodeFormatter_StockNumber($line->StockNumber);
        $line->LocationName = $location->name(intval($line->LocationId));
        $line->LocationCode = $location->itemCode(intval($line->LocationId));
        $line->Description ="";
        unset($line->LocationId);
	}

	$api->returnData($data);
}

