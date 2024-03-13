<?php
//*************************************************************************************************
// FileName : history.php
// FilePath : apiFunctions/stock/
// Author   : Christian Marty
// Date		: 21.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/../../util/_barcodeParser.php";
require_once __DIR__ . "/../../util/_barcodeFormatter.php";

if($api->isGet())
{
	$parameter = $api->getGetData();

	if(!isset($parameter->StockCode)) $api->returnParameterMissingError("StockCode");
	$stockNumber = barcodeParser_StockNumber($parameter->StockCode);
	if($stockNumber === null) $api->returnParameterError("StockCode");

	$query = <<<STR
		SELECT 
			partStock_history.ChangeType, 
			partStock_history.Quantity, 
			partStock_history.CreationDate AS  Date, 
			workOrder.Name AS WorkOrderTitle, 
			workOrder.WorkOrderNumber, 
			partStock_history.Note, 
			partStock_history.EditToken,
		    user.Initials,
		    ROW_NUMBER() OVER (ORDER BY partStock_history.CreationDate) AS ChangeIndex
		FROM partStock_history 
		LEFT JOIN workOrder ON workOrder.Id = partStock_history.WorkOrderId 
		LEFT JOIN user ON user.Id = partStock_history.CreationUserId
		WHERE StockId = (SELECT Id FROM partStock WHERE StockNumber = '$stockNumber') 
		ORDER BY partStock_history.Id ASC
	STR;

	$result = $database->query($query);

	$output = array();
	$quantity = 0;

	foreach ($result as $item)
	{
		$description = "";
		$type = null;
		
		if($item->ChangeType == 'Relative')
		{
			if($item->Quantity >0 )
			{
				$description = "Add ".$item->Quantity."pcs";
				$type = "add";
				$quantity += intval($item->Quantity,10);
			}
			else 
			{
				$description = "Remove ".abs($item->Quantity)."pcs";
				$type = "remove";	
				$quantity += intval($item->Quantity,10);
			}	
		}
		else if($item->ChangeType == 'Absolute')
		{
			$description = "Stocktaking"; 
			$type = "count";	
			$quantity = intval($item->Quantity,10);
		}
		else if($item->ChangeType == 'Create')
		{
			$description = "Create"; 
			$type = "create";	
			$quantity = intval($item->Quantity,10);
		}
		
		$description .= ", New Quantity: ".$quantity;

        $r['ItemCode'] = barcodeFormatter_StockHistoryNumber($stockNumber, $item->ChangeIndex);
		$r['Type'] = $type;
        $r['Date'] = $item->Date;
        $r['Note'] = $item->Note;
		$r['Description'] = trim($description);
		$r['WorkOrderCode'] = barcodeFormatter_WorkOrderNumber($item->WorkOrderNumber);
        $r['NameInitials'] = $item->Initials;

		$output[] = $r;
	}

	$api->returnData($output);
}
