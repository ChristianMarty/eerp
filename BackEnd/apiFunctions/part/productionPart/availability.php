<?php
//*************************************************************************************************
// FileName : availability.php
// FilePath : apiFunctions/part/productionPart/
// Author   : Christian Marty
// Date		: 03.12.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/../../externalApi/octopart.php";

if($api->isGet())
{
    $parameter = $api->getGetData();
    if(!isset($parameter->ProductionPartBarcode)) $api->returnParameterMissingError("ProductionPartBarcode");
    $productionPartBarcode = \Numbering\parser(\Numbering\Category::ProductionPart, $parameter->ProductionPartBarcode);
    if($productionPartBarcode == null) $api->returnParameterError('ProductionPartBarcode');

    $authorizedOnly = $parameter->AuthorizedOnly;
    $includeBrokers = $parameter->Brokers;

    $query = <<<STR
        SELECT 
            manufacturerPart_partNumber.Number,
            manufacturerPart_partNumber.OctopartId
        FROM manufacturerPart_partNumber
        LEFT JOIN productionPart_manufacturerPart_mapping ON productionPart_manufacturerPart_mapping.ManufacturerPartNumberId = manufacturerPart_partNumber.Id
        LEFT JOIN productionPart ON productionPart.Id = productionPart_manufacturerPart_mapping.ProductionPartId
        LEFT JOIN numbering ON numbering.Id = productionPart.NumberingPrefixId      
        WHERE CONCAT(numbering.Prefix,'-',productionPart.Number) = '$productionPartBarcode'
    STR;
    $results = $database->query($query);

    $availability = array();
    foreach ($results as $part)
    {
        if ($part->OctopartId == null) continue;

        $data = octopart_getPartData($part->OctopartId);

        $rowId = 0;
        foreach ($data->data->parts[0]->sellers as $seller)
        {
            if (isset($includeBrokers)) {
                if (!$includeBrokers && $seller->is_broker) continue;
            }

            if (isset($authorizedOnly)) {
                if ($authorizedOnly && $seller->is_authorized === false) continue;
            }

            $vendorName = $database->escape($seller->company->name);
            $query = <<<STR
                SELECT Id, vendor_displayName(Id) AS Name
                FROM vendor_names 
                WHERE Name = $vendorName
            STR;
            $queryResult = $database->query($query);

            $vendorId = null;
            if (count($queryResult))
            {
                $vendorName = $queryResult[0]->Name;
                $vendorId = $queryResult[0]->Id;
            }

            $line = array();
            $line['VendorName'] = $vendorName;
            $line['ManufacturerPartNumber'] = $part->Number;
            $line['VendorId'] = $vendorId;
            $line['RowId'] = $rowId;
            $rowId++;

            foreach ($seller->offers as $offer)
            {
                $line['IsBroker'] = $seller->is_broker;
                $line['IsAuthorized'] = $seller->is_authorized;
                $line['SKU'] = $offer->sku;
                $line['Stock'] = $offer->inventory_level;
                $line['MinimumOrderQuantity'] = $offer->moq;
                $line['URL'] = $offer->click_url;
                if ($offer->factory_lead_days != null) $line['LeadTime'] = intval($offer->factory_lead_days / 7, 10);
                else $line['LeadTime'] = null;
                $line['Prices'] = array();
                foreach ($offer->prices as $price) {
                    $priceLine = array();
                    $priceLine['Price'] = floatval($price->price);
                    $priceLine['Quantity'] = floatval($price->quantity);
                    $priceLine['Currency'] = $price->currency;

                    $line['Prices'][] = $priceLine;
                }
                $availability[] = $line;
            }
        }
    }
	
	$output = array();
	$output['Data'] = $availability;
	$output['Timestamp'] = date("d.m.Y - H:i", time());

	$api->returnData($output);
}
