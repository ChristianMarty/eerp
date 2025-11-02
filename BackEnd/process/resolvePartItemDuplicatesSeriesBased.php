<?php
//*************************************************************************************************
// FileName : resolvePartItemDuplicatesSeriesBased.php
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

$title = "Resolve Part Item Duplicates by Series";
$description = "Resolve Part Item Duplicates based on Series";


// Get Series
echo "Get Duplicates \n";
$query = <<<STR
    SELECT Id, SeriesId, VendorId, Number, GROUP_CONCAT(Id), GROUP_CONCAT(Number), COUNT(*)
    FROM manufacturerPart_item
    WHERE SeriesId IS NOT NULL
    GROUP BY SeriesId,  Number
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
    STR;
    $items = $database->execute($query);

    foreach ($items as $r)
    {
        if($r['VerifiedByUserId'] !== null){
            $chosenId = intval($r['Id']);
            break;
        }
    }

    if($chosenId == 0) continue;
    // Update part number
    $query = <<<STR
        UPDATE manufacturerPart_partNumber SET ItemId = $chosenId
        WHERE ItemId IN($duplicateIds)
    STR;
    $database->execute($query);

    // Mark for delete
    $query = <<<STR
        UPDATE manufacturerPart_item SET ToDelete = b'1'
        WHERE Id IN($duplicateIds)
    STR;
    $database->execute($query);

    $query = <<<STR
        UPDATE manufacturerPart_item SET ToDelete = b'0'
        WHERE Id = $chosenId
    STR;
    $database->execute($query);


}

echo "Done";
exit;
