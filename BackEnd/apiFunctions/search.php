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
				break;
			}
		}

		dbClose($dbLink);
		
		$data["Category"] = $category;
		$data["Item"] =  $parts[1];
		$data["Code"] = $prefix."-".$parts[1];
	}
	else
	{
		sendResponse(null,"Number format invalide.");
	}
	
	
	sendResponse($data);
}
?>
