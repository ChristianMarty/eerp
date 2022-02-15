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

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	
	if(!isset($_GET["SupplierId"]) || !isset($_GET["OrderNumber"])) sendResponse(null, "SupplierId or OrderNumber missing!");
	
	$supplierId = $_GET["SupplierId"];
	$orderNumber = $_GET["OrderNumber"];
	
	
	if($supplierId == 17) // 17 = mouser,  TODO: make this better
	{
		$data = mouser_getOrderInformation($orderNumber);
	}
	else
	{
		sendResponse(null, "Supplier not supported!");
	}
		
	
	sendResponse($data);
}
if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$data = json_decode(file_get_contents('php://input'),true);
	
	if(!isset($data["SupplierId"]) || !isset($data["OrderNumber"])) sendResponse(null, "SupplierId or OrderNumber missing!");
	
	$supplierId = intval($data["SupplierId"]);
	$orderNumber = $data["OrderNumber"];
	
	if($supplierId == 17) // 17 = mouser,  TODO: make this better
	{
		$supplierData = mouser_getOrderInformation($orderNumber);
	}
	
	$poCreate = array();
	$poCreate['SupplierId'] = $supplierId;
	$poCreate['OrderNumber'] = $orderNumber;
	$poCreate['PurchaseDate'] = $supplierData['OrderDate'];
	$poCreate['Currency'] = $supplierData['CurrencyCode'];
	$poCreate['Status'] = 'Placed';
	
	if($data['Title'] != "") $poCreate['Title'] = $data['Title'];
	if($data['Description'] != "") $poCreate['Description'] = $data['Description'];
	
	$poCreate['PoNo']['raw'] = "purchasOrder_generatePoNo()";
	
	$query = dbBuildInsertQuery($dbLink, "purchasOrder", $poCreate);
	
	$query .= "SELECT Id, PoNo FROM purchasOrder WHERE Id = LAST_INSERT_ID();";
	
	$id = 0;
	$poNo = '';
	$error = null;
	
	if(mysqli_multi_query($dbLink,$query))
	{
		do {
			if ($result = mysqli_store_result($dbLink)) {
				while ($row = mysqli_fetch_row($result)) {
					$id = $row[0];
					$poNo = $row[1];
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
	sendResponse($output);
}

?>