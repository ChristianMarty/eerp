<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/part/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";


if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	
	if(!isset($_GET["PartId"])) sendResponse(NULL, "No Part Id specified");
		
	
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

	$baseQuery = "SELECT manufacturerPart.Id AS PartId, vendor.name AS ManufacturerName, manufacturerPart.ManufacturerPartNumber, PartData, partPackage.name AS Package, Status, sum(partStock_getQuantity(partStock.StockNo)) AS StockQuantity, partClass_getName(partClass.Id) AS PartClassName FROM manufacturerPart ";
	$baseQuery .= "LEFT JOIN vendor On vendor.Id = manufacturerPart.VendorId ";
	$baseQuery .= "LEFT JOIN partPackage On partPackage.Id = manufacturerPart.PackageId ";
	$baseQuery .= "LEFT JOIN partStock On partStock.ManufacturerPartId = manufacturerPart.Id ";
	$baseQuery .= "LEFT JOIN partClass On partClass.Id = manufacturerPart.PartClassId ";
	
	$queryParam = array();

	$partId = dbEscapeString($dbLink, $_GET["PartId"]);
	array_push($queryParam, "manufacturerPart.Id = '".$partId."'");


	
	$query = dbBuildQuery($dbLink,$baseQuery,$queryParam);
	
	
	$result = mysqli_query($dbLink,$query);
	$rows = array();
	$rowcount = mysqli_num_rows($result);
	while($r = mysqli_fetch_assoc($result)) 
	{
		//$partId = $r['Id'];
		//unset($r['Id']);
		
		/*// Get Documents
		global $documentRootPath;
		
		$documents = array();
		$documentsRoot = $documentRootPath."/Datasheet/";
		
		$documentsQuery = "SELECT * FROM document WHERE Id IN(".$r['DocumentIds'].")";
		if ($documentsResult = mysqli_query($dbLink,$documentsQuery))
		{
			if( mysqli_num_rows($documentsResult) )
			{
				while($dr = mysqli_fetch_assoc($documentsResult))
				{
					$dr['Path'] = $documentsRoot.$dr['Path'];
					array_push($documents,$dr);
				}
			}
		}*/
		
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
		
	//	$r['Supplier'] = $supplierData;
		$r['PartData'] = $partData;
		//$r['Documents'] = $documents;
		array_push($rows,$r);	
	}

	dbClose($dbLink);	
	sendResponse($rows);
}
else if($_SERVER['REQUEST_METHOD'] == 'PATCH')
{
	$data = json_decode(file_get_contents('php://input'),true);
	
	if(!isset($data['data']['PartId'])) sendResponse(NULL, "No Part Id specified");
	if(!is_numeric($data['data']['PartId'])) sendResponse(NULL, "Part Id is not numeric");
	$partId = intval($data['data']['PartId']);
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$partData = array();
	
	foreach($data['data']['PartData'] as $attributeData)
	{
		$attributeId = dbEscapeString($dbLink, $attributeData['AttributeId']);
		$value = dbEscapeString($dbLink, $attributeData['Value']);
		
		$partData[$attributeId] = $value;
	}
	
	$query = "UPDATE manufacturerPart SET PartData = '".json_encode($partData)."' WHERE  Id = ".$partId;
	
	//dbRunQuery($dbLink,$query);
	echo $query;
	
	
	dbClose($dbLink);	
	sendResponse();
}

?>