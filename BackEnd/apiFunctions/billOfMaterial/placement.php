<?php
//*************************************************************************************************
// FileName : placement.php
// FilePath : apiFunctions/billOfMaterial/
// Author   : Christian Marty
// Date		: 02.12.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/../util/_barcodeFormatter.php";

if($api->isGet(\Permission::BillOfMaterial_View))
{
    $parameter = $api->getGetData();

    if(!isset($parameter->RevisionId)) $api->returnParameterMissingError("RevisionId");
    $revisionId = intval($parameter->RevisionId);

    $query = <<<STR
        SELECT 
               billOfMaterial_item.ReferenceDesignator,
               billOfMaterial_item.Layer,
               billOfMaterial_item.PositionX,
               billOfMaterial_item.PositionY,
               billOfMaterial_item.Rotation, 
               productionPart.Number AS ProductionPartNumber, 
               numbering.Prefix AS ProductionPartPrefix, 
               productionPart.Description 
        FROM billOfMaterial_item 
        LEFT JOIN productionPart ON productionPart.Id = billOfMaterial_item.ProductionPartId
        LEFT JOIN numbering ON numbering.Id = productionPart.NumberingPrefixId
        WHERE BillOfMaterialRevisionId = $revisionId
    STR;

    $result = $database->query($query);
    foreach ($result as $r)
    {
        $r->ProductionPartNumber = barcodeFormatter_ProductionPart($r->ProductionPartPrefix."-".$r->ProductionPartNumber);
    }

    $api->returnData($result);
}
