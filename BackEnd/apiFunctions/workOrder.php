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
		    project.Name AS ProjectName, 
		    project.ProjectNumber AS ProjectNumber,
		    workOrder.Name, 
		    Quantity, 
		    WorkOrderNumber,
		    Status  
		FROM workOrder
		LEFT JOIN project On project.Id = workOrder.ProjectId
	STR;

	$result = $database->query($baseQuery,$queryParam);

    foreach($result as $item) {
        $item->ProjectItemCode = barcodeFormatter_Project($item->ProjectNumber);
        unset($item->ProjectNumber);
        $item->ItemCode = barcodeFormatter_WorkOrderNumber($item->WorkOrderNumber);
    }
    $api->returnData($result);
}
