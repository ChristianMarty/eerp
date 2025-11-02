<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/project/
// Author   : Christian Marty
// Date		: 21.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

if($api->isGet( \Permission::Project_View))
{
    $parameter = $api->getGetData();

    if(!isset($parameter->ProjectNumber)) $api->returnParameterMissingError("ProjectNumber");
    $projectNumber = \Numbering\parser(\Numbering\Category::Project, $parameter->ProjectNumber);
    if($projectNumber === null) $api->returnParameterError("ProjectNumber");

    $query = <<< STR
        SELECT * FROM project 
        WHERE project.ProjectNumber = $projectNumber
    STR;
    $result = $database->query($query);
    \Error\checkErrorAndExit($result);
    \Error\checkNoResultAndExit($result, $parameter->ProjectNumber);

    $output = $result[0];
    $output->ProjectBarcode = \Numbering\format(\Numbering\Category::Project, $output->ProjectNumber);
    $api->returnData($output);
}
