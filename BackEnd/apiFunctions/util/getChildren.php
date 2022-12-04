<?php
//*************************************************************************************************
// FileName : getChildren.php
// FilePath : apiFunctions/utils/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

include_once __DIR__ . "/../databaseConnector.php";

function getChildren($tableName, $parentId)
{
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$query = "SELECT Id, ParentId FROM ".$tableName." ORDER BY `Name` ASC";
	$result = dbRunQuery($dbLink,$query);
	dbClose($dbLink);
	
	$data = array();
	while($r = mysqli_fetch_assoc($result)) 
	{
		$data[] = $r;
	}	
	
	return $parentId.getChild($data, $parentId); 
}

function hasChild($rows,$id): bool
{
	foreach ($rows as $row) 
	{
		if ($row['ParentId'] == $id)return true;
	}
	return false;
}

function getChild($rows, $parentId): string
{  
	$childrenId = "";
	foreach ($rows as $row)
	{
		if ($row['ParentId'] == $parentId)
		{
			$childrenId .= ",".$row['Id'];
			
			if (hasChild($rows,$row['Id']))
			{
				$childrenId .= getChild($rows,$row['Id']);
			}
		}
	}
	
	return $childrenId;
}


?>