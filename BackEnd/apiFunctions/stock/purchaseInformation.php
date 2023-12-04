<?php
//*************************************************************************************************
// FileName : purchaseInformation.php
// FilePath : apiFunctions/stock/
// Author   : Christian Marty
// Date		: 21.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/../util/_barcodeParser.php";
require_once __DIR__ . "/../util/_barcodeFormatter.php";

if($api->isGet())
{
	$parameter = $api->getGetData();

	if(!isset($parameter->StockNo)) $api->returnParameterMissingError("StockNo");
	$stockNumber = barcodeParser_StockNumber($parameter->StockNo);
	if($stockNumber === null) $api->returnParameterError("StockNo");

	$query = <<<STR
		SELECT 
		    PoNo, 
		    Price, 
		    finance_currency.CurrencyCode AS Currency, 
		    PurchaseDate 
		FROM purchaseOrder_itemOrder
		LEFT JOIN purchaseOrder_itemReceive ON purchaseOrder_itemReceive.ItemOrderId = purchaseOrder_itemOrder.Id 
		LEFT JOIN purchaseOrder ON purchaseOrder.Id = purchaseOrder_itemOrder.PurchaseOrderId 
		LEFT JOIN finance_currency ON finance_currency.Id = purchaseOrder.CurrencyId 
		WHERE purchaseOrder_itemReceive.Id = (SELECT partStock.ReceivalId FROM partStock WHERE StockNo = '$stockNumber')
	STR;

	$output = $database->query($query);
	$api->returnData($output);
}
