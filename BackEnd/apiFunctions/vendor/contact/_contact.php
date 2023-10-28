<?php
//*************************************************************************************************
// FileName : _contact.php
// FilePath : apiFunctions/vendor/alias
// Author   : Christian Marty
// Date		: 23.10.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);

namespace vendor;

class contact
{

    static function contactByVendor(int|null $vendorId = null): array
    {
        return self::query(null, $vendorId);
    }

    static function contact(int $contactId): \stdClass
    {
        return (object)self::query($contactId, null)[0];
    }

    private static function query(int|null $contactId, int|null $vendorId): array
    {
        global $database;

        $query =  <<< QUERY
            SELECT
                Id,
                VendorId,
                VendorAddressId,
                Gender,
                JobTitle,
                FirstName,
                LastName,
                Language,
                Phone,
                EMail
            FROM vendor_contact 
        QUERY;

        if($contactId !== null)
        {
            $query .= "WHERE Id = {$contactId} LIMIT 1;";
        }
        else if ($vendorId !== null) {
            $query .= "WHERE VendorId = {$vendorId};";
        }

        try {
            $data = $database->pdo()->query($query);
            return $data->fetchAll();
        }
        catch (\PDOException $e)
        {
            throw new \Exception($e->getMessage());
        }
    }

    static function createContact(int $vendorId, \stdClass $data): int
    {
        global $database;
        try {
            return $database->insert("vendor_contact", self::inputDataVerification($data));
        }
        catch (\Exception $e)
        {
            throw new \Exception($e->getMessage());
        }
    }

    static function updateContact(int $contactId, \stdClass $data):void
    {
        global $database;
        try {
            $database->update("vendor_contact", self::inputDataVerification($data), "Id = {$contactId}");
        }
        catch (\Exception $e)
        {
            throw new \Exception($e->getMessage());
        }
    }

    static private function inputDataVerification(\stdClass $data):array
    {
        $outputData = [];
        $outputData['VendorId'] = intval($data->VendorId);
        $outputData['VendorAddressId'] = intval($data->AddressId);
        $outputData['Gender'] = $data->Gender;
        $outputData['FirstName'] = $data->FirstName;
        $outputData['LastName'] = $data->LastName;
        $outputData['JobTitle'] = $data->JobTitle;
        $outputData['Language'] = $data->Language;
        $outputData['Phone'] = $data->Phone;
        $outputData['EMail'] = $data->EMail;

        return $outputData;
    }
}