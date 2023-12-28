<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/workOrder/
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

	if(!isset($parameter->WorkOrderNumber)) $api->returnParameterMissingError("WorkOrderNumber");
    $workOrderNumber = barcodeParser_WorkOrderNumber($parameter->WorkOrderNumber);
    if($workOrderNumber === null) $api->returnParameterError("WorkOrderNumber");

    $query = <<< STR
        SELECT 
            workOrder.Id AS WorkOrderId, 
            project.Title AS ProjectTitle, 
            workOrder.Title, 
            Quantity, 
            WorkOrderNumber, 
            Status 
        FROM workOrder
        LEFT JOIN project On project.Id = workOrder.ProjectId
        WHERE workOrder.WorkOrderNumber = $workOrderNumber
        LIMIT 1;
    STR;

    $workOrderData = $database->query($query);
    if(count($workOrderData) === 0) $api->returnError("WorkOrderNumber not found");

    $workOrderId = $workOrderData[0]->WorkOrderId;

    $output = array();
    $output['WorkOrderBarcode'] = barcodeFormatter_WorkOrderNumber($workOrderData[0]->WorkOrderNumber);
    $output['WorkOrderNumber'] = $workOrderData[0]->WorkOrderNumber;
    $output['Title'] = $workOrderData[0]->Title;
    $output['ProjectTitle'] = $workOrderData[0]->ProjectTitle;
    $output['Quantity'] = $workOrderData[0]->Quantity;
    $output['Status'] = $workOrderData[0]->Status;

	$partUsed = array();

    $query = <<<STR
    SELECT  
        prodPart.ProductionPartNumber, 
        partStock.StockNo AS StockNumber, 
        manufacturerPart_partNumber.Number AS ManufacturerPartNumber, 
        manufacturerPart_partNumber.Id AS ManufacturerPartNumberId, 
        vendor_displayName(vendor.Id) as ManufacturerDisplayName, 
        vendor.Id AS ManufacturerId,
        partStock_history.Quantity, 
        partStock_history.Date AS RemovalDate, 
        partStock_getPrice(partStock_history.StockId) AS Price
    FROM partStock_history
    LEFT JOIN partStock On partStock.Id = partStock_history.StockId
    LEFT JOIN manufacturerPart_partNumber ON manufacturerPart_partNumber.Id = partStock.ManufacturerPartNumberId
    LEFT JOIN manufacturerPart_item ON manufacturerPart_item.Id = manufacturerPart_partNumber.ItemId
    LEFT JOIN manufacturerPart_series ON manufacturerPart_series.Id = manufacturerPart_item.SeriesId    
    LEFT JOIN vendor On vendor.Id <=> manufacturerPart_series.VendorId OR vendor.Id <=> manufacturerPart_item.VendorId OR vendor.Id <=> manufacturerPart_partNumber.VendorId
    LEFT JOIN (
        SELECT 
            GROUP_CONCAT(CONCAT(numbering.Prefix,'-',productionPart.Number)) AS ProductionPartNumber, 
            productionPart_manufacturerPart_mapping.ManufacturerPartNumberId,
            productionPart_specificationPart_mapping.SpecificationPartRevisionId
        FROM productionPart
        LEFT JOIN productionPart_manufacturerPart_mapping ON productionPart_manufacturerPart_mapping.ProductionPartId = productionPart.Id
        LEFT JOIN productionPart_specificationPart_mapping ON productionPart_specificationPart_mapping.ProductionPartId = productionPart.Id
        LEFT JOIN numbering ON numbering.Id = productionPart.NumberingPrefixId
        GROUP BY ManufacturerPartNumberId, SpecificationPartRevisionId
    )prodPart On prodPart.ManufacturerPartNumberId = partStock.ManufacturerPartNumberId OR prodPart.SpecificationPartRevisionId = partStock.SpecificationPartRevisionId
    WHERE partStock_history.workOrderId = $workOrderId
    STR;

	$result = $database->query($query);
	foreach($result as $item)
	{
        if($item->ProductionPartNumber != null) $item->ProductionPartNumber = explode(",", $item->ProductionPartNumber);
	}

    $output['PartsUsed'] = $result;

    $api->returnData($output);
}
else if($api->isPost())
{
    $data = $api->getPostData();

    if(!isset($data->ProjectId)) $api->returnParameterMissingError("ProjectId");
    if(!isset($data->Quantity)) $api->returnParameterMissingError("Quantity");
    if(!isset($data->Title)) $api->returnParameterMissingError("Title");

    $projectId = intval($data->ProjectId);
    $quantity = intval($data->Quantity);
    $title = $database->escape($data->Title);

    $query = "INSERT INTO workOrder (Title, Quantity, ProjectId, WorkOrderNumber) VALUES ( $title, $quantity, $projectId, workOrder_generateWorkOrderNumber());";

    $insertData = [];
    $insertData['Title'] = $data->Title;
    $insertData['Quantity'] = intval($data->Quantity);
    $insertData['ProjectId'] = intval($data->ProjectId);
    $insertData['WorkOrderNumber']['raw'] = "(SELECT workOrder_generateWorkOrderNumber())";

    $workOrderId = $database->insert("workOrder", $insertData);

    $query = "SELECT Id, WorkOrderNumber FROM workOrder WHERE Id = $workOrderId;";
    $result = $database->query($query);

    $workOrder = [];
    $workOrder['WorkOrderId'] = $result[0]->Id;
    $workOrder['WorkOrderNumber'] = $result[0]->WorkOrderNumber;

    $api->returnData($workOrder);
}
else if($api->isPatch())
{
    $data = $api->getPostData();
    if(!isset($data->WorkOrderNumber)) $api->returnParameterMissingError("WorkOrderNumber");
    if(!isset($data->Status)) $api->returnParameterMissingError("Status");

    $workOrderNumber = barcodeParser_WorkOrderNumber($data->WorkOrderNumber);
    if($workOrderNumber === null)$api->returnParameterError("WorkOrderNumber");

    $woData = array();
    $woData['Status'] = $data->Status;

    $database->update("workOrder", $woData);

    $api->returnEmpty();
}
