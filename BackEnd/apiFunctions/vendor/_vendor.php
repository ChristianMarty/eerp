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

namespace vendor;

class vendor
{
    static function create(string $fullName, bool $isSupplier, bool $isManufacturer, bool $isContractor, bool $isCarrier, bool $isCustomer): int
    {
        global $database;

        $insertData = [];
        $insertData['FullName'] = $fullName;
        $insertData['IsSupplier']  = $isSupplier;
        $insertData['IsManufacturer']  = $isManufacturer;
        $insertData['IsContractor'] = $isContractor;
        $insertData['IsCarrier'] = $isCarrier;
        $insertData['IsCustomer'] = $isCustomer;

        try {
            return $database->insert("vendor", $insertData);
        }
        catch (\Exception $e)
        {
            throw new \Exception($e->getMessage());
        }
    }

    static function getIdByName(string $name): int|null
    {
        global $database;

        $query = "CALL `vendor_idFromName`('$name');";
        try {
            $data = $database->pdo()->query($query);
            $result = $data->fetch();
            if(is_bool($result)) return null;
            return $result->Id;
        }
        catch (\PDOException $e)
        {
            throw new \Exception($e->getMessage());
        }
    }

    static function getContact(int|null $vendorContactId): \stdClass
    {
        if($vendorContactId == null) return new \stdClass();

        global $database;
        $query = <<<STR
            SELECT
                *,
               vendor.FullName AS VendorName, 
               country.Name AS CountryName  
            FROM vendor
            LEFT JOIN vendor_address ON vendor.Id = vendor_address.VendorId 
            LEFT JOIN vendor_contact ON vendor.Id = vendor_contact.VendorId
            LEFT JOIN country ON country.Id = vendor_address.CountryId
            WHERE  vendor_contact.Id = $vendorContactId;
        STR;

        try {
            return $database->query($query)[0]??new \stdClass();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    static function getAddress(int|null $vendorAddressId): \stdClass
    {
        if($vendorAddressId == null) return new \stdClass();

        global $database;
        $query = <<<STR
            SELECT
                *,
               vendor.FullName AS VendorName, 
               country.Name AS CountryName  
            FROM vendor
            LEFT JOIN vendor_address ON  vendor.Id = vendor_address.VendorId
            LEFT JOIN country ON country.Id = vendor_address.CountryId
            WHERE  vendor_address.Id = $vendorAddressId;
        STR;

        try {
            return $database->query($query)[0]??new \stdClass();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
