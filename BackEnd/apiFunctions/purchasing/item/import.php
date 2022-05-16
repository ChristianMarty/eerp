<?php
//*************************************************************************************************
// FileName : import.php
// FilePath : apiFunctions/purchasing/item/
// Author   : Christian Marty
// Date		: 05.01.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../databaseConnector.php";
require_once __DIR__ . "/../../../config.php";
require_once __DIR__ . "/../../externalApi/mouser.php";
require_once __DIR__ . "/../../externalApi/digikey.php";

global $mouserSupplierId;
global $digikeySupplierId;
	
if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	if(!isset($_GET["SupplierId"]) || !isset($_GET["OrderNumber"])) sendResponse(null, "SupplierId or OrderNumber missing!");
	
	$supplierId = $_GET["SupplierId"];
	$orderNumber = $_GET["OrderNumber"];
	
	if($supplierId == $mouserSupplierId)
	{
		$data = mouser_getOrderInformation($orderNumber);
	}
	else if($supplierId == $digikeySupplierId)
	{
		$data = digikey_getOrderInformation($orderNumber);
	}
	else
	{
		sendResponse(null, "Supplier not supported!");
	}
		
	sendResponse($data);
}
else if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$data = json_decode(file_get_contents('php://input'),true);
	
	if(!isset($_GET["PurchaseOrderNo"]) || !isset($_GET["OrderNumber"])) sendResponse(null, "Purchase Order or Order Number missing!");
	
	$poNo = dbEscapeString($dbLink, $_GET["PurchaseOrderNo"]);
	$poNo = strtolower($poNo);
	$poNo = str_replace("po","",$poNo);
	$poNo = str_replace("-","",$poNo);
	
	$orderNumber = $_GET["OrderNumber"];
	
	$query = "SELECT Id, VendorId FROM purchasOrder WHERE PoNo = '".$poNo."'";
	
	$result = dbRunQuery($dbLink,$query);
	
	$supplierId = null;
	$id = 0;
	while($r = mysqli_fetch_assoc($result)) 
	{
		$supplierId = $r['VendorId'];
		$id = $r['Id'];
	}
	
	if($supplierId == $mouserSupplierId)
	{
		$supplierData = mouser_getOrderInformation($orderNumber);
	}
	else if($supplierId == $digikeySupplierId)
	{
		$supplierData = digikey_getOrderInformation($orderNumber);
	}
	
	$poUpdate = array();
	$poUpdate['OrderNumber'] = $orderNumber;
	$poUpdate['PurchaseDate'] = $supplierData['OrderDate'];
	$poUpdate['CurrencyId']['raw'] = "(SELECT Id FROM finance_currency WHERE CurrencyCode = '".$supplierData['CurrencyCode']."')";
	$poUpdate['Status'] = 'Editing';
	
	$query = dbBuildUpdateQuery($dbLink, "purchasOrder", $poUpdate, "PoNo = '".$poNo."'");
	$result = dbRunQuery($dbLink,$query);

	$error = null;
	
	if($result == false)
	{
		$error = "Error description: " . mysqli_error($dbLink);
	}
	
	dbClose($dbLink);
	
	
	foreach($supplierData["Lines"] as $line) 
	{
		$dbLink = dbConnect();
		if($dbLink == null) return null;
		
		$sqlData = array();
		
		$sqlData['LineNo'] = $line['LineNo'];
		$sqlData['Description'] = $line['SupplierDescription'];
		$sqlData['Quantity'] = $line['Quantity'];
		$sqlData['Sku'] = $line['SupplierPartNumber'];
		$sqlData['Price'] = $line['Price'];
		$sqlData['Type'] = 'Part';
		$sqlData['ManufacturerName'] = $line['ManufacturerName'];
		$sqlData['ManufacturerPartNumber'] = $line['ManufacturerPartNumber'];
		$sqlData['OrderReference'] = $line['OrderReference'];
		
		$sqlData['PurchasOrderId'] = $id;
		$query = dbBuildInsertQuery($dbLink,"purchasOrder_itemOrder", $sqlData);
		
		
		dbRunQuery($dbLink,$query);
		dbClose($dbLink);	
	}
	$output = array();
	$output["PurchaseOrderNo"] = $poNo;
	sendResponse($output, $error);
}

?>