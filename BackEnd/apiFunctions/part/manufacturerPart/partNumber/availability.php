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

	$dbLink = dbConnect();

    $query = <<<STR
        SELECT Id, OctopartId FROM manufacturerPart_partNumber WHERE Id = $manufacturerPartNumberId
    STR;

	$queryResult = dbRunQuery($dbLink,$query);
	dbClose($dbLink);
	
	$part = mysqli_fetch_assoc($queryResult);

	if($part == null) sendResponse(null, "ManufacturerPartId not found");
	
	$data = octopart_getPartData($part['OctopartId']);
	$availability = array();
    $rowId = 0;
	foreach($data->data->parts[0]->sellers as $seller)
	{
		$line = array();
		$line['Name'] = $seller->company->name;
        $line['RowId'] = $rowId;
        $rowId++;

		foreach($seller->offers as $offer)
		{
			$line['SKU'] = $offer->sku;
			$line['Stock'] = $offer->inventory_level;
			$line['MinimumOrderQuantity'] = $offer->moq;
			$line['URL'] = $offer->click_url;
			if($offer->factory_lead_days != null) $line['LeadTime'] = intval($offer->factory_lead_days/7,10);
			else $line['LeadTime'] = null;
			$line['Prices'] = array();
			foreach($offer->prices as $price) {
                $priceLine = array();
                $priceLine['Price'] = $price->price;
                $priceLine['Quantity'] = $price->quantity;
                $priceLine['Currency'] = $price->currency;

                $line['Prices'][] = $priceLine;
            }
			$availability[] = $line;
		}
	}
	
	$output = array();
	$output['Data'] = $availability;
	$output['Timestamp'] = date("d.m.Y - H:i", time());

   // $output = json_decode('{"Data":[{"Name":"Conrad","SKU":"1567726","Stock":-2,"MinimumOrderQuantity":null,"URL":"https:\/\/octopart.com\/opatz8j6\/a1?t=dpcXQpqNTfC93gzoEZ0WyYQvtizXRCAL7lbAv_o25mB29aKzDeiO94X24xgeaBje_w7kLioQav7VpyZcBF8TjVhLm_9HQIIhJcjlj9MJXYxVxp9YbgcDBCWdabS58cg5GLDZtGbeRsMtGgdWgRHYJq9rHk-r0dFm4oeilRAfB25GtN-IOPdy6--Zv9jDskQjulQGk2t3DPTFZzWrGB5nSDHznHvkFjZZ8bZMflgLoY5LKHfGPhchTizgrxoxUKaTmspgumE","LeadTime":null,"Prices":[{"Price":2.79,"Quantity":1,"Currency":"EUR"},{"Price":28.99,"Quantity":7,"Currency":"EUR"}]}],"Timestamp":"16.07.2023 - 13:10"}');

	sendResponse($output);
}
?>