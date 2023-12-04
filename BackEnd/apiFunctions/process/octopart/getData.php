<?php
//*************************************************************************************************
// FileName : getData.php
// FilePath : apiFunctions/process/octopart
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

$title = "Octopart Get Data";
$description = "Queries Octopart data based on Octopart Part Id.";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$dbLink = dbConnect();

    $query = <<<STR
        SELECT 
            Id, 
            OctopartId 
        FROM manufacturerPart
        WHERE OctopartPartData IS NULL AND OctopartId IS NOT NULL ORDER BY Id DESC LIMIT 250
    STR;

	$queryResult = dbRunQuery($dbLink,$query);
	dbClose($dbLink);
	
	$i = 0;
	while($part = mysqli_fetch_assoc($queryResult))
	{
		$octopartPartData = getOctopartPartData($part['OctopartId']);

		$partData = $octopartPartData->data->parts[0];

		$dbLink = dbConnect();
		if($dbLink == null) return null;
		
		$query = "UPDATE manufacturerPart SET OctopartPartData = '".dbEscapeString($dbLink,json_encode($partData))."' WHERE Id = ".$part['Id'];
		
		dbRunQuery($dbLink,$query);
		dbClose($dbLink);
		
		$i++;
		if($i>= 250) break;
	}

	sendResponse($partData);
	
}

function getOctopartPartData($OctopartId)
{
	global $octopartApiToken;
	global $octopartApiPath;
	
	$OCTOPART_API = $octopartApiPath.'endpoint?token='.$octopartApiToken;

	$post = '{"query":"{parts(ids: [\"'.$OctopartId.'\"]){id slug mpn manufacturer {name}category{id name}specs{attribute{id name}display_value}}}"}';
	
    $curl = curl_init();
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($curl, CURLOPT_URL, $OCTOPART_API);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

    $result = curl_exec($curl);

    curl_close($curl);

    return json_decode($result);
}

?>