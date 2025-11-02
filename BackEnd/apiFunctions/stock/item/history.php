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

if($api->isGet(\Permission::Stock_View))
{
	$parameter = $api->getGetData();
    if(!isset($parameter->StockCode)) $api->returnData(\Error\parameterMissing("StockCode"));
    $stockNumber = \Numbering\parser(\Numbering\Category::Stock, $parameter->StockCode);
    if($stockNumber === null) $api->returnData(\Error\parameter("StockCode"));

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
		    Cache_ChangeIndex AS ChangeIndex
		FROM partStock_history 
		LEFT JOIN workOrder ON workOrder.Id = partStock_history.WorkOrderId 
		LEFT JOIN user ON user.Id = partStock_history.CreationUserId
		WHERE StockId = (SELECT Id FROM partStock WHERE StockNumber = '$stockNumber') 
		ORDER BY partStock_history.CreationDate ASC
	STR;
	$result = $database->query($query);
    \Error\checkErrorAndExit($result);

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

        $r['ItemCode'] = \Numbering\format(\Numbering\Category::Stock, $stockNumber, $item->ChangeIndex);
		$r['Type'] = $type;
        $r['Date'] = $item->Date;
        $r['Note'] = $item->Note;
		$r['Description'] = trim($description);
		$r['WorkOrderCode'] = \Numbering\format(\Numbering\Category::WorkOrder, $item->WorkOrderNumber);
        $r['NameInitials'] = $item->Initials;
        $r['EditToken'] = $item->EditToken;

		$output[] = $r;
	}

	$api->returnData($output);
}
