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

namespace Vendor;

class Contact
{

    static function contactByVendor(int|null $vendorId = null): array|\Error\Data
    {
        return self::query(null, $vendorId);
    }

    static function query(int|null $contactId, int|null $vendorId = null): array|\Error\Data
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

        $queryParameter = [];
        if($contactId !== null) {
            $queryParameter[] = "Id = $contactId";
        } else if ($vendorId !== null) {
            $queryParameter[] = "VendorId = $vendorId";
        }

        return $database->query($query, $queryParameter);
    }

    static function createContact(int $vendorId, \stdClass $data): int|\Error\Data
    {
        global $database;
        return $database->insert("vendor_contact", self::inputDataVerification($data, true));
    }

    static function updateContact(int $contactId, \stdClass $data):null|\Error\Data
    {
        global $database;
        return $database->update("vendor_contact", self::inputDataVerification($data), "Id = $contactId");
    }

    static private function inputDataVerification(\stdClass $data, bool $isCreate = false):array
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
        if ($isCreate) {
            global $user;
            $outputData['CreationUserId'] = $user->userId();
        }

        return $outputData;
    }
}