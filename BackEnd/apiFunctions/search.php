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
	$search = strtolower($_GET["search"]);
	
	
	$search = strtolower($search);
	$parts = explode('-',$search);
	
	$data = array();
	
	if(count($parts) >= 2)
	{
	
		$category = "";	
		$prefix = "";
		
		switch($parts[0])
		{
			case "loc": $category = "Location";
						$prefix = "Loc";
						break;
			
			case "stk": $category = "Stock";
						$prefix = "Stk";
						break;
						
			case "inv": $category = "Inventory";
						$prefix = "Inv";
						break;
						
			case "po":  $category = "PurchaseOrder";
						$prefix = "PO";
						break;
			
			case "wo":  $category = "WorkOrder";
						$prefix = "WO";
						break;
		}

		$data["Category"] = $category;
		$data["Item"] =  $parts[1];
		$data["Code"] = $prefix."-".$parts[1];
	}
	
	
	sendResponse($data);
}
?>
