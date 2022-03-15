<?php
//*************************************************************************************************
// FileName : part.php
// FilePath : apiFunctions/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/databaseConnector.php";
require_once __DIR__ . "/../config.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$dbLink = dbConnect();
	if($dbLink == null) return null;

	// Query attributes

	$attributes  = array();
	
	$query = "SELECT partAttribute.Id, partAttribute.ParentId, partAttribute.Name, partAttribute.Type, partAttribute.Scale, unitsOfMeasure.Name AS UnitName, unitsOfMeasure.Unit, unitsOfMeasure.Symbol ";
	$query .= "FROM partAttribute ";
	$query .= "LEFT JOIN unitsOfMeasure On unitsOfMeasure.Id = partAttribute.UnitId";
	
	$result = mysqli_query($dbLink,$query);
	while($r = mysqli_fetch_assoc($result))
	{
		$id = $r['Id'];
		unset($r['Id']);
		$attributes[$id] = $r;
	}

	$baseQuery = "SELECT manufacturerPart.Id AS PartId, partManufacturer.name AS ManufacturerName, manufacturerPart.ManufacturerPartNumber, PartData, partPackage.name AS Package, Status,sum(partStock_getQuantity(partStock.StockNo)) AS StockQuantity FROM manufacturerPart "; 
	$baseQuery .= "LEFT JOIN partManufacturer On partManufacturer.Id = manufacturerPart.ManufacturerId ";
	$baseQuery .= "LEFT JOIN partPackage On partPackage.Id = manufacturerPart.PackageId ";
	$baseQuery .= "LEFT JOIN partStock On partStock.ManufacturerPartId = manufacturerPart.Id ";
	
	$queryParam = array();
	
	if(isset($_GET["ManufacturerName"]))
	{
		$temp = dbEscapeString($dbLink, $_GET["ManufacturerName"]);
		array_push($queryParam, "partManufacturer.Name = '".$temp."'");
	}
	
	if(isset($_GET["ManufacturerId"]))
	{
		$temp = dbEscapeString($dbLink, $_GET["ManufacturerId"]);
		array_push($queryParam, "partManufacturer.Id = '".$temp."'");
	}
	
	if(isset($_GET["ManufacturerPartNumber"]))
	{
		$temp = dbEscapeString($dbLink, $_GET["ManufacturerPartNumber"]);
		array_push($queryParam, "manufacturerPart.ManufacturerPartNumber LIKE '".$temp."'");
	}
	
	if(isset($_GET["classId"]))
	{
		$dbLink2 = dbConnect();
		$temp = dbEscapeString($dbLink2, $_GET["classId"]);
		$classIdList = "";
		$query = "CALL getPartClassRecursiveChildren('".$temp."')";
		$result = mysqli_query($dbLink2, $query);
		dbClose($dbLink2);

		while($r = mysqli_fetch_assoc($result)) 
		{
	
			$classIdList .= "'".$r['Id']."',";
		}
		$classIdList = substr($classIdList, 0, -1);

		array_push($queryParam, "PartClassId IN(".$classIdList.")");
	}
	
	$query = dbBuildQuery($dbLink,$baseQuery,$queryParam);
	
	$query  .= " GROUP BY manufacturerPart.Id ";

	$result = mysqli_query($dbLink,$query);
	$rows = array();
	$rowcount = mysqli_num_rows($result);
	while($r = mysqli_fetch_assoc($result)) 
	{
		
		// Decode Attributes
		$partDataRaw = json_decode($r['PartData']);
		$partData = array();
		
		
		if($partDataRaw != null)
		{
			foreach ($partDataRaw as $key =>$value) 
			{
				$dataSet = array();
				$attributeName = $attributes[$key]['Name'];
				if(is_array($value))
				{
					$value['Minimum'] = $value['0'];
					$value['Typical'] = $value['1'];
					$value['Maximum'] = $value['2'];
					unset($value['0']);
					unset($value['1']);
					unset($value['2']);
				}
				
				$dataSet['Name'] = $attributeName;
				$dataSet['AttributeId'] = $key;
				$dataSet['Value']= $value;
				$dataSet['Unit']= $attributes[$key]['Unit'];
				$dataSet['Symbol']= $attributes[$key]['Symbol'];
				array_push($partData,$dataSet);
			}
		}
		
		$r['PartData'] = $partData;
		array_push($rows,$r);	
	}

	dbClose($dbLink);	
	sendResponse($rows);
}
else if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$data = json_decode(file_get_contents('php://input'),true);
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$manufacturerName = dbEscapeString($dbLink,$data['data']['ManufacturerName']);
	$manufacturerPartNumber = dbEscapeString($dbLink,$data['data']['ManufacturerPartNumber']);

	$inserData['ManufacturerId']['raw']  = "(SELECT Id FROM partManufacturer WHERE Name = '".$manufacturerName."')";
	$inserData['ManufacturerPartNumber']  = $manufacturerPartNumber;
	
	$query = dbBuildInsertQuery($dbLink, "manufacturerPart", $inserData);
	
	$result = dbRunQuery($dbLink,$query);
	
	$error = null;
	$manufacturerPart = array();
	if($result == false)
	{
		$error = "Error description: " . dbGetErrorString($dbLink);
	}
	
	$query = "SELECT Id FROM manufacturerPart WHERE Id = LAST_INSERT_ID();";
	$result = dbRunQuery($dbLink,$query);
	
	$manufacturerPart['ManufacturerPartId'] = dbGetResult($result)['Id'];
	
	$result = dbRunQuery($dbLink,$query);
	$stockPart = dbGetResult($result);
	
	dbClose($dbLink);	
	sendResponse($manufacturerPart, $error);
}

?>