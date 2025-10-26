<?php
//*************************************************************************************************
// FileName : resolvePartItemDuplicatesSeriesBased.php
// FilePath : apiFunctions/process/
// Author   : Christian Marty
// Date		: 31.07.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../util/siFormatter.php";
require_once __DIR__ . "/../part/manufacturerPart/_function.php";

$title = "Resolve Part Item Duplicates by Series";
$description = "Resolve Part Item Duplicates based on Series";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
    $dbLink = dbConnect();

// Get Series
    echo "Get Duplicates \n";
    $query = <<<STR
        SELECT Id, SeriesId, VendorId, Number, GROUP_CONCAT(Id), GROUP_CONCAT(Number), COUNT(*)
        FROM manufacturerPart_item
        WHERE SeriesId IS NOT NULL
        GROUP BY SeriesId,  Number
        HAVING COUNT(*) > 1
    STR;
    $results = dbRunQuery($dbLink, $query);

    $duplicates = array();
    while($r = mysqli_fetch_assoc($results))
    {
        $duplicates[] = $r;
    }

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

        $results = dbRunQuery($dbLink, $query);
        while($r = mysqli_fetch_assoc($results))
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
        dbRunQuery($dbLink, $query);

        // Mark for delete
        $query = <<<STR
            UPDATE manufacturerPart_item SET ToDelete = b'1'
            WHERE Id IN($duplicateIds)
        STR;
        dbRunQuery($dbLink, $query);
        $query = <<<STR
            UPDATE manufacturerPart_item SET ToDelete = b'0'
            WHERE Id = $chosenId
        STR;
        dbRunQuery($dbLink, $query);


    }

    dbClose($dbLink);

    echo "Done \n";
    exit;
}
?>