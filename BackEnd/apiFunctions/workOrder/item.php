<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/workOrder/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	
	if(!isset($_GET["WorkOrderNo"])) sendResponse(NULL, "Work Order Number Undefined");

	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$workOrderData = array();
	
	$workOrderNo = dbEscapeString($dbLink, $_GET["WorkOrderNo"]);
	$workOrderNo = strtolower($workOrderNo);
	$workOrderNo = str_replace("wo-","",$workOrderNo);
	
	$query = "SELECT workOrder.Id AS WorkOrderId, project.Title AS ProjectTitle, workOrder.Title, Quantity, WorkOrderNo, Status FROM workOrder ";
	$query .= "LEFT JOIN project On project.Id = workOrder.ProjectId ";
	$query .= "WHERE workOrder.workOrderNo = ".$workOrderNo;
	
	$result = mysqli_query($dbLink,$query);
	while($r = mysqli_fetch_assoc($result)) 
	{
		$workOrderData = $r;
	}
	
	$workOrderId = $workOrderData['WorkOrderId'];
	
	$partUsed = array();


    $query = <<<STR
    SELECT  prodPart.ProductionPartNumber, partStock.StockNo AS StockNumber, ManufacturerPartNumber, vendor.Name as ManufacturerName, partStock_history.Quantity, partStock_history.Date AS RemovalDate, partStock_getPrice(partStock_history.StockId) AS Price
    FROM partStock_history
    LEFT JOIN partStock On partStock.Id = partStock_history.StockId
    LEFT JOIN manufacturerPart On manufacturerPart.Id = partStock.ManufacturerPartId
    LEFT JOIN vendor On vendor.Id = manufacturerPart.VendorId
    LEFT JOIN (
        SELECT GROUP_CONCAT(CONCAT(numbering.Prefix,'-',productionPart.Number)) AS ProductionPartNumber, ManufacturerPartId FROM productionPartMapping 
        LEFT JOIN productionPart On productionPart.Id = productionPartMapping.ProductionPartId
        LEFT JOIN numbering ON numbering.Id = productionPart.NumberingPrefixId
        GROUP BY ManufacturerPartId
    )prodPart On prodPart.ManufacturerPartId = manufacturerPart.Id
    WHERE partStock_history.workOrderId = $workOrderId
    STR;

	$result = mysqli_query($dbLink,$query);
	while($r = mysqli_fetch_assoc($result)) 
	{
        if($r['ProductionPartNumber'] != null) $r['ProductionPartNumber'] = explode(",", $r['ProductionPartNumber']);
		$partUsed[] = $r;
	}
	
	$workOrderData['PartsUsed'] = $partUsed;
	dbClose($dbLink);	
	sendResponse($workOrderData);
}

?>