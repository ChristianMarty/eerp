<?php
//*************************************************************************************************
// FileName : attribute.php
// FilePath : apiFunctions/manufacturerPart/
// Author   : Christian Marty
// Date		: 03.12.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

if($api->isGet())
{
    $parameters = $api->getGetData();

    $children = true;
    if(isset($parameters->children) and !$parameters->children) $children = false;

    $parents = false;
    if(isset($parameters->parents) and $parameters->parents) $parents = true;
    
    $classId = 0;
    if(isset($parameters->classId)) $classId = intval($parameters->classId);
    
    // Query attributes
    $attributes  = array();
    $query = <<<STR
    SELECT  manufacturerPart_attribute.Id,
        manufacturerPart_attribute.ParentId,
        manufacturerPart_attribute.Name, 
        unitOfMeasurement.Symbol, 
        unitOfMeasurement.Unit, 
        manufacturerPart_attribute.Type, 
        Scale
    FROM manufacturerPart_attribute
    LEFT JOIN unitOfMeasurement ON unitOfMeasurement.Id = manufacturerPart_attribute.UnitOfMeasurementId
    STR;

    $result = $database->query($query);
    foreach ($result as $r)
    {
        $attributes[$r->Id] = $r;
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
        $result = $database->query($query);
        foreach ($result as $r)
        {
            $classes[$r->Id] = $r;
        }

        $attributeIdList = getParentAttributes($classes, $classId);

        // Decode Attribute Ids
        foreach ($attributeIdList as $attributeId)
        {
            $attribute = array();
            $attribute['Name'] = $attributes[$attributeId]->Name;
            $attribute['Unit'] = $attributes[$attributeId]->Unit;
            $attribute['Symbol'] = $attributes[$attributeId]->Symbol;
            $attribute['Scale'] = $attributes[$attributeId]->Scale;
            //if($attributes[$attributeId]['UseMinTypMax']) $attribute['MinMax'] = true;
            //else $attribute['MinMax'] = false;
            
            $attributeList[] = $attribute;
        }
    }
    
    $api->returnData($attributeList);
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

function getParentAttributes(array $rows, int $childId)
{  
    $attributeList = array();
    $row = $rows[$childId];

    if ((int)$row->Id == $childId)
    {
        if($row->AttributeList != null) $attributeList = json_decode($row->AttributeList);
        
        if ((int)$row->ParentId != 0)
        {
            $attributeList = array_merge(getParentAttributes($rows, $row->ParentId),$attributeList);
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