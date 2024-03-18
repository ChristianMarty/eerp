<?php
//*************************************************************************************************
// FileName : renderer.php
// FilePath : apiFunctions/
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
    $parameter = $api->getGetData();

    $flat = true;
    $dataset = 0;
    if(isset($parameter->DatasetId)) $dataset = intval($parameter->DatasetId);
    if(isset($parameter->Flat)) $flat = $parameter->Flat;

    $query = <<< QUERY
        SELECT 
            renderer.Id AS Id,
            renderer.Name AS Name,
            renderer.Description AS Description,
            renderer.Code AS Code,
            Render,
            Language,
            renderer_dataset.Name AS DatasetName,
            renderer_medium.Name AS MediumName
        FROM renderer
        LEFT JOIN renderer_dataset ON renderer_dataset.Id = renderer.DatasetId
        LEFT JOIN renderer_medium ON renderer_medium.Id = renderer.MediumId
    QUERY;
    $queryParameters = [];
    if($dataset !== 0) $queryParameters[] = "DatasetId = '$dataset'";

    $result = $database->query($query, $queryParameters, "ORDER BY Name ASC");

    $output = [];
    if($flat === false){
        foreach ($result as $item){
            if(!array_key_exists($item->DatasetName,$output)) $output[$item->DatasetName] = [];
            $output[$item->DatasetName][] = $item;
        }
    }else{
        $output = $result;
    }

    $api->returnData($output);
}