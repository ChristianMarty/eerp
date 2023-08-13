<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/stock/
// Author   : Christian Marty
// Date		: 27.08.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../location/_location.php";
require_once __DIR__ . "/../util/_barcodeParser.php";
require_once __DIR__ . "/../util/_barcodeFormatter.php";
require_once __DIR__ . "/../util/_user.php";

function _stockPartQuery(string $stockNo): string
{
	return <<<STR
	SELECT 	partStock.Id AS PartStockId, 
	        partStock.DeleteRequestUserId, 
	        supplier.Name AS SupplierName, 
	        supplierPart.SupplierPartNumber, 
	       	partStock.OrderReference, 
	       	partStock.StockNo, 
	       	manufacturer.Name AS ManufacturerName, 
	       	manufacturer.Id AS ManufacturerId, 
	       	partStock.LotNumber, 
			manufacturer.Id AS ManufacturerId, 
			manufacturerPart_partNumber.Number AS ManufacturerPartNumber, 
			manufacturerPart_partNumber.Id AS ManufacturerPartNumberId, 
			partStock.Date, 
			manufacturerPart_partNumber.Description,
			manufacturerPart_item.Id AS ManufacturerPartItemId,
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
	LEFT JOIN (SELECT Id, Name FROM vendor)manufacturer ON manufacturer.Id = manufacturerPart_item.VendorId OR manufacturer.Id = manufacturerPart_partNumber.VendorId OR manufacturer.Id = manufacturerPart_series.VendorId
	LEFT JOIN (SELECT Id, Name FROM vendor)supplier ON supplier.Id = supplierPart.VendorId
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

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	if(!isset($_GET["StockNo"]))sendResponse(null, "StockNo not specified");
	$stockNumber = barcodeParser_StockNumber($_GET["StockNo"]);
	if(!$stockNumber) sendResponse(null, "StockNo invalid");

	$dbLink = dbConnect();
	$result = dbRunQuery($dbLink,_stockPartQuery($stockNumber));
	dbClose($dbLink);	

	$r = dbGetResult($result);

	$r['Barcode'] = barcodeFormatter_StockNumber($r['StockNo']);
	if($r['Date']) {
		$date = new DateTime($r['Date']);
		$r['DateCode'] = $date->format("yW");
	}else{
		$r['DateCode'] = "";
	}
	$r['Location'] = location_getName($r['LocationId']);
	$r['HomeLocation'] = location_getName($r['HomeLocationId']);
	$r['LocationPath'] = location_getPath($r['LocationId'], 100);
	$r['HomeLocationPath'] = location_getPath($r['HomeLocationId'], 100);

	if($r['DeleteRequestUserId'] !== null)$r['Deleted'] = true;
	else $r['Deleted'] = false;
	unset($r['DeleteRequestUserId'] );

	sendResponse($r);
}
else if($_SERVER['REQUEST_METHOD'] == 'PATCH')
{

	sendResponse(null, "API moved");
}
else if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$data = json_decode(file_get_contents('php://input'),true);
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;
		
	$orderReference = dbEscapeString($dbLink,$data['OrderReference']);
	$date = dbEscapeString($dbLink,$data['Date']);
	$quantity = dbEscapeString($dbLink,$data['Quantity']);
	$location = dbEscapeString($dbLink,$data['Location']);
	$location = str_replace("Loc-","",$location);
	
	if(isset($data['ReceivalId']))  // If part is created based on purchase receival id
	{
		$receivalId = dbEscapeString($dbLink,$data['ReceivalId']);
		$lotNumber = dbEscapeString($dbLink,$data['LotNumber']);
		
		$query  = "SELECT partStock_create_onReceival(";
		$query .= $receivalId.", ";
		$query .= "(SELECT `Id` FROM `location` WHERE `LocNr`= '".$location."'),";
		$query .= $quantity.",";
		$query .= dbStringNull($date).", ";
		$query .= dbStringNull($orderReference).", ";
		$query .= dbStringNull($lotNumber).", ";
		$query .= dbIntegerNull(user_getId())." ";
		$query .= ") AS StockNo; ";
		
	}
	else // If new part is created
	{
		$manufacturerId = dbEscapeString($dbLink,$data['ManufacturerId']);
		$manufacturerPartNumber = dbEscapeString($dbLink,$data['ManufacturerPartNumber']);
		$supplierId = dbEscapeString($dbLink,$data['SupplierId']);
		$supplierPartNumber = dbEscapeString($dbLink,$data['SupplierPartNumber']);
		$lotNumber = dbEscapeString($dbLink,$data['LotNumber']);

		$query  = "SELECT partStock_create(";
		$query .= "'".$manufacturerId."',";
		$query .= "'".$manufacturerPartNumber."',";
		$query .= "(SELECT `Id` FROM `location` WHERE `LocNr`= '".$location."'),";
		$query .= $quantity.",";
		$query .= "'".$date."', ";
		$query .= dbStringNull($orderReference).", ";
		$query .= dbStringNull($supplierId).", ";
		$query .= dbStringNull($supplierPartNumber).", ";
		$query .= dbStringNull($lotNumber).", ";
		$query .= dbIntegerNull(user_getId())." ";
		$query .= ") AS StockNo; ";
	}
	$result = dbRunQuery($dbLink,$query);

	$stockNo = dbGetResult($result)['StockNo'];

	$query = _stockPartQuery($stockNo);
	
	$result = dbRunQuery($dbLink,$query);
	$stockPart = dbGetResult($result);
	
	$error = null;
	if($stockPart)
	{
		$orderReference = $stockPart['OrderReference'];
		$stockPart['Barcode'] = barcodeFormatter_StockNumber($stockPart['StockNo']);
		$stockPart['Description'] = "";

		if(!empty($orderReference) )  //TODO: Fix
		{
			// Get Description -> Still a hack
			$descriptionQuery = "SELECT Description FROM `partLookup` WHERE PartNo = '".$orderReference."' LIMIT 1";
			$descriptionResult = dbRunQuery($dbLink,$descriptionQuery);
			if(!is_bool($descriptionResult))
			{
				$temp = mysqli_fetch_assoc($descriptionResult);
				if($temp) $stockPart['Description'] = $temp['Description'];
			}
		}
	}
	else
	{
		$error = "Error description: " . mysqli_error($dbLink);
	}
	
	dbClose($dbLink);	
	
	sendResponse($stockPart, $error);
}
else if($_SERVER['REQUEST_METHOD'] == 'DELETE')
{
	$data = json_decode(file_get_contents('php://input'),true);
	$stockNumber = barcodeParser_StockNumber($data['StockNumber']);
	if(!$stockNumber)sendResponse(null, "Stock number format incorrect");

	$dbLink = dbConnect();

	$sqlData['DeleteRequestUserId'] = user_getId();
	$sqlData['DeleteRequestDate']['raw'] = "current_timestamp()";
	$sqlData['DeleteRequestNote'] = $data["Note"];

	$query = dbBuildUpdateQuery($dbLink,"partStock", $sqlData, 'StockNo = "'.$stockNumber.'"');

	dbRunQuery($dbLink,$query);
	dbClose($dbLink);

	sendResponse(null);
}

?>
