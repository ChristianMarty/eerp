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
	
	if(isset($_GET["ManufacturerPartId"])) $manufacturerPartId =  dbEscapeString($dbLink, $_GET["ManufacturerPartId"]);
	if(isset($_GET["SupplierId"])) $supplierId =  dbEscapeString($dbLink, $_GET["SupplierId"]);
	if(isset($_GET["ProductionPartNo"])) $productionPartNo =  dbEscapeString($dbLink, $_GET["ProductionPartNo"]);
	
	$supplierData = array();
	
	$query = "SELECT *, mfrPart.ManufacturerName AS ManufacturerName, vendor.Name AS SupplierName, supplierPart.Id AS SupplierPartId FROM supplierPart ";
	$query.="LEFT JOIN vendor On vendor.Id = supplierPart.VendorId ";
	$query.="LEFT JOIN manufacturerPart On manufacturerPart.Id = supplierPart.ManufacturerPartId ";
	$query.="LEFT JOIN (SELECT ManufacturerPartNumber, manufacturerPart.Id AS Id, vendor.Name AS ManufacturerName FROM manufacturerPart LEFT JOIN vendor On vendor.Id = manufacturerPart.VendorId)mfrPart On mfrPart.Id = supplierPart.ManufacturerPartId ";
	$query.="LEFT JOIN productionPartMapping ON productionPartMapping.ManufacturerPartId = mfrPart.Id ";
	$query.="LEFT JOIN productionPart ON productionPart.Id = productionPartMapping.ProductionPartId ";
	
	
	$parameters = array();
	if(isset($manufacturerPartId)) array_push($parameters, 'supplierPart.ManufacturerPartId = '. $manufacturerPartId);
	if(isset($supplierId)) array_push($parameters, 'supplierPart.VendorId = '.$supplierId);
	if(isset($productionPartNo)) array_push($parameters, "productionPart.PartNo = '".$productionPartNo."'");
	
	$query = dbBuildQuery($dbLink, $query, $parameters);
	
	$supplierParts = dbRunQuery($dbLink,$query);
	
	while($supplier = mysqli_fetch_assoc($supplierParts)) 
	{
		array_push($supplierData, $supplier);
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
	$supplierPartCreate['ManufacturerPartId'] = intval($data['data']['ManufacturerPartId']);
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