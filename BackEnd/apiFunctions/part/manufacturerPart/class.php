<?php
//*************************************************************************************************
// FileName : class.php
// FilePath : apiFunctions/manufacturerPart/
// Author   : Christian Marty
// Date		: 15.05.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../databaseConnector.php";
require_once __DIR__ . "/../../../config.php";


if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$dbLink = dbConnect();

	$query = "SELECT * FROM manufacturerPart_class WHERE Hidden = b'0' ORDER BY `Name` ASC";
	
	$classId = 0;
	
	if(isset($_GET["ClassId"]))
	{
		$classId = dbEscapeString($dbLink, $_GET["ClassId"]);
	}
	
	$result = dbRunQuery($dbLink,$query);
	
	$class = array();
    $output = array();
	while($r = mysqli_fetch_assoc($result)) 
	{
        if(intval($r['Id']) == intval($classId))
        {
            $output['Id'] = intval($r['Id']);
            $output['ParentId'] = intval($r['ParentId']);
            $output['Name'] = $r['Name'];
            //$output['PicturePath'] = $r['PicturePath'];
        }
        $class[] = $r;
	}

    $output['Children'] = buildTree($class,$classId);
	
	dbClose($dbLink);	
	sendResponse($output);
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
    global $assetsRootPath;

	$treeItem = array();
	foreach ($rows as $row)
	{
		if ($row['ParentId'] == $parentId)
		{
			$temp = array();
			
			$temp['Name'] = $row['Name'];
			$temp['Id'] = $row['Id'];
            $temp['PicturePath'] = $assetsRootPath."/".$row['SymbolPath'];
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