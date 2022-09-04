<?php
//*************************************************************************************************
// FileName : stock.php
// FilePath : apiFunctions/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/databaseConnector.php";
require_once __DIR__ . "/util/location.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$baseQuery = "SELECT ManufacturerName, ManufacturerPartNumber, Quantity, StockNo, Date, LocationId FROM partStock_view ";
	
	$queryParam = array();
	
	if(isset($_GET["StockNo"]))
	{
		$stockNo = dbEscapeString($dbLink, trim($_GET["StockNo"]));
		$stockNo = strtolower($stockNo);
		$stockNo = str_replace("stk-","",$stockNo);	
		array_push($queryParam, "StockNo = '".$stockNo."'");			
	}
	 
	if(isset($_GET["ManufacturerPartId"]))
	{
		$temp = dbEscapeString($dbLink, $_GET["ManufacturerPartId"]);
		array_push($queryParam, "ManufacturerPartId = '".$temp."'");		
	}
	
	if(isset($_GET["HideEmpty"]))
	{
		if(filter_var($_GET["HideEmpty"], FILTER_VALIDATE_BOOLEAN)) array_push($queryParam, "Quantity != 0");	
	}
	
	$query = dbBuildQuery($dbLink,$baseQuery,$queryParam);
	
	$result = dbRunQuery($dbLink,$query);
	dbClose($dbLink);	
	
	$locations = getLocations();

	$output = array();

	while($r = dbGetResult($result)) 
	{
		$r['Barcode'] = "STK-".$r['StockNo'];
		$date = new DateTime($r['Date']);
		$r['DateCode'] = $date->format("yW");
		$r['Location'] = buildLocation($locations, $r['LocationId']);

		array_push($output, $r);
	}
	
	sendResponse($output);
}
?>
