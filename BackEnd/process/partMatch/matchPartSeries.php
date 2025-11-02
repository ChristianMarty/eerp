<?php
//*************************************************************************************************
// FileName : matchPartSeries.php
// FilePath : apiFunctions/process/
// Author   : Christian Marty
// Date		: 01.07.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/../../apiFunctions/util/_siFormatter.php";

$title = "Match Part Series";
$description = "Add Manufacturer Part Items to Part Series";
$parameter = null;

// Get Series
echo "Get Part Series Data \n";
$query = <<<STR
    SELECT 
        * 
    FROM manufacturerPart_series
STR;
$seriesResult = $database->query($query);

foreach ($seriesResult as $seriesData)
{
    $vendorId = $seriesData->VendorId;
    echo "Get Parts for VendorId: {$vendorId}\n";
    $query = <<<STR
        SELECT * FROM manufacturerPart_item 
        WHERE manufacturerPart_item.VendorId = $vendorId and SeriesId IS null
    STR;
    $partResult = $database->query($query);
    foreach ($seriesResult as $part)
    {
        if($seriesData->SeriesNameMatch == null) continue;

        $seriesId = $seriesData->Id;
        $partItemId = $part->Id;

        if( preg_match($seriesData->SeriesNameMatch, trim($part->Number))) {
            $query = <<<STR
                UPDATE manufacturerPart_item SET SeriesId = $seriesId WHERE manufacturerPart_item.Id = $partItemId
            STR;
            $database->execute($query);
        }
    }

}
echo "Done";
exit;
