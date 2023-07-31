<?php
//*************************************************************************************************
// FileName : stockLocationSorted.php
// FilePath : apiFunctions/report/
// Author   : Christian Marty
// Date		: 04.01.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../util/location.php";

global $devMode;

$title = "Multiple Stock Location Export";
$description = "Export stock list of Production Parts with multiple stock locations.";

if (!((isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true)||$devMode))
{
	echo "<p>User Session Invalid. Please Log In.<p>";
	exit;
}

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$dbLink = dbConnect();

	$query = <<<STR
		SELECT 
			CONCAT(numbering.Prefix,'-',productionPart.Number) AS PartNo,
			GROUP_CONCAT(StockNo) AS StockNoList, 
			GROUP_CONCAT(partStock_getQuantity(StockNo)) AS Quantity, 
			GROUP_CONCAT(LocationId) AS LocationIdList
		FROM partStock 
		LEFT JOIN productionPart_manufacturerPart_mapping ON productionPart_manufacturerPart_mapping.ManufacturerPartNumberId = partStock.ManufacturerPartNumberId
		LEFT JOIN productionPart ON productionPart.Id = productionPart_manufacturerPart_mapping.ProductionPartId
		LEFT JOIN numbering on productionPart.NumberingPrefixId = numbering.Id
		WHERE productionPart.Number IS NOT NULL AND partStock.Cache_Quantity != 0
		GROUP BY productionPart_manufacturerPart_mapping.ProductionPartId
	STR;

	$stockResult = dbRunQuery($dbLink,$query);
	dbClose($dbLink);

	$locations = getLocations();

	$filename = "Stock Location Export ".date("Y-m-d H:i:s").".csv";
	$csvFile = tempnam("/tmp", $filename); 
	$csvHandle = fopen($csvFile, "w");
	
	$maxParts = 20;
	$header = "Part Number;";
	foreach(range(1,$maxParts) as $i) 
	{
		$header.= "Stock No ".$i."; ";
		$header.= "Quantity ".$i."; ";
		$header.= "Location ".$i."; ";
	}
	
	fwrite($csvHandle, $header.PHP_EOL);

	while($r = mysqli_fetch_assoc($stockResult)) 
	{
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
				$line .= '"'.buildLocation($locations, $locationId[$i]).'";';
			}
			$line .= PHP_EOL;
			fwrite($csvHandle, $line);
		}
	}
	fclose($csvHandle);

	header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="'.$filename.'"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($csvFile));
    readfile($csvFile);
	exit;
}

?>
