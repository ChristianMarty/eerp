<?php
//*************************************************************************************************
// FileName : octopart.php
// FilePath : apiFunctions/externalApi/
// Author   : Christian Marty
// Date		: 16.07.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../config.php";

function octopart_getPartId($manufacturerName, $manufacturerPartNumber )
{
    global $octopartApiPath;
    global $octopartApiToken;
    $uri = $octopartApiPath.'endpoint?token='.$octopartApiToken;

    $post = '{"query":"';
    $post .= '{multi_match(queries: [{ manufacturer: \"'.$manufacturerName.'\", mpn:  \"'.$manufacturerPartNumber.'\"  }])';
    $post .= '{ hits reference parts {id}error}}' ;
    $post .= '","variables":{},"operationName":null}';

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_URL, $uri);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

    $result = curl_exec($curl);
    curl_close($curl);

    return json_decode($result);
}
function octopart_getVendorList(): array
{
    global $database;

    // Get vendor list
    $query = <<<STR
        SELECT Id, Name, vendor_displayName(Id) AS DisplayName
        FROM vendor_names 
    STR;
    $result = $database->query($query);

    $vendorList = array();
    foreach ($result as $r)
    {
        $vendorList[$r->Name] = $r;
    }
    return $vendorList;
}

function octopart_formatAvailabilityData(object|null $data, array $vendorList, bool $authorizedOnly = false, bool  $includeBrokers = false, $includeNoStock = true, $knownSuppliers = false): array
{
    if($data === null) return array();

    $availability = array();
    $rowId = 0;
    foreach($data->data->parts[0]->sellers as $seller)
    {
        if(!$includeBrokers && $seller->is_broker) continue;
        if($authorizedOnly && $seller->is_authorized === false) continue;

        $vendorName = $seller->company->name;

        $line = array();

        if(array_key_exists($vendorName, $vendorList))
        {
            $line['VendorName'] = $vendorList[$vendorName]['DisplayName'];
            $line['VendorId'] = $vendorList[$vendorName]['Id'];
        }
        else
        {
            if($knownSuppliers) continue;

            $line['VendorName'] = $vendorName;
            $line['VendorId'] = null;
        }

        $line['RowId'] = $rowId;
        $rowId++;

        foreach($seller->offers as $offer)
        {
            if(!$includeNoStock && $offer->inventory_level == 0) continue;

            $line['Stock'] = $offer->inventory_level;
            $line['IsBroker'] = $seller->is_broker;
            $line['IsAuthorized'] = $seller->is_authorized;
            $line['SKU'] = $offer->sku;
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
    return $availability;
}


function octopart_getPartData($octopartId)
{
    $octopartId = intval($octopartId);

    if($octopartId === 0) return null;

    global $database;

    
    $query = <<<STR
        SELECT Id, OctopartId, Timestamp, Data FROM octopart_cache WHERE OctopartId = $octopartId
    STR;
    $output = $database->query($query)[0];

    if($output  === null ||  (date("Y-m-d",strtotime($output->Timestamp)) !== date("Y-m-d")))
    {
        $escaped_data = $database->escape(octopart_queryApiPartData($octopartId));
        $query = <<<STR
            REPLACE INTO octopart_cache(OctopartId, Data) VALUES($octopartId, $escaped_data);
        STR;
        $database->query($query);

    }
    else
    {
        $octopartData = $output->Data;
    }

    return json_decode($octopartData);
}

function octopart_queryApiPartData($octopartId)
{
    global $octopartApiPath;
    global $octopartApiToken;
    $uri = $octopartApiPath.'endpoint?token='.$octopartApiToken;

    $post = '{"query":"{parts(ids: [\"'.$octopartId.'\"], currency: \"CHF\"){id sellers{is_broker is_authorized company{name} offers{sku, inventory_level, moq, click_url, factory_lead_days prices{price,quantity,currency,converted_price,converted_currency,conversion_rate}}}}}"}';

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_URL, $uri);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

    $result = curl_exec($curl);
    curl_close($curl);

    return $result;
}

?>