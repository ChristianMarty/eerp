<?php
//*************************************************************************************************
// FileName : cleanManufacturerPartNumbers.php
// FilePath : apiFunctions/process/
// Author   : Christian Marty
// Date		: 13.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . '/../vendor/_preprocessor/_partNumberPreprocessing.php';

$title = "Clean MPNs";
$description = "Reformat Manufacturer Part Numbers based on manufacture specific formatting rules";

$query = <<<QUERY
    SELECT 
        manufacturerPart_partNumber.Id,
        manufacturerPart_partNumber.Number, 
        vendor.PartNumberPreprocessor
    FROM manufacturerPart_partNumber
    LEFT JOIN manufacturerPart_item On manufacturerPart_item.Id = manufacturerPart_partNumber.ItemId
    LEFT JOIN manufacturerPart_series On manufacturerPart_series.Id = manufacturerPart_item.SeriesId
    LEFT JOIN vendor On vendor.Id <=> manufacturerPart_series.VendorId OR vendor.Id <=> manufacturerPart_item.VendorId OR vendor.Id <=> manufacturerPart_partNumber.VendorId
QUERY;

$result = $database->query($query);

foreach($result as $item)
{
    $processor = new PartNumberPreprocess($item->PartNumberPreprocessor);
    $partNumber = $processor->clean($item->Number);

    $updateQuery = "UPDATE manufacturerPart_partNumber SET Number='$partNumber' WHERE  Id='$item->Id'";
    $database->query($updateQuery);
}

echo "Done";