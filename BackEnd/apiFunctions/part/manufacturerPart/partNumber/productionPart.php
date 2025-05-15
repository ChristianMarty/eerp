<?php
//*************************************************************************************************
// FileName : productionPart.php
// FilePath : apiFunctions/manufacturerPart/partNumber/
// Author   : Christian Marty
// Date     : 14.04.2024
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;
global $user;

require_once __DIR__ . "/../../../util/_barcodeParser.php";

if($api->isPost("productionPart.edit"))
{
    $data = $api->getPostData();

    if(!isset($data->ManufacturerPartNumberId)) $api->returnParameterMissingError('ManufacturerPartNumberId');
    if(!isset($data->ProductionPartList)) $api->returnParameterMissingError('ProductionPartList');
    $manufacturerPartNumberId = intval($data->ManufacturerPartNumberId);
    if($manufacturerPartNumberId == 0) $api->returnParameterError("ManufacturerPartNumberId");

    $productionPartNumbers = [];
    foreach($data->ProductionPartList as $item) {
        $productionPartNumber = barcodeParser_ProductionPart($item);
        if($productionPartNumber === null) continue;
        $productionPartNumber = $database->escape($productionPartNumber);
        $productionPartNumbers[] = $productionPartNumber;
    }

    $productionPartNumbersListString = implode(",", $productionPartNumbers);
    $productionPartIdQuery = <<<QUERY
    SELECT 
       productionPart.Id as Id
    FROM productionPart
    LEFT JOIN numbering ON numbering.Id = productionPart.NumberingPrefixId   
    WHERE CONCAT(numbering.Prefix,'-',productionPart.Number) IN ($productionPartNumbersListString)
    QUERY;

    $data = $database->query($productionPartIdQuery);
    $userId = $user->userId();
    $productionPartIdList = [];
    $insertValues = [];
    foreach ($data as $item) {
        $id = $item->Id;
        $productionPartIdList[] = $id;
        $insertValues[] = "($id, $manufacturerPartNumberId, $userId)";
    }

    $insertListString = implode(",", $insertValues);
    $insertQuery = <<<QUERY
        INSERT IGNORE INTO productionPart_manufacturerPart_mapping (ProductionPartId, ManufacturerPartNumberId, CreationUserId)
        VALUES $insertListString
    QUERY;
    $database->execute($insertQuery);

    $productionPartIdListString = implode(",", $productionPartIdList);
    $deleteQuery = <<<QUERY
        DELETE FROM productionPart_manufacturerPart_mapping
        WHERE ManufacturerPartNumberId = $manufacturerPartNumberId AND ProductionPartId NOT IN ($productionPartIdListString);
    QUERY;
    $database->execute($deleteQuery);

    $api->returnEmpty();
}