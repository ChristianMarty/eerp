<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/vendor
// Author   : Christian Marty
// Date		: 08.05.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/_function.php";

	
if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	if(!isset($_GET["VendorAddressId"]))sendResponse(NULL, "No Address Id");
		
	$vendor = getVenderContact($_GET["VendorAddressId"]);

	sendResponse($vendor);
}

	
?>