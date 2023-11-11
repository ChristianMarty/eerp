<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/specificationPart
// Author   : Christian Marty
// Date		: 01.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
global $database;
global $api;

require_once __DIR__ . "/../../util/_barcodeParser.php";
require_once __DIR__ . "/../../util/_barcodeFormatter.php";

if($api->isGet())
{
    $parameters = $api->getGetData();
    if(!isset($parameters->SpecificationPartBarcode)) $api->returnParameterMissingError('SpecificationPartBarcode');

    $specificationPartBarcode= barcodeParser_SpecificationPart($parameters->SpecificationPartBarcode);

    $query = <<<STR
        SELECT 
            specificationPart.Id,
            specificationPart.Type,
            specificationPart.Title
        FROM specificationPart
        WHERE specificationPart.Id = $specificationPartBarcode
    STR;

    $output = $database->query($query)[0];

    $api->returnData($output);
}
if($api->isPost())
{
    $data = $api->getPostData();

    $dbLink = dbConnect();
    $sqlData = array();
    $sqlData['Type'] = $data->Type;
    $sqlData['Title'] = $data->Title;

    $output = array();
    $output['Id'] = $database->insert("specificationPart", $sqlData);
    $api->returnData($output);
}
