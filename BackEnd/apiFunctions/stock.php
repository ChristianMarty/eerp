<?php
//*************************************************************************************************
// FileName : stock.php
// FilePath : apiFunctions/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/databaseConnector.php";
require_once __DIR__ . "/util/location.php";
require_once __DIR__ . "/util/_barcodeParser.php";
require_once __DIR__ . "/util/_barcodeFormatter.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$dbLink = dbConnect();

	$baseQuery = <<<STR
		SELECT StockNo, 
		       manufacturerPart_partNumber.Number AS ManufacturerPartNumber, 
		       manufacturerPart_item.Id AS ManufacturerPartItemId , 
		       vendor.Name AS ManufacturerName, 
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
	
	if(isset($_GET["StockNo"]))
	{
		$stockNo = barcodeParser_StockNumber($_GET["StockNo"]);
		if($stockNo)
		{
			$queryParam[] = "StockNo = '" . $stockNo . "'";
		}
	}
	 
	if(isset($_GET["ManufacturerPartNumberId"]))
	{
		$temp = dbEscapeString($dbLink, $_GET["ManufacturerPartNumberId"]);
		$queryParam[] = "manufacturerPart_partNumber.Id = '" . $temp . "'";
	}
	
	if(isset($_GET["HideEmpty"]))
	{
		if(filter_var($_GET["HideEmpty"], FILTER_VALIDATE_BOOLEAN)) $queryParam[] = "partStock.Cache_Quantity != '0'";
	}
	
	$query = dbBuildQuery($dbLink,$baseQuery,$queryParam);
	
	$result = dbRunQuery($dbLink,$query);
	dbClose($dbLink);	
	
	$locations = getLocations();

	$output = array();

	while($r = dbGetResult($result)) 
	{
		$r['StockNumber'] = $r['StockNo'];
		$r['StockBarcode'] = barcodeFormatter_StockNumber($r['StockNo']);

		$r['Location'] = buildLocation($locations, $r['LocationId']);

		$r['Barcode'] = barcodeFormatter_StockNumber($r['StockNo']); // Legacy

		$output[] = $r;
	}
	
	sendResponse($output);
}
?>
