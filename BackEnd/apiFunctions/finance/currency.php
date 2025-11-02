<?php
//*************************************************************************************************
// FileName : currency.php
// FilePath : apiFunctions/finance/
// Author   : Christian Marty
// Date		: 29.11.2023
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
            *
        FROM finance_currency
    QUERY;
    $result = $database->query($query);
    $api->returnData($result);
}
