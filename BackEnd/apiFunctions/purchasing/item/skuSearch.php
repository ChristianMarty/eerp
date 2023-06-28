<?php
//*************************************************************************************************
// FileName : skuSearch.php
// FilePath : apiFunctions/purchasing/item/
// Author   : Christian Marty
// Date		: 05.01.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../databaseConnector.php";
require_once __DIR__ . "/../../../config.php";
require_once __DIR__ . "/../../externalApi/mouser/mouser.php";
require_once __DIR__ . "/../../externalApi/digikey/digikey.php";
require_once __DIR__ . "/../../externalApi/distrelec/distrelec.php";


global $mouserSupplierId;
global $digikeySupplierId;
global $distrelecSupplierId;
	
if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	if(!isset($_GET["SupplierId"]) || !isset($_GET["SKU"])) sendResponse(null, "SupplierId or SKU missing!");
	
	$supplierId = $_GET["SupplierId"];
	$sku = $_GET["SKU"];

    $data = null;
	if($supplierId == $mouserSupplierId)
	{
        $data = null;
	}
	else if($supplierId == $digikeySupplierId)
	{
		$data = null;
	}
    else if($supplierId == $distrelecSupplierId)
    {
        $data = distrelec_skuSearch($sku);
    }
	else
	{
		sendResponse(null, "Supplier not supported!");
	}
		
	sendResponse($data);
}


?>