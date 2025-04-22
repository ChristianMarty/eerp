<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/stock/history/
// Author   : Christian Marty
// Date		: 21.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;
global $user;

require_once __DIR__ . "/../../util/_barcodeParser.php";
require_once __DIR__ . "/../../util/_barcodeFormatter.php";

if($api->isGet()) {
    $parameter = $api->getGetData();

    if (!isset($parameter->StockHistoryCode)) $api->returnParameterMissingError("StockHistoryCode");
    $stockNumber = barcodeParser_StockNumber($parameter->StockHistoryCode);
    $historyIndex = barcodeParser_StockHistoryNumber($parameter->StockHistoryCode);
    if ($stockNumber === null) $api->returnParameterError("StockCode");
    if ($historyIndex === null) $api->returnParameterError("StockCode History Index");

    $query = <<<STR
    SELECT
        partStock.StockNumber,
        Cache_ChangeIndex AS ChangeIndex,
        partStock_history.ChangeType, 
        partStock_history.Quantity, 
        partStock_history.CreationDate AS  Date, 
        workOrder.Name AS WorkOrderTitle, 
        workOrder.WorkOrderNumber, 
        partStock_history.Note, 
        partStock_history.EditToken,
        user.Initials,
        vendor_displayName(vendor.Id) AS ManufacturerName,
        vendor.Id AS ManufacturerId,
        manufacturerPart_partNumber.Number AS ManufacturerPartNumber,
        manufacturerPart_partNumber.Id AS ManufacturerPartNumberId,
        manufacturerPart_item.Id AS ManufacturerPartItemId,
        partStock.SpecificationPartRevisionId AS SpecificationPartRevisionId
    FROM partStock_history 
    LEFT JOIN partStock ON partStock_history.StockId = partStock.Id
    LEFT JOIN manufacturerPart_partNumber ON manufacturerPart_partNumber.Id = partStock.ManufacturerPartNumberId
    LEFT JOIN manufacturerPart_item On manufacturerPart_item.Id = manufacturerPart_partNumber.ItemId
    LEFT JOIN manufacturerPart_series On manufacturerPart_series.Id = manufacturerPart_item.SeriesId
    LEFT JOIN vendor ON vendor.Id <=> manufacturerPart_partNumber.VendorId OR  vendor.Id <=> manufacturerPart_item.VendorId OR vendor.Id <=> manufacturerPart_series.VendorId
    LEFT JOIN workOrder ON workOrder.Id = partStock_history.WorkOrderId 
    LEFT JOIN user ON user.Id = partStock_history.CreationUserId
    WHERE StockId = (SELECT Id FROM partStock WHERE StockNumber = '$stockNumber')  
        AND partStock_history.Cache_ChangeIndex = '$historyIndex'
    STR;
    $result = $database->query($query);

    if(count($result) == 0) {
        $api->returnError("Stock history code not found");
    }
    $output = $result[0];

    $output->ItemCode = barcodeFormatter_StockHistoryNumber($output->StockNumber, $output->ChangeIndex);
    $output->StockCode = barcodeFormatter_StockNumber($output->StockNumber);
    $output->WorkOrderCode = barcodeFormatter_WorkOrderNumber($output->WorkOrderNumber);

    if($output->ChangeType === "Relative"){
        if($output->Quantity < 0){
            $output->Type = "Remove";
            $output->Quantity = floatval($output->Quantity);
        }else{
            $output->Type = "Add";
            $output->Quantity = floatval($output->Quantity)*-1;
        }
    }else if($output->ChangeType === "Absolute"){
        $output->Type = "Count";
        $output->Quantity = floatval($output->Quantity);

    }else if($output->ChangeType === "Create"){
        $output->Type = "Count";
        $output->Quantity = floatval($output->Quantity);
    }
    unset($output->ChangeType);

    // Add part information
    $part = new stdClass();
    $part->ManufacturerName = $output->ManufacturerName;
    unset($output->ManufacturerName);
    $part->ManufacturerId = intval($output->ManufacturerId);
    unset($output->ManufacturerId);
    $part->ManufacturerPartNumber = $output->ManufacturerPartNumber;
    unset($output->ManufacturerPartNumber);
    if($output->ManufacturerPartNumberId !== null) $part->ManufacturerPartNumberId = intval($output->ManufacturerPartNumberId);
    else $part->ManufacturerPartNumberId = null;
    unset($output->ManufacturerPartNumberId);
    if($output->ManufacturerPartItemId !== null) $part->ManufacturerPartItemId = intval($output->ManufacturerPartItemId);
    else $part->ManufacturerPartItemId = null;
    unset($output->ManufacturerPartItemId);
    if($output->SpecificationPartRevisionId !== null) $part->SpecificationPartRevisionId = intval($output->SpecificationPartRevisionId);
    else $part->SpecificationPartRevisionId = null;
    unset($output->SpecificationPartRevisionId);
    $output->Part = $part;

    $api->returnData($output);
}
else if($api->isPatch())
{
	$data = $api->getPostData();
	if(!isset($data->EditToken)) $api->returnParameterMissingError("EditToken");
	if(!isset($data->Quantity)) $api->returnParameterMissingError("Quantity");

	$token = $database->escape(trim($data->EditToken));
	
	$workOrderNumber = null;
	if(isset($data->WorkOrderNumber))
	{
		$workOrderNumber = barcodeParser_WorkOrderNumber($data->WorkOrderNumber);
	}

    $note = $data->Note??null;
    if($note !== null){
        $note = trim($note);
        if($note == "") $note = null;
    }
	
	$type = strtolower($data->Type);

	if(!is_numeric($data->Quantity)) $api->returnParameterError("Quantity");
	$quantity = intval($data->Quantity);
	
	if($type == "remove") $quantity = abs($quantity)*-1;
	else if ($type == "add") $quantity = abs($quantity);
	else if ($type == "count") $quantity = abs($quantity);
	
	$sqlData = array();
	$sqlData['Quantity'] = $quantity;
	$sqlData['Note'] = $note;
	if($workOrderNumber != null) $sqlData['WorkOrderId']['raw'] = "(SELECT Id FROM workOrder WHERE WorkOrderNumber = $workOrderNumber)";

	$database->update("partStock_history", $sqlData, "EditToken = $token");

	$api->returnEmpty();
}
else if($api->isPost())
{
	$data = $api->getPostData();
	if(!isset($data->StockNumber)) $api->returnParameterMissingError("StockNumber");
	$stockNo = barcodeParser_StockNumber($data->StockNumber);
    if($stockNo === null) $api->returnParameterError("StockNumber");

	$query = "SELECT Id FROM partStock WHERE StockNumber = '$stockNo'";
    $r = $database->query($query);
    if(count($r) === 0) {
        $api->returnError("StockNumber not found");
    }
	$stockId = $r[0]->Id;

	$workOrderNumber = null;
	if(isset($data->WorkOrderNumber)){
		$workOrderNumber = barcodeParser_WorkOrderNumber($data->WorkOrderNumber);
	}

    $note = $data->Note??null;
    if($note !== null){
        $note = trim($note);
        if($note == "") $note = null;
    }

    $output = array();

	$sqlData = array();
	$sqlData['Note'] = $note;
	$sqlData['EditToken']['raw'] = "history_generateEditToken()";
	$sqlData['StockId'] = $stockId;
	
	if(isset($data->RemoveQuantity))
	{
		if(!is_numeric($data->RemoveQuantity)) $api->returnParameterError("RemoveQuantity");
		$removeQuantity = intval($data->RemoveQuantity);

		$sqlData['Quantity'] = abs($removeQuantity)*-1;
		if($workOrderNumber != null) $sqlData['WorkOrderId']['raw'] = "(SELECT Id FROM workOrder WHERE WorkOrderNumber = $workOrderNumber)";
		$sqlData['ChangeType']['raw'] = '"Relative"';
	}
	else if(isset($data->AddQuantity))
	{
		if(!is_numeric($data->AddQuantity)) $api->returnParameterError("AddQuantity");
		$addQuantity = intval($data->AddQuantity);

		$sqlData['Quantity'] = abs($addQuantity);
		$sqlData['ChangeType']['raw'] = '"Relative"';
	}
	else if(isset($data->Quantity))
	{
        if(!is_numeric($data->Quantity)) $api->returnParameterError("Quantity");
        $quantity = intval($data->Quantity);

		if($quantity <0) $api->returnError("Quantity can not by below 0");
		
		$sqlData['Quantity'] = abs($quantity);
		$sqlData['ChangeType']['raw'] = '"Absolute"';
	}
	else
	{
        $api->returnParameterError("Parameter combination");
	}

	$sqlData['CreationUserId'] = $user->userId();

    $stockHistoryId = $database->insert("partStock_history", $sqlData);


    $query = <<<STR
        SELECT 
            partStock.StockNumber,
            partStock_history.Cache_ChangeIndex AS ChangeIndex
        FROM partStock_history 
        LEFT JOIN partStock ON partStock.Id = partStock_history.StockId
        WHERE partStock_history.Id IN($stockHistoryId)
    STR;

    $result = $database->query($query)[0];

    $output = new stdClass();
    $output->ItemCode = barcodeFormatter_StockHistoryNumber($result->StockNumber, $result->ChangeIndex);

    $api->returnData($output);
}
