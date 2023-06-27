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
require_once __DIR__ . "/../util/_barcodeParser.php";
require_once __DIR__ . "/../util/_barcodeFormatter.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	if(!isset($_GET["StockNo"]))sendResponse(null, "StockNo not specified");
	$stockNumber = barcodeParser_StockNumber($_GET["StockNo"]);
	if(!$stockNumber) sendResponse(null, "StockNo invalid");

	$query = <<<STR
		SELECT 
		    workOrder.Title AS Title, 
		    WorkOrderNumber, 
		    partStock_reservation.Quantity 
		FROM partStock_reservation 
		LEFT JOIN  workOrder ON workOrder.Id = partStock_reservation.WorkOrderId 
		WHERE StockId = (SELECT partStock.Id FROM partStock WHERE StockNo = '$stockNumber') 
	STR;

	$dbLink = dbConnect();
	$result = dbRunQuery($dbLink,$query);
	$output = array();
	
	while($r = mysqli_fetch_assoc($result)) 
	{
		$output[] = $r;
	}
	
	dbClose($dbLink);	
	sendResponse($output);
}
?>
