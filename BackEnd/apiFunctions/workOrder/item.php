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
require_once __DIR__ . "/../util/_barcodeParser.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	if(!isset($_GET["WorkOrderNumber"])) sendResponse(NULL, "Work Order Number Undefined");
    $workOrderNumber = barcodeParser_WorkOrderNumber($_GET["WorkOrderNumber"]);

	$dbLink = dbConnect();

    $query = <<< STR
        SELECT workOrder.Id AS WorkOrderId, project.Title AS ProjectTitle, workOrder.Title, Quantity, WorkOrderNumber, Status 
        FROM workOrder
        LEFT JOIN project On project.Id = workOrder.ProjectId
        WHERE workOrder.WorkOrderNumber = $workOrderNumber
    STR;

	$result = mysqli_query($dbLink,$query);
    $workOrderData = array();
	while($r = mysqli_fetch_assoc($result)) 
	{
		$workOrderData = $r;
	}
	
	$workOrderId = $workOrderData['WorkOrderId'];
	
	$partUsed = array();


    $query = <<<STR
    SELECT  
        prodPart.ProductionPartNumber, 
        partStock.StockNo AS StockNumber, 
        manufacturerPart_partNumber.Number AS ManufacturerPartNumber, 
        manufacturerPart_partNumber.Id AS ManufacturerPartNumberId, 
        vendor.Name as ManufacturerName, 
        partStock_history.Quantity, 
        partStock_history.Date AS RemovalDate, 
        partStock_getPrice(partStock_history.StockId) AS Price
    FROM partStock_history
    LEFT JOIN partStock On partStock.Id = partStock_history.StockId
    LEFT JOIN manufacturerPart_partNumber On manufacturerPart_partNumber.Id = partStock.ManufacturerPartNumberId
    LEFT JOIN vendor On vendor.Id = manufacturerPart_partNumber.VendorId 
    LEFT JOIN (
        SELECT GROUP_CONCAT(CONCAT(numbering.Prefix,'-',productionPart.Number)) AS ProductionPartNumber, ManufacturerPartNumberId 
        FROM productionPartMapping 
        LEFT JOIN productionPart On productionPart.Id = productionPartMapping.ProductionPartId
        LEFT JOIN numbering ON numbering.Id = productionPart.NumberingPrefixId
        GROUP BY ManufacturerPartNumberId
    )prodPart On prodPart.ManufacturerPartNumberId = partStock.ManufacturerPartNumberId
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
else if($_SERVER['REQUEST_METHOD'] == 'PATCH')
{
    $data = json_decode(file_get_contents('php://input'),true);

    if(!isset($data["WorkOrderNumber"])) sendResponse(NULL, "Work Order Number Undefined");
    $workOrderNumber = barcodeParser_WorkOrderNumber($data["WorkOrderNumber"]);

    $woData = array();
    $woData['Status'] = $data['Status'];

    $dbLink = dbConnect();
    $query = dbBuildUpdateQuery($dbLink, "workOrder", $woData, "WorkOrderNumber = ".$workOrderNumber);
    $result = dbRunQuery($dbLink,$query);
    dbClose($dbLink);

    sendResponse(null);
}

?>