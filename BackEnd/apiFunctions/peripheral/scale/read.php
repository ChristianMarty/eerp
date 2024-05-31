<?php
//*************************************************************************************************
// FileName : read.php
// FilePath : apiFunctions/peripheral/scale
// Author   : Christian Marty
// Date		: 13.01.2024
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once  __DIR__.'/../_driver/scale/_scale.php';

if($api->isGet())
{
    $parameter = $api->getGetData();
    if(!isset($parameter->PeripheralId)) $api->returnParameterMissingError("PeripheralId");
    if(!is_numeric($parameter->PeripheralId))$api->returnParameterError("PeripheralId");
    $peripheralId = intval($parameter->PeripheralId);

    $scale = new Scale($peripheralId);

    if($scale->hasError()) $api->returnError($scale->getErrorString());

    $output = new stdClass();
    $output->value = $scale->read();

    if($scale->hasError()) $api->returnError($scale->getErrorString());

    $api->returnData($output);
}