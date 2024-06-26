<?php
//*************************************************************************************************
// FileName : exportStock.php
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

$title = "Stock List";
$description = "Export Stock List.";

if($api->isGet())
{
	$query  = <<<QUERY
		SELECT partStock_view.*, 
				GROUP_CONCAT(CONCAT(numbering.Prefix,'-',productionPart.Number) ) AS ProductionPartNumberList 
		FROM  partStock_view
		LEFT JOIN productionPart_manufacturerPart_mapping ON productionPart_manufacturerPart_mapping.ManufacturerPartNumberId = partStock_view.ManufacturerPartNumberId
		LEFT JOIN productionPart ON  productionPart.Id = productionPart_manufacturerPart_mapping.ProductionPartId
		LEFT JOIN numbering on productionPart.NumberingPrefixId = numbering.Id
		GROUP BY StockNumber
	QUERY;
	$result = $database->query($query);

	$filename = "Stock Export ".date("Y-m-d H:i:s").".csv";
	$header = "Stock Number;Manufacturer;Manufacturer Part Number;Date;Quantity;Create Quantity;Stocktaking Date;Create Data;Location;Supplier;Supplier Part Number;PartNo 1;PartNo 2;PartNo 3;PartNo 4;PartNo 5";
	$output = $header.PHP_EOL;
    $location = new Location();
	foreach($result as $line)
	{
		$r = (array)$line;
		$line  = '"'.$r['StockNumber'].'";';
		$line .= '"'.$r['ManufacturerName'].'";';
		$line .= '"'.$r['ManufacturerPartNumber'].'";';
		$line .= '"'.$r['Date'].'";';
		$line .= '"'.$r['Quantity'].'";';
		$line .= '"'.$r['CreateQuantity'].'";';
		$line .= '"'.$r['LastCountDate'].'";';
		$line .= '"'.$r['CreateData'].'";';
		$line .= '"'.$location->name($r['LocationId']).'";';
		$line .= '"'.$r['SupplierName'].'";';
		$line .= '"'.$r['SupplierPartNumber'].'";';
		if($r['ProductionPartNumberList'] !== null) {
			foreach (explode(",", $r['ProductionPartNumberList'], 5) as $partNo) {
				$line .= '"'.$partNo .'";';
			}
		}
		$output .=$line.PHP_EOL;
	}

	$api->returnCSV($output,$filename);
}
