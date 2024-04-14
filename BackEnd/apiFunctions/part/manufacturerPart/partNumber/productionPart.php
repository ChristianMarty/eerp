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


    foreach($data->ProductionPartList as $item)
    {
        $productionPartNumber = barcodeParser_ProductionPart($item);
        $productionPartNumber = $database->escape($productionPartNumber);

        $query = <<<QUERY
        (SELECT 
            productionPart.Id
        FROM productionPart
        LEFT JOIN numbering ON numbering.Id = productionPart.NumberingPrefixId   
        WHERE CONCAT(numbering.Prefix,'-',productionPart.Number) = $productionPartNumber)
        QUERY;

        $sqlData = array();
        $sqlData['ProductionPartId']['raw'] = $query;
        $sqlData['ManufacturerPartNumberId'] = $manufacturerPartNumberId;
        $sqlData['CreationUserId'] = $user->userId();

        $database->insert("productionPart_manufacturerPart_mapping", $sqlData, true);
    }

    $api->returnEmpty();
}