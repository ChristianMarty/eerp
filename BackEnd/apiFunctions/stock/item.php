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
require_once __DIR__ . "/../util/location.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	
	if(!isset($_GET["StockNo"])) sendResponse(Null,"StockNo not set");
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$stockNo = dbEscapeString($dbLink, $_GET["StockNo"]);
	$stockNo = strtolower($stockNo);
	$stockNo = str_replace("stk-","",$stockNo);
	
	$baseQuery = "SELECT * FROM  partStock_view ";
	
	$baseQuery  = "SELECT partStock.Id AS PartStockId, supplier.Name AS SupplierName, supplierPart.SupplierPartNumber, partStock.OrderReference, partStock.StockNo, manufacturer.Name AS ManufacturerName, manufacturer.Id AS ManufacturerId, partStock.LotNumber, ";
	$baseQuery .= "manufacturerPart.VendorId AS ManufacturerId, manufacturerPart.ManufacturerPartNumber, partStock.ManufacturerPartId, partStock.Date, ";
	$baseQuery .= "partStock.LocationId, location_getHomeLocationId_stock(partStock.Id) AS HomeLocationId,  ";
	$baseQuery .= "hc.CreateQuantity,  partStock_getQuantity(partStock.StockNo) AS Quantity, r.ReservedQuantity AS ReservedQuantity, lc.LastCountDate AS LastCountDate, hc.CreateData ";

	$baseQuery .= "FROM partStock ";

	$baseQuery .= "LEFT JOIN (SELECT SupplierPartId, purchasOrder_itemReceive.Id FROM purchasOrder_itemOrder  ";
	$baseQuery .= "LEFT JOIN purchasOrder_itemReceive ON purchasOrder_itemOrder.Id = purchasOrder_itemReceive.ItemOrderId)poLine ON poLine.Id = partStock.ReceivalId ";
	$baseQuery .= "LEFT JOIN supplierPart ON (supplierPart.Id = partStock.SupplierPartId AND partStock.ReceivalId IS NULL) OR (supplierPart.Id = poLine.SupplierPartId) ";
	$baseQuery .= "LEFT JOIN manufacturerPart ON (manufacturerPart.Id = partStock.ManufacturerPartId AND supplierPart.ManufacturerPartId IS NULL) OR manufacturerPart.Id = supplierPart.ManufacturerPartId ";

	$baseQuery .= "LEFT JOIN (SELECT Id, Name FROM vendor)manufacturer ON manufacturer.Id = manufacturerPart.VendorId ";
	$baseQuery .= "LEFT JOIN (SELECT Id, Name FROM vendor)supplier ON supplier.Id = supplierPart.VendorId ";
	$baseQuery .= "LEFT JOIN (SELECT SUM(Quantity) AS ReservedQuantity, StockId FROM partStock_reservation GROUP BY StockId)r ON r.StockId = partStock.Id ";

	$baseQuery .= "LEFT JOIN (SELECT StockId, Quantity AS CreateQuantity, Date AS CreateData FROM partStock_history WHERE ChangeType = 'Create' AND StockId = (SELECT ID FROM partStock WHERE StockNo = '".$stockNo."'))hc ON  hc.StockId = partStock.Id ";
	$baseQuery .= "LEFT JOIN (SELECT StockId, Date AS LastCountDate FROM partStock_history WHERE ChangeType = 'Absolute' AND StockId = (SELECT ID FROM partStock WHERE StockNo = '".$stockNo."') ORDER BY Date DESC LIMIT 1)lc ON  lc.StockId = partStock.Id ";

	$baseQuery .= "WHERE partStock.StockNo = '".$stockNo."' ";
		
		
	$result = dbRunQuery($dbLink,$baseQuery);
	dbClose($dbLink);	
	
	$locations = getLocations();

	$output = array();
	$gctNr = null;
	$stockNoValid = false;
	while($r = dbGetResult($result)) 
	{
		$gctNr  = $r['OrderReference'];
		$r['Barcode'] = "STK-".$r['StockNo'];
		if($r['Date']) {
			$date = new DateTime($r['Date']);
			$r['DateCode'] = $date->format("yW");
		}else{
			$r['DateCode'] = "";
		}
		$r['Location'] = buildLocation($locations, $r['LocationId']);
		$r['HomeLocation'] = buildLocation($locations, $r['HomeLocationId']);
		$r['OrderReference']  = $r['OrderReference'];
		
		$output[] = $r;
		$stockNoValid = true;
	}
	
	if(isset($_GET["StockNo"]) AND $stockNoValid == true)
	{
		$output[0]['LocationPath'] = buildLocationPath($locations, $output[0]['LocationId'], 100);
		$output[0]['HomeLocationPath'] = buildLocationPath($locations, $output[0]['HomeLocationId'], 100);
	}
	
	// Get Description	-> This is a hack
	if(!empty($gctNr) and isset($_GET["StockNo"]) AND $stockNoValid)
	{
		$dbLink = dbConnect();
		if($dbLink == null) return null;
		
		$descriptionQuery = "SELECT Description FROM `partLookup` WHERE PartNo = ".$gctNr." LIMIT 1";
		
		$descriptionResult = dbRunQuery($dbLink,$descriptionQuery);
		if(!is_bool($descriptionResult))
		{
			$r = dbGetResult($descriptionResult);
			if(!is_bool($r)) $output[0]['Description'] = $r['Description'];
			else $output[0]['Description'] = "Invalide Production Part Nr!";
		}
		else $output[0]['Description'] = "Invalide Production Part Nr!";
		dbClose($dbLink);	

	}
	
	sendResponse($output);
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
		
	$orderReference = dbEscapeString($dbLink,$data['data']['OrderReference']);
	$date = dbEscapeString($dbLink,$data['data']['Date']); 
	$quantity = dbEscapeString($dbLink,$data['data']['Quantity']);
	$location = dbEscapeString($dbLink,$data['data']['Location']);	
	$location = str_replace("Loc-","",$location);
	
	if(isset($data['data']['ReceivalId']))  // If part is created based on purchas receival id 
	{
		$receivalId = dbEscapeString($dbLink,$data['data']['ReceivalId']);
		$lotNumber = dbEscapeString($dbLink,$data['data']['LotNumber']);
		
		$query  = "SELECT partStock_create_onReceival(";
		$query .= $receivalId.", ";
		$query .= "(SELECT `Id` FROM `location` WHERE `LocNr`= '".$location."'),";
		$query .= $quantity.",";
		$query .= dbStringNull($date).", ";
		$query .= dbStringNull($orderReference).", ";
		$query .= dbStringNull($lotNumber)." ";
		$query .= ") AS StockNo; ";
		
	}
	else // If part is created from scratch 
	{
		$manufacturerId = dbEscapeString($dbLink,$data['data']['ManufacturerId']);
		$manufacturerPartNumber = dbEscapeString($dbLink,$data['data']['ManufacturerPartNumber']);
		$supplierId = dbEscapeString($dbLink,$data['data']['SupplierId']);
		$supplierPartNumber = dbEscapeString($dbLink,$data['data']['SupplierPartNumber']);
		$lotNumber = dbEscapeString($dbLink,$data['data']['LotNumber']);

		$query  = "SELECT partStock_create(";
		$query .= "'".$manufacturerId."',";
		$query .= "'".$manufacturerPartNumber."',";
		$query .= "(SELECT `Id` FROM `location` WHERE `LocNr`= '".$location."'),";
		$query .= $quantity.",";
		$query .= "'".$date."', ";
		$query .= dbStringNull($orderReference).", ";
		$query .= dbStringNull($supplierId).", ";
		$query .= dbStringNull($supplierPartNumber).", ";
		$query .= dbStringNull($lotNumber)." ";
		$query .= ") AS StockNo; ";

	}

	$result = dbRunQuery($dbLink,$query);

	$stockNo = dbGetResult($result)['StockNo'];

	$query = "SELECT * FROM partStock_view WHERE StockNo = '".$stockNo."';";
	
	$result = dbRunQuery($dbLink,$query);
	$stockPart = dbGetResult($result);
	
	$error = null;
	if($stockPart)
	{
		$orderReference = $stockPart['OrderReference'];
		$stockPart['Barcode'] = "STK-".$stockPart['StockNo'];
		$stockPart['OrderReference']  = "GCT-".$stockPart['OrderReference'];

		$stockPart['Description'] = "";
		
		
		if(!empty($orderReference) )
		{
			// Get Description -> Still a hack
			$descriptionQuery = "SELECT Description FROM `partLookup` WHERE PartNo = ".$orderReference." LIMIT 1";
			
			$descriptionResult = dbRunQuery($dbLink,$descriptionQuery);
			if(!is_bool($descriptionResult))
			{
				$stockPart['Description'] = mysqli_fetch_assoc($descriptionResult)['Description'];
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

?>
