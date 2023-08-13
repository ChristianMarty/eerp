<?php
//*************************************************************************************************
// FileName : manufacturerPart.php
// FilePath : apiFunctions/
// Author   : Christian Marty
// Date		: 15.05.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/_part.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$dbLink = dbConnect();

	// Query attributes

	$attributes  = array();

    $query = <<<STR
	SELECT manufacturerPart_attribute.Id, 
	       manufacturerPart_attribute.ParentId, 
	       manufacturerPart_attribute.Name, 
	       manufacturerPart_attribute.Type, 
	       manufacturerPart_attribute.Scale, 
	       unitOfMeasurement.Name AS UnitName, 
	       unitOfMeasurement.Unit, 
	       unitOfMeasurement.Symbol 
	FROM manufacturerPart_attribute 
	LEFT JOIN unitOfMeasurement On unitOfMeasurement.Id = manufacturerPart_attribute.UnitOfMeasurementId
	STR;
	
	$result = mysqli_query($dbLink,$query);
	while($r = mysqli_fetch_assoc($result))
	{
		$id = $r['Id'];
		unset($r['Id']);
		$attributes[$id] = $r;
	}

    $baseQuery = <<<STR
        SELECT
            manufacturerPart_item.Id AS PartId, 
            vendor_displayName(vendor.Id) AS ManufacturerName, 
            vendor.Id AS ManufacturerId, 
            manufacturerPart_partNumber.Number AS ManufacturerPartNumber, 
            manufacturerPart_item.Number AS ManufacturerPartNumberTemplate,
            manufacturerPart_item.Description,
            manufacturerPart_item.Attribute AS PartData, 
            GROUP_CONCAT(manufacturerPart_partPackage.name) AS Package, 
            SUM(partStock_getQuantity(partStock.StockNo)) AS StockQuantity 
        FROM manufacturerPart_item
        LEFT JOIN manufacturerPart_series On manufacturerPart_series.Id = manufacturerPart_item.SeriesId
        LEFT JOIN vendor On vendor.Id = manufacturerPart_item.VendorId OR vendor.Id = manufacturerPart_series.VendorId 
        LEFT JOIN manufacturerPart_partNumber ON manufacturerPart_partNumber.ItemId = manufacturerPart_item.Id
        LEFT JOIN manufacturerPart_partPackage On manufacturerPart_partPackage.Id = manufacturerPart_partNumber.PackageId 
        LEFT JOIN partStock On partStock.ManufacturerPartNumberId = manufacturerPart_partNumber.Id 
    STR;


	$queryParam = array();
	
	if(isset($_GET["ManufacturerName"]))
	{
		$temp = dbEscapeString($dbLink, $_GET["ManufacturerName"]);
		$queryParam[] = "vendor.Name = '" . $temp . "'";
	}
	
	if(isset($_GET["VendorId"]))
	{
		$temp = dbEscapeString($dbLink, $_GET["VendorId"]);
		$queryParam[] = "vendor.Id = '" . $temp . "'";
	}
	
	if(isset($_GET["ManufacturerPartNumber"]))
	{
		$temp = dbEscapeString($dbLink, $_GET["ManufacturerPartNumber"]);
		$queryParam[] = "manufacturerPart_partNumber.Number LIKE '" . $temp . "'";
	}
	
	if(isset($_GET["ClassId"]))
	{
		$dbLink2 = dbConnect();
		$temp = dbEscapeString($dbLink2, $_GET["ClassId"]);
		$classIdList = "";
		$query = "CALL manufacturerPart_class_getChildrenRecursive('".$temp."')";
		$result = mysqli_query($dbLink2, $query);
		dbClose($dbLink2);

		while($r = mysqli_fetch_assoc($result)) 
		{
	
			$classIdList .= "'".$r['Id']."',";
		}
		$classIdList = substr($classIdList, 0, -1);

		$queryParam[] = "PartClassId IN(" . $classIdList . ")";
	}
	
	$query = dbBuildQuery($dbLink,$baseQuery,$queryParam);
	
	$query  .= " GROUP BY manufacturerPart_item.Id ";

	$result = mysqli_query($dbLink,$query);
	$rows = array();
	$rowcount = mysqli_num_rows($result);
	while($r = mysqli_fetch_assoc($result)) 
	{
        // Decode Attributes
        $partDataRaw = null;
        if($r['PartData'] != null ) $partDataRaw = json_decode($r['PartData']);

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
				$partData[] = $dataSet;
			}
		}
		
		$r['PartData'] = $partData;

        $r['ManufacturerPartNumberTemplateWithoutParameters'] = manufacturerPart_numberWithoutParameters($r['ManufacturerPartNumberTemplate']);
        if($r['ManufacturerPartNumberTemplateWithoutParameters'] ==  NULL) $r['ManufacturerPartNumberTemplateWithoutParameters'] = $r['ManufacturerPartNumber'];
		$rows[] = $r;
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

	$insertData['VendorId']['raw']  = "(SELECT Id FROM vendor WHERE Name = '".$manufacturerName."')";
	$insertData['ManufacturerPartNumber']  = $manufacturerPartNumber;
	
	$query = dbBuildInsertQuery($dbLink, "manufacturerPart", $insertData);
	
	$result = dbRunQuery($dbLink,$query);
	
	$error = null;
	$manufacturerPart = array();
	if(!$result)
	{
		$error = "Error description: " . dbGetErrorString($dbLink);
	}
	
	$query = <<< STR
        SELECT Id FROM manufacturerPart WHERE Id = LAST_INSERT_ID();
    STR;

	$result = dbRunQuery($dbLink,$query);
	
	$manufacturerPart['ManufacturerPartId'] = dbGetResult($result)['Id'];
	
	$result = dbRunQuery($dbLink,$query);
	$stockPart = dbGetResult($result);
	
	dbClose($dbLink);	
	sendResponse($manufacturerPart, $error);
}

?>