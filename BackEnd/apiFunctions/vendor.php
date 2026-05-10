<?php
//*************************************************************************************************
// FileName : vendor.php
// FilePath : apiFunctions/
// Author   : Christian Marty
// Date		: 28.10.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

if($api->isGet(Permission::Vendor_List))
{
    $parameter = $api->getGetData();

	$query = <<< QUERY
        SELECT
            vendor.Id,
            CONCAT("Ven-",VendorNumber) AS ItemCode,
            VendorNumber,
            FullName,
            ShortName,
            AbbreviatedName,
            vendor_displayName(vendor.Id) AS DisplayName,
            IsSupplier,
            IsManufacturer,
            IsContractor,
            IsCarrier,
            IsCustomer,
            ParentId,
            GROUP_CONCAT(vendor_alias.Name) AS AliasName
        FROM vendor
        LEFT JOIN vendor_alias on vendor.Id = vendor_alias.VendorId
    QUERY;

    $queryParam = [];
    if($parameter->Supplier??false === true) $queryParam[] = "IsSupplier = b'1'";
    if($parameter->Manufacturer??false === true) $queryParam[] = "IsManufacturer = b'1'";
    if($parameter->Contractor??false === true) $queryParam[] = "IsContractor = b'1'";
    if($parameter->Carrier??false === true) $queryParam[] = "IsCarrier = b'1'";
    if($parameter->Customer??false === true) $queryParam[] = "IsCustomer = b'1'";

    $result = $database->query($query,$queryParam, " GROUP BY vendor.Id ORDER BY `FullName` ASC ");
    \Error\checkErrorAndExit($result);

    foreach ($result as &$line) {
        Database::makeBool($line->IsSupplier);
        Database::makeBool($line->IsManufacturer);
        Database::makeBool($line->IsContractor);
        Database::makeBool($line->IsCarrier);
        Database::makeBool($line->IsCustomer);
        $line->AliasName = str_replace(",", "; ", $line->AliasName??"");
	}

    $output = [];
    if($parameter->IncludeChildren??false === true){
        $classId = 0;
        $output = buildTree($result,$classId);
    }else{
        $output = $result;
    }

    $api->returnData($output);
}

function hasChild(array $rows, int $id): bool
{
	foreach ($rows as $row) {
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
