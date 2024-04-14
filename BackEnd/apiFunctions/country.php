<?php
//*************************************************************************************************
// FileName : country.php
// FilePath : apiFunctions/
// Author   : Christian Marty
// Date		: 21.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

if($api->isGet())
{
    $query = <<< QUERY
        SELECT
            Id,
            PhonePrefix,
            Alpha2Code,
            Alpha2Code AS CountryCode,
            Alpha3Code,
            NumericCode,
            ShortName,
            ShortName AS Name
        FROM country
        ORDER BY ShortName ASC
    QUERY;
    $result = $database->query($query);
    $api->returnData($result);
}
