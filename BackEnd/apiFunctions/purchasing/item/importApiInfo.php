<?php
//*************************************************************************************************
// FileName : importApiInfo.php
// FilePath : apiFunctions/purchasing/item/
// Author   : Christian Marty
// Date		: 05.01.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../databaseConnector.php";
require_once __DIR__ . "/../../../config.php";
require_once __DIR__ . "/../../externalApi/mouser.php";
require_once __DIR__ . "/../../externalApi/digikey.php";

global $mouserSupplierId;
global $digikeySupplierId;
	
if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	if(!isset($_GET["SupplierId"])) sendResponse(null, "SupplierId missing!");
	
	$supplierId = $_GET["SupplierId"];
	$data = array();
	
	if($supplierId == $mouserSupplierId)
	{
		$data['Authenticated'] = true;
		$data['AuthenticationUrl'] = '';
	}
	else if($supplierId == $digikeySupplierId)
	{
		if(digikey_isAuthenticated())
		{
			$data['Authenticated'] = true;
			$data['AuthenticationUrl'] = '';
		}
		else
		{
			$data['Authenticated'] = false;
			$data['AuthenticationUrl'] = digikey_auth();
		}
	}
	else
	{
		sendResponse(null, "Supplier not supported!");
	}
		
	sendResponse($data);
}
?>