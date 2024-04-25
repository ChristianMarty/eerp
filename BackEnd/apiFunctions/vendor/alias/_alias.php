<?php
//*************************************************************************************************
// FileName : _alias.php
// FilePath : apiFunctions/vendor/alias
// Author   : Christian Marty
// Date		: 23.10.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);

namespace vendor;

class alias
{
    static function alias(int $aliasId): \stdClass
    {
        global $database;

        $query = "SELECT * FROM vendor_alias WHERE Id = {$aliasId} LIMIT 1";
        try {
            $data = $database->pdo()->query($query);
            $result = $data->fetch();
        }
        catch (\PDOException $e)
        {
            throw new \Exception($e->getMessage());
        }

        return $result;
    }

    static function aliasesForVendor(int $vendorId): array
    {
        global $database;

        $query = "SELECT * FROM vendor_alias WHERE VendorId = {$vendorId} ";
        try {
            $data = $database->pdo()->query($query);
            $result = $data->fetchAll();
        }
        catch (\PDOException $e)
        {
            throw new \Exception($e->getMessage());
        }

        return $result;
    }

    static function createAlias(int $vendorId, string $name, string|null $note = null): int
    {
        global $database;
        global $user;

        $insertData = [];
        $insertData['VendorId']= $vendorId;
        $insertData['Name']  = $name;
        $insertData['Note']  = $note;
        $insertData['CreationUserId'] = $user->userId();

        try {
            return $database->insert("vendor_alias", $insertData);
        }
        catch (\Exception $e)
        {
            throw new \Exception($e->getMessage());
        }
    }

    static function updateAlias(int $aliasId, string $name = "", string|null $note = null):void
    {
        global $database;

        $updateData = [];
        $updateData['Name']  = $name;
        $updateData['Note']  = $note;

        try {
            $database->update("vendor_alias", $updateData, "Id = {$aliasId}");
        }
        catch (\Exception $e)
        {
            throw new \Exception($e->getMessage());
        }
    }
}