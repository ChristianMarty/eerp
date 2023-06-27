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
	
if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	if(!isset($_GET["SupplierId"]) || !isset($_GET["OrderNumber"])) sendResponse(null, "SupplierId or OrderNumber missing!");

    $dbLink = dbConnect();
	
    $supplierId = dbEscapeString($dbLink, $_GET["SupplierId"]);
    $query = "SELECT * FROM vendor WHERE Id = ".$supplierId.";";
    $result = dbRunQuery($dbLink,$query);
    $supplierData = mysqli_fetch_assoc($result);
    dbClose($dbLink);

	$orderNumber = $_GET["OrderNumber"];

    $name = $supplierData['API'];
    if($name === null) sendResponse(null, "Supplier not supported!");

    require_once __DIR__ . "/../../externalApi/".$name."/".$name.".php";

    $data = call_user_func($name."_getOrderInformation", $orderNumber);


	sendResponse($data);
}
else if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$data = json_decode(file_get_contents('php://input'),true);
	
	if(!isset($_GET["PurchaseOrderNo"]) || !isset($_GET["OrderNumber"])) sendResponse(null, "PurchaseOrder or OrderNumber missing!");
	

	$purchaseOrderNo = strtolower($_GET["PurchaseOrderNo"]);
	$purchaseOrderNo = str_replace("po-","",$purchaseOrderNo);
	
	$orderNumber = $_GET["OrderNumber"];
	
	$query = "SELECT Id, VendorId FROM purchaseOrder WHERE PoNo = ".$purchaseOrderNo.";";
	
	$result = dbRunQuery($dbLink,$query);
	
	$vendorId = 0;
	$id = 0;
	
	while($r = mysqli_fetch_assoc($result)) 
	{
		$vendorId = $r['VendorId'];
		$id = $r['Id'];
	}
	
	$query = "SELECT * FROM vendor WHERE Id = ".$vendorId.";";
    $result = dbRunQuery($dbLink,$query);
    $vendorMetaData = mysqli_fetch_assoc($result);
	
	$name = $vendorMetaData['API'];
    if($name === null) sendResponse(null, "Supplier not supported!");

    require_once __DIR__ . "/../../externalApi/".$name."/".$name.".php";

    $supplierData = call_user_func($name."_getOrderInformation", $orderNumber);
	
	
	$poData = array();
	$poData['OrderNumber'] = $orderNumber;
	$poData['PurchaseDate'] = $supplierData['OrderDate'];
	$poData['CurrencyId']['raw'] = "(SELECT Id FROM finance_currency WHERE CurrencyCode = '".$supplierData['CurrencyCode']."')";
	
	$query = dbBuildUpdateQuery($dbLink, "purchaseOrder", $poData, "PoNo = ".$purchaseOrderNo);
	
	dbRunQuery($dbLink,$query);
	
	/*$poCreate = array();
	$poCreate['VendorId'] = $supplierId;
	$poCreate['OrderNumber'] = $orderNumber;
	$poCreate['PurchaseDate'] = $supplierData['OrderDate'];
	$poCreate['CurrencyId']['raw'] = "(SELECT Id FROM finance_currency WHERE CurrencyCode = '".$supplierData['CurrencyCode']."')";
	$poCreate['Status'] = 'Placed';
	
	if($data['Title'] != "") $poCreate['Title'] = $data['Title'];
	if($data['Description'] != "") $poCreate['Description'] = $data['Description'];
	
	$poCreate['PoNo']['raw'] = "purchaseOrder_generatePoNo()";
	
	$query = dbBuildInsertQuery($dbLink, "purchaseOrder", $poCreate);
	
	$query .= "SELECT Id, PoNo FROM purchaseOrder WHERE Id = LAST_INSERT_ID();";
	
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
	
	dbClose($dbLink);*/
	
	
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
		$sqlData['StockPart']['raw'] = "b'1'";
		
		$sqlData['PurchaseOrderId'] = $id;
		$query = dbBuildInsertQuery($dbLink,"purchaseOrder_itemOrder", $sqlData);
		
		
		dbRunQuery($dbLink,$query);
		dbClose($dbLink);	
	}
	$output = array();
	$output["PurchaseOrderNo"] = $purchaseOrderNo;
	sendResponse($output);
}

?>