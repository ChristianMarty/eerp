<?php
//*************************************************************************************************
// FileName : supplier.php
// FilePath : apiFunctions/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/databaseConnector.php";
require __DIR__ . "/../config.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$query = "SELECT * FROM vendor ";	
	
	$queryParam = array();

	if(isset($_GET["Supplier"]) AND filter_var($_GET["Supplier"], FILTER_VALIDATE_BOOLEAN)) $queryParam[] = "IsSupplier = b'1'";
	if(isset($_GET["Manufacturer"]) AND filter_var($_GET["Manufacturer"], FILTER_VALIDATE_BOOLEAN)) $queryParam[] = "IsManufacturer = b'1'";
    if(isset($_GET["Contractor"]) AND filter_var($_GET["Contractor"], FILTER_VALIDATE_BOOLEAN)) $queryParam[] = "IsContractor = b'1'";

	$query = dbBuildQuery($dbLink, $query, $queryParam);
	
	$query .= " ORDER BY `Name` ASC ";
	
	$classId = 0;
	
	$result = dbRunQuery($dbLink,$query);
	
	$suppliers = array();
	while($r = mysqli_fetch_assoc($result)) 
	{
		$temp = array();
		$temp['Id'] = intval($r['Id']);
		$temp['ParentId'] = intval($r['ParentId']);
		$temp['Name'] = $r['Name'];
		$temp['ShortName'] = $r['ShortName'];
		
		if($r['IsSupplier'] != 0) $temp['IsSupplier'] = true;
		else $temp['IsSupplier'] = false;
		if($r['IsManufacturer'] != 0) $temp['IsManufacturer'] = true;
		else $temp['IsManufacturer'] = false;
        if($r['IsContractor'] != 0) $temp['IsContractor'] = true;
        else $temp['IsContractor'] = false;
		
		$suppliers[] = $temp;
	}
	
	$locationsTree = array();

	$locationsTree = buildTree($suppliers,$classId);
	
	dbClose($dbLink);	
	sendResponse($locationsTree);
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
			$temp = $row;
			#$temp['Name'] = $row['Name'];
			#$temp['Id'] = $row['Id'];
		
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