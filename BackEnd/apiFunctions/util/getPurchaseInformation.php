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

    $query = <<<STR
        SELECT 
            PoNo, 
            Price, 
            finance_currency.CurrencyCode AS Currency, 
            PurchaseDate, 
            Sku AS SupplierPartNumber, 
            vendor.Name AS SupplierName, 
            OrderReference, 
            vendor.Id AS VendorId 
        FROM purchaseOrder_itemOrder
        LEFT JOIN purchaseOrder_itemReceive ON purchaseOrder_itemReceive.ItemOrderId = purchaseOrder_itemOrder.Id
        LEFT JOIN purchaseOrder ON purchaseOrder.Id = purchaseOrder_itemOrder.PurchaseOrderId
        LEFT JOIN vendor ON vendor.Id = purchaseOrder.VendorId
        LEFT JOIN finance_currency ON finance_currency.Id = purchaseOrder.CurrencyId
        WHERE purchaseOrder_itemReceive.Id = $receivalId
    STR;

	$result = dbRunQuery($dbLink,$query);
	$output = array();

	while($r = mysqli_fetch_assoc($result)) 
	{
		$r["VendorId"] = intval($r["VendorId"]);
		$output[] = $r;
	}

	dbClose($dbLink);
	return $output;
}


?>