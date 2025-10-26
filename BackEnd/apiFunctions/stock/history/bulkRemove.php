<?php
//*************************************************************************************************
// FileName : bulkRemove.php
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

if($api->isPost(\Permission::Stock_History_Remove))
{
	$data = $api->getPostData();

	$workOrderNumber = null;
	if(isset($data->WorkOrderNumber)) $workOrderNumber= barcodeParser_WorkOrderNumber($data->WorkOrderNumber);
	
	$workOrder = null;
	if($workOrderNumber !== null)
	{
		$query = <<<STR
			SELECT * FROM workOrder WHERE WorkOrderNumber = '$workOrderNumber';
		STR;
		$workOrder = $database->query($query)[0];
	}

	$historyIdList = [];
	foreach($data->Items as $key => $line)
    {
        $note = null;
        if (isset($line->Note)) {
            $note = trim($line->Note);
            if ($note == "") $note = null;
        }

        $stockNo = barcodeParser_StockNumber($line->ItemCode);

        $query = <<<STR
			SELECT Id FROM partStock WHERE StockNumber = '$stockNo';
		STR;
        $stockId = $database->query($query)[0]->Id;
        $removeQuantity = intval($line->RemoveQuantity);
        $sqlData = array();
        $sqlData['Note'] = $note;
        $sqlData['EditToken']['raw'] = "history_generateEditToken()";
        $sqlData['StockId'] = $stockId;
        $sqlData['Quantity'] = abs($removeQuantity) * -1;
        $sqlData['ChangeType']['raw'] = '"Relative"';
        $sqlData['CreationUserId'] = $user->userId();;
        if ($workOrder !== null) $sqlData['WorkOrderId'] = $workOrder->Id;

        $database->insert("partStock_history", $sqlData);
        $historyIdList[] = $database->lastInsertId();
    }

    $historyIdStr = implode(',',$historyIdList);
    $query = <<<STR
        SELECT 
            partStock.StockNumber,
            partStock_history.ChangeType, 
            partStock_history.Quantity, 
            partStock_history.CreationDate AS  Date, 
            workOrder.Name AS WorkOrderTitle, 
            workOrder.WorkOrderNumber, 
            partStock_history.Note, 
            partStock_history.EditToken,
            user.Initials,
            partStock_history.Cache_ChangeIndex AS ChangeIndex
        FROM partStock_history 
        LEFT JOIN partStock ON partStock.Id = partStock_history.StockId
        LEFT JOIN workOrder ON workOrder.Id = partStock_history.WorkOrderId 
        LEFT JOIN user ON user.Id = partStock_history.CreationUserId
        WHERE partStock_history.Id IN($historyIdStr)
        ORDER BY partStock_history.Id ASC
    STR;

    $result = $database->query($query);

    $output = [];
    foreach($result as $line)
    {
        $item = new stdClass();
        $item->ItemCode = barcodeFormatter_StockHistoryNumber($line->StockNumber, $line->ChangeIndex);
        $item->ManufacturerName = "";
        $item->ManufacturerPartNumber = "";
        $item->Note = $line->Note??"";
        $item->Quantity = $line->Quantity;

        $output[] = $item;
    }

    $api->returnData($output);
}
