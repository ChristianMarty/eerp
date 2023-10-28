<?php
//*************************************************************************************************
// FileName : vendor.php
// FilePath : apiFunctions/
// Author   : Christian Marty
// Date		: 28.10.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
global $database;
global $api;

if($api->isGet())
{
    $parameter = $api->getGetData();

	$query = <<< QUERY
        SELECT
            Id,
            FullName,
            ShortName,
            AbbreviatedName,
            vendor_displayName(Id) as DisplayName,
            IsSupplier,
            IsManufacturer,
            IsContractor,
            ParentId
        FROM vendor
    QUERY;

    $queryParam = [];
    if($parameter->Supplier??false === true) $queryParam[] = "IsSupplier = b'1'";
    if($parameter->Manufacturer??false === true) $queryParam[] = "IsManufacturer = b'1'";
    if($parameter->Contractor??false === true) $queryParam[] = "IsContractor = b'1'";

    $data = $database->query($query,$queryParam, " ORDER BY `FullName` ASC ");

    foreach ($data as &$line)
	{
        Database::toBool($line->IsSupplier);
        Database::toBool($line->IsManufacturer);
        Database::toBool($line->IsContractor);
	}

    $output = [];
    if($parameter->IncludeChildren??false === true){
        $classId = 0;
        $output = buildTree($data,$classId);
    }else{
        $output = $data;
    }

    $api->returnData($output);
}

function hasChild(array $rows, int $id): bool
{
	foreach ($rows as $row) 
	{
		if ($row->ParentId == $id)return true;
	}
	return false;
}

function buildTree(array $rows, int $parentId): array
{  
	$treeItem = [];
	foreach ($rows as $row)
	{
		if ($row->ParentId == $parentId)
		{
			$temp = $row;

			if (hasChild($rows,$row->Id))
			{
				$temp->Children =  buildTree($rows,$row->Id);
			}
			$treeItem[] = $temp;
		}
	}
	
	return $treeItem;
}
	
?>