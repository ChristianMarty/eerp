<?php
//*************************************************************************************************
// FileName : category.php
// FilePath : apiFunctions/inventory/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";


if($_SERVER['REQUEST_METHOD'] == 'GET')
{

	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$query = "SELECT * FROM inventory_categorie ORDER BY `Name` ASC";	
	
	$classId = 0;
	
	$result = dbRunQuery($dbLink,$query);
	
	$locations = array();
	while($r = mysqli_fetch_assoc($result)) 
	{
		$locations[] = $r;
	}
	
	$locationsTree = array();

	$locationsTree = buildTree($locations,$classId);
	
	dbClose($dbLink);	
	sendResponse($locationsTree);
}

function hasChild($rows,$id)
{
	foreach ($rows as $row) 
	{
		if ($row['ParentId'] == $id)return true;
	}
	return false;
}

function buildTree($rows, $parentId)
{  
	$treeItem = array();
	foreach ($rows as $row)
	{
		if ($row['ParentId'] == $parentId)
		{
			$temp = array();
			
			$temp['Name'] = $row['Name'];
			$temp['Id'] = $row['Id'];
		
			if (hasChild($rows,$row['Id']))
			{
				$temp['Children'] = array();
				$temp['Children'] =  buildTree($rows,$row['Id']);
			}
			$treeItem[] = $temp;
		}
	}
	
	return $treeItem;
}
	
?>