<?php
//*************************************************************************************************
// FileName : updatePartLookup.php
// FilePath : apiFunctions/process/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../util/siFormatter.php";

$title = "Update Description";
$description = "Update production part description";
$parameter = null;

function getParentTemplate($rows, $childId)
{
    $temp = array();
    $row = $rows[$childId];
    if($row['Template'] != null) $temp = $row['Template'];
    if ($row['ParentId'] != 0)
    {
        $temp = array_merge(getParentTemplate($rows, $row['ParentId']),$temp);
    }
    return $temp;
}

function getParentName($rows, $childId, $output = ["Name" => null, "ShortName" => null, "Prefix" => null ])
{
    $row = $rows[$childId];

    if($output['Name'] == null) $output['Name'] = $row['Name'];
    if($output['ShortName'] == null) $output['ShortName'] = $row['ShortName'];
    if($output['Prefix'] == null) $output['Prefix'] = $row['Prefix'];

    if($row['ParentId'] != 0 && ($row['Name'] == null || $output['ShortName']  == null || $output['Prefix']  == null))
    {
        $temp = getParentName($rows, $row['ParentId'], $output);
        if($output['Name'] == null) $output['Name'] = $temp['Name'];
        if($output['ShortName'] == null) $output['ShortName'] = $temp['ShortName'];
        if($output['Prefix'] == null) $output['Prefix'] = $temp['Prefix'];
    }
    return $output;
}

function getParentAttribute($rows, $childId)
{
    $output = ["UomId" => null,"SymbolOverride" => null ];
    $row = $rows[$childId];
    if ($row['ParentId'] == 0)
    {
        $output['UomId'] = $row['UnitOfMeasurementId'];
        $output['SymbolOverride'] = $row['SymbolOverride'];
    }
    else if($row['UnitOfMeasurementId'] == null || $output['SymbolOverride']  == null)
    {
        $temp = getParentAttribute($rows, $row['ParentId']);
        if($output['UomId'] == null) $output['UomId'] = $temp['UomId'];
        if($output['SymbolOverride'] == null) $output['SymbolOverride'] = $temp['SymbolOverride'];
    }
    return $output;
}

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
    $dbLink = dbConnect();

// Get and process Attributes
    $query = <<<STR
        SELECT Id, Name, Unit, Symbol FROM unitOfMeasurement;
    STR;

    $result = dbRunQuery($dbLink, $query);
    $uom = array();
    while($r = mysqli_fetch_assoc($result))
    {
        $r['Id'] = intval($r['Id']);
        $uom[$r['Id']] = $r;
    }
    $uom[0]  = ['Name' => "", 'Unit' => "", 'Symbol' => ""];

    $query = <<<STR
        SELECT Id, ParentId, Name, Symbol AS SymbolOverride, UnitOfMeasurementId FROM partAttribute;
    STR;
    $result = dbRunQuery($dbLink, $query);

    $attributeRaw = array();
    while($r = mysqli_fetch_assoc($result))
    {
        $r['Id'] = intval($r['Id']);
        $r['ParentId'] = intval($r['ParentId']);
        $r['UnitOfMeasurementId'] = intval($r['UnitOfMeasurementId']);
        $attributeRaw[$r['Id']] = $r;
    }

    $attributeList = array();
    foreach ($attributeRaw as $item)
    {
        $temp = getParentAttribute($attributeRaw,$item['Id']);
        $temp = array_merge($item, $uom[$temp['UomId']]);
        unset($temp['UnitOfMeasurementId']);

        if($temp['SymbolOverride']) $temp['Symbol'] = $temp['SymbolOverride'];
        unset($temp['SymbolOverride']);

        $attributeList[$item['Id']] = $temp;
    }

// Get and process part class template
    $query = "SELECT Id, ParentId, DescriptionTemplate, Name, ShortName, Prefix FROM partClass";
    $result = dbRunQuery($dbLink, $query);

    $templateRaw = array();
    while($r = mysqli_fetch_assoc($result))
    {
        $templateString = $r['DescriptionTemplate'];
        unset($r['DescriptionTemplate']);
        $temp = $r;

        if($templateString) $temp['Template'] = json_decode($templateString,true);
        else $temp['Template'] = null;

        $temp['Id'] = intval($temp['Id']);
        $temp['ParentId'] = intval($temp['ParentId']);

        $templateRaw[$r['Id']]  = $temp;
    }

    $templateList = array();
    foreach ($templateRaw as $item)
    {
        $item['Template'] = getParentTemplate($templateRaw,$item['Id']);
        $temp = getParentName($templateRaw, $item['Id']);

        $item['Name'] = $temp['Name'];
        $item['ShortName'] = $temp['ShortName'];
        $item['Prefix'] = $temp['Prefix'];

        $templateList[$item['Id']] = $item;
    }



// Template creation finished -> generate description
    $query = <<<STR
        SELECT manufacturerPart.Id, manufacturerPart.PartClassId, manufacturerPart.ManufacturerPartNumber, manufacturerPart.PartData,
               vendor.Name AS VendorName, partPackage.Name AS PackageName
        FROM manufacturerPart 
        LEFT JOIN vendor ON manufacturerPart.VendorId = vendor.Id
        LEFT JOIN partPackage ON manufacturerPart.PackageId = partPackage.Id
        WHERE Description IS NULL AND PartClassId IS NOT NULL
    STR;
    $result = dbRunQuery($dbLink, $query);

    while($r = mysqli_fetch_assoc($result))
    {
        $template = $templateList[intval($r['PartClassId'])];
        if(!$template) continue;

        if($r['PartData'])$partData = json_decode($r['PartData'],true);
        else $partData = null;

        ksort($template,  $flags = SORT_REGULAR);

        $description = "";
        foreach ($template['Template'] as $key => $value)
        {
            $type = explode('_',$value);

            switch ($type[0]) {
                case "Class":
                    $description .= $template['Name'];
                    break;

                case "ClassShort":
                    $description .= $template['ShortName'];
                    break;

                case "MPN":
                    $description .= $r['ManufacturerPartNumber'];
                    break;

                case "Vendor":
                    $description .= $r['VendorName'];
                    break;

                case "Package":
                    $description .= $r['PackageName'];
                    break;

                case "AttrId":
                    if(!$partData) break;
                    if(!array_key_exists($type[1], $partData)) break;

                    $data = $partData[$type[1]];
                    $attribute = $attributeList[$type[1]];

                    if(is_array($data))
                    {
                        foreach ($data as $item)
                        {
                            $description .= siFormatter($item) . "-";
                        }
                        $description = substr($description, 0, -1);
                    }
                    else
                    {
                        $description .= siFormatter($partData[$type[1]]);
                    }
                    $description.= $attribute['Symbol'];
                    break;
            }

            $description.=" ";

        }

        if(strlen($description)) {
            $query = "UPDATE manufacturerPart SET Description = '" . dbEscapeString($dbLink, $description) . "' WHERE Id = " . $r['Id'];
            dbRunQuery($dbLink, $query);

            echo '<pre>';
            echo var_dump($description);
            echo '</pre>';
        }

    }

    $query = <<<STR
    UPDATE productionPart SET DESCRIPTION = 
        (
        SELECT Description FROM manufacturerPart 
        LEFT JOIN productionPart_manufacturerPart_mapping ON productionPart_manufacturerPart_mapping.ManufacturerPartId = manufacturerPart.Id WHERE productionPart_manufacturerPart_mapping.ProductionPartId = productionPart.Id LIMIT 1
        ) 
    WHERE DESCRIPTION IS NULL
    STR;

    dbRunQuery($dbLink, $query);

    dbClose($dbLink);

    exit;
}


?>