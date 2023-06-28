<?php
//*************************************************************************************************
// FileName : class.php
// FilePath : apiFunctions/part/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";


if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$dbLink = dbConnect();
	
	$query = "SELECT * FROM manufacturerPart_class ORDER BY `Name` ASC";
	
	$classId = 0;
	
	if(isset($_GET["classId"]))
	{
		$classId = dbEscapeString($dbLink, $_GET["classId"]);
	}
	
	$result = dbRunQuery($dbLink,$query);
	
	$locations = array();
	while($r = mysqli_fetch_assoc($result)) 
	{
		$locations[] = $r;
	}
	
	$classTree = array();

	$classTree = buildTree($locations,$classId);
	
	dbClose($dbLink);	
	sendResponse($classTree);
}

function hasChild($rows,$id): bool
{
	foreach ($rows as $row) 
	{
		if ($row['ParentId'] == $id)return true;
	}
	return false;
}

function buildTree($rows, $parentId): array
{  
	$treeItem = array();
	foreach ($rows as $row)
	{
		if ($row['ParentId'] == $parentId)
		{
			$temp = array();
			
			$temp['Name'] = $row['Name'];
			$temp['Id'] = $row['Id'];
			if($row['NoParts'] == 0) $temp['NoParts'] = false;
			else $temp['NoParts'] = true;
			$temp['Prefix'] = $row['Prefix'];

		
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