<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/renderer
// Author   : Christian Marty
// Date		: 17.02.2024
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

if($api->isGet("renderer.view"))
{
    $parameters = $api->getGetData();

    if(!isset($parameters->RendererId)) $api->returnParameterMissingError("RendererId");
    $rendererId = intval($parameters->RendererId);
    if($rendererId == 0) $api->returnParameterError("RendererId");

    $queryParameters = [];
    $queryParameters[] = "renderer.Id = $rendererId";

    $query = <<< QUERY
        SELECT 
            renderer.Id,
            renderer.Name,
            renderer.Description,
            renderer.Render,
            renderer.Language,
            renderer.Code,
            
            renderer_medium.Name AS MediumName,
            renderer_medium.Description AS MediumDescription,
            renderer_medium.Hight AS MediumHight,
            renderer_medium.Width AS MediumWidth,
            renderer_medium.Rotation AS MediumRotation,
            renderer_medium.Resolution AS MediumResolution,
            
            renderer_dataset.Name AS DatasetName,
            renderer_dataset.Description AS DatasetDescription,
            renderer_dataset.Variables AS DatasetVariables
        FROM renderer
        LEFT JOIN renderer_dataset on renderer.DatasetId = renderer_dataset.Id
        LEFT JOIN renderer_medium on renderer.MediumId = renderer_medium.Id
    QUERY;
    $result = $database->query($query, $queryParameters, "LIMIT 1");

    $output = $result[0];

    $output->Medium = new stdClass();
    $output->Medium->Name = $output->MediumName;
    unset($output->MediumName);
    $output->Medium->Description = $output->MediumDescription;
    unset($output->MediumDescription);
    $output->Medium->Hight = $output->MediumHight;
    unset($output->MediumHight);
    $output->Medium->Width = $output->MediumWidth;
    unset($output->MediumWidth);
    $output->Medium->Rotation = $output->MediumRotation;
    unset($output->MediumRotation);
    $output->Medium->Resolution = $output->MediumResolution;
    unset($output->MediumResolution);

    $output->Dataset = new stdClass();
    $output->Dataset->Name = $output->DatasetName;
    unset($output->DatasetName);
    $output->Dataset->Description = $output->DatasetDescription;
    unset($output->DatasetDescription);

    $output->Dataset->Variables = explode(',', $output->DatasetVariables);
    unset($output->DatasetVariables);

    $api->returnData($output);
}