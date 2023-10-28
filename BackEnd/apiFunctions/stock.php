<?php
//*************************************************************************************************
// FileName : stock.php
// FilePath : apiFunctions/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
global $database;
global $api;

require_once __DIR__ . "/location/_location.php";
require_once __DIR__ . "/util/_barcodeParser.php";
require_once __DIR__ . "/util/_barcodeFormatter.php";

if($api->isGet("stock.view"))
{
	$parameters = $api->getGetData();

	$baseQuery = <<<STR
		SELECT StockNo AS StockNumber, 
		       manufacturerPart_partNumber.Number AS ManufacturerPartNumber, 
		       manufacturerPart_item.Id AS ManufacturerPartItemId , 
		       vendor_displayName(vendor.Id) AS ManufacturerName, 
		       Cache_Quantity AS Quantity, 
		       LocationId, 
		       vendor.Id AS ManufacturerId
		FROM partStock 
		LEFT JOIN manufacturerPart_partNumber ON manufacturerPart_partNumber.Id = partStock.ManufacturerPartNumberId 
		LEFT JOIN manufacturerPart_item On manufacturerPart_item.Id = manufacturerPart_partNumber.ItemId
		LEFT JOIN manufacturerPart_series On manufacturerPart_series.Id = manufacturerPart_item.SeriesId
		LEFT JOIN vendor ON vendor.Id = manufacturerPart_item.VendorId OR vendor.Id = manufacturerPart_partNumber.VendorId OR vendor.Id = manufacturerPart_series.VendorId
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


	$data = $database->query($baseQuery,$queryParam);
	foreach ($data as &$line)
	{
		$line->StockBarcode = barcodeFormatter_StockNumber($line->StockNumber);
		$line->Location = location_getName(intval($line->LocationId));
		$line->Barcode = $line->StockBarcode;
	}

	$api->returnData($data);
}

