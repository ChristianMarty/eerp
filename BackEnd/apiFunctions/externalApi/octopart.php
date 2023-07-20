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

function octopart_getPartData($octopartId)
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

    return json_decode($result);
}

?>