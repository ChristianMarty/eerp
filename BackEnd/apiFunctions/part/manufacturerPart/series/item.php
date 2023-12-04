<?php
//*************************************************************************************************
// FileName : item.php.php
// FilePath : apiFunctions/manufacturerPart/series/
// Author   : Christian Marty
// Date		: 25.04.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/../_function.php";
require_once __DIR__ . "/../../../util/_getDocuments.php";

if($api->isGet())
{
    $parameters = $api->getGetData();

    if(!isset($parameters->ManufacturerPartSeriesId)) $api->returnParameterMissingError("ManufacturerPartSeriesId");
    $manufacturerPartSeriesId = intval($parameters->ManufacturerPartSeriesId);
    if($manufacturerPartSeriesId == 0) $api->returnParameterError("ManufacturerPartSeriesId");

    $query = <<<STR
        SELECT
            manufacturerPart_series.Id AS ManufacturerPartSeriesId, 
            manufacturerPart_series.Title,
            vendor_displayName(vendor.Id) AS  ManufacturerName, 
            manufacturerPart_class.Name AS ClassName, 
            manufacturerPart_series.Description, NumberTemplate,
            manufacturerPart_series.DocumentIds AS SeriesDocumentIds
        FROM manufacturerPart_series
        LEFT JOIN manufacturerPart_class ON manufacturerPart_class.Id = manufacturerPart_series.ClassId
        LEFT JOIN vendor on vendor.Id = manufacturerPart_series.VendorId
        WHERE manufacturerPart_series.Id = '$manufacturerPartSeriesId'
        LIMIT 1
    STR;

    $output = $database->query($query)[0];

    $parameter= getParameter($manufacturerPartSeriesId);
    $output->Parameter = $parameter;

    if($parameter == null) $output->Parameter = array();
    foreach($output->Parameter  as &$p)
    {
        if(!isset($p->Values)) $p->Values = array();
    }

    $query = <<<STR
        SELECT *
        FROM manufacturerPart_item
        WHERE manufacturerPart_item.SeriesId = '$manufacturerPartSeriesId'
    STR;

    $manufacturerPartItem = $database->query($query);
    foreach ($manufacturerPartItem as $r)
    {
        $r->ItemId = $r->Id;
    }
    $output->Item = $manufacturerPartItem;


    foreach ($output->Item as $item)
    {
        $itemId = $item->Id;
        $query = <<<STR
            SELECT *
            FROM manufacturerPart_partNumber
            WHERE manufacturerPart_partNumber.ItemId = '$itemId'
        STR;

        $item->Description = descriptionFromNumber($output->NumberTemplate,$parameter,$item->Number);
        $manufacturerPartNumber = $database->query($query);

        foreach ($manufacturerPartNumber as $r)
        {
            $r->Description = descriptionFromNumber($item->Number,$parameter,$r->Number);
            $r->PartNumberId = intval($r->Id);
        }
        $item->PartNumber = $manufacturerPartNumber;
    }

    $output->Documents = getDocumentsFromIds($output->SeriesDocumentIds);
    unset($output->SeriesDocumentIds);

    $api->returnData($output);
}
else if($api->isPost())
{
    $data = $api->getPostData();

    if(!isset($data->VendorId))  $api->returnParameterMissingError("VendorId");
    if(!isset($data->Title))  $api->returnParameterMissingError("Title");
    if(!isset($data->Description))  $api->returnParameterMissingError("Description");

    $seriesCreate = array();
    $seriesCreate['VendorId'] = intval($data->VendorId);
    $seriesCreate['Description'] = trim($data->Description);
    $seriesCreate['Title'] = trim($data->Title);

    $output = [];
    $output["ManufacturerPartSeriesId"] = $database->insert("manufacturerPart_series", $seriesCreate);

    $api->returnData($output);
}
