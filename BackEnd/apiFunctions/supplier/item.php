<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/supplier/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require __DIR__ . "/../../config.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$supplierId = dbEscapeString($dbLink, $_GET["SupplierId"]);
	
	$query = "SELECT * FROM vendor ";
	$query .= "WHERE Id = ".$supplierId;
	
	$classId = 0;
	
	$result = dbRunQuery($dbLink,$query);
	
	$suppliers = array();
	while($r = mysqli_fetch_assoc($result)) 
	{
		$suppliers[] = $r;
	}
	
	$locationsTree = array();

	$locationsTree = buildTree($suppliers,$classId);
	
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
			$temp = $row;
		
			if (hasChild($rows,$row['Id']))
			{
				$temp['Children'] = array();
				$temp['Children'] =  buildTree($rows,$row['Id']);
			}
			array_push($treeItem, $temp);
		}
	}
	
	return $treeItem;
}
	
?>