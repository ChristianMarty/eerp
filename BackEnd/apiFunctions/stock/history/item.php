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

if($api->isPatch())
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
	
	$note = null;
	if(isset($data->Note))
	{
		$note = trim($data->Note);
		if($note == "") $note = null;
		else $note = $database->escape($note);
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
	if(!isset($data->StockNo)) $api->returnParameterMissingError("StockNo");
	$stockNo = barcodeParser_StockNumber($data->StockNo);
    if($stockNo === null) $api->returnParameterError("StockNo");

	$query = "SELECT Id FROM partStock WHERE StockNumber = '$stockNo'";
	$stockId = $database->query($query)[0]->Id;

	$workOrderNumber = null;
	if(isset($data->WorkOrderNumber))
	{
		$workOrderNumber = barcodeParser_WorkOrderNumber($data->WorkOrderNumber);
	}

    $note = $data->Note??null;
    if($note !== null){
        $note = trim($note);
        if($note == "") $note = null;
        else $note = $database->escape($note);
    }

    $output = array();

	$sqlData = array();
	$sqlData['Note'] = $note;
	$sqlData['EditToken']['raw'] = "history_generateEditToken()";
	$sqlData['StockId']['raw'] = $stockId;
	
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
        if(!is_numeric($data->Quantity)) $api->returnParameterError("RemoveQuantity");
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

    $database->insert("partStock_history", $sqlData);

    $api->returnEmpty();
}
