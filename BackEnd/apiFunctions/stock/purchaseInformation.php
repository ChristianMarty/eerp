<?php
//*************************************************************************************************
// FileName : purchaseInformation.php
// FilePath : apiFunctions/stock/
// Author   : Christian Marty
// Date		: 05.01.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../util/_barcodeParser.php";
require_once __DIR__ . "/../util/_barcodeFormatter.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	if(!isset($_GET["StockNo"]))sendResponse(null, "StockNo not specified");
	$stockNumber = barcodeParser_StockNumber($_GET["StockNo"]);
	if(!$stockNumber) sendResponse(null, "StockNo invalid");
		
	$dbLink = dbConnect();
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
