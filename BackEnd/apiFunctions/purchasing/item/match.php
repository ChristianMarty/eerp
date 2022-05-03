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
require_once __DIR__ . "/../../externalApi/mouser.php";

function loadDatabaseData($purchaseOrderNo)
{
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$query  = "SELECT LineNo, purchasOrder_itemOrder.Type, purchasOrder_itemOrder.ManufacturerPartNumber, manufacturerPart.Id AS ManufacturerPartId, purchasOrder_itemOrder.ManufacturerName, manufacturer.Name AS ManufacturerNameDatabase, manufacturer.Id AS PartVendorId, purchasOrder_itemOrder.Sku, supplierPart.Id AS SupplierPartId ";
	$query .= "FROM purchasOrder_itemOrder ";
	$query .= "LEFT JOIN purchasOrder ON purchasOrder.Id = purchasOrder_itemOrder.PurchasOrderId ";
	$query .= "LEFT JOIN (SELECT Id, Name, Alias, AliasDigikey FROM vendor)manufacturer ON manufacturer.Name = purchasOrder_itemOrder.ManufacturerName OR manufacturer.Alias = purchasOrder_itemOrder.ManufacturerName OR manufacturer.AliasDigikey = purchasOrder_itemOrder.ManufacturerName ";
	$query .= "LEFT JOIN manufacturerPart ON manufacturerPart.VendorId = manufacturer.Id AND manufacturerPart.ManufacturerPartNumber = purchasOrder_itemOrder.ManufacturerPartNumber ";
	$query .= "LEFT JOIN supplierPart ON supplierPart.VendorId = purchasOrder.VendorId AND supplierPart.SupplierPartNumber =  purchasOrder_itemOrder.Sku ";
	$query .= "WHERE purchasOrder.PoNo = ".$purchaseOrderNo;
	$query .= " ORDER BY LineNo";
	
	$result = dbRunQuery($dbLink,$query);

	$lines = array();
	
	while($r = mysqli_fetch_assoc($result)) 
	{
		if($r['PartVendorId'] != null) $r['ManufacturerName'] = $r['ManufacturerNameDatabase'];
		unset($r['ManufacturerNameDatabase']);
		
		array_push($lines, $r);
	}
	
	dbClose($dbLink);
	return $lines;
}

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	if(!isset($_GET["PurchaseOrderNo"])) sendResponse(null, "PurchaseOrderNo missing!");
	
	$purchaseOrderNo = intval($_GET["PurchaseOrderNo"]);
	
	$output["Lines"] = array();
	
	$lines = loadDatabaseData($purchaseOrderNo);
	
	foreach($lines as $line)
	{
		if($line['Type'] ==  "Part")
		{
			array_push($output["Lines"], $line);
		}
	}
		
	sendResponse($output);
}
else if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$data = json_decode(file_get_contents('php://input'),true);
	
	if(!isset($data["PurchaseOrderNo"]) || !isset($data["Command"])) sendResponse(null, "PurchaseOrderNo or Command missing!");
	
	$purchaseOrderNo = intval($data["PurchaseOrderNo"]);
	$command = $data["Command"];
	
	if($command == "Save")
	{
		$dbLink = dbConnect();
		if($dbLink == null) return null;
		
		$query  = "UPDATE purchasOrder_itemOrder ";
		$query .= "LEFT JOIN purchasOrder ON purchasOrder.Id = purchasOrder_itemOrder.PurchasOrderId ";
		$query .= "SET SupplierPartId = ";
		$query .= "(SELECT supplierPart.Id FROM supplierPart "; 
		$query .= "WHERE supplierPart.SupplierPartNumber =  purchasOrder_itemOrder.Sku AND supplierPart.VendorId = purchasOrder.VendorId ";
		$query .= ") ";
		$query .= "WHERE purchasOrder_itemOrder.Type = 'Part' AND purchasOrder.PoNo = ".$purchaseOrderNo;
		
		dbRunQuery($dbLink,$query);
		dbClose($dbLink);
	}
	else if($command == "Create")
	{
		$lines = loadDatabaseData($purchaseOrderNo);
		
		foreach($lines as $line)
		{
			if($line['Type'] == "Generic") continue;
			
			if($line['PartVendorId'] == null)
			{
				$data = array();
				$data['Name'] = $line['ManufacturerName'];
				$data['IsManufacturer'] = '1';
				
				$dbLink = dbConnect();
				$query  = dbBuildInsertQuery($dbLink,'vendor', $data);
				
				dbRunQuery($dbLink, $query);
				dbClose($dbLink);
			}
			
			if($line['ManufacturerPartId'] == null)
			{
				$data = array();
				
				if($line['PartVendorId'] == null)  $data['VendorId']['raw'] = "(SELECT Id FROM vendor WHERE Name = '".$line['ManufacturerName']."')";
				else $data['VendorId'] = $line['PartVendorId'];
			
				$data['ManufacturerPartNumber'] = $line['ManufacturerPartNumber'];
				$dbLink = dbConnect();
				$query = dbBuildInsertQuery($dbLink,'manufacturerPart', $data);

				dbRunQuery($dbLink, $query);
				dbClose($dbLink);
			}
		}
		
		
		$lines = loadDatabaseData($purchaseOrderNo);
		foreach($lines as $line)
		{
			if($line['SupplierPartId'] == null)
			{
				$data = array();
				
				$data['ManufacturerPartId'] = $line['ManufacturerPartId'];
				$data['VendorId']['raw'] = "(SELECT VendorId FROM purchasOrder WHERE PoNo = '".$purchaseOrderNo."')";
				$data['SupplierPartNumber'] = $line['Sku']; 
				$dbLink = dbConnect();
				$query = dbBuildInsertQuery($dbLink,'supplierPart', $data);
				dbRunQuery($dbLink, $query);
				dbClose($dbLink);
			}
		}
	}

}

?>