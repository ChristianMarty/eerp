<?php
//*************************************************************************************************
// FileName : class.php
// FilePath : apiFunctions/manufacturerPart/
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

	$query = "SELECT * FROM manufacturerPart_class WHERE Hidden = b'0' ORDER BY `Name` ASC";

    $classId = 0;
    if(isset($parameter->ClassId)) $classId = intval($parameter->ClassId);

    $classes = $database->query($query);

	$class = [];
    $output = [];
    foreach ($classes as $r)
    {
        if(intval($r->Id) == intval($classId))
        {
            $output['Id'] = intval($r->Id);
            $output['ParentId'] = intval($r->ParentId);
            $output['Name'] = $r->Name;
            //$output['PicturePath'] = $r['PicturePath'];
        }
        $class[] = $r;
	}

    $output['Children'] = buildTree($class,$classId);

    $api->returnData($output);
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
    global $assetsRootPath;

	$treeItem = array();
	foreach ($rows as $row)
	{
		if ($row->ParentId == $parentId)
		{
			$temp = array();

			$temp['Name'] = $row->Name;
			$temp['Id'] = $row->Id;
            $temp['PicturePath'] = $assetsRootPath."/".$row->SymbolPath;
			if($row->NoParts == 0) $temp['NoParts'] = false;
			else $temp['NoParts'] = true;
			$temp['Prefix'] = $row->Prefix;


			if (hasChild($rows,$row->Id))
			{
				$temp['Children'] =  buildTree($rows,$row->Id);
			}
			$treeItem[] = $temp;
		}
	}
	return $treeItem;
}
