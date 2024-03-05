<?php
//*************************************************************************************************
// FileName : manufacturerPart.php
// FilePath : apiFunctions/
// Author   : Christian Marty
// Date		: 03.12.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/_part.php";

if($api->isGet())
{
    $parameter = $api->getGetData();

	// Query attributes
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
	
	$result = $database->query($query);
    $attributes = [];
	foreach ($result as $r)
	{
		$id = $r->Id;
		unset($r->Id);
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
            SUM(partStock_getQuantity(partStock.StockNumber)) AS StockQuantity 
        FROM manufacturerPart_item
        LEFT JOIN manufacturerPart_series On manufacturerPart_series.Id = manufacturerPart_item.SeriesId
        LEFT JOIN vendor On vendor.Id = manufacturerPart_item.VendorId OR vendor.Id = manufacturerPart_series.VendorId 
        LEFT JOIN manufacturerPart_partNumber ON manufacturerPart_partNumber.ItemId = manufacturerPart_item.Id
        LEFT JOIN manufacturerPart_partPackage On manufacturerPart_partPackage.Id = manufacturerPart_partNumber.PackageId 
        LEFT JOIN partStock On partStock.ManufacturerPartNumberId = manufacturerPart_partNumber.Id 
    STR;


	$queryParam = array();
	
	if(isset($parameter->ManufacturerName))
	{
		$temp = $database->escape($parameter->ManufacturerName);
		$queryParam[] = "vendor.Name = $temp ";
	}
	
	if(isset($parameter->VendorId))
	{
		$temp = intval($parameter->VendorId);
		$queryParam[] = "vendor.Id = '$temp'";
	}
	
	if(isset($parameter->ManufacturerPartNumber))
	{
        $temp = $database->escape($parameter->ManufacturerPartNumber);
		$queryParam[] = "manufacturerPart_partNumber.Number LIKE $temp ";
	}
	
	if(isset($parameter->ClassId))
	{
		$temp = intval($parameter->ClassId);
		$query = "CALL manufacturerPart_class_getChildrenRecursive('$temp')";
        $result = $database->query($query);

        $classIdList = "";
		foreach ($result as $r)
		{
			$classIdList .= "'".$r->Id."',";
		}
		$classIdList = substr($classIdList, 0, -1);

		$queryParam[] = "PartClassId IN(" . $classIdList . ")";
	}

	$result = $database->query($baseQuery,$queryParam," GROUP BY manufacturerPart_item.Id ");
	$rows = array();
	foreach ($result as $r)
	{
        // Decode Attributes
        $partDataRaw = null;
        if($r->PartData != null ) $partDataRaw = json_decode($r->PartData);

		$partData = array();
		if($partDataRaw != null)
		{
			foreach ($partDataRaw as $key =>$value) 
			{
				$dataSet = array();
				$attributeName = $attributes[$key]->Name;
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
				$dataSet['Unit']= $attributes[$key]->Unit;
				$dataSet['Symbol']= $attributes[$key]->Symbol;
				$partData[] = $dataSet;
			}
		}
		
		$r->PartData = $partData;

        $r->ManufacturerPartNumberTemplateWithoutParameters = manufacturerPart_numberWithoutParameters($r->ManufacturerPartNumberTemplate);
        if($r->ManufacturerPartNumberTemplateWithoutParameters ==  NULL) $r->ManufacturerPartNumberTemplateWithoutParameters = $r->ManufacturerPartNumber;
		$rows[] = $r;
	}

	$api->returnData($rows);
}
