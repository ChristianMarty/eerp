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
global $user;

if($api->isGet(Permission::Vendor_View))
{
    $parameter = $api->getGetData();
    if(!isset($parameter->AddressId)) $api->returnData(\Error\parameterMissing("AddressId"));
    $addressId = intval($parameter->AddressId);
    if($addressId === 0) $api->returnData(\Error\parameter("AddressId"));

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
            WHERE Id = $addressId
    QUERY;
    $result = $database->query($query);
    \Error\checkErrorAndExit($result);
    \Error\checkNoResultAndExit($result, $parameter->AddressId);

    $api->returnData($result[0]);
}
else if($api->isPost(Permission::Vendor_Edit))
{
    $data = $api->getPostData();
    if(!isset($data->AddressId)) $api->returnData(\Error\parameterMissing("VendorId"));
    $vendorId = intval($data->AddressId);
    if($vendorId === 0) $api->returnData(\Error\parameter("VendorId"));

    $countryNumericCode = intval($data->CountryNumericCode);

    $insertData = [];
    $insertData['VendorId'] = $vendorId;
    $insertData['CountryId']['raw'] = "(SELECT Id FROM country WHERE NumericCode = '$countryNumericCode')";
    $insertData['Street'] = $data->Street;
    $insertData['PostalCode'] = $data->PostalCode;
    $insertData['City'] = $data->City;
    $insertData['VatTaxNumber'] = $data->VatTaxNumber;
    $insertData['CustomsAccountNumber'] = $data->CustomsAccountNumber;
    $insertData['CreationUserId'] = $user->userId();

    $result = $database->insert("vendor_address", $insertData);

    $api->returnData($result);
}
else if($api->isPatch(Permission::Vendor_Edit))
{
    $data = $api->getPostData();
    if(!isset($data->AddressId))$api->returnData(\Error\parameterMissing("AddressId"));
    $addressId = intval($data->AddressId);
    if($addressId === 0) $api->returnData(\Error\parameter("AddressId"));

    $countryNumericCode = intval($data->CountryNumericCode);

    $insertData = [];
    $insertData['CountryId']['raw'] = "(SELECT Id FROM country WHERE NumericCode = '$countryNumericCode')";
    $insertData['Street'] = $data->Street;
    $insertData['PostalCode'] = $data->PostalCode;
    $insertData['City'] = $data->City;
    $insertData['VatTaxNumber'] = $data->VatTaxNumber;
    $insertData['CustomsAccountNumber'] = $data->CustomsAccountNumber;

    $result = $database->update("vendor_address", $insertData,"Id = {$addressId}");

    $api->returnData($result);
}
