<?php
//*************************************************************************************************
// FileName : address.php
// FilePath : apiFunctions/vendor
// Author   : Christian Marty
// Date		: 08.05.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

if($api->isGet())
{
    $parameters = $api->getGetData();

    $query = <<<STR
        SELECT 
           vendor_address.Id AS AddressId,
           VendorId,
           Street,
           PostalCode,
           City,
           PhonePrefix,
           country.Name AS CountryName,
        country.CountryCode
        FROM vendor_address
        LEFT JOIN country ON country.Id = vendor_address.CountryId 
    STR;

    $queryParam= [];
    if(isset($parameters->VendorId))$queryParam[] = "VendorId = ".intval($parameters->VendorId);

    $addresses = $database->query($query,$queryParam);

    $api->returnData($addresses);
}
