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
	
	if(isset($_GET["OrderImportSupported"])) array_push($queryParam,"OrderImportSupported = true");
	if(isset($_GET["Supplier"]) AND filter_var($_GET["Supplier"], FILTER_VALIDATE_BOOLEAN)) array_push($queryParam, "IsSupplier = b'1'");
	if(isset($_GET["Manufacturer"]) AND filter_var($_GET["Manufacturer"], FILTER_VALIDATE_BOOLEAN)) array_push($queryParam, "IsManufacturer = b'1'");

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
		if($r['OrderImportSupported'] != 0) $temp['OrderImportSupported'] = true;
		else $temp['OrderImportSupported'] = false;
		
		$suppliers[] = $temp;
	}
	
	$locationsTree = array();

	$locationsTree = buildTree($suppliers,$classId);
	
	dbClose($dbLink);	
	sendResponse($locationsTree);
}
else if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$data = json_decode(file_get_contents('php://input'),true);
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$vendorName = dbEscapeString($dbLink,$data['Name']);
	$inserData['IsSupplier']['raw']  = "b'1'";
	$inserData['Name']  = $vendorName;
	
	$query = dbBuildInsertQuery($dbLink, "vendor", $inserData);
	
	$result = dbRunQuery($dbLink,$query);
	
	$error = null;
	$data = array();
	if($result == false)
	{
		$error = "Error description: " . dbGetErrorString($dbLink);
	}
	
	$query = "SELECT Id FROM vendor WHERE Id = LAST_INSERT_ID();";
	$result = dbRunQuery($dbLink,$query);
	
	$result = dbGetResult($result);
	
	if(isset($result['Id']))
	{
		$data['VendorId'] = $result['Id'];
	}
	else
	{
		$error = "Vendor creation failed! Maybe it already exists? ";
	}
	
	dbClose($dbLink);	
	sendResponse($data, $error);
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
			#$temp['Name'] = $row['Name'];
			#$temp['Id'] = $row['Id'];
		
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