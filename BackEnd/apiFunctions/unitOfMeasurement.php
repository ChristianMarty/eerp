<?php
//*************************************************************************************************
// FileName : unitOfMeasurement.php
// FilePath : apiFunctions/
// Author   : Christian Marty
// Date		: 23.10.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

if($api->isGet())
{
    $parameters = $api->getGetData();
    $queryParam = [];
    if(isset($parameters->Countable))
    {
        if($parameters->Countable === true) $queryParam[] = "Countable = b'1'";
        else $queryParam[] = "Countable = b'0'";
    }

    $query = <<<QUERY
        SELECT
            Id,
            Name,
            Unit,
            Symbol,
            Countable,
            BaseUnit,
            ConversionFactor
        FROM unitOfMeasurement
    QUERY;

    try {
        $result = $database->query($query,$queryParam);

        foreach($result as &$item) {
            $item->Countable = boolval($item->Countable);
        }
        $api->returnData($result);
    }
    catch (\Exception $e)
    {
        $api->returnError();
    }
}
