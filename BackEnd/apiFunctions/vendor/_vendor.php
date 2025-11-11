<?php
//*************************************************************************************************
// FileName : _vendor.php
// FilePath : apiFunctions/vendor
// Author   : Christian Marty
// Date		: 24.06.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);

namespace Vendor;

class vendor
{
    static function create(string $fullName, bool $isSupplier, bool $isManufacturer, bool $isContractor, bool $isCarrier, bool $isCustomer): int|\Error\Data
    {
        $fullName = trim($fullName);
        $existing = self::getIdByName($fullName);
        if($existing instanceof \Error\Data OR $existing!==null){
            return $existing;
        }

        global $database;
        global $user;
        $insertData = [];
        $insertData['FullName'] = $fullName;
        $insertData['IsSupplier']  = $isSupplier;
        $insertData['IsManufacturer']  = $isManufacturer;
        $insertData['IsContractor'] = $isContractor;
        $insertData['IsCarrier'] = $isCarrier;
        $insertData['IsCustomer'] = $isCustomer;
        $insertData['CreationUserId'] = $user->userId();

        return $database->insert("vendor", $insertData);
    }

    static function getIdByName(string $name): int|null|\Error\Data
    {
        global $database;
        $nameEscaped = $database->escape($name);
        $query = <<<STR
            SELECT 
                Id 
            FROM vendor_names 
            WHERE Name = $nameEscaped
        STR;
        $existing = $database->query($query);

        if($existing instanceof \Error\Data) {
            return $existing;
        }else if(count($existing)){
            return $existing[0]->Id;
        }else {
            return null;
        }
    }

    static function getContact(int|null $vendorContactId): \stdClass|\Error\Data
    {
        if($vendorContactId === null){
            return new \stdClass();
        }

        global $database;
        $query = <<<STR
            SELECT
                *,
               vendor.FullName AS VendorName, 
               country.ShortName AS CountryName  
            FROM vendor
            LEFT JOIN vendor_address ON vendor.Id = vendor_address.VendorId 
            LEFT JOIN vendor_contact ON vendor.Id = vendor_contact.VendorId
            LEFT JOIN country ON country.Id = vendor_address.CountryId
            WHERE  vendor_contact.Id = $vendorContactId;
        STR;
        $result = $database->query($query);
        if($result instanceof \Error\Data){
            return $result;
        }
        if(count($result)){
            return $result[0];
        }
        return new \stdClass();
    }

    static function getAddress(int|null $vendorAddressId): \stdClass|\Error\Data
    {
        if($vendorAddressId == null){
            return new \stdClass();
        }

        global $database;
        $query = <<<STR
            SELECT
                *,
               vendor.FullName AS VendorName, 
               country.ShortName AS CountryName  
            FROM vendor
            LEFT JOIN vendor_address ON  vendor.Id = vendor_address.VendorId
            LEFT JOIN country ON country.Id = vendor_address.CountryId
            WHERE  vendor_address.Id = $vendorAddressId;
        STR;
        $result = $database->query($query);
        if($result instanceof \Error\Data){
            return $result;
        }
        if(count($result)){
            return $result[0];
        }
        return new \stdClass();
    }

    static function getAliases(int $vendorId): array|\Error\Data
    {
        global $database;
        $query = "SELECT * FROM vendor_alias WHERE VendorId = $vendorId";
        return $database->query($query);
    }
}
