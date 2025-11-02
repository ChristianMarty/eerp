<?php
//*************************************************************************************************
// FileName : resolvePartNumberDuplicatesPartsItemBased.php
// FilePath : apiFunctions/process/
// Author   : Christian Marty
// Date		: 31.07.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/../apiFunctions/part/manufacturerPart/_function.php";

$title = "Resolve Part Number Duplicates by Item";
$description = "Resolve Part Number Duplicates based on part Item";



// Get Series
echo "Get Duplicates \n";
$query = <<<STR
    SELECT Id, VendorId, ItemId, Number, GROUP_CONCAT(Id), GROUP_CONCAT(Number), COUNT(*)
    FROM manufacturerPart_partNumber
    WHERE ItemId IS NOT NULL
    GROUP BY ItemId,  Number
    HAVING COUNT(*) > 1
STR;
$duplicates = $database->execute($query);

foreach($duplicates as $duplicateLine)
{
    $chosenId = 0;

    echo "Get Items \n";
    $duplicateIds =  $duplicateLine['GROUP_CONCAT(Id)'];
    $query = <<<STR
        SELECT *
        FROM manufacturerPart_item
        WHERE Id IN($duplicateIds)
        ORDER BY Id
    STR;

    $results = $database->execute($query);
    foreach($results as $r)
    {
        // Just use the first Ib because it should be all the same
        $chosenId = intval($r['Id']);
        break;
    }

    if($chosenId == 0) continue;

    // Update stock parts
    $query = <<<STR
        UPDATE partStock SET ManufacturerPartNumberId = $chosenId
        WHERE ManufacturerPartNumberId IN($duplicateIds)
    STR;
    $database->execute($query);

    // Update supplier part
    $query = <<<STR
        UPDATE supplierPart SET ManufacturerPartNumberId = $chosenId
        WHERE ManufacturerPartNumberId IN($duplicateIds)
    STR;
    $database->execute($query);

    // Update production part mapping
    $query = <<<STR
        UPDATE IGNORE productionPart_manufacturerPart_mapping SET ManufacturerPartNumberId = $chosenId
        WHERE ManufacturerPartNumberId IN($duplicateIds)
    STR;
    $database->execute($query);

    // Mark for delete
    $query = <<<STR
        UPDATE manufacturerPart_partNumber SET ToDelete = b'1'
        WHERE Id IN($duplicateIds)
    STR;
    $database->execute($query);

    $query = <<<STR
        UPDATE manufacturerPart_partNumber SET ToDelete = b'0'
        WHERE Id = $chosenId
    STR;
    $database->execute($query);
}

echo "Done \n";
exit;
