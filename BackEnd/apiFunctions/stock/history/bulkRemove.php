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

if($api->isPost())
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

	// validate data
	foreach($data->Items as $key => $line)
    {
        $note = null;
        if (isset($line->Note)) {
            $note = trim($line->Note);
            if ($note == "") $note = null;
            $note = $database->escape($note);
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
    }
    $api->returnEmpty();
}
