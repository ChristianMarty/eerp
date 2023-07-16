<?php
//*************************************************************************************************
// FileName : availability.php
// FilePath : apiFunctions/part/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../externalApi/octopart.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$manufacturerPartId = 0;
	
	if(isset($_GET["ManufacturerPartId"])) $manufacturerPartId =  intval($_GET["ManufacturerPartId"],10);
	else sendResponse(null, "ManufacturerPartId unspecified");

	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$query = "SELECT Id, OctopartId FROM `manufacturerPart` WHERE Id = ".$manufacturerPartId;

	$queryResult = dbRunQuery($dbLink,$query);
	dbClose($dbLink);
	
	$part = mysqli_fetch_assoc($queryResult);
	
	if($part == null) sendResponse(null, "ManufacturerPartId not found");
	
	$data = octopart_getPartData($part['OctopartId']);

	$availability = array();
	
	foreach($data->data->parts[0]->sellers as $seller)
	{
		$line = array();
		$line['Name'] = $seller->company->name;
		foreach($seller->offers as $offer)
		{
			$line['SKU'] = $offer->sku;
			$line['Stock'] = $offer->inventory_level;
			$line['MinimumOrderQuantity'] = $offer->moq;
			$line['URL'] = $offer->click_url;
			if($offer->factory_lead_days != null) $line['LeadTime'] = intval($offer->factory_lead_days/7,10);
			else $line['LeadTime'] = null;
			$line['Price'] = array();
			foreach($offer->prices as $price)
			{
				$priceLine = array();
				$priceLine['Price'] = $price->price;
				$priceLine['Quantity'] = $price->quantity;
				$priceLine['Currency'] = $price->currency;
				
				$line['Price'][] = $priceLine;
			}
			
			$availability[] = $line;
		}
	}
	
	$output = array();
	$output['Data'] = $availability;
	$output['Timestamp'] = date("d.m.Y - H:i", time());

	sendResponse($output);
}
?>