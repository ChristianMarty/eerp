<?php
//*************************************************************************************************
// FileName : attribute.php
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
	$children = true;
	if(isset($_GET["children"]))
	{
		if(!$_GET["children"]) $children = false;
	}
	
	$parents = false;
	if(isset($_GET["parents"]))
	{
		if($_GET["parents"]) $parents = true;
	}
	
	$classId = 0;
	if(isset($_GET["classId"]))
	{
		$classId = intval($_GET["classId"],10);
	}
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	// Query attributes
	$attributes  = array();
    $query = <<<STR
        SELECT 
            manufacturerPart_attribute.Id,
            manufacturerPart_attribute.ParentId, 
            manufacturerPart_attribute.Name, 
            unitOfMeasurement.Symbol, 
            unitOfMeasurement.Unit, 
            manufacturerPart_attribute.Type, 
            Scale
        FROM `manufacturerPart_attribute` 
        LEFT JOIN `unitOfMeasurement` ON unitOfMeasurement.Id = manufacturerPart_attribute.UnitOfMeasurementId
    STR;
	
	$result = dbRunQuery($dbLink,$query);
	while($r = mysqli_fetch_assoc($result)) 
	{
		$attributes[$r['Id']] = $r;
	}
	
	$attributeList = array();
	
	if(!$parents)
	{
		$attributeList = buildTree($attributes,$classId,$children, $parents);
	}
	else if($classId != 0)
	{
		// Query Classes
		$classes  = array();
		$query = "SELECT * FROM manufacturerPart_class";
		$result = mysqli_query($dbLink,$query);
		while($r = mysqli_fetch_assoc($result))
		{
			$classes[$r['Id']] = $r;
		}

		$attributeIdList = getParentAttributes($classes, $classId);

		// Decode Attribute Ids
		foreach ($attributeIdList as $attributeId)
		{
			$attribute = array();
			$attribute['Name'] = $attributes[$attributeId]['Name'];
			$attribute['Unit'] = $attributes[$attributeId]['Unit'];
			$attribute['Scale'] = $attributes[$attributeId]['Scale'];
			//if($attributes[$attributeId]['UseMinTypMax']) $attribute['MinMax'] = true;
			//else $attribute['MinMax'] = false;
			
			$attributeList[] = $attribute;
		}
	}
	
	dbClose($dbLink);	
	sendResponse($attributeList);
}
else if($_SERVER['REQUEST_METHOD'] == 'PATCH')
{
	
}

function getUnitType($attributes, $id)
{	
	if($attributes[$id]['Type'] != null) 
	{
		return $attributes[$id]['Type'];
	}
	else if ($attributes[$id]['ParentId'] != 0)
	{
		return getUnitType($attributes, $attributes[$id]['ParentId']);
	}
	else
	{
		return "";
	}
}

function getUnitOfMeasure($attributes, $id)
{	
	if($attributes[$id]['Unit'] != null) 
	{
		return $attributes[$id];
	}
	else if ($attributes[$id]['ParentId'] != 0)
	{
		return getUnitOfMeasure($attributes, $attributes[$id]['ParentId']);
	}
	else
	{
		return array("Unit"=>"","Symbol"=>"");
	}
}

function getParentAttributes($rows, $childId)
{  
	$attributeList = array();

	$row = $rows[$childId];
	if ((int)$row['Id'] == (int)$childId)
	{
		if($row['AttributeList'] != null) $attributeList = json_decode($row['AttributeList']);
		
		if ((int)$row['ParentId'] != 0)
		{
			$attributeList = array_merge(getParentAttributes($rows, $row['ParentId']),$attributeList);
		}
	}
	
	return $attributeList;
}

function hasChild($rows,$id): bool
{
	foreach ($rows as $row) 
	{
		if ($row['ParentId'] == $id)return true;
	}
	return false;
}

function buildTree($attributes, $parentId, $children, $parents): array
{  
	$treeItem = array();
	foreach ($attributes as $row)
	{
		if ($row['ParentId'] == $parentId)
		{
			$uom = getUnitOfMeasure($attributes, $row['Id']);
			$unitType = getUnitType($attributes, $row['Id']);
			
			$temp = array();
			
			$temp['Name'] = $row['Name'];
			$temp['Id'] = $row['Id'];
			$temp['Unit'] = $uom['Unit'];
			$temp['Symbol'] = $uom['Symbol'];
			$temp['Type'] = $unitType;
			$temp['Scale'] = $row['Scale'];
		
			if ($children && hasChild($attributes,$row['Id']))
			{
				$temp['Children'] = array();
				$temp['Children'] =  buildTree($attributes,$row['Id'], $children, $parents);
			}
			$treeItem[] = $temp;
		}
	}
	
	return $treeItem;
}
?>