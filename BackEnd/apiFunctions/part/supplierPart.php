<?php
//*************************************************************************************************
// FileName : supplierPart.php
// FilePath : apiFunctions/part/
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
	else sendResponse(null, "ManufacturerPartId unspecified");
	
	$supplierData = array();
	
	$query = "SELECT * FROM supplierPart";
	$query.=" LEFT JOIN supplier On supplier.Id = supplierPart.SupplierId ";
	$query.=" WHERE supplierPart.ManufacturerPartId = ".$manufacturerPartId."";
	
	
	$supplierParts = dbRunQuery($dbLink,$query);
	
	while($supplier = mysqli_fetch_assoc($supplierParts)) 
	{
		array_push($supplierData, $supplier);
	}

	dbClose($dbLink);	
	sendResponse($supplierData);
}
?>