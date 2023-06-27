<?php
//*************************************************************************************************
// FileName : datamigration.php
// FilePath : apiFunctions/process/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../util/siFormatter.php";

$title = "data migration";
$description = "data migration";
$parameter = null;

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
    $dbLink = dbConnect();

    $query = <<<STR
        UPDATE productionPartMapping SET ManufacturerPartNumberId = NULL;
        UPDATE supplierPart SET ManufacturerPartNumberId = NULL;
        UPDATE partStock SET ManufacturerPartNumberId = NULL;
    STR;

// Migrate parts
    $query = <<<STR
        SELECT * FROM manufacturerPart_old
    STR;

    $result = dbRunQuery($dbLink, $query);

    while($r = mysqli_fetch_assoc($result))
    {
        $oldId = $r['Id'];
        $vendorId = dbStringNull($r['VendorId']);
        $manufacturerPartNumber = dbStringNull($r['ManufacturerPartNumber']);
        $partClassId = dbStringNull($r['PartClassId']);
        $description  = dbStringNull($r['Description']);
        $partData  = dbStringNull($r['PartData']);
        $octopartId  = dbStringNull($r['OctopartId']);
        $packageId  = dbStringNull($r['PackageId']);
        $documentIds  = dbStringNull($r['DocumentIds']);

        $query = <<<STR
            INSERT IGNORE INTO manufacturerPart_item (VendorId, PackageId, PartClassId, Number, Attribute, Description, DocumentIds, OldId) 
            VALUES($vendorId, $packageId, $partClassId, $manufacturerPartNumber, $partData, $description, $documentIds, $oldId)
        STR;
        dbRunQuery($dbLink, $query);

        $query = <<<STR
            INSERT IGNORE INTO manufacturerPart_partNumber (ItemId, Number)
            VALUES((SELECT Id FROM manufacturerPart_item WHERE VendorId = $vendorId AND Number = $manufacturerPartNumber), $manufacturerPartNumber)
        STR;
        dbRunQuery($dbLink, $query);

        $query = <<<STR
            SELECT 
                manufacturerPart_item.Id AS manufacturerPartItemId, 
                manufacturerPart_partNumber.Id AS manufacturerPartNumberId, 
                manufacturerPart_item.OldId
            FROM manufacturerPart_item  
            LEFT JOIN manufacturerPart_partNumber ON manufacturerPart_partNumber.ItemId	= manufacturerPart_item.Id
            WHERE manufacturerPart_item.Id = (
                SELECT Id FROM manufacturerPart_item 
                WHERE VendorId = $vendorId AND Number = $manufacturerPartNumber
              ) 
              AND manufacturerPart_partNumber.Number = $manufacturerPartNumber
        STR;

        $result2 = dbRunQuery($dbLink, $query);
        $r2 = mysqli_fetch_assoc($result2);

        $manufacturerPartNumberId = $r2['manufacturerPartNumberId'];
        $oldId = $r2['OldId'];

        $query = <<<STR
            UPDATE productionPartMapping SET ManufacturerPartNumberId = $manufacturerPartNumberId
            WHERE productionPartMapping.ManufacturerPartId = $oldId;
        STR;
        dbRunQuery($dbLink, $query);

        $query = <<<STR
            UPDATE supplierPart SET ManufacturerPartNumberId = $manufacturerPartNumberId
            WHERE supplierPart.ManufacturerPartId = $oldId;
        STR;
        dbRunQuery($dbLink, $query);

        $query = <<<STR
            UPDATE partStock SET ManufacturerPartNumberId = $manufacturerPartNumberId
            WHERE partStock.ManufacturerPartId = $oldId;
        STR;
        dbRunQuery($dbLink, $query);
    }
    dbClose($dbLink);
    exit;
}
?>