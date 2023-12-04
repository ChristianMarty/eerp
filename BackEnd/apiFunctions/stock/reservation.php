<?php
//*************************************************************************************************
// FileName : reservation.php
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
		    workOrder.Title AS Title, 
		    WorkOrderNumber, 
		    partStock_reservation.Quantity 
		FROM partStock_reservation 
		LEFT JOIN  workOrder ON workOrder.Id = partStock_reservation.WorkOrderId 
		WHERE StockId = (SELECT partStock.Id FROM partStock WHERE StockNo = '$stockNumber') 
	STR;

	$output = $database->query($query);
	$api->returnData($output);
}
