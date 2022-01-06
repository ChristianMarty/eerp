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

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	if(!isset($_GET["StockNo"]))sendResponse(null, "StockNo not specified");
		
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$temp = dbEscapeString($dbLink, $_GET["StockNo"]);
	$temp = strtolower($temp);
	$stockNo = str_replace("stk-","",$temp);

	$query  = "SELECT PoNo, Price, Currency FROM purchasOrder_itemOrder ";
	$query .= "LEFT JOIN purchasOrder_itemReceive ON purchasOrder_itemReceive.ItemOrderId = purchasOrder_itemOrder.Id ";
	$query .= "LEFT JOIN purchasOrder ON purchasOrder.Id = purchasOrder_itemOrder.PurchasOrderId ";
	$query .= "WHERE purchasOrder_itemReceive.Id = (SELECT partStock.ReceivalId FROM partStock WHERE StockNo = '".$stockNo."') ";

	
	$result = dbRunQuery($dbLink,$query);
	$gctNr = null;
	
	$quantity = 0;
	
	while($r = mysqli_fetch_assoc($result)) 
	{
		$output = $r;
	}
	
	dbClose($dbLink);	
	sendResponse($output);
}
?>
