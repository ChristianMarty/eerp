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

	array_push($queryParam, "IsSupplier = b'1'");
	
	$query = dbBuildQuery($dbLink, $query, $queryParam);
	
	$query .= " ORDER BY `Name` ASC ";
	
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
else if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$data = json_decode(file_get_contents('php://input'),true);
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$supplierName = dbEscapeString($dbLink,$data['SupplierName']);
	$inserData['IsSupplier']['raw']  = "b'1'";
	$inserData['Name']  = $supplierName;
	
	$query = dbBuildInsertQuery($dbLink, "vendor", $inserData);
	
	$result = dbRunQuery($dbLink,$query);
	
	$error = null;
	$manufacturerPart = array();
	if($result == false)
	{
		$error = "Error description: " . dbGetErrorString($dbLink);
	}
	
	$query = "SELECT Id FROM manufacturerPart WHERE Id = LAST_INSERT_ID();";
	$result = dbRunQuery($dbLink,$query);
	
	$manufacturerPart['SupplierId'] = dbGetResult($result)['Id'];
	
	$result = dbRunQuery($dbLink,$query);
	$stockPart = dbGetResult($result);
	
	dbClose($dbLink);	
	sendResponse($manufacturerPart, $error);
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