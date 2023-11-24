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
            CountryCode,
            Name
        FROM country
        ORDER BY Name ASC
    QUERY;
    $result = $database->query($query);

    $api->returnData($result);
}
