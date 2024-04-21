<?php
//*************************************************************************************************
// FileName : characteristics.php
// FilePath : apiFunctions/manufacturerPart/item/
// Author   : Christian Marty
// Date		: 03.12.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

if($api->isGet()) {
    $parameters = $api->getGetData();

    if (!isset($parameters->ManufacturerPartItemId)) $api->returnParameterMissingError("ManufacturerPartItemId");
    $manufacturerPartItemId = intval($parameters->ManufacturerPartItemId);
    if ($manufacturerPartItemId == 0) $api->returnParameterError("ManufacturerPartItemId");

    $query = <<<STR
        SELECT 
            manufacturerPart_item.Id AS PartId, 
            manufacturerPart_item.Number AS PartNumber, 
            Attribute,
            manufacturerPart_class.Id AS PartClassId,
            manufacturerPart_class.Name AS PartClassName
        FROM manufacturerPart_item
        LEFT JOIN manufacturerPart_class On manufacturerPart_class.Id = manufacturerPart_item.PartClassId
        WHERE manufacturerPart_item.Id = '$manufacturerPartItemId'
        LIMIT 1
    STR;
    $output = $database->query($query)[0];

    $api->returnData($output);
}else if($api->isPatch()) {

    $data = $api->getPostData();

    if (!isset($data->ManufacturerPartItemId)) $api->returnParameterMissingError("ManufacturerPartItemId");
    $manufacturerPartItemId = intval($data->ManufacturerPartItemId);
    if ($manufacturerPartItemId == 0) $api->returnParameterError("ManufacturerPartItemId");

    if (!isset($data->ClassId)) $api->returnParameterMissingError("ClassId");
    $classId = intval($data->ClassId);
    if ($classId == 0) $api->returnParameterError("ClassId");

    $sqlData = array();
    $sqlData['PartClassId'] = $classId;

    $database->update("manufacturerPart_item", $sqlData, "Id = $manufacturerPartItemId");

    $api->returnEmpty();
}