<?php
//*************************************************************************************************
// FileName : workOrder.php
// FilePath : apiFunctions/
// Author   : Christian Marty
// Date		: 21.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/util/_barcodeFormatter.php";

if($api->isGet())
{
	$parameter = $api->getGetData();

	$queryParam = array();
	
	if(isset($parameter->Status))
	{
		$status = $database->escape($parameter->Status);
		$queryParam[] = "Status = $status";
	}
	else if(isset($parameter->HideClosed) && $parameter->HideClosed === true)
	{
		$queryParam[] = "Status != 'Complete'";
	}

	$baseQuery = <<<STR
		SELECT 
		    workOrder.Id, 
		    project.Title AS ProjectTitle, 
		    workOrder.Title, 
		    Quantity, 
		    WorkOrderNumber,
		    Status  
		FROM workOrder
		LEFT JOIN project On project.Id = workOrder.ProjectId
	STR;

	$result = $database->query($baseQuery,$queryParam);

	foreach($result as $item)
	{
		$item->WorkOrderId = $item->Id;
		unset($item->Id);
		$item->WorkOrderBarcode = barcodeFormatter_WorkOrderNumber($item->WorkOrderNumber);
	}
	$api->returnData($result);
}
