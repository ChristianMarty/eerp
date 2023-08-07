<?php
//*************************************************************************************************
// FileName : matchPartSeries.php
// FilePath : apiFunctions/process/
// Author   : Christian Marty
// Date		: 01.07.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../databaseConnector.php";
require_once __DIR__ . "/../../../config.php";
require_once __DIR__ . "/../../util/siFormatter.php";

$title = "Match Part Series";
$description = "Add Manufacturer Part Items to Part Series";
$parameter = null;

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
    $dbLink = dbConnect();

// Get Series
    echo "Get Part Series Data \n";
    $query = <<<STR
        SELECT * FROM manufacturerPart_series
    STR;
    $seriesResult = dbRunQuery($dbLink, $query);

    while($seriesData = mysqli_fetch_assoc($seriesResult))
    {
        $vendorId = $seriesData['VendorId'];
        echo "Get Parts for VendorId: {$vendorId}\n";
        $query = <<<STR
            SELECT * FROM manufacturerPart_item 
            WHERE manufacturerPart_item.VendorId = $vendorId and SeriesId IS null
        STR;
        $partResult = dbRunQuery($dbLink, $query);
        while($part = mysqli_fetch_assoc($partResult))
        {
            if($seriesData['SeriesNameMatch'] == null) continue;

            $seriesId = $seriesData['Id'];
            $partItemId = $part['Id'];

            if( preg_match($seriesData['SeriesNameMatch'], trim($part['Number']))) {
                $query = <<<STR
                    UPDATE manufacturerPart_item SET SeriesId = $seriesId WHERE manufacturerPart_item.Id = $partItemId
                STR;

                dbRunQuery($dbLink, $query);
            }
        }

    }
    dbClose($dbLink);
    exit;
}
?>