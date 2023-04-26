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

function loadDatabaseData($purchaseOrderNo)
{
	$dbLink = dbConnect();
	if($dbLink == null) return null;

    $purchaseOrderNo = dbEscapeString($dbLink, $purchaseOrderNo);

    $query = <<<STR
        SELECT purchasOrder_itemOrder.Id AS OrderLineId, supplier.Name AS SupplierName, LineNo, purchasOrder_itemOrder.Type, purchasOrder_itemOrder.ManufacturerPartNumber, manufacturerPart.Id AS ManufacturerPartId, purchasOrder_itemOrder.ManufacturerName, manufacturer.Name AS ManufacturerNameDatabase, manufacturer.Id AS PartVendorId, purchasOrder_itemOrder.Sku, supplierPart.Id AS SupplierPartId
        FROM purchasOrder_itemOrder
        LEFT JOIN purchasOrder ON purchasOrder.Id = purchasOrder_itemOrder.PurchasOrderId
        
        LEFT JOIN (
            SELECT  Id, ShortName AS Name FROM vendor WHERE ShortName IS NOT NULL
            UNION
            SELECT  Id, Name FROM vendor
            UNION
            SELECT VendorId AS Id, NAME FROM vendor_alias
        )manufacturer ON manufacturer.Name = purchasOrder_itemOrder.ManufacturerName
        
        LEFT JOIN manufacturerPart ON manufacturerPart.VendorId = manufacturer.Id AND manufacturerPart.ManufacturerPartNumber = purchasOrder_itemOrder.ManufacturerPartNumber
        LEFT JOIN supplierPart ON supplierPart.VendorId = purchasOrder.VendorId AND supplierPart.SupplierPartNumber =  purchasOrder_itemOrder.Sku
        LEFT JOIN (SELECT Id, Name FROM vendor)supplier on supplier.Id = supplierPart.VendorId
        WHERE purchasOrder.PoNo = $purchaseOrderNo
        ORDER BY LineNo
    STR;
	
	$result = dbRunQuery($dbLink,$query);

	$lines = array();
	
	while($r = mysqli_fetch_assoc($result)) 
	{
		if($r['PartVendorId'] != null) $r['ManufacturerName'] = $r['ManufacturerNameDatabase'];
		unset($r['ManufacturerNameDatabase']);
		
		$lines[] = $r;
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
			$output["Lines"][] = $line;
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