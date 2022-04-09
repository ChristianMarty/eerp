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
	
	$query  = "SELECT LineNo, purchasOrder_itemOrder.ManufacturerPartNumber, manufacturerPart.Id AS ManufacturerPartId, purchasOrder_itemOrder.ManufacturerName, partManufacturer.Name AS ManufacturerNameDatabase, partManufacturer.Id AS PartManufacturerId, purchasOrder_itemOrder.Sku, supplierPart.Id AS SupplierPartId ";
	$query .= "FROM purchasOrder_itemOrder ";
	$query .= "LEFT JOIN purchasOrder ON purchasOrder.Id = purchasOrder_itemOrder.PurchasOrderId ";
	$query .= "LEFT JOIN partManufacturer ON partManufacturer.Name = purchasOrder_itemOrder.ManufacturerName OR partManufacturer.Alias = purchasOrder_itemOrder.ManufacturerName OR partManufacturer.AliasDigikey = purchasOrder_itemOrder.ManufacturerName ";
	$query .= "LEFT JOIN manufacturerPart ON manufacturerPart.ManufacturerId = partManufacturer.Id AND manufacturerPart.ManufacturerPartNumber = purchasOrder_itemOrder.ManufacturerPartNumber ";
	$query .= "LEFT JOIN supplierPart ON supplierPart.SupplierId = purchasOrder.SupplierId AND supplierPart.SupplierPartNumber =  purchasOrder_itemOrder.Sku ";
	$query .= "WHERE purchasOrder.PoNo = ".$purchaseOrderNo;
	$query .= " ORDER BY LineNo";
	
	$result = dbRunQuery($dbLink,$query);

	$lines = array();
	
	while($r = mysqli_fetch_assoc($result)) 
	{
		if($r['PartManufacturerId'] != null) $r['ManufacturerName'] = $r['ManufacturerNameDatabase'];
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
	
	$output["Lines"] = loadDatabaseData($purchaseOrderNo);
		
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
		$query .= "WHERE supplierPart.SupplierPartNumber =  purchasOrder_itemOrder.Sku AND supplierPart.SupplierId = purchasOrder.SupplierId ";
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
			if($line['PartManufacturerId'] == null)
			{
				$data = array();
				$data['Name'] = $line['ManufacturerName'];
				$dbLink = dbConnect();
				$query  = dbBuildInsertQuery($dbLink,'partManufacturer', $data);
				dbRunQuery($dbLink, $query);
				dbClose($dbLink);
			}
			
			if($line['ManufacturerPartId'] == null)
			{
				$data = array();
				
				if($line['PartManufacturerId'] == null)  $data['ManufacturerId']['raw'] = "(SELECT Id FROM partManufacturer WHERE Name = '".$line['ManufacturerName']."')";
				else $data['ManufacturerId'] = $line['PartManufacturerId'];
			
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
				$data['SupplierId']['raw'] = "(SELECT SupplierId FROM purchasOrder WHERE PoNo = '".$purchaseOrderNo."')";
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