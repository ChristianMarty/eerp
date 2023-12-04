<?php
//*************************************************************************************************
// FileName : template.php
// FilePath : apiFunctions/manufacturerPart/series/
// Author   : Christian Marty
// Date		: 26.07.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

if($api->isGet())
{
    $parameters = $api->getGetData();

    if(!isset($parameters->ManufacturerPartSeriesId)) $api->returnParameterMissingError("ManufacturerPartSeriesId");
    $manufacturerPartSeriesId = intval($parameters->ManufacturerPartSeriesId);
    if($manufacturerPartSeriesId == 0) $api->returnParameterError("ManufacturerPartSeriesId");

    $query = <<<STR
        SELECT
            manufacturerPart_series.Id AS ManufacturerPartSeriesId, 
            manufacturerPart_series.SeriesNameMatch,
            manufacturerPart_series.NumberTemplate,
            manufacturerPart_series.Parameter
        FROM manufacturerPart_series
        WHERE manufacturerPart_series.Id = '$manufacturerPartSeriesId'
        LIMIT 1
    STR;

    $output = $database->query($query)[0];

    $output->ManufacturerPartSeriesId = intval($output->ManufacturerPartSeriesId);
    if($output->Parameter !== null) $output->Parameter = json_decode($output->Parameter);
    else $output->Parameter = array();

    $api->returnData($output);
}
else if($api->isPost())
{
    $data = $api->getPostData();

    if(!isset($data->ManufacturerPartSeriesId)) $api->returnParameterMissingError("ManufacturerPartSeriesId");
    $manufacturerPartSeriesId = intval($data->ManufacturerPartSeriesId);
    if($manufacturerPartSeriesId == 0) $api->returnParameterError("ManufacturerPartSeriesId");

    $seriesCreate = array();
    $seriesCreate['NumberTemplate'] = trim($data['NumberTemplate']);
    $seriesCreate['SeriesNameMatch'] = trim($data['SeriesNameMatch']);
    $seriesCreate['Parameter'] = json_encode($data['Parameter']);

    $database->update("manufacturerPart_series", $seriesCreate, "Id = ".$manufacturerPartSeriesId);

    $api->returnEmpty();
}
