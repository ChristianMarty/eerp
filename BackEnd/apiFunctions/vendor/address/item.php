<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/vendor/address
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
    $data = $api->getGetData();
    if(!isset($data->AddressId))$api->returnParameterMissingError("AddressId");

    $addressId = intval($data->AddressId);
    $query =  <<< QUERY
            SELECT
                Id,
                VendorId,
                CountryId,
                Street,
                PostalCode,
                City,
                VatTaxNumber,
                CustomsAccountNumber
            FROM vendor_address 
            WHERE Id = {$addressId} 
            LIMIT 1;
    QUERY;

    try {
        $result = $database->query($query)[0];
        $api->returnData($result);
    }
    catch (\Exception $e)
    {
        $api->returnError($e->getMessage());
    }
}
else if($api->isPost())
{
    $data = $api->getPostData();
    if (!isset($data->VendorId)) $api->returnParameterMissingError("VendorId");
    $vendorId = intval($data->VendorId);

    $insertData = [];
    $insertData['VendorId'] = $vendorId;
    $insertData['CountryId'] = intval($data->CountryId);
    $insertData['Street'] = $data->Street;
    $insertData['PostalCode'] = $data->PostalCode;
    $insertData['City'] = $data->City;
    $insertData['VatTaxNumber'] = $data->VatTaxNumber;
    $insertData['CustomsAccountNumber'] = $data->CustomsAccountNumber;

    try {
        $database->insert("vendor_address", $insertData);
        $api->returnEmpty();
    } catch (\Exception $e) {
        $api->returnError($e->getMessage());
    }
}
else if($api->isPatch())
{
    $data = $api->getPostData();
    if(!isset($data->AddressId))$api->returnParameterMissingError("AddressId");
    $addressId = intval($data->AddressId);

    $insertData = [];
    $insertData['CountryId'] = intval($data->CountryId);
    $insertData['Street'] = $data->Street;
    $insertData['PostalCode'] = $data->PostalCode;
    $insertData['City'] = $data->City;
    $insertData['VatTaxNumber'] = $data->VatTaxNumber;
    $insertData['CustomsAccountNumber'] = $data->CustomsAccountNumber;

    try {
        $database->update("vendor_address", $insertData,"Id = {$addressId}");
        $api->returnEmpty();
    } catch (\Exception $e) {
        $api->returnError($e->getMessage());
    }
}
