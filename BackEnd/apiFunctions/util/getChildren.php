<?php
//*************************************************************************************************
// FileName : getChildren.php
// FilePath : apiFunctions/utils/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);

function getChildren(string $tableName, int $parentId): string
{
	global $database;
	
	$query = "SELECT Id, ParentId FROM ".$tableName." ORDER BY `Name` ASC";
    $data = $database->query($query);

	return $parentId.getChild($data, $parentId); 
}

function hasChild(array $rows, int $id): bool
{
	foreach ($rows as $row) {
		if ($row->ParentId == $id) return true;
	}
	return false;
}

function getChild(array $rows, int $parentId): string
{  
	$childrenId = "";
	foreach ($rows as $row) {
		if ($row->ParentId == $parentId) {
			$childrenId .= ",".$row->Id;
			if (hasChild($rows,$row->Id))
			{
				$childrenId .= getChild($rows,$row->Id);
			}
		}
	}
	return $childrenId;
}

?>