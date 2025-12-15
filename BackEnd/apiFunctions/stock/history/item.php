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

require_once __DIR__ . "/../_stock.php";

if($api->isGet(\Permission::Stock_History_View)) {

    $parameter = $api->getGetData();
    if (!isset($parameter->StockHistoryCode)) $api->returnData(\Error\parameterMissing("StockHistoryCode"));
    $stockNumber = \Numbering\parser(\Numbering\Category::Stock, $parameter->StockHistoryCode);
    $historyIndex = \Numbering\parser(\Numbering\Category::StockHistoryIndex, $parameter->StockHistoryCode);
    if ($stockNumber === null) $api->returnData(\Error\parameter("StockCode"));
    if ($historyIndex === null) $api->returnData(\Error\parameter("StockCode History Index"));

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
    \Error\checkErrorAndExit($result);
    \Error\checkNoResultAndExit($result, $parameter->StockHistoryCode);

    $output = $result[0];
    $output->ItemCode = \Numbering\format(\Numbering\Category::Stock, $output->StockNumber, $output->ChangeIndex);
    $output->StockCode = \Numbering\format(\Numbering\Category::Stock, $output->StockNumber);
    $output->WorkOrderCode = \Numbering\format(\Numbering\Category::WorkOrder, $output->WorkOrderNumber);

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
else if($api->isPatch(\Permission::Stock_History_Edit))
{
	$data = $api->getPostData();
	if(!isset($data->EditToken)) $api->returnData(\Error\parameterMissing("EditToken"));
	if(!isset($data->Quantity)) $api->returnData(\Error\parameterMissing("Quantity"));
    if(!is_numeric($data->Quantity)) $api->returnData(\Error\parameter("Quantity"));
    $quantity = intval($data->Quantity);

	$workOrderNumber = null;
	if(isset($data->WorkOrderNumber)) {
		$workOrderNumber = \Numbering\parser(\Numbering\Category::WorkOrder, $data->WorkOrderNumber);
	}

    $note = $data->Note??null;
    if($note !== null){
        $note = trim($note);
        if($note == "") $note = null;
    }
	
	$type = strtolower($data->Type);

	if($type == "remove") $quantity = abs($quantity)*-1;
	else if ($type == "add") $quantity = abs($quantity);
	else if ($type == "count") $quantity = abs($quantity);
	
	$sqlData = array();
	$sqlData['Quantity'] = $quantity;
	$sqlData['Note'] = $note;
	if($workOrderNumber != null) $sqlData['WorkOrderId']['raw'] = "(SELECT Id FROM workOrder WHERE WorkOrderNumber = $workOrderNumber)";

    $token = $database->escape(trim($data->EditToken));
	$database->update("partStock_history", $sqlData, "EditToken = $token");

	$api->returnEmpty();
}
else if($api->isPost())
{
	$data = $api->getPostData();
	if(!isset($data->StockNumber)) $api->returnData(\Error\parameterMissing("StockNumber"));
    $stockNumber = \Numbering\parser(\Numbering\Category::Stock, $data->StockNumber);
    if($stockNumber === null) $api->returnData(\Error\parameter("StockNumber"));

    $stockNumberEscaped = $database->escape($stockNumber);
	$query = <<< QUERY
        SELECT 
            Id 
        FROM partStock 
        WHERE StockNumber = $stockNumberEscaped
    QUERY;
    $result = $database->query($query);
    \Error\checkErrorAndExit($result);
    \Error\checkNoResultAndExit($result, $data->StockNumber);

	$stockId = $result[0]->Id;

	$workOrderNumber = null;
	if(isset($data->WorkOrderNumber)){
		$workOrderNumber = \Numbering\parser(\Numbering\Category::WorkOrder, $data->WorkOrderNumber);
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
    $sqlData['CreationUserId'] = $user->userId();
	
	if(isset($data->RemoveQuantity)) {
        $api->checkPermission(\Permission::Stock_History_Remove);

		if(!is_numeric($data->RemoveQuantity)) $api->returnData(\Error\parameter("RemoveQuantity"));
		$removeQuantity = intval($data->RemoveQuantity);

		$sqlData['Quantity'] = abs($removeQuantity)*-1;
		if($workOrderNumber != null) $sqlData['WorkOrderId']['raw'] = "(SELECT Id FROM workOrder WHERE WorkOrderNumber = $workOrderNumber)";
		$sqlData['ChangeType']['raw'] = '"Relative"';

	} else if(isset($data->AddQuantity)) {
        $api->checkPermission(\Permission::Stock_History_Add);

		if(!is_numeric($data->AddQuantity)) $api->returnData(\Error\parameter("AddQuantity"));
		$addQuantity = intval($data->AddQuantity);

		$sqlData['Quantity'] = abs($addQuantity);
		$sqlData['ChangeType']['raw'] = '"Relative"';

	} else if(isset($data->Quantity)) {
        $api->checkPermission(\Permission::Stock_History_Count);

        if(!is_numeric($data->Quantity)) $api->returnData(\Error\parameter("Quantity"));
        $quantity = intval($data->Quantity);

		if($quantity < 0) $api->returnData(\Error\generic("Quantity can not by below 0"));
		
		$sqlData['Quantity'] = abs($quantity);
		$sqlData['ChangeType']['raw'] = '"Absolute"';

        \Stock\Stock::clearCountingRequest(null, $stockNumber);

	} else {
        $api->returnData(\Error\parameter("Parameter combination"));
	}
    $stockHistoryId = $database->insert("partStock_history", $sqlData);
    \Error\checkErrorAndExit($stockHistoryId);

    $query = <<<STR
        SELECT 
            partStock.StockNumber,
            partStock_history.Cache_ChangeIndex AS ChangeIndex
        FROM partStock_history 
        LEFT JOIN partStock ON partStock.Id = partStock_history.StockId
        WHERE partStock_history.Id IN($stockHistoryId)
    STR;
    $result = $database->query($query);
    \Error\checkErrorAndExit($result);
    \Error\checkNoResultAndExit($result, $data->StockNumber);

    $result = $result[0];
    $output = new stdClass();
    $output->ItemCode = \Numbering\format(\Numbering\Category::Stock, $result->StockNumber, $result->ChangeIndex);

    $api->returnData($output);
}
