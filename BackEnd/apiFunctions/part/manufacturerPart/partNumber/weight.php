<?php
//*************************************************************************************************
// FileName : weight.php
// FilePath : apiFunctions/manufacturerPart/partNumber/
// Author   : Christian Marty
// Date     : 21.05.2024
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

if($api->isGet())
{
    $parameters = $api->getGetData();
    if(!isset($parameters->PartNumberId))$api->returnParameterMissingError("PartNumberId");

    $partNumber = intval($parameters->PartNumberId);

    $query = <<<STR
        SELECT
               manufacturerPart_partNumber.SinglePartWeight
        FROM manufacturerPart_partNumber
        WHERE manufacturerPart_partNumber.Id = '$partNumber'
    STR;

    $output = $database->query($query)[0];

    $api->returnData($output);
}
else if($api->isPatch())
{
    $data = $api->getPostData();
    if(!isset($data->PartNumberId))$api->returnParameterMissingError("PartNumberId");
    if(!isset($data->SinglePartWeight))$api->returnParameterMissingError("SinglePartWeight");

    $partNumber = intval($data->PartNumberId);

    $partNumberWeight = array();
    $partNumberWeight['SinglePartWeight'] = floatval($data->SinglePartWeight);

    $database->update("manufacturerPart_partNumber", $partNumberWeight, "manufacturerPart_partNumber.Id = '$partNumber'");

    $api->returnEmpty();
}
