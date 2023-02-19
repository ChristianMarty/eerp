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

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
//	$baseQuery = "SELECT ManufacturerName, ManufacturerPartNumber, Quantity, StockNo, Date, LocationId FROM partStock_view ";
	
	$baseQuery  = "SELECT StockNo, ManufacturerPartNumber , vendor.Name AS ManufacturerName, Cache_Quantity AS Quantity, partStock.Date, LocationId FROM partStock ";
	$baseQuery .= "LEFT JOIN (	SELECT SupplierPartId, purchasOrder_itemReceive.Id FROM purchasOrder_itemOrder  ";
	$baseQuery .= "				LEFT JOIN purchasOrder_itemReceive ON purchasOrder_itemOrder.Id = purchasOrder_itemReceive.ItemOrderId)poLine ON poLine.Id = partStock.ReceivalId ";
	$baseQuery .= "LEFT JOIN supplierPart ON (supplierPart.Id = partStock.SupplierPartId AND partStock.ReceivalId IS NULL) OR (supplierPart.Id = poLine.SupplierPartId) ";
	$baseQuery .= "LEFT JOIN manufacturerPart ON (manufacturerPart.Id = partStock.ManufacturerPartId AND supplierPart.ManufacturerPartId IS NULL) OR manufacturerPart.Id = supplierPart.ManufacturerPartId ";
	$baseQuery .= "LEFT JOIN  vendor ON vendor.Id = manufacturerPart.VendorId ";

	$queryParam = array();
	
	if(isset($_GET["StockNo"]))
	{
		$stockNo = dbEscapeString($dbLink, trim($_GET["StockNo"]));
		$stockNo = strtolower($stockNo);
		$stockNo = str_replace("stk-","",$stockNo);	
		$queryParam[] = "StockNo = '" . $stockNo . "'";
	}
	 
	if(isset($_GET["ManufacturerPartId"]))
	{
		$temp = dbEscapeString($dbLink, $_GET["ManufacturerPartId"]);
		$queryParam[] = "manufacturerPart.Id = '" . $temp . "'";
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
		$r['Barcode'] = "STK-".$r['StockNo'];
		if($r['Date']) {
			$date = new DateTime($r['Date']);
			$r['DateCode'] = $date->format("yW");
		}
		else{
			$r['DateCode'] = null;
		}
		$r['Location'] = buildLocation($locations, $r['LocationId']);

		$output[] = $r;
	}
	
	sendResponse($output);
}
?>
