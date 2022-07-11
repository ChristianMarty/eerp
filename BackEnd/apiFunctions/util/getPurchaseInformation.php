<?php
//*************************************************************************************************
// FileName : getPurchaseInformation.php
// FilePath : apiFunctions/utils/
// Author   : Christian Marty
// Date		: 17.04.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

include_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";

function getPurchaseInformation($receivalId)
{
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$query  = "SELECT PoNo, Price, finance_currency.CurrencyCode AS Currency, PurchaseDate, Sku AS SupplierPartNumber, vendor.Name AS SupplierName, OrderReference, vendor.Id AS VendorId FROM purchasOrder_itemOrder ";
	$query .= "LEFT JOIN purchasOrder_itemReceive ON purchasOrder_itemReceive.ItemOrderId = purchasOrder_itemOrder.Id ";
	$query .= "LEFT JOIN purchasOrder ON purchasOrder.Id = purchasOrder_itemOrder.PurchasOrderId ";
	$query .= "LEFT JOIN vendor ON vendor.Id = purchasOrder.VendorId ";
	$query .= "LEFT JOIN finance_currency ON finance_currency.Id = purchasOrder.CurrencyId ";
	$query .= "WHERE purchasOrder_itemReceive.Id = ".$receivalId;

	$result = dbRunQuery($dbLink,$query);
	$gctNr = null;
	$output = array();
	$quantity = 0;
	
	while($r = mysqli_fetch_assoc($result)) 
	{
		$r["VendorId"] = intval($r["VendorId"]);
		$output = $r;
	}
	
	
	
	dbClose($dbLink);

	return $output;
}


?>