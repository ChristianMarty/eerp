<?php
//*************************************************************************************************
// FileName : availability.php
// FilePath : apiFunctions/manufacturerPart/partNumber/
// Author   : Christian Marty
// Date		: 16.07.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../../databaseConnector.php";
require_once __DIR__ . "/../../../../config.php";
require_once __DIR__ . "/../../../externalApi/octopart.php";


if($_SERVER['REQUEST_METHOD'] == 'GET')
{
   if(!isset($_GET["ManufacturerPartNumberId"]))  sendResponse(null, "ManufacturerPartNumberId unspecified");
    $manufacturerPartNumberId =  intval($_GET["ManufacturerPartNumberId"]);

    if(isset($_GET["AuthorizedOnly"])) $authorizedOnly = filter_var($_GET["AuthorizedOnly"],FILTER_VALIDATE_BOOLEAN);
    if(isset($_GET["Brokers"])) $includeBrokers = filter_var($_GET["Brokers"],FILTER_VALIDATE_BOOLEAN);

	$dbLink = dbConnect();
    $query = <<<STR
        SELECT Id, OctopartId FROM manufacturerPart_partNumber WHERE Id = $manufacturerPartNumberId
    STR;
	$queryResult = dbRunQuery($dbLink,$query);
	dbClose($dbLink);
	
	$part = mysqli_fetch_assoc($queryResult);

	if($part == null) sendResponse(null, "Manufacturer Part Number Id not found");
	
	$data = octopart_getPartData($part['OctopartId']);
	$availability = array();
    $rowId = 0;
	foreach($data->data->parts[0]->sellers as $seller)
	{
        if(isset($includeBrokers)) {
            if(!$includeBrokers && $seller->is_broker) continue;
        }

        if(isset($authorizedOnly)) {
            if($authorizedOnly && $seller->is_authorized === false) continue;
        }

        $dbLink = dbConnect();
        $vendorName = dbEscapeString($dbLink, $seller->company->name);
        $query = <<<STR
            SELECT Id, vendor_displayName(Id) AS Name
            FROM vendor_names 
            WHERE Name = '$vendorName'
        STR;

        $queryResult = dbRunQuery($dbLink,$query);
        dbClose($dbLink);

        $vendorId = null;
        if(mysqli_num_rows($queryResult))
        {
            $vendor = mysqli_fetch_assoc($queryResult);
            $vendorName = $vendor['Name'];
            $vendorId = intval($vendor['Id']);
        }

		$line = array();
		$line['VendorName'] = $vendorName;
        $line['VendorId'] = $vendorId;
        $line['RowId'] = $rowId;
        $rowId++;

		foreach($seller->offers as $offer)
		{
            $line['IsBroker'] = $seller->is_broker;
            $line['IsAuthorized'] = $seller->is_authorized;
			$line['SKU'] = $offer->sku;
			$line['Stock'] = $offer->inventory_level;
			$line['MinimumOrderQuantity'] = $offer->moq;
			$line['URL'] = $offer->click_url;
			if($offer->factory_lead_days != null) $line['LeadTime'] = intval($offer->factory_lead_days/7,10);
			else $line['LeadTime'] = null;
			$line['Prices'] = array();
			foreach($offer->prices as $price) {
                $priceLine = array();
                $priceLine['Price'] = floatval($price->price);
                $priceLine['Quantity'] = floatval($price->quantity);
                $priceLine['Currency'] = $price->currency;

                $line['Prices'][] = $priceLine;
            }
			$availability[] = $line;
		}
	}
	
	$output = array();
	$output['Data'] = $availability;
	$output['Timestamp'] = date("d.m.Y - H:i", time()); //*/

	sendResponse($output);
}
?>