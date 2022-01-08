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
	if($dbLink == null) return null;
	
	$output = array();
	
	$query = "SELECT partManufacturer.Name AS ManufacturerName, manufacturerPart.ManufacturerPartNumber,supplier.Name AS SupplierName, supplierPart.SupplierPartNumber, purchasOrder_itemReceive.QuantityReceived, purchasOrder_itemOrder.OrderReference ";
	$query .= "FROM purchasOrder_itemReceive ";
	$query .= "LEFT JOIN purchasOrder_itemOrder ON purchasOrder_itemOrder.Id = purchasOrder_itemReceive.ItemOrderId ";
	$query .= "LEFT JOIN purchasOrder ON purchasOrder.Id = purchasOrder_itemOrder.PurchasOrderId ";
	$query .= "LEFT JOIN supplierPart ON supplierPart.Id = purchasOrder_itemOrder.SupplierPartId ";
	$query .= "LEFT JOIN supplier ON supplier.Id = supplierPart.SupplierId ";
	$query .= "LEFT JOIN manufacturerPart ON manufacturerPart.Id = supplierPart.ManufacturerPartId ";
	$query .= "LEFT JOIN partManufacturer ON partManufacturer.Id = manufacturerPart.ManufacturerId ";
	$query .= "WHERE purchasOrder_itemReceive.Id = ".$receivalId;
	
	$result = dbRunQuery($dbLink,$query);
	
	while($r = mysqli_fetch_assoc($result)) 
	{
		$output = $r;
	}
	
	dbClose($dbLink);	
	sendResponse($output,null);
	
}
else if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$data = json_decode(file_get_contents('php://input'),true);
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$lineId = $data['data']['LineId'];
	$lineNo = $data['data']['LineNo'];
	$purchasOrderId = $data['data']['PurchasOrderId'];
	$receivedQuantity = $data['data']['ReceivedQuantity'];
	$receivedDate = $data['data']['ReceivedDate'];
	
	$row = array();
	$row['ItemOrderId'] = $lineId;
	$row['QuantityReceived'] = $receivedQuantity;
	$row['ReceivalDate'] = $receivedDate;
	
	$query = dbBuildInsertQuery($dbLink, "purchasOrder_itemReceive",$row);
	
	$result = dbRunQuery($dbLink,$query);
	
	dbClose($dbLink);	
	
	
	sendResponse(null,null);
}

?>