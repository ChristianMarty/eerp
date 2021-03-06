<?php
//*************************************************************************************************
// FileName : exportStock.php
// FilePath : apiFunctions/report/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../util/location.php";

global $devMode;

$titel = "Stock List";
$description = "Export Stock List.";

if (!((isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true)||$devMode))
{
	echo "<p>User Session Invalid. Please Log In.<p>";
	exit;
}


if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$query  = "SELECT partStock_view.*, GROUP_CONCAT(PartNo) AS PartNoList FROM  partStock_view ";
	$query .= "LEFT JOIN productionPartMapping ON productionPartMapping.ManufacturerPartId = partStock_view.ManufacturerPartId ";
	$query .= "LEFT JOIN productionPart ON  productionPart.Id = productionPartMapping.ProductionPartId ";
	$query .= "GROUP BY StockNo";
	
	$stockResult = dbRunQuery($dbLink,$query);

	dbClose($dbLink);
	
	
	$locations = getLocations();
	
	$filename = "Stock Export ".date("Y-m-d H:i:s").".csv";
	
	$csvFile = tempnam("/tmp", $filename); 
	$csvHandlee = fopen($csvFile, "w");
	

	
	$header = "Order Reference;Stock No;Manufacturer;Manufacturer Part Number;Date;Quantity;Create Quantity;Stocktaking Date;Create Data;Location;Supplier;Supplier Part Number;PartNo 1;PartNo 2;PartNo 3;PartNo 4;PartNo 5";
	fwrite($csvHandlee, $header.PHP_EOL);
	
	while($r = mysqli_fetch_assoc($stockResult)) 
	{	
		$line  = $r['OrderReference'].";";
		$line .= $r['StockNo'].";";
		$line .= $r['ManufacturerName'].";";
		$line .= $r['ManufacturerPartNumber'].";";
		$line .= $r['Date'].";";
		$line .= $r['Quantity'].";";
		$line .= $r['CreateQuantity'].";";
		$line .= $r['LastCountDate'].";";
		$line .= $r['CreateData'].";";
		$line .= buildLocation($locations, $r['LocationId']).";";
		$line .= $r['SupplierName'].";";
		$line .= $r['SupplierPartNumber'].";";
		foreach(explode(",",$r['PartNoList'],5) as $partNo)
		{
			$line .= $partNo.";";
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
