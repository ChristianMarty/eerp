<?php
//*************************************************************************************************
// FileName : exportManufacturerParts.php
// FilePath : apiFunctions/report/
// Author   : Christian Marty
// Date		: 13.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

$title = "Manufacturer Part List";
$description = "Export Manufacturer Part List with Lifecycle Status.";

if($api->isGet())
{
    $query = <<<QUERY
        SELECT 
            vendor_displayName(vendor.Id) AS Name, 
            manufacturerPart_partNumber.Number AS ManufacturerPartNumber, 
            GROUP_CONCAT(CONCAT(numbering.Prefix,'-',productionPart.Number)) AS PartNoList
        FROM manufacturerPart_partNumber
        LEFT JOIN manufacturerPart_item ON manufacturerPart_partNumber.ItemId = manufacturerPart_item.Id
        LEFT JOIN manufacturerPart_series ON manufacturerPart_series.Id = manufacturerPart_item.SeriesId
        LEFT JOIN vendor ON vendor.Id <=> manufacturerPart_item.VendorId or vendor.Id <=> manufacturerPart_series.VendorId OR vendor.Id <=> manufacturerPart_partNumber.VendorId
        LEFT JOIN productionPart_manufacturerPart_mapping ON productionPart_manufacturerPart_mapping.ManufacturerPartNumberId = manufacturerPart_partNumber.Id
        LEFT JOIN productionPart ON  productionPart.Id = productionPart_manufacturerPart_mapping.ProductionPartId
        LEFT JOIN numbering on productionPart.NumberingPrefixId = numbering.Id
        GROUP BY manufacturerPart_partNumber.Id
    QUERY;
	$result = $database->query($query);

	$filename = "Manufacturer Part Export ".date("Y-m-d H:i:s").".csv";
	$header = "Manufacturer; ManufacturerPartNumber; PartNo 1;PartNo 2;PartNo 3;PartNo 4;PartNo 5";
	$output = $header.PHP_EOL;

	foreach($result as $line)
	{
		$r = (array)$line;
		$line  = '"'.$r['Name'].'";';
		$line .= '"'.$r['ManufacturerPartNumber'].'";';
		if($r['PartNoList'] !== null) {
			foreach (explode(",", $r['PartNoList'], 5) as $partNo) {
				$line .= '"'.$partNo.'";';
			}
		}
		$output .=$line.PHP_EOL;
	}

	$api->returnCSV($output,$filename);
}

