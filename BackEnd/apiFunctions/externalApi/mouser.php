<?php
//*************************************************************************************************
// FileName : mouser.php
// FilePath : apiFunctions/externalApi/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../config.php";

function getMouserPartData($mouserPartNumber)
{
	global $mouserApiPath;
	global $mouserApiKey;

	$post = '{ "SearchByPartRequest": { "mouserPartNumber": "'.$mouserPartNumber.'", "partSearchOptions": "string"}}';
	$url = $mouserApiPath.'/search/partnumber?apiKey='.$mouserApiKey;
	
    $curl = curl_init();
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

    $result = curl_exec($curl);

    curl_close($curl);

    return json_decode($result);
}

?>