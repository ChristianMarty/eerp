<?php
//*************************************************************************************************
// FileName : stockLocationSorted.php
// FilePath : apiFunctions/report/
// Author   : Christian Marty
// Date		: 13.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/../apiFunctions/location/_location.php";

$title = "Multiple Stock Location Export";
$description = "Export stock list of Production Parts with multiple stock locations.";


$query = <<<QUERY
    SELECT 
        CONCAT(numbering.Prefix,'-',productionPart.Number) AS PartNo,
        GROUP_CONCAT(StockNumber) AS StockNoList, 
        GROUP_CONCAT(partStock_getQuantity(StockNumber)) AS Quantity, 
        GROUP_CONCAT(LocationId) AS LocationIdList
    FROM partStock 
    LEFT JOIN productionPart_manufacturerPart_mapping ON productionPart_manufacturerPart_mapping.ManufacturerPartNumberId = partStock.ManufacturerPartNumberId
    LEFT JOIN productionPart ON productionPart.Id = productionPart_manufacturerPart_mapping.ProductionPartId
    LEFT JOIN numbering on productionPart.NumberingPrefixId = numbering.Id
    WHERE productionPart.Number IS NOT NULL AND partStock.Cache_Quantity != 0
    GROUP BY productionPart_manufacturerPart_mapping.ProductionPartId
QUERY;
$result = $database->query($query);

$filename = "Stock Location Export ".date("Y-m-d H:i:s").".csv";

$maxParts = 20;
$header = "Part Number;";
foreach(range(1,$maxParts) as $i)
{
    $header.= "Stock No ".$i."; ";
    $header.= "Quantity ".$i."; ";
    $header.= "Location ".$i."; ";
}
$output = $header.PHP_EOL;

$location = new Location();

foreach($result as $line)
{
    $r = (array)$line;
    $locationId = explode(",",$r['LocationIdList']);
    $numberOfLocations = count(array_count_values($locationId));

    if( $numberOfLocations > 1)
    {
        $stockNo = explode(",",$r['StockNoList']);
        $quantity = explode(",",$r['Quantity']);

        $line  = '"'.$r['PartNo'].'";';
        foreach(range(0,$numberOfLocations-1) as $i)
        {
            $line .= '"'.$stockNo[$i].'";';
            $line .= '"'.$quantity[$i].'";';
            $line .= '"'.$location->name(intval($locationId[$i])).'";';
        }
        $output .= $line.PHP_EOL;
    }
}
$api->returnCSV($output,$filename);
