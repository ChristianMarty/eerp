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

if($api->isGet(Permission::WorkOrder_List))
{
	$parameter = $api->getGetData();

	$queryParam = [];
	if(isset($parameter->Status))
	{
		$status = $database->escape($parameter->Status);
		$queryParam[] = "Status = $status";
	}
	else if(isset($parameter->HideClosed) && $parameter->HideClosed === true)
	{
		$queryParam[] = "Status != 'Complete'";
	}

    $query = <<<STR
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
	$result = $database->query($query, $queryParam);
    \Error\checkErrorAndExit($result);

    foreach($result as $item) {
        $item->ProjectItemCode = \Numbering\format(\Numbering\Category::Project, $item->ProjectNumber);
        unset($item->ProjectNumber);
        $item->ItemCode = \Numbering\format(\Numbering\Category::WorkOrder, $item->WorkOrderNumber);
    }
    $api->returnData($result);
}
