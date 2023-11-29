<?php
//*************************************************************************************************
// FileName : category.php
// FilePath : apiFunctions/inventory/
// Author   : Christian Marty
// Date		: 21.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/../databaseConnector.php";

if($api->isGet())
{
    $query = <<< QUERY
        SELECT 
            Id,
            ParentId,
            Name
        FROM inventory_category
        ORDER BY Name ASC
    QUERY;

    $locations = $database->query($query);

    $classId = 0;
    $locationsTree = array();
    $locationsTree = buildTree($locations, $classId);
    $api->returnData($locationsTree);
}

function hasChild(array $rows, int $id):bool
{
	foreach ($rows as $row) 
	{
		if ($row->ParentId == $id)return true;
	}
	return false;
}

function buildTree(array $rows, int $parentId): array
{  
	$treeItem = array();
	foreach ($rows as $row)
	{
		if ($row->ParentId == $parentId)
		{
			$temp = array();
			
			$temp['Name'] = $row->Name;
			$temp['Id'] = $row->Id;
		
			if (hasChild($rows,$row->Id))
			{
				$temp['Children'] =  buildTree($rows,$row->Id);
			}
			$treeItem[] = $temp;
		}
	}
	
	return $treeItem;
}
