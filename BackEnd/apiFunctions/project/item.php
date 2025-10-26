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

require_once __DIR__ . "/../util/_barcodeParser.php";
require_once __DIR__ . "/../util/_barcodeFormatter.php";

if($api->isGet( \Permission::Project_View))
{
    $parameter = $api->getGetData();

    if(!isset($parameter->ProjectNumber)) $api->returnParameterMissingError("ProjectNumber");
    $projectNumber = barcodeParser_Project($parameter->ProjectNumber);
    if($projectNumber === null) $api->returnParameterError("ProjectNumber");

    $query = <<< STR
        SELECT * FROM project 
        WHERE project.ProjectNumber = $projectNumber
    STR;

    $result = $database->query($query)[0];
    $result->ProjectBarcode = barcodeFormatter_Project($result->ProjectNumber);
    $api->returnData($result);
}
