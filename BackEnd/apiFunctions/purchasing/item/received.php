<?php
//*************************************************************************************************
// FileName : received.php
// FilePath : apiFunctions/purchasing/item/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../databaseConnector.php";
require_once __DIR__ . "/../../../config.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	if(!isset($_GET["ReceivalId"]))sendResponse(null, "Receival Id not specified");
	
	$receivalId = intval($_GET["ReceivalId"]);
	
	$dbLink = dbConnect();
	
	$output = array();

    $query = <<<STR
        SELECT 
            purchaseOrder_itemReceive.Id as ReceivalId, 
            manufacturer.Name AS ManufacturerName, 
            manufacturerPart.ManufacturerPartNumber,
            supplier.Name AS SupplierName, 
            supplierPart.SupplierPartNumber, 
            purchaseOrder_itemReceive.QuantityReceived, 
            purchaseOrder_itemOrder.OrderReference, 
            purchaseOrder_itemOrder.SupplierPartId
        FROM purchaseOrder_itemReceive
        LEFT JOIN purchaseOrder_itemOrder ON purchaseOrder_itemOrder.Id = purchaseOrder_itemReceive.ItemOrderId
        LEFT JOIN purchaseOrder ON purchaseOrder.Id = purchaseOrder_itemOrder.PurchaseOrderId
        LEFT JOIN supplierPart ON supplierPart.Id = purchaseOrder_itemOrder.SupplierPartId
        LEFT JOIN (SELECT Id, Name FROM vendor)supplier ON supplier.Id = supplierPart.VendorId
        LEFT JOIN manufacturerPart ON manufacturerPart.Id = supplierPart.ManufacturerPartId
        LEFT JOIN (SELECT Id, Name FROM vendor)manufacturer  ON manufacturer.Id = manufacturerPart.VendorId
        WHERE purchaseOrder_itemReceive.Id = $receivalId
    STR;

	$result = dbRunQuery($dbLink,$query);
	
	while($r = mysqli_fetch_assoc($result)) 
	{
		$r['ReceivalId'] = intval($r['ReceivalId']);
		$r['SupplierPartId'] = intval($r['SupplierPartId']);
		$output = $r;
	}
	
	dbClose($dbLink);	
	sendResponse($output,null);
}
else if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$data = json_decode(file_get_contents('php://input'),true);
	
	$dbLink = dbConnect();
	
	$lineId = $data['data']['LineId'];
	$lineNo = $data['data']['LineNo'];
	$purchaseOrderId = $data['data']['PurchaseOrderId'];
	$receivedQuantity = $data['data']['ReceivedQuantity'];
	$receivedDate = $data['data']['ReceivedDate'];
	
	$row = array();
	$row['ItemOrderId'] = $lineId;
	$row['QuantityReceived'] = $receivedQuantity;
	$row['ReceivalDate'] = $receivedDate;
	if(isset($_SESSION["userid"]))$row['UserId'] = $_SESSION["userid"];
	else $row['UserId'] = null;
	
	$query = dbBuildInsertQuery($dbLink, "purchaseOrder_itemReceive", $row);

	$query = $query ."SELECT LAST_INSERT_ID();";

	$output = array();
	$error= null;

	if(mysqli_multi_query($dbLink,$query))
	{
		do {
			if ($result = mysqli_store_result($dbLink)) {
				while ($row = mysqli_fetch_row($result)) {
					$output["ReceivalId"] = intval($row[0]);
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