<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/vendor
// Author   : Christian Marty
// Date		: 08.05.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
global $database;
global $api;

require_once __DIR__. "/_vendor.php";
require_once __DIR__. "/contact/_contact.php";

if($api->isGet(Permission::Vendor_View))
{
    $parameter = $api->getGetData();
    if(!isset($parameter->VendorId)) $api->returnData(\Error\parameterMissing("VendorId"));
    $vendorId = intval($parameter->VendorId);
    if($vendorId === 0) $api->returnData(\Error\parameter("VendorId"));

    $query = <<< STR
        SELECT
            vendor.Id as Id,
            vendor.FullName as FullName,
            vendor.CustomerNumber as CustomerNumber,
            vendor_displayName(vendor.Id) as DisplayName,
            vendor.ShortName as ShortName,
            vendor.AbbreviatedName as AbbreviatedName,
            vendor.IsSupplier as IsSupplier,
            vendor.IsManufacturer as IsManufacturer,
            vendor.IsContractor as IsContractor,
            vendor.IsCarrier AS IsCarrier,
            vendor.IsCustomer AS IsCustomer,
            vendor.ParentId as ParentId,
            vendor.Note AS Note,
            vendor_displayName(parent.Id) AS ParentName
        FROM vendor 
        LEFT JOIN vendor parent on parent.Id = vendor.ParentId
        WHERE vendor.Id = $vendorId
        LIMIT 1
    STR;
    $result = $database->query($query);
    \Error\checkErrorAndExit($result);
    \Error\checkNoResultAndExit($result, $parameter->VendorId);

    $output = $result[0];

    $output->CustomerNumber = $output->CustomerNumber??"";
    $output->ShortName = $output->ShortName??"";
    $output->AbbreviatedName = $output->AbbreviatedName??"";
    $output->Note = $output->Note??"";

	if($output->ParentId == 0){
        $output->ParentId = null;
        $output->ParentName = null;
    }

    if($output->IsSupplier == 1)$output->IsSupplier = true;
    else $output->IsSupplier = false;

    if($output->IsManufacturer == 1)$output->IsManufacturer = true;
    else $output->IsManufacturer = false;

    if($output->IsContractor == 1)$output->IsContractor = true;
    else $output->IsContractor = false;

    if($output->IsCarrier == 1)$output->IsCarrier = true;
    else $output->IsCarrier = false;

    if($output->IsCustomer == 1)$output->IsCustomer = true;
    else $output->IsCustomer = false;

	$output->Alias = \Vendor\vendor::getAliases($vendorId);
	
	// Get Children
    $query = <<< STR
        SELECT
            Id,
            vendor_displayName(vendor.Id) AS Name,
            CustomerNumber
        FROM vendor 
        WHERE ParentId = $vendorId
    STR;
	$output->Children = $database->query($query);

	// Get Addresses
    $query =  <<<STR
        SELECT 
            vendor_address.Id, 
            PhonePrefix, 
            country.Alpha2Code AS CountryCode, 
            country.ShortName AS CountryName, 
            CountryId, 
            PostalCode, 
            City, 
            Street, 
            VatTaxNumber, 
            CustomsAccountNumber 
        FROM vendor_address
        LEFT JOIN country ON country.Id = CountryId
        WHERE VendorId = $vendorId
    STR;
	$output->Address = $database->query($query);
	
	// Get Contacts
    $query = <<< QUERY
        SELECT 
            Id, 
            Gender, 
            FirstName, 
            LastName, 
            JobTitle, 
            Language, 
            Phone, 
            EMail 
        FROM vendor_contact
        WHERE VendorId = $vendorId
    QUERY;
    $output->Contact = $database->query($query);

    $api->returnData($output);
}
else if($api->isPatch(Permission::Vendor_Edit))
{
	$data = $api->getPostData();

    if(!isset($data->VendorId)) $api->returnParameterMissingError("VendorId");
    $vendorId = intval($data->VendorId);
    if($vendorId === 0) $api->returnParameterError("VendorId");

	$insertData = array();
    $insertData['FullName']  = $data->FullName!==null&&strlen($data->FullName) !== 0 ? trim($data->FullName):null;
    $insertData['ShortName']  = $data->ShortName!==null&&strlen($data->ShortName) !== 0 ? trim($data->ShortName):null;
    $insertData['AbbreviatedName']  = $data->AbbreviatedName!==null&&strlen($data->AbbreviatedName) !== 0 ? trim($data->AbbreviatedName):null;
    $insertData['CustomerNumber']  = $data->CustomerNumber!==null&&strlen($data->CustomerNumber) !== 0 ? trim($data->CustomerNumber):null;

    $insertData['IsSupplier'] = $data->IsSupplier;
    $insertData['IsManufacturer'] = $data->IsManufacturer;
    $insertData['IsContractor'] = $data->IsContractor;
    $insertData['IsCarrier'] = $data->IsCarrier;
    $insertData['IsCarrier'] = $data->IsCarrier;
    $insertData['IsCustomer'] = $data->IsCustomer;

    $insertData['ParentId'] = $data->ParentId ?? null;

    $result = $database->update("vendor", $insertData, "Id = $vendorId");
    \Error\checkErrorAndExit($result);
	
    $api->returnEmpty();
}
else if($api->isPost(Permission::Vendor_Create))
{
    $data = $api->getPostData();

    $result = \Vendor\vendor::create($data->FullName,$data?->IsSupplier??false,$data?->IsManufacturer??false,$data?->IsContractor??false,$data?->IsCarrier??false,$data?->IsCustomer??false);
    \Error\checkErrorAndExit($result);

    $output=[];
    $output['VendorId'] = $result;
    $api->returnData($output);
}
