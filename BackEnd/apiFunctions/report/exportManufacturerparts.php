<?php
//*************************************************************************************************
// FileName : exportManufacturerparts.php
// FilePath : apiFunctions/report/
// Author   : Christian Marty
// Date		: 16.11.2021
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../util/location.php";

global $devMode;

$title = "Manufacturer Part List";
$description = "Export Manufacturer Part List with Lifecycle Status.";

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
		    vendor_displayName(vendor.Id) AS Name, 
		    manufacturerPart_partNumber.Number AS ManufacturerPartNumber, 
		    GROUP_CONCAT(CONCAT(numbering.Prefix,'-',productionPart.Number)) AS PartNoList
		FROM manufacturerPart_partNumber
		LEFT JOIN manufacturerPart_item ON manufacturerPart_partNumber.ItemId = manufacturerPart_item.Id
		LEFT JOIN manufacturerPart_series ON manufacturerPart_series.Id = manufacturerPart_item.SeriesId
		LEFT JOIN vendor ON vendor.Id = manufacturerPart_item.VendorId or vendor.Id = manufacturerPart_series.VendorId OR manufacturerPart_partNumber.VendorId
		LEFT JOIN productionPartMapping ON productionPartMapping.ManufacturerPartNumberId = manufacturerPart_partNumber.Id
		LEFT JOIN productionPart ON  productionPart.Id = productionPartMapping.ProductionPartId
		LEFT JOIN numbering on productionPart.NumberingPrefixId = numbering.Id
		GROUP BY manufacturerPart_partNumber.Id
	STR;

	$stockResult = dbRunQuery($dbLink,$query);

	dbClose($dbLink);

	$locations = getLocations();
	
	$filename = "Manufacturer Part Export ".date("Y-m-d H:i:s").".csv";
	
	$csvFile = tempnam("/tmp", $filename); 
	$csvHandlee = fopen($csvFile, "w");
	
	
	$header = "Manufacturer; ManufacturerPartNumber; PartNo 1;PartNo 2;PartNo 3;PartNo 4;PartNo 5";
	fwrite($csvHandlee, $header.PHP_EOL);
	
	while($r = mysqli_fetch_assoc($stockResult)) 
	{	
		$line  = '"'.$r['Name'].'";';
		$line .= '"'.$r['ManufacturerPartNumber'].'";';
		if($r['PartNoList'] !== null) {
			foreach (explode(",", $r['PartNoList'], 5) as $partNo) {
				$line .= '"'.$partNo.'";';
			}
		}
		
		fwrite($csvHandlee, $line.PHP_EOL);
	}
	
	header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="'.$filename.'"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($csvFile));
    readfile($csvFile);
	fclose($csvHandlee);
	exit;
}

?>
