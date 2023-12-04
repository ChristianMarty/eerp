<?php
//*************************************************************************************************
// FileName : bom.php
// FilePath : apiFunctions/billOfMaterial/
// Author   : Christian Marty
// Date		: 02.12.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/../util/_barcodeParser.php";
require_once __DIR__ . "/../util/_barcodeFormatter.php";

if($api->isGet())
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
        $r->ProductionPartBarcode = barcodeFormatter_ProductionPart($r->ProductionPartPrefix."-".$r->ProductionPartNumber);
        $r->ProductionPartNumber = $r->ProductionPartBarcode; // TODO: Legacy->remove
	}
	
	$api->returnData($result);
}
else if($api->isPost())
{
	$data = $api->getPostData();
    if(!isset($data->RevisionId)) $api->returnParameterMissingError("RevisionId");
    if(!isset($data->Bom)) $api->returnParameterMissingError("Bom");
    $revisionId = intval($data->RevisionId);

	foreach ( $data->Bom as $line)
	{
		$sqlData = array();
		$sqlData['BillOfMaterialRevisionId'] = $revisionId;

        $productionPartBarcode = barcodeParser_ProductionPart($line->ProductionPartBarcode);
        $productionPartBarcode = $database->escape($productionPartBarcode);

        $getProductionPartIdQuery = <<<STR
            (
            SELECT productionPart.Id 
            FROM productionPart 
            LEFT JOIN numbering ON numbering.Id = productionPart.NumberingPrefixId 
            WHERE CONCAT (numbering.Prefix,'-',productionPart.Number) = $productionPartBarcode
            )
        STR;

        $sqlData['ProductionPartId']['raw'] = $getProductionPartIdQuery;

		$sqlData['ReferenceDesignator'] = trim($line->ReferenceDesignator);
		$sqlData['PositionX'] = trim($line->XPosition);
		$sqlData['PositionY'] = trim($line->YPosition);
		$sqlData['Rotation'] = trim($line->Rotation);
		$sqlData['Layer'] = trim($line->Layer);
		$sqlData['Description'] = trim($line->Description);

        $database->insert("billOfMaterial_item", $sqlData);
	}
	
    $api->returnEmpty();
}
