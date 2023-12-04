<?php
//*************************************************************************************************
// FileName : package.php
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

	$query = "SELECT * FROM manufacturerPart_partPackage ORDER BY `Name` ASC";	

	$packages = $database->query($query);

    $api->returnData(buildTree($packages,0));
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
			
			if($row->SMD == 1) $temp['SMD'] = true;
			elseif ($row->SMD == null) $temp['SMD'] = null;
			else $temp['SMD'] = false;
			
			
			$temp['PinCount'] = $row->PinCount;
		
			if (hasChild($rows,$row->Id))
            {
				$temp['Children'] =  buildTree($rows,$row->Id);
			}
			$treeItem[] = $temp;
		}
	}
	
	return $treeItem;
}
