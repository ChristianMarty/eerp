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
require_once __DIR__ . "/../databaseConnector.php";

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

function octopart_formatAvailabilityData(object|null $data, bool $authorizedOnly = false, bool  $includeBrokers = false): array
{
    if($data === null) return array();

    $availability = array();
    $rowId = 0;
    foreach($data->data->parts[0]->sellers as $seller)
    {
        if(!$includeBrokers && $seller->is_broker) continue;
        if($authorizedOnly && $seller->is_authorized === false) continue;


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
    return $availability;
}


function octopart_getPartData($octopartId)
{
    $octopartId = intval($octopartId);

    if($octopartId === 0) return null;

    $dbLink = dbConnect();
    $query = <<<STR
        SELECT Id, OctopartId, Timestamp, Data FROM octopart_cache WHERE OctopartId = $octopartId
    STR;
    $result = dbRunQuery($dbLink, $query);
    dbClose($dbLink);

    $output = dbGetResult($result);

    if($output  === null ||  (date("Y-m-d",strtotime($output['Timestamp'])) !== date("Y-m-d")))
    {
        $octopartData = octopart_queryApiPartData($octopartId);

        $dbLink = dbConnect();
        $escaped_data = dbEscapeString($dbLink, $octopartData);
        $query = <<<STR
            REPLACE INTO octopart_cache(OctopartId, Data) VALUES($octopartId, '$escaped_data');
        STR;
        dbRunQuery($dbLink, $query);
        dbClose($dbLink);
    }
    else
    {
        $octopartData = $output['Data'];
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