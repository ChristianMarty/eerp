<?php
//*************************************************************************************************
// FileName : search.php
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
	if(!isset($_GET["search"])) sendResponse(null,"Search term not specified");
	
	$search = trim(strtolower($_GET["search"]));
	$parts = explode('-',$search);
	
	$data = array();

	$found = false;

	if(count($parts) >= 2)
	{
		$category = "";	
		$prefix = "";
		
		$dbLink = dbConnect();
		
		$query = "SELECT * FROM numbering ";
		$result = dbRunQuery($dbLink,$query);
		
		while($r = mysqli_fetch_assoc($result)) 
		{
			if(strtolower($r['Prefix']) == $parts[0])
			{
				$category = $r['Category'];
				$prefix = $r['Prefix'];
				$found = true;
				break;
			}
		}

		dbClose($dbLink);

		if($found)
		{
			$data["Category"] = $category;
			$data["Item"] = $parts[1];
			$data["Code"] = $prefix . "-" . $parts[1];
		}
		else
		{
			$output = search_MPN($search);
			if(!empty($output)) sendResponse($output);
			else sendResponse(null,"Number format invalid.");
		}
	}
	else
	{
		// Search MPN manufacturerPart
		$output = search_MPN($search);
		if(!empty($output)) sendResponse($output);
		else sendResponse(null,"Number format invalid.");
	}

	$output = array();
	$output[] = $data;
	sendResponse($output);
}

function search_MPN($input): array
{
	$dbLink = dbConnect();
	$input = dbEscapeString($dbLink,$input);

	$query = "SELECT Id, ManufacturerPartNumber FROM manufacturerPart WHERE ManufacturerPartNumber LIKE '$input'";
	$result = dbRunQuery($dbLink,$query);

	$output = array();

	while($r = mysqli_fetch_assoc($result))
	{
		$temp = array();
		$temp["Category"] = 'ManufacturerPartNumber';
		$temp["Item"] = $r['ManufacturerPartNumber'];
		$temp["Code"] = $r['ManufacturerPartNumber'];
		$temp["Id"] = $r['Id'];

		$output[] = $temp;

	}

	return $output;
}
?>
