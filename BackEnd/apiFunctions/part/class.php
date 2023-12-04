<?php
//*************************************************************************************************
// FileName : class.php
// FilePath : apiFunctions/part/
// Author   : Christian Marty
// Date		: 03.12.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

if($api->isGet())
{
    $parameter = $api->getGetData();

	$classId = 0;
	if(isset($parameter->classId)) $classId = intval($parameter->classId);

    $query = "SELECT * FROM manufacturerPart_class ORDER BY `Name` ASC";
    $classes = $database->query($query);

	$classTree = buildTree($classes,$classId);

    $api->returnData($classTree);
}

function hasChild(array $rows,int $id): bool
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
			if($row->NoParts == 0) $temp['NoParts'] = false;
			else $temp['NoParts'] = true;
			$temp['Prefix'] = $row->Prefix;

			if (hasChild($rows,$row->Id))
			{
				$temp['Children'] = array();
				$temp['Children'] =  buildTree($rows,$row->Id);
			}
			$treeItem[] = $temp;
		}
	}
	
	return $treeItem;
}
