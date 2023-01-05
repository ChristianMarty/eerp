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
	
	$query  = "SELECT PartNo, Empty, GROUP_CONCAT(StockNo) AS StockNoList, GROUP_CONCAT(partStock_getQuantity(StockNo)) AS Quantity, GROUP_CONCAT(LocationId) AS LocationIdList ";
	$query .= "FROM partStock LEFT JOIN productionPartMapping ON productionPartMapping.ManufacturerPartId = partStock.ManufacturerPartId ";
	$query .= "LEFT JOIN productionPart ON productionPart.Id = productionPartMapping.ProductionPartId ";
	$query .= "WHERE PartNo IS NOT NULL AND partStock.Empty = 0 ";
	$query .= "GROUP BY productionPartMapping.ProductionPartId ";
	
	$stockResult = dbRunQuery($dbLink,$query);

	dbClose($dbLink);
	
	
	$locations = getLocations();
	
	$filename = "Stock Location Export ".date("Y-m-d H:i:s").".csv";
	
	$csvFile = tempnam("/tmp", $filename); 
	$csvHandlee = fopen($csvFile, "w");
	
	$maxParts = 20;
	$header = "Part Number;";
	
	foreach(range(1,$maxParts) as $i) 
	{
		$header.= "Stock No ".$i."; ";
		$header.= "Quantity ".$i."; ";
		$header.= "Location ".$i."; ";
	}
	
	fwrite($csvHandlee, $header.PHP_EOL);

	while($r = mysqli_fetch_assoc($stockResult)) 
	{	

		$locationId = explode(",",$r['LocationIdList'],$maxParts);
		
		if( count(array_count_values($locationId)) > 1)
		{
			$line  = $r['PartNo'].";";
			
			$stockNo = explode(",",$r['StockNoList'],$maxParts);
			$quantity = explode(",",$r['Quantity'],$maxParts);
			
			foreach(range(0,$maxParts) as $i) 
			{
				$line .= $stockNo[$i].";";
				$line .= $quantity[$i].";";
				$line .= buildLocation($locations, $locationId[$i]).";";
			}

			
			fwrite($csvHandlee, $line.PHP_EOL);
		}
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
