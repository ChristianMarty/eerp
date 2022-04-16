<?php
//*************************************************************************************************
// FileName : purchaseInformation.php
// FilePath : apiFunctions/inventory/
// Author   : Christian Marty
// Date		: 05.01.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	if(isset($_GET["InvNo"]))
	{
		$invNo = $_GET["InvNo"];
		$invNo = strtolower($invNo);
		$invNo = str_replace("inv","",$invNo);
		$invNo = str_replace("-","",$invNo);
		$invNo = intval($invNo);
	}
	else
	{
		sendResponse($output,"No inventory item specified");
	}
		
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$query  = "SELECT PoNo, Price, Currency, PurchaseDate, Sku AS SupplierPartNumber, supplier.Name AS SupplierName, OrderReference FROM purchasOrder_itemOrder ";
	$query .= "LEFT JOIN purchasOrder_itemReceive ON purchasOrder_itemReceive.ItemOrderId = purchasOrder_itemOrder.Id ";
	$query .= "LEFT JOIN purchasOrder ON purchasOrder.Id = purchasOrder_itemOrder.PurchasOrderId ";
	$query .= "LEFT JOIN supplier ON supplier.Id = purchasOrder.SupplierId ";
	$query .= "WHERE purchasOrder_itemReceive.Id = (SELECT inventory.ReceivalId FROM inventory WHERE InvNo = '".$invNo."') ";


	$result = dbRunQuery($dbLink,$query);
	$gctNr = null;
	$output = array();
	$quantity = 0;
	
	while($r = mysqli_fetch_assoc($result)) 
	{
		$output = $r;
	}
	
	dbClose($dbLink);	
	sendResponse($output);
}
?>
