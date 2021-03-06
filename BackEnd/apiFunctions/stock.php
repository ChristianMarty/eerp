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
	
	$baseQuery = "SELECT * FROM  partStock_view ";
	
	$queryParam = array();
	
	if(isset($_GET["StockNo"]))
	{
		$temp = dbEscapeString($dbLink, $_GET["StockNo"]);
		$temp = strtolower($temp);
		$temp = str_replace("stk-","",$temp);
		array_push($queryParam, "StockNo LIKE '".$temp."'");		
	}
	
	if(isset($_GET["ManufacturerPartId"]))
	{
		$temp = dbEscapeString($dbLink, $_GET["ManufacturerPartId"]);
		array_push($queryParam, "ManufacturerPartId = '".$temp."'");		
	}
	
	if(isset($_GET["HideEmpty"]))
	{
		if(filter_var($_GET["HideEmpty"], FILTER_VALIDATE_BOOLEAN)) array_push($queryParam, "Quantity != 0");	
	}
	
	$query = dbBuildQuery($dbLink,$baseQuery,$queryParam);
	
	$result = dbRunQuery($dbLink,$query);
	dbClose($dbLink);	
	
	$locations = getLocations();

	$output = array();
	$gctNr = null;
	$stockNoValid = false;
	while($r = dbGetResult($result)) 
	{
		$gctNr  = $r['OrderReference'];
		$r['Barcode'] = "STK-".$r['StockNo'];
		$date = new DateTime($r['Date']);
		$r['DateCode'] = $date->format("yW");
		$r['Location'] = buildLocation($locations, $r['LocationId']);
		$r['HomeLocation'] = buildLocation($locations, $r['HomeLocationId']);
		$r['OrderReference']  = $r['OrderReference'];
		
		array_push($output, $r);
		$stockNoValid = true;
	}
	
	if(isset($_GET["StockNo"]) AND $stockNoValid == true)
	{
		$output[0]['LocationPath'] = buildLocationPath($locations, $output[0]['LocationId'], 100);
		$output[0]['HomeLocationPath'] = buildLocationPath($locations, $output[0]['HomeLocationId'], 100);
	}
	
	// Get Description	-> This is a hack
	if(!empty($gctNr) and isset($_GET["StockNo"]) AND $stockNoValid == true)
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
	$data = json_decode(file_get_contents('php://input'),true);
	
	$output = array();
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$StockId = dbEscapeString($dbLink, $_GET["StockNo"]);
	$StockId = strtolower($StockId);
	$StockId = str_replace("stk-","",$StockId);

	if(isset($data["WorkOrderId"])) $workOrderId = dbEscapeString($dbLink, $data["WorkOrderId"]);
	else $workOrderId = null;
		
	if($workOrderId == null) $workOrderId = 0;
		
	if(isset($data["RemoveQuantity"]))
	{
		$RemoveQuantity = dbEscapeString($dbLink, $data["RemoveQuantity"]);
	
		if(!is_numeric($RemoveQuantity))sendResponse($output,"Quantity is not numeric");
		$RemoveQuantity = intval($RemoveQuantity);
		$output["RemoveQuantity"] = $RemoveQuantity;
		
		$query = "CALL partStock_quantityRemove('".$StockId."','".$RemoveQuantity."','".$workOrderId."')";
	}
	else if(isset($data["AddQuantity"]))
	{
		$AddQuantity = dbEscapeString($dbLink, $data["AddQuantity"]);
		
		if(!is_numeric($AddQuantity))sendResponse($output,"Quantity is not numeric");
		$AddQuantity = intval($AddQuantity);
		$output["AddQuantity"] = $AddQuantity;
		
		$query = "CALL partStock_quantityAdd('".$StockId."','".$AddQuantity."','".$workOrderId."')";
	}
	else if(isset($data["Quantity"]))
	{
		$Quantity = dbEscapeString($dbLink, $data["Quantity"]);
		if(!is_numeric($Quantity))sendResponse($output,"Quantity is not numeric");
		
		$output["Quantity"] = $Quantity;
		
		$Quantity = intval($Quantity);
		
		$query = "CALL partStock_quantitySet('".$StockId."','".$Quantity."')";
	}
	else
	{
		sendResponse($output,"Parameter Error");
	}

	$output["StockNo"] = $StockId;
	
	$result = dbRunQuery($dbLink,$query);
	
	$error = null;
	$msg = mysqli_error($dbLink);
	if($msg != "")
	{
		$error = "Error description: " . mysqli_error($dbLink);
	}

	dbClose($dbLink);	
	sendResponse($output, $error);
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
		
		$query  = "SELECT partStock_create_onReceival(";
		$query .= $receivalId.", ";
		$query .= "(SELECT `Id` FROM `location` WHERE `LocNr`= '".$location."'),";
		$query .= $quantity.",";
		$query .= dbStringNull($date).", ";
		$query .= dbStringNull($orderReference);
		$query .= ") AS StockNo; ";
		
	}
	else // If part is created from scratch 
	{
		$manufacturerId = dbEscapeString($dbLink,$data['data']['ManufacturerId']);
		$manufacturerPartNumber = dbEscapeString($dbLink,$data['data']['ManufacturerPartNumber']);
		$supplierId = dbEscapeString($dbLink,$data['data']['SupplierId']);
		$supplierPartNumber = dbEscapeString($dbLink,$data['data']['SupplierPartNumber']);	

		$query  = "SELECT partStock_create(";
		$query .= "'".$manufacturerId."',";
		$query .= "'".$manufacturerPartNumber."',";
		$query .= "(SELECT `Id` FROM `location` WHERE `LocNr`= '".$location."'),";
		$query .= $quantity.",";
		$query .= "'".$date."', ";
		$query .= dbStringNull($orderReference).", ";
		$query .= dbStringNull($supplierId).", ";
		$query .= dbStringNull($supplierPartNumber)." ";
		$query .= ") AS StockNo; ";

	}

	$result = dbRunQuery($dbLink,$query);

	$stockNo = dbGetResult($result)['StockNo'];

	$query = "SELECT * FROM partStock_view WHERE StockNo = '".$stockNo."';";
	
	$result = dbRunQuery($dbLink,$query);
	$stockPart = dbGetResult($result);
	
	$error = null;
	if($stockPart != false)
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
