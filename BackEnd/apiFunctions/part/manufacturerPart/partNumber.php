<?php
//*************************************************************************************************
// FileName : partNumber.php
// FilePath : apiFunctions/manufacturerPart/
// Author   : Christian Marty
// Date		: 03.12.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/_function.php";
require_once __DIR__ . "/../_part.php";

if($api->isGet())
{
    $parameters = $api->getGetData();

    $baseQuery = <<<STR
        SELECT
        manufacturerPart_partNumber.Number AS PartNumber,
        vendor_displayName(vendor.Id) AS ManufacturerName,
        vendor.Id AS ManufacturerId,
        manufacturerPart_partNumber.Id AS PartNumberId,
        manufacturerPart_item.Id AS PartId,
        manufacturerPart_item.Number AS ManufacturerPartNumberTemplate,
        manufacturerPart_item.MarkingCode,
        manufacturerPart_series.Title AS SeriesTitle,
        manufacturerPart_series.Id AS SeriesId,
        manufacturerPart_partNumber.Description
        FROM manufacturerPart_partNumber
        LEFT JOIN manufacturerPart_item On manufacturerPart_item.Id = manufacturerPart_partNumber.ItemId
        LEFT JOIN manufacturerPart_series On manufacturerPart_series.Id = manufacturerPart_item.SeriesId
        LEFT JOIN vendor ON vendor.Id = manufacturerPart_item.VendorId OR vendor.Id = manufacturerPart_partNumber.VendorId OR vendor.Id = manufacturerPart_series.VendorId
    STR;

    $parameter = array();
    if(isset($parameters->ManufacturerPartNumber))
    {
        $manufacturerPartNumber = $database->escape($parameters->ManufacturerPartNumber);
        $parameter[] =  "manufacturerPart_partNumber.Number LIKE $manufacturerPartNumber";
    }

    if(isset($parameters->VendorId))
    {
        $vendorId = intval($parameters->VendorId);
        $parameter[] = "vendor.Id = ".$vendorId;
    }

    $output = $database->query($baseQuery,$parameter,"ORDER BY PartNumber");

    foreach ($output as $r)
    {
        $r->ManufacturerPartNumberTemplateWithoutParameters = manufacturerPart_numberWithoutParameters($r->ManufacturerPartNumberTemplate);
    }

    $api->returnData($output);
}