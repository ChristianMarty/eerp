<?php
//*************************************************************************************************
// FileName : reservation.php
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
	
	$query  = "SELECT workOrder.Title AS Title, WorkOrderNo, partStock_reservation.Quantity FROM partStock_reservation ";
	$query .= "LEFT JOIN  workOrder ON workOrder.Id = partStock_reservation.WorkOrderId ";
	$query .= "WHERE StockId = (SELECT partStock.Id FROM partStock WHERE StockNo = '".$stockNo."') ";
	

	
	$result = dbRunQuery($dbLink,$query);
	$output = array();
	$gctNr = null;
	
	$quantity = 0;
	
	while($r = mysqli_fetch_assoc($result)) 
	{
		array_push($output, $r);
	}
	
	dbClose($dbLink);	
	sendResponse($output);
}
?>
