<?php
//*************************************************************************************************
// FileName : supplierPart.php
// FilePath : apiFunctions/supplier/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	if(isset($_GET["ManufacturerPartNumberId"])) $manufacturerPartNumberId =  dbEscapeString($dbLink, $_GET["ManufacturerPartNumberId"]);
	if(isset($_GET["SupplierId"])) $supplierId =  dbEscapeString($dbLink, $_GET["SupplierId"]);
	if(isset($_GET["ProductionPartNo"])) $productionPartNo =  dbEscapeString($dbLink, $_GET["ProductionPartNo"]);
	
	$supplierData = array();
    $query = <<<STR
        SELECT *, 
               manufacturerPart_partNumber.Number AS ManufacturerPartNumbe, 
               vendor.Id AS SupplierId,
               vendor_displayName(vendor.Id) AS SupplierName,
               supplierPart.Id AS SupplierPartId 
        FROM supplierPart 
        LEFT JOIN vendor On vendor.Id = supplierPart.VendorId 
        LEFT JOIN manufacturerPart_partNumber On manufacturerPart_partNumber.Id = supplierPart.ManufacturerPartNumberId 
        LEFT JOIN productionPart_manufacturerPart_mapping ON productionPart_manufacturerPart_mapping.ManufacturerPartNumberId = manufacturerPart_partNumber.Id 
        LEFT JOIN productionPart ON productionPart.Id = productionPart_manufacturerPart_mapping.ProductionPartId 
    STR;

	$parameters = array();
	if(isset($manufacturerPartNumberId)) $parameters[] = " supplierPart.ManufacturerPartNumberId = " . $manufacturerPartNumberId;
	if(isset($supplierId)) $parameters[] = " supplierPart.VendorId = " . $supplierId;
	if(isset($productionPartNo)) $parameters[] = " productionPart.PartNo = '" . $productionPartNo . "'";
	
	$query = dbBuildQuery($dbLink, $query, $parameters);
	
	$supplierParts = dbRunQuery($dbLink,$query);
	
	while($supplier = mysqli_fetch_assoc($supplierParts)) 
	{
		$supplierData[] = $supplier;
	}

	dbClose($dbLink);	
	sendResponse($supplierData);
}
else if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$data = json_decode(file_get_contents('php://input'),true);

	$supplierPartCreate = array();
	$supplierPartCreate['ManufacturerPartNumberId'] = intval($data['data']['ManufacturerPartNumberId']);
	$supplierPartCreate['VendorId'] = intval($data['data']['SupplierId']);
	$supplierPartCreate['SupplierPartNumber'] = $data['data']['SupplierPartNumber'];
	$supplierPartCreate['SupplierPartLink'] = $data['data']['SupplierPartLink'];
	$supplierPartCreate['Note'] = $data['data']['Note'];

	$query = dbBuildInsertQuery($dbLink, "supplierPart", $supplierPartCreate);
	
	$query .= "SELECT Id FROM supplierPart WHERE Id = LAST_INSERT_ID();";
	
	$output = array();
	$error = null;
	
	if(mysqli_multi_query($dbLink,$query))
	{
		do {
			if ($result = mysqli_store_result($dbLink)) {
				while ($row = mysqli_fetch_row($result)) {
					$output["PurchaseOrderNo"] = $row[0];
				}
				mysqli_free_result($result);
			}
			if(!mysqli_more_results($dbLink)) break;
		} while (mysqli_next_result($dbLink));
	}
	else
	{
		$error = "Error description: " . mysqli_error($dbLink);
	}

	dbClose($dbLink);	
	sendResponse($output,$error);
}
?>