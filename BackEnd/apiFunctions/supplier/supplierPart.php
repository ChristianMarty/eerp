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
	
	
	$supplierData = array();
	
	$query = "SELECT *, supplierPart.Id AS SupplierPartId FROM supplierPart ";
	$query.="LEFT JOIN supplier On supplier.Id = supplierPart.SupplierId ";
	
	$parameters = array();
	if(isset($manufacturerPartId)) array_push($parameters, 'supplierPart.ManufacturerPartId = '. $manufacturerPartId);
	if(isset($supplierId)) array_push($parameters, 'supplierPart.SupplierId = '.$supplierId);
	
	$query = dbBuildQuery($dbLink, $query, $parameters);
	
	$supplierParts = dbRunQuery($dbLink,$query);
	
	while($supplier = mysqli_fetch_assoc($supplierParts)) 
	{
		array_push($supplierData, $supplier);
	}

	dbClose($dbLink);	
	sendResponse($supplierData);
}
?>