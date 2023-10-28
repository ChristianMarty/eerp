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

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";

class vendor
{
    static function getIdByName(string $name): int|null
    {
        global $database;

        $query = "CALL `vendor_idFromName`('$name');";
        try {
            $data = $database->pdo()->query($query);
            return $data->fetch();
        }
        catch (\PDOException $e)
        {
            throw new \Exception($e->getMessage());
        }
    }

    static function getContact(int|null $vendorContactId): array
    {
        if($vendorContactId == null) return [];

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
            return $database->query($query);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    static function getAddress(int|null $vendorAddressId): array
    {
        if($vendorAddressId == null) return [];

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
            return $database->query($query);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
